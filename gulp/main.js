const gulp = require('gulp');
const runSequence = require('run-sequence');

require('require-dir')('./tasks');

let release = false;

if (process.argv.indexOf("--release") > -1) {
    release = true;
}

gulp.task('build', (cb) => {
	if (release) {
		return runSequence(
			'lint:script',
			'copy',
			['styles', 'scripts'],
			'bump-ver',
		cb);
	} else {
		return runSequence(
			'clean',
			'lint:script',
			'copy',
			['styles', 'scripts'],
			'bump-ver',
		cb);
	}
});