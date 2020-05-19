define ->
  angular.module('coreModule').registerController 'Modal_DevToolExecuteCmdController', ($scope, params, close, $element, $timeout, validationService, $q,$rootScope,$sce)->
    console.log "Modal_DevToolExecuteCmdController inited, params:" , params  if window.console and console.log

    #load angular-validation



    $scope.params=params
    $scope.iframeUrl=$sce.trustAsResourceUrl('/BE/Pages/ng_pagemanager/'+params.menuItem.command.args.cmd+'/'+params.referencePage.id)

    $scope.close = (result)->
      console.log "closed my stuff" , null  if window.console and console.log
      close result, 500

    
