const gulp = require('gulp');
const uglify = require('gulp-uglify');
const concat = require('gulp-concat');
const babel = require('gulp-babel');
const runSequence = require('run-sequence');

let release = false;

if (process.argv.indexOf("--release") > -1) {
	release = true;
}

gulp.task('scripts', (cb) => {
	return runSequence(
		['scripts:components', 'scripts:scripts'],
	cb);
});

gulp.task('scripts:components', () => {
	if (release) {
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
	} else {
		return gulp.src([
			'./fe/src/**/*.js',
			'!./fe/src/main.js'
		])
		.pipe(babel({
			presets: ['es2015']
		}))
		.pipe(concat('components.js'))
		.pipe(gulp.dest('./public/dist/js/'));
	}
});

gulp.task('scripts:scripts', () => {
	if (release) {
		return gulp.src('./fe/src/main.js')
			.pipe(babel({
				presets: ['es2015']
			}))
			.pipe(uglify())
			.pipe(concat('scripts.js'))
			.pipe(gulp.dest('./public/dist/js/'));
	} else {
		return gulp.src('./fe/src/main.js')
			.pipe(babel({
				presets: ['es2015']
			}))
			.pipe(concat('scripts.js'))
			.pipe(gulp.dest('./public/dist/js/'));
	}
});