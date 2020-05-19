productsearchApp=angular.module("productsearch", [])

productsearchApp.controller "AppController", ($scope, $attrs)->
  $scope.app=
    query:
      filters:
        category:{}
        brand:{}
    settings:
      apiUrl:'/de/produkte/skier/ng_quicksearch'

  $scope.init=(extraSettings)->
    console.log "init" , extraSettings  if window.console and console.log
    if extraSettings
      angular.extend $scope.app.settings, extraSettings
    if extraSettings?.query
      angular.extend $scope.app.query, extraSettings.query

 
productsearchApp.directive "productSearch", ->
  restrict: "AE"
  replace: true
  template: " <div ng-include=\"'/mysite/ng/productsearch/partials/productsearch.html'\"></div>"
  controller: ["$scope", "$element", "$attrs", "$filter", "$q", "$http", "$timeout", ($scope, $element, $attrs, $filter, $q, $http, $timeout ) ->
     
      $scope.app.itemlist=
        page:'all'
        items:[]
        listStatus:'new'

      $scope.doSearch=->
        $scope.app.itemlist.listStatus='loading'
        $http.post('/home/products/ng_quicksearch',{query:$scope.app.query}).success (data)->
            if data.status is 'ok'
              angular.extend($scope.app.itemlist,data.payload)
              if $scope.app.itemlist.items.length
                $scope.app.itemlist.listStatus='loaded'
              else
                $scope.app.itemlist.listStatus='empty'

      $scope.$watch 'app.query.keyword', (val)->
        $scope.doSearch()


  ]




