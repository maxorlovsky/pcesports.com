const gulp = require('gulp');
const jshint = require('gulp-jshint');
 
gulp.task('lint:script', function() {
  return gulp.src('./fe/app/**/*.js')
    .pipe(jshint())
    .pipe(jshint.reporter('default'));
});