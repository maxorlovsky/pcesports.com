const gulp = require('gulp');
const concat = require('gulp-concat');

gulp.task('copy', function() {
	// Copy assets
  	gulp.src('./fe/assets/**/*')
    	.pipe(gulp.dest('./public/dist/assets/'));

    // Copy html files
    gulp.src('./fe/src/views/*.html')
    	.pipe(gulp.dest('./public/dist/html/'));

	// React files
	gulp.src([
			'./node_modules/react/dist/react.js',
			'./node_modules/react-*/dist/react-*.js'
		])
    	.pipe(concat('react.js'))
        .pipe(gulp.dest('./public/dist/js/'));
});