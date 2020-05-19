define ->
  angular.module('coreModule').registerController 'action_update_page_controller', ($scope, topScope, params)->

    console.log "action_update_page_controller" , params  if window.console and console.log

    hideNotificationFunction=null

    main=->
      hideNotificationFunction=topScope.showNotification 'please wait <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">',0
      commandArgs=
        pageId:params.pageId
        pageData: params.args
      topScope.callPageManager('updatePage',commandArgs).then doFinalizePageUpdate, topScope.app.failPopup



    doFinalizePageUpdate=(responseFromServer)->
      hideNotificationFunction()
      treeref=topScope.app.pageTreeRef
      if treeref
        topScope.reloadNodeInPageTree params.pageId
      else 
        window.top.location.reload()



    main()




