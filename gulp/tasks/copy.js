const gulp = require('gulp');
const concat = require('gulp-concat');
const rename = require("gulp-rename");
const runSequence = require('run-sequence');

gulp.task('copy', (cb) => {
	return runSequence(
		['copy:assets', 'copy:html', 'copy:vue', 'copy:index'],
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

// FontAwesome fonts
gulp.task('copy:fontawesome', () => {
	return gulp.src('./node_modules/font-awesome/fonts/*')
        .pipe(gulp.dest('./public/dist/assets/font/'));
});