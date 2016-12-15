var gulp = require('gulp')
var rename = require("gulp-rename")
var cssBase64 = require('gulp-css-base64')
var cleanCSS = require('gulp-clean-css')
var concat = require('gulp-concat')
var replace = require('gulp-replace')
var imagemin = require('gulp-imagemin')
var changed = require("gulp-changed")
var merge = require("merge2")
var addsrc = require('gulp-add-src')
var insert = require('gulp-insert')
var sourcemaps = require('gulp-sourcemaps')
var bourbon = require('bourbon')
var sass = require('gulp-sass')
var uglify = require('gulp-uglify')
var typedoc = require("gulp-typedoc")
var shell = require('gulp-shell')
var path = require('path')
var through = require('through2')
var sorcery = require('sorcery')
var closureCompiler = require('google-closure-compiler').gulp()

var sourcemapsOptions = {
    mapFile: (mapFilePath) => {
        return mapFilePath.replace('.min.js.map', '.js.map').replace('.bundle.js.map', '.bundle.map')
    }
}

var comment = `/*!	
 *	@author		HitkoDev http://hitko.eu/videobox
 *	@copyright	Copyright (C) 2016 HitkoDev All Rights Reserved.
 *	@license	http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program. If not, see <http://www.gnu.org/licenses/>
 */`

gulp.task('default', ['licence'], () => { })

gulp.task('licence', [
    'compress',
    'documentation'
], () => {
    return gulp.src(['./dist/videobox.*.js', './dist/videobox.*.css'])
        .pipe(insert.prepend(comment + "\n"))
        .pipe(gulp.dest('./dist'))
})

gulp.task('licence:nodep', [
    'wrap'
], () => {
    return gulp.src(['./dist/videobox.*.js', './dist/videobox.*.css'])
        .pipe(insert.prepend(comment + "\n"))
        .pipe(gulp.dest('./dist'))
})

gulp.task('wrap', [
    'compress:nodep',
    'documentation'
], () => {

})

gulp.task('documentation', () => {
    return gulp.src(['./build/**/*.d.ts', './typings/index.d.ts'])
        .pipe(typedoc({
            module: "es2015",
            target: "es6",
            includeDeclarations: true,
            out: "./docs",
            mode: "file",
            excludeExternals: true,
            theme: 'minimal',
            excludePrivate: true
        }))
})

gulp.task('images', () => {
    return gulp.src('./src/images/**')
        .pipe(changed('./build/images'))
        .pipe(imagemin({
            progressive: true,
            optimizationLevel: 7,
            multipass: true,
            interlaced: true
        }))
        .pipe(gulp.dest('./build/images'))
})

gulp.task('scripts:rollup', shell.task([
    path.join('node_modules', '.bin', 'rollup') + ' -c'
]))

var bcd = function (options) {

    var base = ''
    if ('base' in options && options['base'])
        base = options['base'].trim()

    if (!base)
        base = '.'

    delete options['base']

    var map = {}

    for (var src in options)
        if (typeof options[src] == 'string')
            map[options[src]] = path.join(base, src, options[src])
        else
            for (var i = 0; i < options[src].length; i++)
                map[options[src][i]] = path.join(base, src, options[src][i])

    function transform(file, encoding, callback) {
        if (file.sourceMap)
            for (var i = 0; i < file.sourceMap.sources.length; i++)
                if (file.sourceMap.sources[i] in map)
                    file.sourceMap.sources[i] = map[file.sourceMap.sources[i]]

        this.push(file);
        callback();
    }

    return through.obj(transform);
}

var efg = function () {

    function transform(file, encoding, callback) {
        if (file.sourceMap) {
            var rel = path.relative('.', file.path)
            var chain = sorcery.loadSync(rel)
            file.sourceMap = chain.apply()
        }

        this.push(file)
        callback()
    }

    return through.obj(transform)
}

