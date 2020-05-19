define(function() {
  return angular.module('coreModule').registerController('action_copy_page_controller', function($scope, topScope, params) {
    if (window.console && console.log) {
      console.log("action_copy_page_controller", params);
    }
    topScope.app.clipboard = [params.pageId];
    return topScope.showNotification('page copied');
  });
});
