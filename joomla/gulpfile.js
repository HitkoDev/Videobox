var gulp = require('gulp');
var typescript = require('gulp-typescript');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var compass = require('gulp-compass');
var cssnano = require('gulp-cssnano');

gulp.task('default', function () {

});

gulp.task('dist', [
    'lib'
], function () {
    
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
        
    gulp.src('./dist/lib/**')
        .pipe(gulp.dest('../../joomla/libraries/videobox'));
})

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
})