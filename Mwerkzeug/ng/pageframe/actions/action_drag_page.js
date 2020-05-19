define(function() {
  return angular.module('coreModule').registerController('action_drag_page_controller', function($scope, topScope, params) {
    if (!topScope.app.markPageForDragging) {
      topScope.app.markPageForDragging = function(pageId) {
        var icon, pageObj;
        pageObj = topScope.app.getPageInPageTree(pageId);
        icon = topScope.app.pageTreeRef.get_icon(pageObj);
        icon = icon.replace(' ani-buzz-out ', ' ');
        return topScope.app.pageTreeRef.set_icon(pageObj, icon + ' draggable ani-buzz ');
      };
    }
    if (!topScope.app.unmarkPageForDragging) {
      topScope.app.unmarkPageForDragging = function(pageId) {
        var icon, pageObj;
        pageObj = topScope.app.getPageInPageTree(pageId);
        icon = topScope.app.pageTreeRef.get_icon(pageObj);
        icon = icon.replace(' draggable ani-buzz ', '');
        return topScope.app.pageTreeRef.set_icon(pageObj, icon);
      };
    }
    if (topScope.app.lastPageIdMarkedForDragging) {
      topScope.app.unmarkPageForDragging(topScope.app.lastPageIdMarkedForDragging);
      topScope.app.lastPageIdMarkedForDragging = null;
    }
    topScope.app.markPageForDragging(params.pageId);
    return topScope.app.lastPageIdMarkedForDragging = params.pageId;
  });
});
