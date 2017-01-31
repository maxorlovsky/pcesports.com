const gulp = require('gulp');
const uglify = require('gulp-uglify');
const concat = require('gulp-concat');
const babel = require('gulp-babel');
const webpack = require('gulp-webpack');
const clean = require('gulp-clean');

gulp.task('scripts', () => {
    gulp.src([
			'./fe/src/**/*.js'
		])
		.pipe(babel({
			presets: ['es2015', 'react']
		}))
    	.pipe(uglify())
    	.pipe(concat('scripts.js'))
        .pipe(gulp.dest('./public/dist/js/'));

	gulp.src('./public/dist/js/bundle', 
			{read: false}
		)
		.pipe(clean());

	gulp.src('./public/dist/js/scripts.js')
		.pipe(webpack({
			output: {
        		filename: 'bundle.js',
      		}
		}))
		.pipe(gulp.dest('./public/dist/js/'));
});