/* jshint undef: false, unused: false, camelcase: false */

var coffeeBaseDir = '.';
var coffeeFiles = coffeeBaseDir + '/**/*.coffee';

var sassBaseDir = './sass';
var sassFiles = sassBaseDir + '/**/*.scss';

var cssBaseDir = './css';
var cssFiles = cssBaseDir + '/**/*.css';

//  ----  code here ---

var
  sass = require('gulp-sass'),
  coffee = require('gulp-coffee'),
  watch = require('gulp-watch'),
  notify = require('gulp-notify'),
  livereload = require('gulp-livereload'),
  filter = require('gulp-filter'),
  plumber = require('gulp-plumber'),
  tap = require('gulp-tap'),
  util = require('gulp-util'),
  exec = require('child_process').exec,
  sourcemaps = require('gulp-sourcemaps')
gulp = require('gulp');
request = require('then-request');



// /*==========  helpers  ==========*/

var isPartial = function (file) {
  var ret = (/^_/).test(path.basename(file.path));
  return ret;
};

var noPartials = function (file) {
  return !isPartial(file);
};

gulp.task('default', ['sass:watch', 'sass:watch_partials', 'coffee:watch', 'css:watch']);

gulp.task('css:watch', function () {
  livereload.listen();
  return watch(cssFiles, {
      ignoreInitial: true
    })
    .pipe(notify("✔ CSS file changed: <%= file.relative %>"))
    .pipe(tap(function (file, t) {
      request('GET', 'http://localhost:' + livereload.server.port + '/changed?files=' + file.path);
    }));
});

gulp.task('sass:watch', function () {

  return watch(sassFiles, {
      ignoreInitial: true
    })
    .pipe(filter(noPartials)) //avoid compiling SCSS partials
    .pipe(plumber())
    .pipe(notify("... compiling <%= file.relative %>!"))
    .pipe(sass({
      outputStyle: 'nested',
      includePaths: ['/www/gulp/node_modules/'],
      precision: 10
    }).on('error', function (err) {
      notify().write({
        message: "⚠ " + err.formatted
      });
      util.beep();
    })).pipe(gulp.dest(cssBaseDir));

});

gulp.task('sass:watch_partials', function () {

  return watch(sassFiles, {
      ignoreInitial: true
    })
    .pipe(filter(isPartial))
    .pipe(notify("... partial modified: <%= file.relative %>"))
    .pipe(tap(function (file, t) {
      var regExp = '^[^/]*import.*partials/' + path.basename(file.path, '.scss').replace(/^_/, '') + '.*$';
      var dir2look = path.normalize(path.dirname(file.path) + "/..");

      exec("touch `grep -l '" + regExp + "' " + dir2look + "/*.scss` ");

    }));

});

gulp.task('coffee:watch', function () {

  return watch(coffeeFiles, {
      ignoreInitial: true
    })
    .pipe(plumber())
    // .pipe(iced({bare: true}))
    .pipe(coffee({
      bare: true
    }).on('error', function (err) {
      notify().write({
        message: "⚠ " + err.message
      });
      util.beep();
    }))
    .pipe(notify("✔ coffee-file created: <%= file.relative %>!"))
    .pipe(gulp.dest(coffeeBaseDir));

});
