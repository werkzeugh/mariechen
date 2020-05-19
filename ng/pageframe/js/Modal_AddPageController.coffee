define ->
  angular.module('coreModule').registerController 'Modal_AddPageController', ($scope, params,close)->
    console.log "Modal_AddPageController inited" , null  if window.console and console.log
    

    $scope.close = (clickedOK)->
      if clickedOK
        result=
          status:'ok'
          pageData:$scope.model
      else
        result=
          status:'cancelled'

      close result, 500
      

    $scope.params=params

    if params.insertData 
      $scope.model=$scope.params.insertData
    else
      $scope.model=
        Title: "new Page"

