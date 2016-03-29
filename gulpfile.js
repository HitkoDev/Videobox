var gulp = require('gulp');
var compass = require('gulp-compass');
var typescript = require('gulp-typescript');
var uglify = require('gulp-uglify');
var rename = require("gulp-rename");
var cssBase64 = require('gulp-css-base64');
var cssnano = require('gulp-cssnano');
var concat = require('gulp-concat');
var fontcustom = require('gulp-fontcustom');
var replace = require('gulp-replace');
var imagemin = require('gulp-imagemin');
var changed = require("gulp-changed");
var merge = require("merge2");
var typedoc = require("gulp-typedoc");
var addsrc = require('gulp-add-src');
var svgmin = require('gulp-svgmin');

gulp.task('default', [
    'compress'
], function() {

});

gulp.task('style', function() {
    return gulp.src('./src/sass/*.scss')
        .pipe(compass({
            css: 'src/css',
            sass: 'src/sass'
        }))
        .pipe(cssBase64({
            baseDir: "./dist"
        }))
        .pipe(gulp.dest('./dist'));
});

gulp.task('scripts', function() {
    var tsResult = gulp.src('./src/js/**.ts')
        .pipe(typescript({
            declaration: true,
            noExternalResolve: true,
            target: 'ES5',
            sourcemap: true
        }));

    return merge([
        tsResult.dts
            .pipe(addsrc('./src/js/interfaces.d.ts'))
            .pipe(concat('videobox.ts'))
            .pipe(typescript({
                declaration: true,
                target: 'ES5'
            })).dts
            .pipe(gulp.dest('./dist/definitions')),

        tsResult.js
            .pipe(concat('videobox.js'))
            .pipe(gulp.dest('./dist'))
    ]);
});

gulp.task('documentation', function() {
    return gulp.src([
        './src/js/videobox.ts',
        './src/js/vbinline.ts',
        './src/js/vbslider.ts',
        './src/js/interfaces.d.ts'
    ])
        .pipe(typedoc({
            module: "commonjs",
            target: "es5",
            includeDeclarations: true,
            out: "./docs",
            mode: "file",
            excludeExternals: true,
            theme: 'minimal'
        }));
});

gulp.task('images', function() {
    return gulp.src('./src/images/**')
        .pipe(changed('./dist/images'))
        .pipe(imagemin({
            progressive: true,
            optimizationLevel: 7,
            multipass: true,
            interlaced: true
        }))
        .pipe(gulp.dest('./dist/images'));
});

gulp.task('compress', [
    'scripts',
    'style',
    'images'
], function() {
    return merge([
        gulp.src(['./dist/videobox.js'])
            .pipe(uglify({
                preserveComments: 'license'
            }))
            .pipe(rename({
                suffix: '.min'
            }))
            .pipe(gulp.dest('./dist')),

        gulp.src(['./dist/videobox.css'])
            .pipe(cssnano())
            .pipe(rename({
                suffix: '.min'
            }))
            .pipe(gulp.dest('./dist'))
    ]);
});

gulp.task('icons', function() {
    return merge([
        gulp.src(['./src/icons/*.svg'])
            .pipe(fontcustom({
                font_name: 'Videobox',
                'css-selector': '.vb-icon-{{glyph}}',
                templates: ['_icons.scss'],
                preprocessor_path: '/font'
            }))
            .pipe(gulp.dest('./dist/font')),

        gulp.src('./dist/font/*.scss')
            .pipe(replace('-{{glyph}}', ', [class^="vb-icon-"], [class*=" vb-icon-"]'))
            .pipe(concat('_icons.scss'))
            .pipe(gulp.dest('./src/sass'))
    ]);
});

gulp.task('compress-font', function() {
    return gulp.src('./dist/font/*.svg')
        .pipe(svgmin({
            plugins: [{
                removeUselessDefs: false
            }]
        }))
        .pipe(gulp.dest('./dist/font'));
});