const gulp = require('gulp');
const jshint = require('gulp-jshint');
 
gulp.task('lint:script', () => {
  return gulp.src('./fe/src/**/*.js')
    .pipe(jshint({
      esversion: 6,
      multistr: true
    }))
    .pipe(jshint.reporter('default'));
});