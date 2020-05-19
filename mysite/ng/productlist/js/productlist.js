var productListApp;

productListApp = angular.module("productlist", []);

productListApp.controller("AppController", function($scope, $attrs) {
  $scope.app = {
    query: {
      filters: {
        category: {},
        brand: {}
      }
    },
    settings: {}
  };
  $scope.init = function(extraSettings) {
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
  $scope.app.searchFormSubmit = function(page) {
    if (window.console && console.log) {
      console.log("submit click", null);
    }
    return $scope.$broadcast("searchFormSubmit", page);
  };
  $scope.app.applyFilters = function() {
    if (window.console && console.log) {
      console.log("applyFilters", null);
    }
    return $scope.$broadcast("applyFilters");
  };
  $scope.app.toggle = function(clickedObj, query) {
    var newval;
    if (window.console && console.log) {
      console.log("toggle", clickedObj, query);
    }
    newval = !query[clickedObj.key];
    query[clickedObj.key] = newval;
    if (clickedObj.items) {
      angular.forEach(clickedObj.items, function(subItem) {
        return $scope.app.query.filters[clickedObj.key][subItem.key] = newval;
      });
    }
    return $scope.app.applyFilters();
  };
  return $scope.app.toggleAllFilterValues = function(filter) {
    var newval;
    newval = 'n/a';
    angular.forEach(filter.items, function(filterValue) {
      if (newval === 'n/a') {
        newval = $scope.app.query.filters[filter.key][filterValue.key] ? false : true;
      }
      return $scope.app.query.filters[filter.key][filterValue.key] = newval;
    });
    return $scope.app.applyFilters();
  };
});

productListApp.directive("productList", function() {
  return {
    restrict: "AE",
    replace: true,
    templateUrl: "/mysite/ng/productlist/partials/productlist.html",
    controller: [
      "$scope", "$element", "$attrs", "$filter", "$q", "$http", "$timeout", function($scope, $element, $attrs, $filter, $q, $http, $timeout) {
        var filterFilter, pageChange;
        if (window.console && console.log) {
          console.log("productlist called", $scope);
        }
        $scope.app.itemlist = {
          page: 'all',
          items: [],
          allItems: null,
          filteredItems: [],
          pagination: {
            items_per_page: 200
          },
          listStatus: 'new'
        };
        $scope.recalcPagination = function(mylist) {
          mylist.pagination.total_items = mylist.filteredItems.length;
          if (mylist.pagination.total_items === 0) {
            return mylist.listStatus = 'empty';
          } else {
            return mylist.listStatus = 'loaded';
          }
        };
        filterFilter = $filter('filter');
        $scope.applyFilters = function(mylist, filters) {
          var filtersInUse, tempList;
          if (window.console && console.log) {
            console.log("applyFilters", mylist, filters);
          }
          filtersInUse = 0;
          angular.forEach(filters, function(filterValues, filterKey) {
            return angular.forEach(filterValues, function(filterValueIsActive, filterValueName) {
              if (filterValueIsActive) {
                return filtersInUse++;
              }
            });
          });
          if (window.console && console.log) {
            console.log("filtersInUse", filtersInUse);
          }
          if (filtersInUse === 0) {
            mylist.filteredItems = mylist.allItems;
            return;
          }
          tempList = [];
          angular.forEach(mylist.allItems, function(item, key) {
            var useItem;
            useItem = false;
            angular.forEach(filters, function(filterValues, filterKey) {
              return angular.forEach(filterValues, function(filterValueIsActive, filterValueName) {
                if (filterValueIsActive && item[filterKey] === filterValueName) {
                  return useItem = true;
                }
              });
            });
            if (useItem) {
              return tempList.push(item);
            }
          });
          mylist.filteredItems = tempList;
          if (window.console && console.log) {
            return console.log("mylist.filteredItems", mylist.filteredItems.length);
          }
        };
        pageChange = function(newPage, lastPage) {
          var begin, end, mylist;
          mylist = $scope.app.itemlist;
          if (newPage === lastPage) {
            return;
          }
          if (window.console && console.log) {
            console.log("pageChange", "" + lastPage + " ➜ " + newPage);
          }
          mylist.pagination.page = newPage;
          begin = (mylist.pagination.page - 1) * mylist.pagination.items_per_page;
          end = begin + mylist.pagination.items_per_page;
          if (window.console && console.log) {
            console.log("slice", begin, end);
          }
          return mylist.items = mylist.filteredItems.slice(begin, end);
        };
        $scope.$watch("app.itemlist.page", pageChange);
        $scope.doApplyFilters = function() {
          $scope.applyFilters($scope.app.itemlist, $scope.app.query.filters);
          $scope.recalcPagination($scope.app.itemlist, 1);
          return pageChange(1, 0);
        };
        $scope.$on('applyFilters', function(event) {
          return $scope.doApplyFilters();
        });
        $scope.$on('searchFormSubmit', function(event, page) {
          return $scope.doSearchFormSubmit(page);
        });
        $scope.doSearchFormSubmit = function(page) {
          if (window.console && console.log) {
            console.log("do call ", $scope.app.settings.baseurl);
          }
          $scope.app.itemlist.page = 'all';
          $scope.app.itemlist.listStatus = 'loading';
          return $http.post($scope.app.settings.baseurl + 'ng_items', {
            query: $scope.app.query
          }).success(function(data) {
            if (data.status === 'ok') {
              angular.extend($scope.app.itemlist, data.payload);
              $scope.applyFilters($scope.app.itemlist, $scope.app.query.filters);
              $scope.recalcPagination($scope.app.itemlist);
              $scope.app.itemlist.page = 1;
              $timeout(function() {
                return $scope.app.processSameHeights();
              }, 100);
              return $timeout(function() {
                return $scope.app.processSameHeights();
              }, 2000);
            }
          });
        };
        if (window.console && console.log) {
          console.log("initial call", null);
        }
        $scope.doSearchFormSubmit(0);
        return $scope.app.processSameHeights = function() {
          if (window.console && console.log) {
            console.log("processSameHeights", null);
          }
          return jQuery('.products-item').matchHeight();
        };
      }
    ]
  };
});
