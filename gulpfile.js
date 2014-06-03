// Include gulp
var gulp = require('gulp');

// Include Our Plugins
var minifyCSS = require('gulp-minify-css');
var rename = require('gulp-rename');
var prefix = require('gulp-autoprefixer');
var sass = require('gulp-sass');
var csscomb = require('gulp-csscomb');

gulp.task('css-admin', function() {
    gulp.src('./css/scss/nwxrviewadmin.scss')
        .pipe(sass({ style: 'expanded' }))
        .pipe(prefix('last 1 version', '> 1%', 'ie 8', 'ie 7'))
        .pipe(csscomb())
        .pipe(gulp.dest('./css/'))
        .pipe(minifyCSS({keepBreaks:true}))
        .pipe(rename( {extname: '.min.css'}))
        .pipe(gulp.dest('./css/'))
});

gulp.task('css-style', function() {
    gulp.src('./css/scss/nwxrviewstyle.css')
         .pipe(sass({ style: 'expanded' }))
        .pipe(prefix('last 1 version', '> 1%', 'ie 8', 'ie 7'))
        .pipe(csscomb())
        .pipe(gulp.dest('./css/'))
        .pipe(minifyCSS({keepBreaks:true}))
        .pipe(rename( {extname: '.min.css'}))
        .pipe(gulp.dest('./css/'))
});

gulp.task('watch', function() {
    gulp.watch('./css/scss/nwxrviewadmin.scss', ['css-admin']);
    gulp.watch('./css/scss/nwxrviewstyle.scss', ['css-style']);
});

gulp.task('default', ['css-admin', 'css-style']);