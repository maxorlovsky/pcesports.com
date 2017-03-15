const gulp = require('gulp');
const concat = require('gulp-concat');
const rename = require("gulp-rename");

gulp.task('copy', function() {
	// Copy assets
  	gulp.src('./fe/assets/**/*')
    	.pipe(gulp.dest('./public/dist/assets/'));

	// FontAwesome fonts
	gulp.src('./node_modules/font-awesome/fonts/*')
        .pipe(gulp.dest('./public/dist/assets/font/'));

    // Copy html files with rename
    gulp.src('./fe/src/**/*.html')
		.pipe(rename(function (path) {
			let split = path.dirname.split(/[\\\/]+/).pop();
			let newName = path.basename.replace(path.basename, split);
			path.dirname = '';
			path.basename = newName;
		}))
    	.pipe(gulp.dest('./public/dist/html/'));

	// VueJS files
	gulp.src([
			'./node_modules/vue/dist/vue.min.js',
			'./node_modules/vue-*/dist/vue-*.min.js',
			'./node_modules/axios/dist/axios.min.js'
		])
    	.pipe(concat('libs.js'))
        .pipe(gulp.dest('./public/dist/js/'));
});