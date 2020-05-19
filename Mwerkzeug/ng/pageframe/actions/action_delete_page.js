define(function() {
  return angular.module('coreModule').registerController('action_delete_page_controller', function($scope, topScope, params, $rootScope) {
    var commandArgs, doDeletePage, doFinalizePageDeletion, doShowModal, hideNotificationFunction, main, orFail;
    if (window.console && console.log) {
      console.log("action_delete_page_controller:", params);
    }
    hideNotificationFunction = null;
    orFail = topScope.app.failPopup;
    params.topScope = topScope;
    commandArgs = {
      pageId: params.pageId
    };
    main = function() {
      return topScope.callPageManager('getNodeData', commandArgs).then(doShowModal, orFail);
    };
    doShowModal = function(nodeData) {
      params.page = nodeData;
      return topScope.app.showModal('DeletePage', params).then(doDeletePage);
    };
    doDeletePage = function(data) {
      if (data === 'ok') {
        hideNotificationFunction = topScope.showNotification('please wait <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">', 0);
        return topScope.callPageManager('deletePage', commandArgs).then(doFinalizePageDeletion, orFail);
      }
    };
    doFinalizePageDeletion = function(responseFromServer) {
      var ret, treeref;
      hideNotificationFunction();
      treeref = topScope.app.pageTreeRef;
      if (treeref) {
        return ret = treeref.delete_node(params.pageId);
      } else {
        return window.top.location.reload();
      }
    };
    return main();
  });
});
