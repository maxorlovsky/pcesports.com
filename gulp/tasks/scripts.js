const gulp = require('gulp');
const uglify = require('gulp-uglify');
const concat = require('gulp-concat');
const babel = require('gulp-babel');
const runSequence = require('run-sequence');

gulp.task('scripts', (cb) => {
	return runSequence(
		['scripts:components', 'scripts:scripts'],
	cb);
});

gulp.task('scripts:components', () => {
    return gulp.src([
			'./fe/src/**/*.js',
			'!./fe/src/main.js'
		])
		.pipe(babel({
			presets: ['es2015']
		}))
    	.pipe(uglify())
    	.pipe(concat('components.js'))
        .pipe(gulp.dest('./public/dist/js/'));
});

gulp.task('scripts:scripts', () => {
	return gulp.src('./fe/src/main.js')
		.pipe(babel({
			presets: ['es2015']
		}))
    	.pipe(uglify())
    	.pipe(concat('scripts.js'))
        .pipe(gulp.dest('./public/dist/js/'));
});