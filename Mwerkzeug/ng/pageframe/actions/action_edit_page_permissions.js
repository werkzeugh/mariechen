define(function() {
  return angular.module('coreModule').registerController('action_edit_page_permissions_controller', function($scope, topScope, params, $rootScope) {
    var main;
    main = function() {
      var url;
      url = '/BE/Pages/edit/' + params.pageId + '/99_C4P_Place_PagePermissions';
      return window.open(url, 'rightframe');
    };
    return main();
  });
});
