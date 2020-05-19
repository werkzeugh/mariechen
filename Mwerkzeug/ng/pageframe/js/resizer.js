define(function() {
  return angular.module('mc.resizer', []).directive('resizer', function($document) {
    return function($scope, $element, $attrs) {
      var mousemove, mouseup;
      mousemove = function(event) {
        var x, y;
        if ($attrs.resizer === 'vertical') {
          x = event.pageX;
          if ($attrs.resizerMax && x > $attrs.resizerMax) {
            x = parseInt($attrs.resizerMax);
          }
          $element.css({
            left: x + 'px'
          });
          $($attrs.resizerLeft).css({
            width: x + 'px'
          });
          $($attrs.resizerRight).css({
            left: x + parseInt($attrs.resizerWidth) + 'px'
          });
        } else {
          y = window.innerHeight - event.pageY;
          $element.css({
            bottom: y + 'px'
          });
          $($attrs.resizerTop).css({
            bottom: y + parseInt($attrs.resizerHeight) + 'px'
          });
          $($attrs.resizerBottom).css({
            height: y + 'px'
          });
        }
      };
      mouseup = function() {
        jQuery('body').removeClass('resizing');
        $document.unbind('mousemove', mousemove);
        $document.unbind('mouseup', mouseup);
      };
      $element.on('mousedown', function(event) {
        event.preventDefault();
        if (window.console && console.log) {
          console.log("hide", null);
        }
        $document.on('mousemove', mousemove);
        $document.on('mouseup', mouseup);
        jQuery('body').addClass('resizing');
      });
    };
  });
});
