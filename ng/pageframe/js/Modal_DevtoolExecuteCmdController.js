define(function() {
  return angular.module('coreModule').registerController('Modal_DevToolExecuteCmdController', function($scope, params, close, $element, $timeout, validationService, $q, $rootScope, $sce) {
    if (window.console && console.log) {
      console.log("Modal_DevToolExecuteCmdController inited, params:", params);
    }
    $scope.params = params;
    $scope.iframeUrl = $sce.trustAsResourceUrl('/BE/Pages/ng_pagemanager/' + params.menuItem.command.args.cmd + '/' + params.referencePage.id);
    return $scope.close = function(result) {
      if (window.console && console.log) {
        console.log("closed my stuff", null);
      }
      return close(result, 500);
    };
  });
});
