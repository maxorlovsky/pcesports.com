const gulp = require('gulp');
const browserSync = require('browser-sync').create();

gulp.task('watch', ['serve'], () => {
	gulp.watch('./fe/src/**/*.js', ['lint:script', 'scripts', browserSync.reload])
		.on("change", watchChange)
		.on("error", watchError);

	gulp.watch('./fe/styles/*.scss', ['styles', browserSync.reload])
		.on("change", watchChange)
		.on("error", watchError);
});


gulp.task('serve', (cb) => {
	browserSync.init({}, cb);
});

function watchChange(event) {
	console.log(`File ${event.path} was ${event.type}.`);
}

function watchError(error) {
	console.error($.util.colors.red(`Error occurred while running watched task. Error details: ${error}`));
}