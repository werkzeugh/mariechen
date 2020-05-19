lagerstandApp=angular.module("lagerstand",  ['ui.bootstrap'])

angular.module("lagerstand").controller "lagerstandMainCtrl", ($scope,$http,$location)->
  $scope.app ={
    test:Date.now()
  }
  $scope.query=
    keyword:$location.hash()

  $scope.items=[]
  $scope.searchTerms=[]
  $scope.listStatus='new'

  console.log "kw" , $scope.query  if window.console and console.log

  $scope.loadProducts= ->
    $location.hash $scope.query.keyword

    if $scope.query.keyword
      $scope.listStatus='loading'
      $http.post('/BE/Lagerstand/ng_products', {query:$scope.query}).then (res) ->
        if res.data and res.data.status is "ok"
          $scope.items= res.data.items
          $scope.searchTerms=res.data.searchTerms
          if $scope.items.length is 0
            $scope.listStatus='empty'
          else
            $scope.listStatus='loaded'
          
          # $scope.mainListLoaded.resolve()

  $scope.saveValue=(i,si)->
    console.log "saveValue" , si  if window.console and console.log
    $http.post('/BE/Lagerstand/ng_update_shopitem',{product_id:i.id,variant_id:si.id,newvalues:{InStock:si.InStock}}).success (data) ->
      if data and data.status is "ok"
        si.saved=1

  $scope.loadProducts()

  # $scope.$watch 'query.keyword', (tmpStr)->
  #   console.log(tmpStr);
  #   if (!tmpStr || tmpStr.length == 0)
  #     return 0;
  #   # if searchStr is still the same..
  #   # go ahead and retrieve the data
  #   if tmpStr is $scope.query.keyword
  #     $scope.loadProducts()
  #   else
  #     console.log "noway" , null  if window.console and console.log
          



lagerstandApp.directive "ngEnter", ->
  (scope, element, attrs) ->
    element.bind "keydown keypress", (event) ->
      if event.which is 13
        scope.$apply ->
          scope.$eval attrs.ngEnter
          return

        event.preventDefault()
      return

    return

lagerstandApp.filter "highlight", ($sce) ->
  (str, termsToHighlight) ->
    
    # Sort terms by length
    termsToHighlight.sort (a, b) ->
      b.length - a.length
    
    # Regex to simultaneously replace terms
    regex = new RegExp("(" + termsToHighlight.join("|") + ")", "gi")
    if str
      return $sce.trustAsHtml str.replace(regex, "<span class=\"match\">$&</span>")
    else
      return $sce.trustAsHtml str


