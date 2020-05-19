define(function() {
  return angular.module('coreModule').registerController('action_edit_page_controller', function($scope, topScope, params, $rootScope) {
    var doFinalizePageEdit, doShowModal, doUpdatePage, hideNotificationFunction, main, orFail;
    if (window.console && console.log) {
      console.log("action_edit_page_controller:", params);
    }
    orFail = topScope.app.failPopup;
    params.topScope = topScope;
    hideNotificationFunction = null;
    if (!params.args) {
      params.args = {
        mode: 'edit'
      };
    }
    main = function() {
      var commandArgs;
      commandArgs = angular.extend({}, params.args);
      commandArgs.pageId = params.pageId;
      return topScope.callPageManager('getInfoForPageCreation', commandArgs).then(doShowModal, orFail);
    };
    doShowModal = function(infoForPageCreation) {
      $scope.infoForPageCreation = infoForPageCreation;
      $rootScope.$broadcast("$routeChangeStart");
      return topScope.app.showModal('EditPageSettings', angular.extend(params, infoForPageCreation)).then(doUpdatePage);
    };
    doUpdatePage = function(data) {
      var commandArgs;
      commandArgs = {
        pageId: params.pageId,
        pageData: data.pageData
      };
      hideNotificationFunction = topScope.showNotification('please wait <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">', 0);
      return topScope.callPageManager('updatePage', commandArgs).then(doFinalizePageEdit, orFail);
    };
    doFinalizePageEdit = function(responseFromServer) {
      var pageId, pageObj, treeref;
      hideNotificationFunction();
      pageId = params.pageId;
      treeref = topScope.app.pageTreeRef;
      if (treeref) {
        if (pageId) {
          pageObj = topScope.app.getPageInPageTree(pageId);
          topScope.editPage(pageId);
          return topScope.app.pageTreeRef.load_node(pageObj, function(a, b) {
            if (window.console && console.log) {
              console.log("node loaded", a, b);
            }
            return topScope.selectPage(pageId);
          });
        }
      } else {
        return window.top.location.reload();
      }
    };
    return main();
  });
});
