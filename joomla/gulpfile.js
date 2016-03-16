var gulp = require('gulp');
var typescript = require('gulp-typescript');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var compass = require('gulp-compass');
var cssnano = require('gulp-cssnano');

gulp.task('default', function () {

});

gulp.task('dist', [
    'lib',
    'plg'
], function () {
    
    // compress JS and CSS
    gulp.src(['./dist/**/*.js', '!./dist/**/*.min.js'])
        .pipe(uglify({
            preserveComments: 'license'
        }))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./dist'));

    gulp.src(['./dist/**/*.css', '!./dist/**/*.min.css'])
        .pipe(cssnano())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./dist'));

});

gulp.task('install', [
    'dist'
], function () {
    
    // install library
    gulp.src('./dist/lib/**')
        .pipe(gulp.dest('../../joomla/libraries/videobox'));
        
        
    // install system plugin
    gulp.src('./dist/plg/language/**')
        .pipe(gulp.dest('../../joomla/administrator/language'));

    gulp.src(['./dist/plg/**', '!./dist/plg/language/**'])
        .pipe(gulp.dest('../../joomla/plugins/system/videobox'));

});

gulp.task('lib', function () {
    var tsResult = gulp.src('./src/lib/**/*.ts')
        .pipe(typescript({
            declaration: true,
            noExternalResolve: true,
            target: 'ES5',
            sourcemap: true
        }));

    tsResult.dts.pipe(gulp.dest('./dist/definitions'));
    tsResult.js.pipe(gulp.dest('./dist/lib'));

    gulp.src('./src/lib/sass/*.scss')
        .pipe(compass({
            css: 'src/lib/css',
            sass: 'src/lib/sass'
        }))
        .pipe(gulp.dest('./dist/lib/css'));

    gulp.src('./src/lib/**/*.php')
        .pipe(gulp.dest('./dist/lib'));

    gulp.src(['../node_modules/videobox-js/dist/*.css'])
        .pipe(gulp.dest('./dist/lib/css'));

    gulp.src(['../node_modules/videobox-js/dist/*.js'])
        .pipe(gulp.dest('./dist/lib/js'));

    gulp.src(['../node_modules/videobox-js/dist/video-js/**'])
        .pipe(gulp.dest('./dist/lib/video-js'));
});

gulp.task('plg', function () {

    gulp.src('./src/plg/**')
        .pipe(gulp.dest('./dist/plg'));
});