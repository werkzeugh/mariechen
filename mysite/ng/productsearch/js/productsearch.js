var productsearchApp;

productsearchApp = angular.module("productsearch", []);

productsearchApp.controller("AppController", function($scope, $attrs) {
  $scope.app = {
    query: {
      filters: {
        category: {},
        brand: {}
      }
    },
    settings: {
      apiUrl: '/de/produkte/skier/ng_quicksearch'
    }
  };
  return $scope.init = function(extraSettings) {
    if (window.console && console.log) {
      console.log("init", extraSettings);
    }
    if (extraSettings) {
      angular.extend($scope.app.settings, extraSettings);
    }
    if (extraSettings != null ? extraSettings.query : void 0) {
      return angular.extend($scope.app.query, extraSettings.query);
    }
  };
});

productsearchApp.directive("productSearch", function() {
  return {
    restrict: "AE",
    replace: true,
    template: " <div ng-include=\"'/mysite/ng/productsearch/partials/productsearch.html'\"></div>",
    controller: [
      "$scope", "$element", "$attrs", "$filter", "$q", "$http", "$timeout", function($scope, $element, $attrs, $filter, $q, $http, $timeout) {
        $scope.app.itemlist = {
          page: 'all',
          items: [],
          listStatus: 'new'
        };
        $scope.doSearch = function() {
          $scope.app.itemlist.listStatus = 'loading';
          return $http.post('/home/products/ng_quicksearch', {
            query: $scope.app.query
          }).success(function(data) {
            if (data.status === 'ok') {
              angular.extend($scope.app.itemlist, data.payload);
              if ($scope.app.itemlist.items.length) {
                return $scope.app.itemlist.listStatus = 'loaded';
              } else {
                return $scope.app.itemlist.listStatus = 'empty';
              }
            }
          });
        };
        return $scope.$watch('app.query.keyword', function(val) {
          return $scope.doSearch();
        });
      }
    ]
  };
});
