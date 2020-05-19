define ->
  angular.module('coreModule').registerController 'action_drag_page_controller', ($scope, topScope, params)->
    
    unless topScope.app.markPageForDragging
      topScope.app.markPageForDragging=(pageId)->
        pageObj=topScope.app.getPageInPageTree(pageId)
        icon=topScope.app.pageTreeRef.get_icon(pageObj)
        icon=icon.replace(' ani-buzz-out ',' ')
        topScope.app.pageTreeRef.set_icon(pageObj,icon+' draggable ani-buzz ')

    unless topScope.app.unmarkPageForDragging
      topScope.app.unmarkPageForDragging=(pageId)->
        pageObj=topScope.app.getPageInPageTree(pageId)
        icon=topScope.app.pageTreeRef.get_icon(pageObj)
        icon=icon.replace(' draggable ani-buzz ','')
        topScope.app.pageTreeRef.set_icon(pageObj,icon)

    if topScope.app.lastPageIdMarkedForDragging
      topScope.app.unmarkPageForDragging(topScope.app.lastPageIdMarkedForDragging)
      topScope.app.lastPageIdMarkedForDragging=null

    topScope.app.markPageForDragging(params.pageId)
    topScope.app.lastPageIdMarkedForDragging=params.pageId




