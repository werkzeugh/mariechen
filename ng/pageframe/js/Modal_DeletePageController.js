define(function() {
  return angular.module('coreModule').registerController('Modal_DeletePageController', function($scope, params, close, $timeout, $element) {
    if (window.console && console.log) {
      console.log("Modal_DeletePageController inited", null);
    }
    $timeout(function() {
      return jQuery('.btn-primary', $element).focus();
    }, 500);
    $scope.close = function(type) {
      return close(type, 500);
    };
    return $scope.params = params;
  });
});
