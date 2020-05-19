define ->
  angular.module('coreModule').registerController 'action_edit_page_controller', ($scope, topScope, params,$rootScope)->
 
    console.log "action_edit_page_controller:" , params  if window.console and console.log

    orFail=topScope.app.failPopup
    params.topScope=topScope
    hideNotificationFunction=null
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
      topScope.app.showModal('EditPageSettings',angular.extend(params,infoForPageCreation)).then doUpdatePage

    doUpdatePage=(data)->
      commandArgs=
        pageId:params.pageId
        pageData:data.pageData

      hideNotificationFunction=topScope.showNotification 'please wait <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">',0

      topScope.callPageManager('updatePage',commandArgs).then doFinalizePageEdit, orFail

    doFinalizePageEdit=(responseFromServer)->
      hideNotificationFunction()
      pageId=params.pageId
      treeref=topScope.app.pageTreeRef
      if treeref
        if pageId
          pageObj=topScope.app.getPageInPageTree(pageId)
          topScope.editPage(pageId)
          topScope.app.pageTreeRef.load_node pageObj, (a,b)->
            console.log "node loaded" , a,b  if window.console and console.log
            topScope.selectPage(pageId)
      else 
        window.top.location.reload()


    main()


