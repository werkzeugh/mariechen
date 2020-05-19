define ->
  angular.module('coreModule').registerController 'action_delete_page_controller', ($scope, topScope, params,$rootScope)->
 
    console.log "action_delete_page_controller:" , params  if window.console and console.log

    hideNotificationFunction=null
    orFail=topScope.app.failPopup
    params.topScope=topScope
    commandArgs=
      pageId:params.pageId

    main=->
      topScope.callPageManager('getNodeData',commandArgs).then doShowModal, orFail


    doShowModal=(nodeData)->
      params.page=nodeData
      topScope.app.showModal('DeletePage',params).then doDeletePage

    doDeletePage=(data)->
      if data is 'ok'
        hideNotificationFunction=topScope.showNotification 'please wait <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">',0

        topScope.callPageManager('deletePage',commandArgs).then doFinalizePageDeletion, orFail

    doFinalizePageDeletion=(responseFromServer)->
      hideNotificationFunction()
      treeref=topScope.app.pageTreeRef
      if treeref
        ret=treeref.delete_node params.pageId
      else 
        window.top.location.reload()


    main()


