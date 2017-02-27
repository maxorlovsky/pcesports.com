const gulp = require('gulp');
const runSequence = require('run-sequence');

require('require-dir')('./tasks');

//add clean
gulp.task('release', ['copy', 'styles', 'scripts']);

gulp.task('build', (cb) => {
	runSequence(
		'lint:script',
		'copy',
		['styles', 'scripts'],
	cb);
});