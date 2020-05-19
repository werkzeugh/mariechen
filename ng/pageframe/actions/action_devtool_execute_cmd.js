define(function() {
  return angular.module('coreModule').registerController('action_devtool_execute_cmd_controller', function($scope, topScope, params, $rootScope) {
    var doShowModal, main, orFail;
    if (window.console && console.log) {
      console.log("action_devtool_execute_cmd_controller:", params);
    }
    orFail = topScope.app.failPopup;
    params.topScope = topScope;
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
      return topScope.app.showModal('DevToolExecuteCmd', angular.extend(params, infoForPageCreation));
    };
    return main();
  });
});
