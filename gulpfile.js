const gulp = require('gulp');
const sass = require('gulp-sass');
const scss = require('gulp-scss');
const browserSync = require('browser-sync');
const concat = require('gulp-concat');
const uglify = require('gulp-uglifyjs');
const cssNano = require('gulp-cssnano');
const rename = require('gulp-rename');
const rubySass = require('gulp-ruby-sass');
const imagemin = require('gulp-imagemin');
const fs = require("fs");
const pngquant = require('imagemin-pngquant');
const replace = require('gulp-replace');
const string_replace = require('gulp-string-replace');
const webpack = require('webpack');
const WebpackDevServer = require('webpack-dev-server');
const gutil = require('gulp-util');
const genv = require('gulp-env');


const  Config = {
    style: '',

};

const entry = './src/';
const dist = {
	dev:  './dev/',
	prod: './prod/'
}





gulp.task('compile-and-minify-all-sass', function(){
    return gulp.src('./dev/**/*.+(scss|sass)')
    .pipe(sass())
    .pipe(gulp.dest('./dev'))
    .pipe(cssNano({ zindex: false }))
    .pipe(rename(function (path) {
        //path.dirname += "/";
        path.basename += "-min";
        //path.extname = ""
    }))
    .pipe(gulp.dest('./src/'));
});

gulp.task('replace-placeholders-with-config', ['compile-and-minify-all-sass'], function() {
  Config.style = fs.readFileSync("src/style-min.css", "utf8");

  return gulp.src('./dev/config.json')
    .pipe(string_replace('%STYLE%', Config.style))
    .pipe(gulp.dest('./src/'));
});

gulp.task('templates', function(){
  gulp.src(['file.txt'])
    .pipe(replace('bar', 'foo'))
    .pipe(gulp.dest('build/'));
});

gulp.task('replace_1', function() {
  gulp.src(["./config.json"])
    .pipe(string_replace('environment', 'production'))
    .pipe(gulp.dest('./build/config.js'))
});

gulp.task('minify-all-img', function(){
    return gulp.src('dev/images/**/*')
    .pipe(imagemin({
        interlaced: true,
        progressive: true,
        svgoPlugins: [{removeViewBox: false}],
        use: [pngquant()]
    }))
    .pipe(gulp.dest('dist/images'));
});

gulp.task('transfer', function(){
    // Только внутренние файлы папки fonts
    let buildFonts = gulp.src('dev/fonts/*')
    .pipe(gulp.dest('dist/fonts'));

    //let buildImages = gulp.src('dev/images/**/*')
    // .pipe(gulp.dest('dist/images'));

    let buildPhp = gulp.src(['dev/php/**','!dev/php/passwords.php'])
    .pipe(gulp.dest('dist/php'));

    let buildContent = gulp.src('dev/content/**/*.+(php|js|css)')
    .pipe(gulp.dest('dist/content'));

    let buildIndex = gulp.src('dev/index.php')
    .pipe(gulp.dest('dist'));

});




gulp.task('watch', ['build-dev'], function(){
    //gulp.watch('dev/images/**/*', ['minify-all-img']);
    gulp.watch( entry + 'fonts/**/*', ['fonts-to-dev']);
    gulp.watch( entry + 'backend/**/*', ['php-to-dev']);
    gulp.watch( entry + 'index.php', ['index-to-dev']);
    gulp.watch( entry + 'frontend/**/*.+(scss|sass)', ['webpack-build']);
    gulp.watch( entry + 'frontend/**/*.+(js|jsx)', ['webpack-build']);
});



// Development build
gulp.task('build-dev', ['webpack-build', 'fonts-to-dev', 'index-to-dev', 'php-to-dev']);

gulp.task('fonts-to-dev', function(){
    let buildFonts = gulp.src( entry + 'fonts/**/*' )
    .pipe(gulp.dest( dist.dev + 'fonts/' ));

    //let buildImages = gulp.src('dev/images/**/*')
    // .pipe(gulp.dest('dist/images'));

    //let buildContent = gulp.src('dev/content/**/*.+(php|js|css)')
    //.pipe(gulp.dest('dist/content'));
});
gulp.task('php-to-dev', function(){
    let buildPhp = gulp.src([ entry + 'backend/**/*' ])
    .pipe(gulp.dest( dist.dev + 'php/' ));
});
gulp.task('index-to-dev', function(){
    let buildIndex = gulp.src( entry + 'index.php')
    .pipe(gulp.dest( dist.dev ));
});

// Production build
gulp.task('build-prod', ['_set-env:prod', 'webpack-build', 'other-files-to-prod']);

gulp.task('other-files-to-prod', function(){

    let buildFonts = gulp.src( entry + 'fonts/**/*' )
    .pipe(gulp.dest( dist.prod + 'fonts/' ));

    let buildPhp = gulp.src([ entry + 'backend/**/*' ])
    .pipe(gulp.dest( dist.prod + 'php/' ));

    let buildIndex = gulp.src( entry + 'index.php')
    .pipe(gulp.dest( dist.prod ));

});

gulp.task('webpack-build', function(callback) {
    // modify some webpack config options
    let myConfig = Object.create(require('./webpack.config.js'));
    // run webpack
    webpack(myConfig, function(err, stats) {
        if(err) throw new gutil.PluginError('webpack-build', err);
        gutil.log('[webpack-build]', stats.toString({
            colors: true
        }));
        callback();
    });
});

gulp.task('set-dev-node-env', function() {
	gutil.log("set-env", "ENV => development");
    return process.env.NODE_ENV = 'development';
});

gulp.task('set-prod-node-env', function() {
	gutil.log("set-env", "ENV => production");
    return process.env.NODE_ENV = 'production';
});

gulp.task("_set-env:prod", function() {
    gutil.log("set-env", "ENV => production");
    genv({
        vars: {
            NODE_ENV: "production"
        }
    });
});





