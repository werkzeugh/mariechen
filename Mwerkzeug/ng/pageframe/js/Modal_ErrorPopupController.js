define(function() {
  return angular.module('coreModule').registerController('Modal_ErrorPopupController', function($scope, params, close) {
    if (window.console && console.log) {
      console.log("Modal_ErrorPopupController inited", $scope);
    }
    $scope.params = params;
    return $scope.close = function(result) {
      return close(result, 500);
    };
  });
});
