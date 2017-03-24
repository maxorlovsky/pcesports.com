const gulp = require('gulp');
const minify = require('gulp-minify');
const uglify = require('gulp-uglify');
const runSequence = require('run-sequence');

gulp.task('minify', ['minify:components', 'minify:scripts']);

gulp.task('minify:components', () => {
  	gulp.src('./public/dist/js/components.js', { base: "./" })
        .pipe(uglify())
        .pipe(gulp.dest('.'));
});

gulp.task('minify:scripts', () => {
    gulp.src('./public/dist/js/scripts.js', { base: "./" })
        .pipe(uglify())
        .pipe(gulp.dest('.'));
});