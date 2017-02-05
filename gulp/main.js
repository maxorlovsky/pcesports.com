const gulp = require('gulp');
const runSequence = require('run-sequence');

require('require-dir')('./tasks');

//add clean
gulp.task('default', ['copy', 'styles', 'scripts']);

gulp.task('dev', (cb) => {
	runSequence(
		'lint:script',
		'copy',
		['styles', 'scripts'],
	cb);
});