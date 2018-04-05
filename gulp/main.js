const gulp = require('gulp');
const runSequence = require('run-sequence');

require('require-dir')('./tasks');

gulp.task('build', (cb) => {
    return runSequence(
        'copy',
        'bump-ver',
    cb);
});