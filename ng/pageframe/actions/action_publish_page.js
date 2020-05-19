define(function() {
  return angular.module('coreModule').registerController('action_update_page_controller', function($scope, topScope, params, $rootScope) {
    var doFinalizePageUpdate, main;
    main = function() {
      ({
        commandArgs: {
          pageData: {
            Hidden: 0
          }
        }
      });
      return topScope.callPageManager('updatePage', commandArgs).then(doFinalizePageUpdate, orFail);
    };
    doFinalizePageUpdate = function(responseFromServer) {
      var ret;
      return ret = topScope.app.pageTreeRef.delete_node(params.pageId);
    };
    return main();
  });
});
