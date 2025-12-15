var gulp = require('gulp');
var changed = require('gulp-changed');
var imagemin = require('gulp-imagemin');
var browserSync = require('browser-sync').create();

var paths = {
    //画像関連
    //アップローダー処理でランダムなファイル名になった画像ファイルがuploadsフォルダに入るので、それをそのまま圧縮して上書き
    srcDir : 'uploads',
    dstDir : 'uploads',

    //コード関連
    srcPhp: '**/*.php',
    srcJs: 'js/**/*.js'
};

gulp.task('image-min', function() {
    var srcGlob = paths.srcDir + '/**/*.{jpg,jpeg,png,gif}';
    var dstGlob = paths.dstDir;

    return gulp.src(srcGlob)
        .pipe(changed(dstGlob))
        .pipe(imagemin([
            imagemin.gifsicle({interlaced: true}),
            imagemin.jpegtran({progressive: true}),
            imagemin.optipng({optimizationLevel: 5})
        ]))
        .pipe(gulp.dest(dstGlob));
});

gulp.task('serve', function() {
    browserSync.init({
        proxy: "http://localhost:8888/",
        open: true,
        notify: false
    });

    //画像圧縮＆リロード
    gulp.watch(paths.srcDir + '/**/*.{jpg,jpeg,png,gif}', ['image-min']);

    //コード変更時のリロード
    gulp.watch([paths.srcPhp, paths.srcJs]).on('change', browserSync.reload);
});

//デフォルトタスク
gulp.task('default', ['image-min', 'serve']);