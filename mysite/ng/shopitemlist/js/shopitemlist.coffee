shopitemlistApp=angular.module("shopitemlist", [])

shopitemlistApp.controller "AppController", ($scope, $attrs)->
  $scope.app=
    settings:{}
    selectedShopItem:null
    currentAmount:''

  $scope.init=(extraSettings)->
    console.log "init" , extraSettings  if window.console and console.log
    if extraSettings
      angular.extend $scope.app.settings, extraSettings
    if extraSettings?.query
      angular.extend $scope.app.query, extraSettings.query

    if $scope.app.settings.shopItems
      $scope.app.selectedShopItem=$scope.app.settings.shopItems[0]
      $scope.app.currentAmount=1






shopitemlistApp.directive "shopitemList", ->
  restrict: "AE"
  replace: true
  templateUrl: "/mysite/ng/shopitemlist/partials/shopitemlist.html"
  controller: ["$scope", "$element", "$attrs", "$filter", "$q", "$http", "$timeout", ($scope, $element, $attrs, $filter, $q, $http, $timeout ) ->
      console.log "shopitemlist called" , $scope  if window.console and console.log
      

      # $scope.$watch 'app.selectedShopItem', ->
      #   $scope.app.currentAmount=''
    
      $scope.chooseItem=(item)->
        if $scope.app.chooseMode 
          $scope.app.selectedShopItem=item
          $scope.app.chooseMode=false
          $scope.app.currentAmount=1



  ]

shopitemlistApp.filter('range', ->
  (input, min, max) ->
    input.push(i) for i in [parseInt(min,10)..parseInt(max,10)]
    return input
)


trusted = {}
shopitemlistApp.filter 'trusted',['$sce', ($sce)->
        return (html)->
          return trusted[html] || (trusted[html] = $sce.trustAsHtml(html));
]


