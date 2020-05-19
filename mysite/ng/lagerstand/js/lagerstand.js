var lagerstandApp;

lagerstandApp = angular.module("lagerstand", ['ui.bootstrap']);

angular.module("lagerstand").controller("lagerstandMainCtrl", function($scope, $http, $location) {
  $scope.app = {
    test: Date.now()
  };
  $scope.query = {
    keyword: $location.hash()
  };
  $scope.items = [];
  $scope.searchTerms = [];
  $scope.listStatus = 'new';
  if (window.console && console.log) {
    console.log("kw", $scope.query);
  }
  $scope.loadProducts = function() {
    $location.hash($scope.query.keyword);
    if ($scope.query.keyword) {
      $scope.listStatus = 'loading';
      return $http.post('/BE/Lagerstand/ng_products', {
        query: $scope.query
      }).then(function(res) {
        if (res.data && res.data.status === "ok") {
          $scope.items = res.data.items;
          $scope.searchTerms = res.data.searchTerms;
          if ($scope.items.length === 0) {
            return $scope.listStatus = 'empty';
          } else {
            return $scope.listStatus = 'loaded';
          }
        }
      });
    }
  };
  $scope.saveValue = function(i, si) {
    if (window.console && console.log) {
      console.log("saveValue", si);
    }
    return $http.post('/BE/Lagerstand/ng_update_shopitem', {
      product_id: i.id,
      variant_id: si.id,
      newvalues: {
        InStock: si.InStock
      }
    }).success(function(data) {
      if (data && data.status === "ok") {
        return si.saved = 1;
      }
    });
  };
  return $scope.loadProducts();
});

lagerstandApp.directive("ngEnter", function() {
  return function(scope, element, attrs) {
    element.bind("keydown keypress", function(event) {
      if (event.which === 13) {
        scope.$apply(function() {
          scope.$eval(attrs.ngEnter);
        });
        event.preventDefault();
      }
    });
  };
});

lagerstandApp.filter("highlight", function($sce) {
  return function(str, termsToHighlight) {
    var regex;
    termsToHighlight.sort(function(a, b) {
      return b.length - a.length;
    });
    regex = new RegExp("(" + termsToHighlight.join("|") + ")", "gi");
    if (str) {
      return $sce.trustAsHtml(str.replace(regex, "<span class=\"match\">$&</span>"));
    } else {
      return $sce.trustAsHtml(str);
    }
  };
});
