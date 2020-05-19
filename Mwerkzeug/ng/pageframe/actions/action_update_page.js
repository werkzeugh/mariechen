define(function() {
  return angular.module('coreModule').registerController('action_update_page_controller', function($scope, topScope, params) {
    var doFinalizePageUpdate, hideNotificationFunction, main;
    if (window.console && console.log) {
      console.log("action_update_page_controller", params);
    }
    hideNotificationFunction = null;
    main = function() {
      var commandArgs;
      hideNotificationFunction = topScope.showNotification('please wait <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">', 0);
      commandArgs = {
        pageId: params.pageId,
        pageData: params.args
      };
      return topScope.callPageManager('updatePage', commandArgs).then(doFinalizePageUpdate, topScope.app.failPopup);
    };
    doFinalizePageUpdate = function(responseFromServer) {
      var treeref;
      hideNotificationFunction();
      treeref = topScope.app.pageTreeRef;
      if (treeref) {
        return topScope.reloadNodeInPageTree(params.pageId);
      } else {
        return window.top.location.reload();
      }
    };
    return main();
  });
});
