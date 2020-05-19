define(function() {
  return angular.module('coreModule').registerController('action_move_page_controller', function($scope, topScope, params) {
    var fail, hideNotificationFunction, icon, moveInfo, pageObj, success;
    pageObj = topScope.app.getPageInPageTree(params.pageId);
    icon = topScope.app.pageTreeRef.get_icon(pageObj);
    topScope.app.pageTreeRef.set_icon(pageObj, icon.replace(' draggable ani-buzz ', ' ani-buzz-out '));
    moveInfo = params.moveInfo;
    if (moveInfo.newparent === '#') {
      moveInfo.newPage = 0;
    }
    hideNotificationFunction = topScope.showNotification('please wait <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">', 0);
    success = function(response) {
      if (window.console && console.log) {
        console.log("response success", response);
      }
      params.defered.resolve({
        success: true,
        response: response
      });
      return hideNotificationFunction();
    };
    fail = function(response) {
      hideNotificationFunction();
      if (window.console && console.log) {
        console.log("response failed", response);
      }
      topScope.showNotification(response.msg);
      return params.defered.resolve({
        success: false,
        response: response
      });
    };
    return topScope.callPageManager('movePage', {
      pageId: params.pageId,
      moveInfo: moveInfo
    }).then(success, fail);
  });
});
