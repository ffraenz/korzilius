
'use strict'

import gulp from 'gulp'
import sourcemaps from 'gulp-sourcemaps'
import rollup from 'rollup-stream'
import babel from 'rollup-plugin-babel'
import source from 'vinyl-source-stream'
import buffer from 'vinyl-buffer'
import rename from 'gulp-rename'
import uglify from 'gulp-uglify'
import standard from 'gulp-standard'
import mocha from 'gulp-mocha'
import sass from 'gulp-ruby-sass'
import autoprefixer from 'gulp-autoprefixer'
import cleanCSS from 'gulp-clean-css'

let meta = require('./package.json')

let paths = {
  js: {
    src: './front/scripts',
    dist: './public/assets/scripts'
  },
  sass: {
    src: './front/styles',
    dist: './public/assets/styles'
  }
}

gulp.task('lint-js', () => {
  return gulp.src(paths.src + '/**/*.js')
    .pipe(standard())
    .pipe(standard.reporter('default', {
      breakOnError: true,
      quiet: true
    }))
})

var rollupCache

gulp.task('js', ['lint-js'], () => {

  // run module builder and return a stream
  const stream = rollup({
    entry: paths.js.src + '/main.js',
    external: [
    ],
    plugins: [
      babel()
    ],
    format: 'umd',
    moduleId: meta.name,
    moduleName: meta.moduleName,
    sourceMap: true,
    cache: rollupCache
  })

  return stream

    // enable rollup cache
    .on('bundle', (bundle) => {
      rollupCache = bundle;
    })

    // handle errors gracefully
    .on('error', (e) => {
      console.error(e.message)
      if (e.codeFrame !== undefined) {
        console.error(e.codeFrame);
      }
      stream.emit('end')
    })

    // set output filename
    .pipe(source('script.js', paths.js.src))

    // buffer the output, most gulp plugins do not support streams
    .pipe(buffer())

    // init sourcemaps with inline sourcemap produced by rollup-stream
    .pipe(sourcemaps.init({
      loadMaps: true
    }))

    // minify code
    .pipe(uglify())

    // save result
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.js.dist))
})

gulp.task('sass', function() {
  return sass(paths.sass.src + '/main.scss', {
    style: 'expanded',
    loadPath: [
      'front',
      'node_modules'
    ]
  })
    .on('error', function (error) {
        console.error(error.message);
    })
    .pipe(autoprefixer('last 2 version', 'ie 11', '> 1%'))
    .pipe(rename('style.css'))
    .pipe(gulp.dest(paths.sass.dist))
    .pipe(cleanCSS({ processImport: false }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(paths.sass.dist));
});

gulp.task('watch', () => {
  gulp.watch([
    paths.js.src + '/../**/*.js'
  ], ['js'])
  gulp.watch([
    paths.sass.src + '/../**/*.scss'
  ], ['sass'])
})

gulp.task('default', ['js', 'sass', 'watch'])
