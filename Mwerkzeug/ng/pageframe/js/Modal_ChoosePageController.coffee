define ->
  angular.module('coreModule').registerController 'Modal_ChoosePageController', ($scope, params,close, $timeout, $element)->
    console.log "Modal_ChoosePageController inited" , null  if window.console and console.log
    


    $timeout( ->
      jQuery('.btn-primary',$element).focus();
    ,500)

    $scope.close = (type)->
      close type, 500
      
    $scope.params=params


