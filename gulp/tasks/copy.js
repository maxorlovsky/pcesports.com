const gulp = require('gulp');
const concat = require('gulp-concat');
const rename = require("gulp-rename");
const runSequence = require('run-sequence');

gulp.task('copy', (cb) => {
	return runSequence(
		['copy:assets', 'copy:html', 'copy:vue', 'copy:index', 'copy:hammerjsmap', 'copy:polyfills'],
		'copy:fontawesome',
	cb);
});

// Copy assets
gulp.task('copy:assets', () => {
  	return gulp.src('./fe/assets/**/*')
    	.pipe(gulp.dest('./public/dist/assets/'));
});

// Copy html files with rename
gulp.task('copy:html', () => {
    return gulp.src('./fe/src/**/*.html')
		.pipe(rename((path) => {
			let split = path.dirname.split(/[\\\/]+/).pop();
			let newName = path.basename.replace(path.basename, split);
			path.dirname = '';
			path.basename = newName;
		}))
    	.pipe(gulp.dest('./public/dist/html/'));
});

// VueJS files
gulp.task('copy:vue', () => {
	return gulp.src([
			'./node_modules/vue/dist/vue.min.js',
			'./node_modules/vue-*/dist/vue-*.min.js',
			'./node_modules/axios/dist/axios.min.js',
			'./node_modules/marked/marked.min.js',
			'./node_modules/hammerjs/hammer.min.js'
		])
    	.pipe(concat('libs.js'))
        .pipe(gulp.dest('./public/dist/js/'));
});

// index.html
gulp.task('copy:index', () => {
	return gulp.src([
			'./fe/index.html',
		])
        .pipe(gulp.dest('./public/'));
});

// Polyfills
gulp.task('copy:polyfills', () => {
	return gulp.src([
			'./node_modules/babel-polyfill/dist/polyfill.min.js',
			'./node_modules/promise-polyfill/promise.min.js',
			'./node_modules/whatwg-fetch/fetch.js'
		])
    	.pipe(concat('polyfills.js'))
        .pipe(gulp.dest('./public/dist/js/'));
});

// FontAwesome fonts
gulp.task('copy:fontawesome', () => {
	return gulp.src('./node_modules/font-awesome/fonts/*')
        .pipe(gulp.dest('./public/dist/assets/font/'));
});

// HammerJS Map file
gulp.task('copy:hammerjsmap', () => {
	return gulp.src('./node_modules/hammerjs/hammer.min.js.map')
        .pipe(gulp.dest('./public/dist/js/'));
});