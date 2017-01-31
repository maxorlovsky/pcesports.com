const gulp = require('gulp');
const sass = require('gulp-sass');
const uglifycss = require('gulp-uglifycss');

gulp.task('styles', () => {
    gulp.src('./fe/styles/global.scss')
    	.pipe(sass().on('error', sass.logError))
    	.pipe(uglifycss({
			"uglyComments": true
		}))
		.pipe(gulp.dest('./public/dist/styles/'));
});