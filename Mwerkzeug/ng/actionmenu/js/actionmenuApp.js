angular.module("actionmenuApp", ['ui.bootstrap']);

angular.module("actionmenuApp").directive("actionmenuItems", function() {
  return {
    restrict: "A",
    replace: true,
    templateUrl: "/Mwerkzeug/ng/actionmenu/partials/actionmenu-items.html",
    scope: {
      pageId: '@'
    },
    controller: [
      "$scope", "$element", "$attrs", "$filter", "$q", "$http", function($scope, $element, $attrs, $filter, $q, $http) {
        $scope.menuItems = [];
        $scope.topWindow = window.top;
        $scope.app = $scope.topWindow.app;
        if (window.console && console.log) {
          console.log("directive actionmenuItems Loaded, app=", $scope.app);
        }
        $scope.loadMenuItems = function() {
          return $http.post('/BE/Pages/ng_pagemanager/actionmenuItemsForPage', {
            'id': $scope.pageId,
            'clipboard': $scope.app.clipboard
          }).success(function(data) {
            if (data.status === 'ok') {
              $scope.menuItems = data.payload;
              if (window.console && console.log) {
                return console.log("got menuitems", $scope.menuItems);
              }
            }
          });
        };
        $scope.menuitemClick = function(menuItem) {
          if (window.console && console.log) {
            console.log("menuitemClick", menuItem);
          }
          return $scope.callTopWindowAngularFunction('runPageAction', {
            pageId: $scope.pageId,
            menuItem: menuItem
          });
        };
        $scope.callTopWindowAngularFunction = function(functionName, data) {
          var promise, topWindow;
          topWindow = window.parent;
          return promise = topWindow.callAngularFunction(functionName, data);
        };
        return $scope.loadMenuItems();
      }
    ]
  };
});
