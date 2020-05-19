define(function() {
  return angular.module('coreModule').registerController('YesNoController', function($scope, close) {
    if (window.console && console.log) {
      console.log("YesNoController inited", null);
    }
    return $scope.close = function(result) {
      if (window.console && console.log) {
        console.log("closed my stuff", null);
      }
      return close(result, 500);
    };
  });
});
