productListApp=angular.module("productlist", [])

productListApp.controller "AppController", ($scope, $attrs)->
  $scope.app=
    query:
      filters:
        category:{}
        brand:{}
    settings:{}

  $scope.init=(extraSettings)->
    console.log "init" , extraSettings  if window.console and console.log
    if extraSettings
      angular.extend $scope.app.settings, extraSettings
    if extraSettings?.query
      angular.extend $scope.app.query, extraSettings.query

  $scope.app.searchFormSubmit=(page)->
    console.log "submit click" , null  if window.console and console.log
    $scope.$broadcast "searchFormSubmit", page

  $scope.app.applyFilters=()->
    console.log "applyFilters" , null  if window.console and console.log
    $scope.$broadcast "applyFilters"
    

  $scope.app.toggle= (clickedObj,query)->
    console.log "toggle" , clickedObj,query  if window.console and console.log
    newval=!(query[clickedObj.key])
    query[clickedObj.key]=newval

    if clickedObj.items
      angular.forEach clickedObj.items, (subItem) ->
        $scope.app.query.filters[clickedObj.key][subItem.key]=newval

    $scope.app.applyFilters()


  $scope.app.toggleAllFilterValues=(filter) ->
    newval='n/a'
    angular.forEach filter.items, (filterValue) ->
      if newval is 'n/a' 
        newval= if $scope.app.query.filters[filter.key][filterValue.key] then false else true
      $scope.app.query.filters[filter.key][filterValue.key]=newval

    $scope.app.applyFilters()


productListApp.directive "productList", ->
  restrict: "AE"
  replace: true
  templateUrl: "/mysite/ng/productlist/partials/productlist.html"
  controller: ["$scope", "$element", "$attrs", "$filter", "$q", "$http", "$timeout", ($scope, $element, $attrs, $filter, $q, $http, $timeout ) ->
      console.log "productlist called" , $scope  if window.console and console.log
     
      $scope.app.itemlist=
        page:'all'
        items:[]
        allItems:null
        filteredItems:[]
        pagination:
          items_per_page:200
        listStatus:'new'

      $scope.recalcPagination = (mylist)->
        mylist.pagination.total_items=mylist.filteredItems.length
        if mylist.pagination.total_items is 0 
          mylist.listStatus='empty'
        else
          mylist.listStatus='loaded'

    
      filterFilter=$filter('filter')
      $scope.applyFilters=(mylist,filters)->
        console.log "applyFilters" ,mylist,filters   if window.console and console.log

        filtersInUse=0
        angular.forEach filters, (filterValues, filterKey) ->
          angular.forEach filterValues, (filterValueIsActive, filterValueName) ->
            if filterValueIsActive
              filtersInUse++

        console.log "filtersInUse" , filtersInUse  if window.console and console.log
        if filtersInUse is 0
          mylist.filteredItems=mylist.allItems
          return 

        tempList=[]
        angular.forEach mylist.allItems, (item, key) ->
          useItem=false
          angular.forEach filters, (filterValues, filterKey) ->
            angular.forEach filterValues, (filterValueIsActive, filterValueName) ->
              if filterValueIsActive and item[filterKey] is filterValueName
                useItem=true

          if useItem 
            tempList.push item

        mylist.filteredItems=tempList
        console.log "mylist.filteredItems" , mylist.filteredItems.length  if window.console and console.log        

      pageChange = (newPage, lastPage) ->
        mylist=$scope.app.itemlist
        return  if newPage is lastPage
        console.log "pageChange" , "#{lastPage} ➜ #{newPage}" if window.console and console.log
        mylist.pagination.page=newPage
        begin = (mylist.pagination.page - 1) * mylist.pagination.items_per_page
        end = begin + mylist.pagination.items_per_page
        console.log "slice" , begin, end  if window.console and console.log
        mylist.items = mylist.filteredItems.slice(begin, end)

      $scope.$watch "app.itemlist.page", pageChange



      $scope.doApplyFilters=->
        $scope.applyFilters($scope.app.itemlist,$scope.app.query.filters)
        $scope.recalcPagination($scope.app.itemlist,1)
        pageChange(1,0)

      $scope.$on 'applyFilters', (event)->
        # console.log "received event" , event  if window.console and console.log
        $scope.doApplyFilters()

      $scope.$on 'searchFormSubmit', (event,page)->
        # console.log "received event" , event  if window.console and console.log
        $scope.doSearchFormSubmit(page)

      $scope.doSearchFormSubmit=(page)->
        console.log "do call " , $scope.app.settings.baseurl  if window.console and console.log
        $scope.app.itemlist.page='all'
        $scope.app.itemlist.listStatus='loading'

        $http.post($scope.app.settings.baseurl+'ng_items',{query:$scope.app.query}).success (data)->
          if data.status is 'ok'
            angular.extend($scope.app.itemlist,data.payload)
            $scope.applyFilters($scope.app.itemlist,$scope.app.query.filters)
            $scope.recalcPagination($scope.app.itemlist)
            $scope.app.itemlist.page=1

            $timeout( ->
              $scope.app.processSameHeights()
            ,100)

            $timeout( ->
              $scope.app.processSameHeights()
            ,2000)


      console.log "initial call" , null  if window.console and console.log
      $scope.doSearchFormSubmit(0) #load result list immediately
      

      $scope.app.processSameHeights= ->
        console.log "processSameHeights" , null  if window.console and console.log
        jQuery('.products-item').matchHeight()



  ]




