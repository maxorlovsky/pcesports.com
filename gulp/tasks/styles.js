const gulp = require('gulp');
const concat = require('gulp-concat');
const sass = require('gulp-sass');
const sassGlob = require('gulp-sass-glob');
const uglifycss = require('gulp-uglifycss');

gulp.task('styles', () => {
    return gulp.src('./fe/styles/global.scss')
		.pipe(sassGlob())
    	.pipe(sass().on('error', sass.logError))
    	.pipe(uglifycss({
			"uglyComments": true
		}))
		.pipe(concat('combined.css'))
		.pipe(gulp.dest('./public/dist/styles/'));
});