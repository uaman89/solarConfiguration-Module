var gulp = require('gulp'),
    gp_concat     = require('gulp-concat'),
    gp_rename     = require('gulp-rename'),
    gp_uglify     = require('gulp-uglify'),
    gp_sourcemaps = require('gulp-sourcemaps'),
    minifyCSS     = require('gulp-minify-css'),
    browserSync = require('browser-sync').create(),
    sass = require('gulp-sass');
    //gulpFilter    = require('gulp-filter'),
    //mainBowerFiles   = require('main-bower-files')
    ;

//SASS
gulp.task('sass', function () {
    return gulp.src('__sources/styles/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('__sources/styles/'));
});

gulp.task('sass:watch', function () {
    gulp.watch('./sass/**/*.scss', ['sass']);
});

// JS-HEADER
gulp.task('js-to-header', function(){
    return gulp.src([
        'bower_components/jquery/dist/jquery.min.js',
        'bower_components/angular/angular.min.js',
    ])
        //.pipe(gp_sourcemaps.init())
        .pipe(gp_concat('header_script.concat.js'))
        .pipe(gulp.dest('js'))
        .pipe(gp_rename('header_script.min.js'))
        //.pipe(gp_uglify())
        //.pipe(gp_sourcemaps.write('./'))
        .pipe(gulp.dest('js'));
});

// JS-BOTTOM
gulp.task('js-to-bottom', function(){
    return gulp.src([
        'bower_components/bootstrap/dist/js/bootstrap.min.js',
        'bower_components/three.js/build/three.min.js',
        'bower_components/three.js/examples/js/renderers/CanvasRenderer.js',
        'bower_components/three.js/examples/js/renderers/Projector.js',
        'bower_components/three.js/examples/js/controls/OrbitControls.js',
        'bower_components/three.js/examples/js/Detector.js',
        '__sources/js/**/*.js',
        //'__sources/js/models/*.js',
        //'__sources/js/app.js',
        //'__sources/js/directives/*.js',
        //'__sources/js/controllers/*.js',
        //'__sources/js/index.js',
        ])
        //.pipe(gp_sourcemaps.init())
        .pipe(gp_concat('bottom_script.concat.js'))
        .pipe(gulp.dest('js'))
        .pipe(gp_rename('bottom_script.min.js'))
        //.pipe(gp_uglify())
        //.pipe(gp_sourcemaps.write('./'))
        .pipe(gulp.dest('js'));
});


// CSS
gulp.task('build-css', function(){
    gulp.src([
            'bower_components/angular/angular-csp.css',
            'bower_components/bootstrap/dist/css/bootstrap.min.css',
            '__sources/styles/**/*.css'                                            //my styles
        ])
        //.pipe(gp_sourcemaps.init())
        .pipe(gp_concat('main.concat.css'))
        .pipe(gulp.dest('styles'))
        .pipe(gp_rename('main.min.css'))
        //.pipe(minifyCSS())
        //.pipe(gp_sourcemaps.write('./'))
        .pipe(gulp.dest('styles'))
});

// BROWSER SYNC
gulp.task('browser-sync', function() {
    browserSync.init({
        proxy: "http://solar.seotm.biz/admin/index.php?module=106"
        //server: {
        //    baseDir: "./"
        //}
    });

    gulp.watch( '__sources/js/**/*.js', ['js-to-bottom'] );
    gulp.watch( 'js/*.min.js' ).on('change', browserSync.reload);

    gulp.watch( '__sources/styles/**/*.scss', ['sass']);
    gulp.watch( '__sources/styles/*.css', ['build-css'] ).on('change', browserSync.reload);

    gulp.watch( 'index.html' ).on('change', browserSync.reload);
    gulp.watch( 'templates/*.html' ).on('change', browserSync.reload);
});


//autobuild without reload page
gulp.task( 'watch-only', function(){
    gulp.watch( '__sources/js/**/*.js', ['js-to-bottom'] );
    gulp.watch( '__sources/styles/**/*.scss', ['sass']);
    gulp.watch( '__sources/styles/*.css', ['build-css'] );
});

gulp.task('default', ['js-to-header', 'js-to-bottom', 'build-css'], function(){});

