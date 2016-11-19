var gulp = require('gulp')
var svgmin = require('gulp-svgmin')
var concat = require('gulp-concat')
var fontcustom = require('gulp-fontcustom')
var replace = require('gulp-replace')
var merge = require('merge2')
var shell = require('gulp-shell')

gulp.task('icons:font', () => {
    return gulp.src(['./*.svg'])
        .pipe(fontcustom({
            font_name: 'Videobox',
            'css-selector': '.vb-icon-{{glyph}}',
            templates: ['_icons.scss'],
            preprocessor_path: '/font'
        }))
        .pipe(gulp.dest('../../build/font'))
})

gulp.task('icons:sass', [
    'icons:font'
], () => {
    return gulp.src('../../build/font/*.scss')
        .pipe(replace('-{{glyph}}', ', [class^="vb-icon-"], [class*=" vb-icon-"]'))
        .pipe(concat('_icons.scss'))
        .pipe(gulp.dest('../../build/font'))
        .pipe(shell([
            'sass-convert -i --indent 4 <%= file.path %>'
        ]))
})

gulp.task('icons', [
    'icons:sass'
], () => {
    return merge([
        gulp.src('../../build/font/*.svg')
            .pipe(svgmin({
                plugins: [{
                    removeUselessDefs: false
                }]
            }))
            .pipe(gulp.dest('../../build/font')),

        gulp.src('../../build/font/_icons.scss')
            .pipe(gulp.dest('../sass'))
    ])
})
