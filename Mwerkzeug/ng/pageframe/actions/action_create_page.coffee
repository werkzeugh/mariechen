define ->
  angular.module('coreModule').registerController 'action_create_page_controller', ($scope, topScope, params,$rootScope)->
 
    console.log "action_create_page_controller:" , params  if window.console and console.log

    orFail=topScope.app.failPopup
    params.topScope=topScope
    hideNotificationFunction=null

    main=->
      commandArgs=angular.extend {}, params.args
      commandArgs.pageId=params.pageId
      #check if we can create a page here and get a list of possible-Page-Types and a default page-type
      topScope.callPageManager('getInfoForPageCreation',commandArgs).then doShowModal, orFail

    doShowModal=(infoForPageCreation)->
      $scope.infoForPageCreation=infoForPageCreation
      $rootScope.$broadcast("$routeChangeStart") #used to trigger scope-reset on validation service
      topScope.app.showModal('EditPageSettings',angular.extend(params,infoForPageCreation)).then doCreatePage

    doCreatePage=(data)->

      hideNotificationFunction=topScope.showNotification 'please wait <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">',0

      commandArgs=
        pageData:data.pageData
        infoForPageCreation:$scope.infoForPageCreation

      topScope.callPageManager('createPage',commandArgs).then doFinalizePageCreation, orFail

    doFinalizePageCreation=(responseFromServer)->
      hideNotificationFunction()
      console.log "afterCreatePageCalled data:" ,responseFromServer   if window.console and console.log
      pageId=responseFromServer.page.id
      if pageId
        pageObj=topScope.app.getPageInPageTree($scope.infoForPageCreation.parentPage.id)
        topScope.editPage(pageId)
        topScope.app.pageTreeRef.load_node pageObj, (a,b)->
          console.log "node loaded" , a,b  if window.console and console.log
          topScope.selectPage(pageId)


    main()


