define(function() {
  return angular.module('coreModule').registerController('action_create_page_controller', function($scope, topScope, params, $rootScope) {
    var doCreatePage, doFinalizePageCreation, doShowModal, hideNotificationFunction, main, orFail;
    if (window.console && console.log) {
      console.log("action_create_page_controller:", params);
    }
    orFail = topScope.app.failPopup;
    params.topScope = topScope;
    hideNotificationFunction = null;
    main = function() {
      var commandArgs;
      commandArgs = angular.extend({}, params.args);
      commandArgs.pageId = params.pageId;
      return topScope.callPageManager('getInfoForPageCreation', commandArgs).then(doShowModal, orFail);
    };
    doShowModal = function(infoForPageCreation) {
      $scope.infoForPageCreation = infoForPageCreation;
      $rootScope.$broadcast("$routeChangeStart");
      return topScope.app.showModal('EditPageSettings', angular.extend(params, infoForPageCreation)).then(doCreatePage);
    };
    doCreatePage = function(data) {
      var commandArgs;
      hideNotificationFunction = topScope.showNotification('please wait <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">', 0);
      commandArgs = {
        pageData: data.pageData,
        infoForPageCreation: $scope.infoForPageCreation
      };
      return topScope.callPageManager('createPage', commandArgs).then(doFinalizePageCreation, orFail);
    };
    doFinalizePageCreation = function(responseFromServer) {
      var pageId, pageObj;
      hideNotificationFunction();
      if (window.console && console.log) {
        console.log("afterCreatePageCalled data:", responseFromServer);
      }
      pageId = responseFromServer.page.id;
      if (pageId) {
        pageObj = topScope.app.getPageInPageTree($scope.infoForPageCreation.parentPage.id);
        topScope.editPage(pageId);
        return topScope.app.pageTreeRef.load_node(pageObj, function(a, b) {
          if (window.console && console.log) {
            console.log("node loaded", a, b);
          }
          return topScope.selectPage(pageId);
        });
      }
    };
    return main();
  });
});
