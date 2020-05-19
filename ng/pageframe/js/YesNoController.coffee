define ->
  angular.module('coreModule').registerController 'YesNoController', ($scope, close)->
    console.log "YesNoController inited" , null  if window.console and console.log
    $scope.close = (result)->
      console.log "closed my stuff" , null  if window.console and console.log
      close result, 500