gulp.task('scripts', [
    'scripts:rollup'
], () => {
    return merge([
        gulp.src('./build/videobox.js')
            .pipe(sourcemaps.init({ loadMaps: true, largeFile: true }))
            .pipe(closureCompiler({
                js_output_file: 'videobox.min.js',
                language_out: 'ECMASCRIPT5',
                warning_level: 'QUIET'
            }))
            .pipe(bcd({
                '../': 'build/videobox.js'
            }))
            .pipe(sourcemaps.write('.', sourcemapsOptions))
            .pipe(gulp.dest('./dist')),

        gulp.src(['./node_modules/web-animations-js/web-animations.min.js', './build/videobox.js'])
            .pipe(sourcemaps.init({ loadMaps: true, largeFile: true }))
            .pipe(closureCompiler({
                js_output_file: 'videobox.bundle.js',
                language_out: 'ECMASCRIPT5',
                warning_level: 'QUIET'
            }))
            .pipe(bcd({
                '../': [
                    'node_modules/web-animations-js/web-animations.min.js',
                    'build/videobox.js'
                ]
            }))
            .pipe(sourcemaps.write('.', sourcemapsOptions))
            .pipe(gulp.dest('./dist'))

    ])
})

gulp.task('sass', [
    'sass:convert'
], () => {
    return gulp.src('./src/sass/*.scss')
        .pipe(sourcemaps.init())
        .pipe(sass({
            includePaths: [
                bourbon.includePaths
            ],
            outputStyle: 'expanded'
        }))
        .pipe(replace(/(^|\})\s*[^\{\}]*\{\s*\}\s*/igm, ''))
        .pipe(replace(/(^|\})\s*[^\{\}]*\{\s*\}\s*/igm, ''))
        .pipe(replace(/(^|\})\s*[^\{\}]*\{\s*\}\s*/igm, ''))
        .pipe(cssBase64({
            baseDir: "./build"
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('./build'))
})

gulp.task('sass:convert', [
    'overrides'
], () => {
    return gulp.src(['./src/sass/**/*.scss', '!./src/sass/_icons.scss'], { read: false })
        .pipe(changed('.', {
            hasChanged: (stream, cb, sourceFile, targetPath) => changed.compareLastModifiedTime(stream, cb, sourceFile, path.resolve(process.cwd(), './build/videobox.css'))
        }))
        .pipe(shell([
            'sass-convert -i --indent 4 <%= file.path %>'
        ]))

})

gulp.task('overrides', () => {
    return gulp.src(['./src/sass/**/!(videobox)*.scss', '!./src/sass/overrides.scss'])
        .pipe(concat('overrides.scss'))
        .pipe(replace(/\/\/[^]*?$/igm, ''))
        .pipe(replace(/\@[^\;\{]*?\;/ig, ''))
        .pipe(replace(/\/\*[^]*?\*\//ig, ''))
        .pipe(replace(/[\w\-]+\s*\:((?!(\$primary\-color|\$primary\-light|\:))[^])*?\;/igm, ''))
        .pipe(addsrc('./src/sass/videobox.scss'))
        .pipe(replace(/\/\*[^]*?\*\//ig, ''))
        .pipe(concat('overrides.scss'))
        .pipe(replace(/\@import [^\;\{]*?\;/ig, ''))
        .pipe(replace(/(\$\s*)*\$\s+/igm, ''))
        .pipe(insert.prepend(".vb-overrides-wrap {\n"))
        .pipe(insert.append("\n}"))
        .pipe(gulp.dest('./src/sass'))
})

gulp.task('compress', [
    'sass',
    'images',
    'scripts'
], () => {
    return compress()
})

gulp.task('compress:nodep', () => {
    return compress()
})

function compress() {
    return merge([
        gulp.src('./build/*.css')
            .pipe(sourcemaps.init({ loadMaps: true }))
            .pipe(cleanCSS())
            .pipe(rename({
                suffix: '.min'
            }))
            .pipe(sourcemaps.write('.', {
                mapFile: (mapFilePath) => {
                    return mapFilePath.replace('.min.css.map', '.css.map')
                }
            }))
            .pipe(gulp.dest('./dist')),

        gulp.src('./build/images/**/*.png')
            .pipe(gulp.dest('./dist')),

        gulp.src('./dist/*.js')
            .pipe(sourcemaps.init({ loadMaps: true }))
            .pipe(efg())
            .pipe(uglify())
            .pipe(sourcemaps.write('.', sourcemapsOptions))
            .pipe(gulp.dest('./dist'))
    ])
}

gulp.task('watch', () => {
    gulp.watch('./build/**/*.d.ts', ['documentation'])
    gulp.watch('./build/**/*.js', ['scripts'])
    gulp.watch('./src/sass/**/*.scss', ['sass'])
})