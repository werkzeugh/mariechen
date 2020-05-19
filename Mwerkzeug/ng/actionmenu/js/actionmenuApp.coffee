angular.module("actionmenuApp", ['ui.bootstrap'])


angular.module("actionmenuApp").directive "actionmenuItems", ->
  restrict: "A"
  replace:true
  templateUrl: "/Mwerkzeug/ng/actionmenu/partials/actionmenu-items.html"
  scope: 
    pageId: '@'
  controller: ["$scope", "$element", "$attrs", "$filter", "$q", "$http", ($scope, $element, $attrs, $filter, $q, $http ) ->

    $scope.menuItems=[]
    $scope.topWindow=window.top
    $scope.app=$scope.topWindow.app

    console.log "directive actionmenuItems Loaded, app=" , $scope.app  if window.console and console.log

    $scope.loadMenuItems=()->
      $http.post('/BE/Pages/ng_pagemanager/actionmenuItemsForPage',{'id':$scope.pageId,'clipboard':$scope.app.clipboard}).success (data)->
        if data.status is 'ok'
          $scope.menuItems=data.payload
          console.log "got menuitems" , $scope.menuItems  if window.console and console.log

    $scope.menuitemClick=(menuItem)->
      console.log "menuitemClick" , menuItem  if window.console and console.log
      $scope.callTopWindowAngularFunction('runPageAction',{pageId:$scope.pageId,menuItem:menuItem})

    $scope.callTopWindowAngularFunction=(functionName, data)->
      topWindow=window.parent
      promise=topWindow.callAngularFunction(functionName,data)


    $scope.loadMenuItems()

  ]


