var gulp = require('gulp'),

// Import dependencies
    concat   = require('gulp-concat'),
    less   = require('gulp-less'),
    cleanCSS   = require('gulp-clean-css'),
    uglify   = require('gulp-uglify'),
    recess = require('gulp-recess'),
    minifyCSS = require('gulp-minify-css'),
    path   = require('path');



    gulp.task('css', function(){
        gulp.src([
            // all css and less sources
           // './web/src/css/style.css',
            './app/Resources/public/css/*.css',

        ]) 
            .pipe(concat('style.min.css'))
            .pipe(less())
            .pipe(minifyCSS())
            //.pipe(gulp.dest('./app/Resources/public/css/min'))
            .pipe(gulp.dest('./web/css'));
        ;
    });

    gulp.task('js',function () {
        gulp.src([
            // all js sources
            /* lib */
            './node_modules/jquery/dist/jquery.js',
            './node_modules/bootstrap/dist/js/bootstrap.js',
            './node_modules/moment/min/moment-with-locales.js',
            './node_modules/eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker.js',
            './node_modules/selectize/dist/js/standalone/selectize.js',
            /* app */
            './app/Resources/public/js/*.js',
            /* StockBundle */
            './src/StockBundle/Resources/public/js/ux.js',
        ])
            .pipe(concat('app.min.js'))
            //.pipe(uglify()) //Retire la minification pendant le dev pour verification debug du js
            //.pipe(gulp.dest('./app/Resources/public/js/min'));
            .pipe(gulp.dest('./web/js/'));
    });
    gulp.task('fonts',function () {
      gulp.src([
          // all fonts sources
          '.app/Resources/public/fonts/*',
      ])
          .pipe(gulp.dest('./web/fonts'));
    });

    gulp.task('watch',function () {
        gulp.watch([
            '.app/Resources/public/less/*.less',
            '.app/Resources/public/css/*.css',
            '.src/*Bundle/Resources/public/less/**/*.less',
            '.app/Resources/public/js/*.js',
            ['css']
        ]);

        gulp.watch([
            '.src/*Bundle/Resources/public/js/*.js',
            '.app/Resources/public/js/*.js',
            ['js']
        ]);
    });
    gulp.task('prod',[
        'css',
        'js',
        'fonts'
    ]);
    gulp.task('default',[
        'prod',
        'watch'
    ]);