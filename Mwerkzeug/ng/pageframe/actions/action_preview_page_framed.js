define(function() {
  return angular.module('coreModule').registerController('action_preview_page_framed_controller', function($scope, topScope, params, $rootScope) {
    var main;
    main = function() {
      var url;
      url = '/BE/Pages/show/495';
      return window.open(url, 'rightframe');
    };
    return main();
  });
});
