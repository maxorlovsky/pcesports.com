const gulp = require('gulp');
const concat = require('gulp-concat');
const sass = require('gulp-sass');
const uglifycss = require('gulp-uglifycss');

gulp.task('styles', () => {
    gulp.src([
			'./fe/styles/global.scss',
			'./fe/src/**/*.scss'
		])
    	.pipe(sass().on('error', sass.logError))
    	.pipe(uglifycss({
			"uglyComments": true
		}))
		.pipe(concat('combined.css'))
		.pipe(gulp.dest('./public/dist/styles/'));
});