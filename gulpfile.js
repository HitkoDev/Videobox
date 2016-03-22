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

gulp.task('default', [
    'compress'
], function () {
    gulp.src([
        'node_modules/video.js/dist/video.js',
        'node_modules/video.js/dist/video.min.js',
        'node_modules/video.js/dist/video-js.css',
        'node_modules/video.js/dist/video-js.min.css',
        'node_modules/video.js/dist/video-js.swf',
    ]).pipe(gulp.dest('./dist/video-js'));
});

gulp.task('style', function () {
    gulp.src('./src/sass/*.scss')
        .pipe(compass({
            css: 'src/css',
            sass: 'src/sass'
        }))
        .pipe(cssBase64({
            baseDir: "./dist"
        }))
        .pipe(gulp.dest('./dist'));
});

gulp.task('style:watch', function () {
    gulp.watch('./src/sass/**/*.scss', ['style']);
});

gulp.task('scripts', function () {
    var tsResult = gulp.src('./src/js/**.ts')
        .pipe(typescript({
            declaration: true,
            noExternalResolve: true,
            target: 'ES5',
            sourcemap: true
        }));

    tsResult.dts.pipe(gulp.dest('./dist/definitions'));

    tsResult.js
        .pipe(concat('videobox.js'))
        .pipe(gulp.dest('./dist'))
});

gulp.task('images', function () {
    gulp.src('./src/images/**')
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
], function () {
    gulp.src(['./dist/videobox.js'])
        .pipe(uglify({
            preserveComments: 'license'
        }))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./dist'));

    gulp.src(['./dist/videobox.css'])
        .pipe(cssnano())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./dist'));
});

gulp.task('icons', function () {
    gulp.src(['./src/icons/*.svg'])
        .pipe(fontcustom({
            font_name: 'Videobox',
            'css-selector': '.vb-icon-{{glyph}}',
            templates: ['_icons.scss'],
            preprocessor_path: '/font'
        }))
        .pipe(gulp.dest('./dist/font'));

    gulp.src('./dist/font/*.scss')
        .pipe(replace('-{{glyph}}', ', [class^="vb-icon-"], [class*=" vb-icon-"]'))
        .pipe(concat('_icons.scss'))
        .pipe(gulp.dest('./src/sass'));
});