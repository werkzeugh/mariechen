define ->
  angular.module('coreModule').registerController 'action_devtool_execute_cmd_controller', ($scope, topScope, params,$rootScope)->
 
    console.log "action_devtool_execute_cmd_controller:" , params  if window.console and console.log

    orFail=topScope.app.failPopup
    params.topScope=topScope

    unless params.args
      params.args=
        mode:'edit'

    main=->
      commandArgs=angular.extend {}, params.args
      commandArgs.pageId=params.pageId
      topScope.callPageManager('getInfoForPageCreation',commandArgs).then doShowModal, orFail

    doShowModal=(infoForPageCreation)->
      $scope.infoForPageCreation=infoForPageCreation
      $rootScope.$broadcast("$routeChangeStart") #used to trigger scope-reset on validation service
      topScope.app.showModal('DevToolExecuteCmd',angular.extend(params,infoForPageCreation))

    main()


