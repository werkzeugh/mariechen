define ->
  angular.module('coreModule').registerController 'Modal_ErrorPopupController', ($scope, params,close)->
    console.log "Modal_ErrorPopupController inited" , $scope  if window.console and console.log
    $scope.params=params
    $scope.close = (result)->
      close result, 500
