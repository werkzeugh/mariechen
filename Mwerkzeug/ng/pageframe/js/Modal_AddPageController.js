define(function() {
  return angular.module('coreModule').registerController('Modal_AddPageController', function($scope, params, close) {
    if (window.console && console.log) {
      console.log("Modal_AddPageController inited", null);
    }
    $scope.close = function(clickedOK) {
      var result;
      if (clickedOK) {
        result = {
          status: 'ok',
          pageData: $scope.model
        };
      } else {
        result = {
          status: 'cancelled'
        };
      }
      return close(result, 500);
    };
    $scope.params = params;
    if (params.insertData) {
      return $scope.model = $scope.params.insertData;
    } else {
      return $scope.model = {
        Title: "new Page"
      };
    }
  });
});
