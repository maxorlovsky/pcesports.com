const gulp = require('gulp');
const runSequence = require('run-sequence');

gulp.task('copy', (cb) => {
    return runSequence(
        ['copy:assets', 'copy:index'],
        'copy:fontawesome',
    cb);
});

// Copy assets
gulp.task('copy:assets', () => {
      return gulp.src('./fe/assets/**/*')
        .pipe(gulp.dest('./public/dist/assets/'));
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