const gulp = require('gulp');
const uglify = require('gulp-uglify-es').default;
const concat = require('gulp-concat');
const rename = require('gulp-rename');
const notify = require('gulp-notify');
const plumber = require('gulp-plumber');
const sass = require('gulp-sass');
const cssnano = require('gulp-cssnano');
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');
const merge = require('merge-stream');
const imagemin = require('gulp-imagemin');
const fs = require('fs');
const path = require('path');

// Language
const checktextdomain = require('gulp-checktextdomain');
const wpPot = require('gulp-wp-pot');

// Require modules from node_modules
const rollup = require('gulp-better-rollup');
const babel = require('rollup-plugin-babel');
const resolve = require('rollup-plugin-node-resolve');
const commonjs = require('rollup-plugin-commonjs');


const settings = require('./package.json');

/** SCSS Task */
gulp.task('css', () => {

	const things = ['plugins', 'themes'];

	const tasks = things.map((thing) => {
		const folders = getFolders('./' + thing + '/');
		const tasks = folders.map((dir) => {
			return gulp.src(['./' + thing + '/' + dir + '/css/*.scss'])
				.pipe(plumber())
				.pipe(sourcemaps.init())
				.pipe(sass({
					precision: 10,
					includePaths: [
						'node_modules'
					]
				}).on('error', sass.logError))
				.pipe(autoprefixer('last 2 version', 'ie 9', 'ios 6', 'android 4'))
				.pipe(sourcemaps.write())
				.pipe(gulp.dest('./../www/content/'+thing+'/' + dir + '/'))
				.pipe(rename({
					suffix: '.min'
				}))
				.pipe(cssnano({
					zindex: false
				}))
				.pipe(gulp.dest('./../www/content/'+thing+'/' + dir + '/'))
		});
		return merge(tasks);
	});
	return merge(tasks);
});

/* Scripts task */
gulp.task('scripts', function () {
	const things = ['plugins', 'themes'];

	const tasks = things.map((thing) => {
		const folders = getFolders('./' + thing + '/');
		const tasks = folders.map((dir) => {
			return gulp.src(['./' + thing + '/' + dir + '/js/*.js'])
				.pipe(sourcemaps.init())
				// .pipe(rollup({
				// 	plugins: [babel({
				// 		presets: ['@babel/env']
				// 	}), resolve(), commonjs()]
				// }, 'umd'))
				.pipe(sourcemaps.write())
				.pipe(gulp.dest('./../www/content/'+thing+'/' + dir + '/assets/js/'))
				.pipe(rename({
					suffix: '.min'
				}))
				.pipe(uglify().on('error', handleErrors))
				.pipe(gulp.dest('./../www/content/'+thing+'/' + dir + '/assets/js/'));
			});
			return merge(tasks);
		});
	return merge(tasks);
});

gulp.task('watch', function () {
	gulp.watch(['./themes/**/css/*/**.scss', './themes/**/css/*.scss'], gulp.series(['css']));
	gulp.watch('./themes/**/js/*.js', gulp.series(['scripts']));
});

gulp.task('default', gulp.series(['css', 'scripts', 'watch']), () => {
	//
});

function getFolders(dir) {
	return fs.readdirSync(dir)
		.filter(function (file) {
			return fs.statSync(path.join(dir, file)).isDirectory();
		});
}

const handleErrors = function () {
    const args = Array.prototype.slice.call(arguments);

    // Send error to notification center with gulp-notify
    notify.onError({
        title: "Compile Error",
        message: "<%= error.message %>"
    }).apply(this, args);

    this.emit('end');
}