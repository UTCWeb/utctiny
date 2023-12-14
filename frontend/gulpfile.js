var gulp = require('gulp');
var sass = require('gulp-sass')(require('sass'));
var browserSync = require('browser-sync').create();

gulp.task('sass', () => {
    return gulp.src("assets/sass/*.scss")
        .pipe(sass())
        .pipe(gulp.dest("dist/"))
        .pipe(browserSync.stream());
});

gulp.task('start', gulp.series('sass', function () {
    browserSync.init({
        proxy: "utctiny.lndo.site"
    });
    gulp.watch("assets/sass/*.scss", gulp.series('sass'));
    gulp.watch("assets/img/*.png").on('change', browserSync.reload);
    gulp.watch("assets/img/*.svg").on('change', browserSync.reload);
    gulp.watch("*.php").on('change', browserSync.reload);
}));

gulp.task('default', gulp.series('start'));