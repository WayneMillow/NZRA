$(document).ready(function() {
  $('[data-include]').each(function() {
    doInclude($(this));
  });
});
function doInclude(include) {
  console.log(include.text());
  var file = './includes/' + include.attr('data-include') + '.html';
  console.log(file);
  include.load(file);
  $.get(file, function(data){include.append(data)})
}