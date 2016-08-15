var gulp = require('gulp')
var ts = require('gulp-typescript')
var rename = require("gulp-rename")
var cssBase64 = require('gulp-css-base64')
var cleanCSS = require('gulp-clean-css')
var concat = require('gulp-concat')
var fontcustom = require('gulp-fontcustom')
var replace = require('gulp-replace')
var imagemin = require('gulp-imagemin')
var changed = require("gulp-changed")
var merge = require("merge2")
var typedoc = require("gulp-typedoc")
var addsrc = require('gulp-add-src')
var svgmin = require('gulp-svgmin')
var insert = require('gulp-insert')
var sourcemaps = require('gulp-sourcemaps')
var bourbon = require('bourbon')
var sass = require('gulp-sass')
var shell = require('gulp-shell')

var projectFile = 'tsconfig.json'
var tsProject = ts.createProject(projectFile)
var projectDefinitions = ts.createProject(projectFile, {
    declaration: true,
    out: './videobox.js'
})

gulp.task('default', [
    'compress',
    'bundle',
    'documentation'
], () => {
    return gulp.src(['./build/**/*.min.css', './build/**/*.css.map'])
        .pipe(gulp.dest('.'))
})

gulp.task('bundle', [
    'scripts'
], shell.task([
    'jspm bundle-sfx build/index.js videobox.min.js --minify'
]))

gulp.task('scripts', () => {
    return merge([
        tsProject.src()
            .pipe(sourcemaps.init())
            .pipe(ts(tsProject)).js
            .pipe(sourcemaps.write('.'))
            .pipe(gulp.dest('./build')),

        projectDefinitions.src()
            .pipe(ts(projectDefinitions)).dts
            .pipe(gulp.dest('.'))
    ])
})

gulp.task('documentation', [
    'scripts'
], () => {
    return gulp.src([
        './videobox.d.ts'
    ])
        .pipe(typedoc({
            module: "commonjs",
            target: "es5",
            includeDeclarations: true,
            out: "./docs",
            mode: "file",
            excludeExternals: true,
            theme: 'minimal'
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

gulp.task('sass', [
    'overrides'
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
    'images'
], () => {
    return gulp.src(['./build/**/*.css', '!./build/**/*.min.css'])
        .pipe(cleanCSS())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./build'))
})

gulp.task('icons:font', () => {
    return merge([
        gulp.src(['./src/icons/*.svg'])
            .pipe(fontcustom({
                font_name: 'Videobox',
                'css-selector': '.vb-icon-{{glyph}}',
                templates: ['_icons.scss'],
                preprocessor_path: '/font'
            }))
            .pipe(gulp.dest('./build/font')),

        gulp.src('./build/font/*.scss')
            .pipe(replace('-{{glyph}}', ', [class^="vb-icon-"], [class*=" vb-icon-"]'))
            .pipe(concat('_icons.scss'))
            .pipe(gulp.dest('./src/sass'))
    ])
})

gulp.task('icons', [
    'icons:font'
], () => {
    return gulp.src('./build/font/*.svg')
        .pipe(svgmin({
            plugins: [{
                removeUselessDefs: false
            }]
        }))
        .pipe(gulp.dest('./build/font'))
})