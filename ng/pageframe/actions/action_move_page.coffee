define ->
  angular.module('coreModule').registerController 'action_move_page_controller', ($scope, topScope, params)->
    
    pageObj=topScope.app.getPageInPageTree(params.pageId)

    icon=topScope.app.pageTreeRef.get_icon(pageObj)
    topScope.app.pageTreeRef.set_icon(pageObj,icon.replace(' draggable ani-buzz ',' ani-buzz-out '))

    moveInfo=params.moveInfo
    if moveInfo.newparent is '#'
      moveInfo.newPage = 0

    

    hideNotificationFunction=topScope.showNotification 'please wait <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">',0

    success=(response)->
      console.log "response success" , response  if window.console and console.log
      params.defered.resolve {success:true,response:response}
      hideNotificationFunction()

    fail=(response)->
      hideNotificationFunction()  
      console.log "response failed" , response  if window.console and console.log
      topScope.showNotification response.msg
      params.defered.resolve {success:false,response:response}

    topScope.callPageManager('movePage',{pageId:params.pageId,moveInfo}).then(success,fail)

