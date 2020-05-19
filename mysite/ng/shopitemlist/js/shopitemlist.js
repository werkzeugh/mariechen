var shopitemlistApp, trusted;

shopitemlistApp = angular.module("shopitemlist", []);

shopitemlistApp.controller("AppController", function($scope, $attrs) {
  $scope.app = {
    settings: {},
    selectedShopItem: null,
    currentAmount: ''
  };
  return $scope.init = function(extraSettings) {
    if (window.console && console.log) {
      console.log("init", extraSettings);
    }
    if (extraSettings) {
      angular.extend($scope.app.settings, extraSettings);
    }
    if (extraSettings != null ? extraSettings.query : void 0) {
      angular.extend($scope.app.query, extraSettings.query);
    }
    if ($scope.app.settings.shopItems) {
      $scope.app.selectedShopItem = $scope.app.settings.shopItems[0];
      return $scope.app.currentAmount = 1;
    }
  };
});

shopitemlistApp.directive("shopitemList", function() {
  return {
    restrict: "AE",
    replace: true,
    templateUrl: "/mysite/ng/shopitemlist/partials/shopitemlist.html",
    controller: [
      "$scope", "$element", "$attrs", "$filter", "$q", "$http", "$timeout", function($scope, $element, $attrs, $filter, $q, $http, $timeout) {
        if (window.console && console.log) {
          console.log("shopitemlist called", $scope);
        }
        return $scope.chooseItem = function(item) {
          if ($scope.app.chooseMode) {
            $scope.app.selectedShopItem = item;
            $scope.app.chooseMode = false;
            return $scope.app.currentAmount = 1;
          }
        };
      }
    ]
  };
});

shopitemlistApp.filter('range', function() {
  return function(input, min, max) {
    var i, _i, _ref, _ref1;
    for (i = _i = _ref = parseInt(min, 10), _ref1 = parseInt(max, 10); _ref <= _ref1 ? _i <= _ref1 : _i >= _ref1; i = _ref <= _ref1 ? ++_i : --_i) {
      input.push(i);
    }
    return input;
  };
});

trusted = {};

shopitemlistApp.filter('trusted', [
  '$sce', function($sce) {
    return function(html) {
      return trusted[html] || (trusted[html] = $sce.trustAsHtml(html));
    };
  }
]);
