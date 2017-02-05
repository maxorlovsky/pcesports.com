const gulp = require('gulp');
const jshint = require('gulp-jshint');
 
gulp.task('lint:script', function() {
  return gulp.src('./fe/src/**/*.js')
    .pipe(jshint({
      esversion: 6
    }))
    .pipe(jshint.reporter('default'));
});