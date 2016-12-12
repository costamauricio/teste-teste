var gulp = require('gulp');
var express = require('express');

gulp.task('server', () => {
  
  var app = express();
  app.use(express.static(__dirname + '/web/'));
  app.listen(80);
});
