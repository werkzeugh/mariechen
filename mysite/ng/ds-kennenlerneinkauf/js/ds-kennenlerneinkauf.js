var app = angular.module("DsKennenlerneinkauf",[]); // ['ngAnimate']);


app.controller("DsKennenlerneinkaufCtrl",function($scope,$location,$http) {

  $scope.myStatus='form';
  $scope.fdata={};
  
  $scope.setStatus=function(x)
  {
    $scope.myStatus=x;
  };
  
  $scope.status=function(key)
  {
     if (window.console && console.log) { console.log('checkstatus',key);  }
    return ($scope.myStatus===key);
  };


});



app.directive("dsKennenlerneinkauf", function() {
    return {
        restrict: "A",
        replace: true,
        transclude: true,
        templateUrl:'/mysite/ng/ds-kennenlerneinkauf/partials/ds-kennenlerneinkauf.html',
        scope:true,
        controller: ['$scope','$element', '$attrs', '$filter','$transclude','$http',
        function($scope, $element, $attrs, $filter, $transclude, $http) { 
        
          
          $scope.requestCode=function()
          {
             if (window.console && console.log) { console.log('fdata',$scope.fdata);  }
            
             var url='/PromoCode/ng_request_kennenlerncode';
             $http.post(url,$scope.fdata).then(function(response){
       
               if(response.data.status)
               {
                 $scope.setStatus(response.data.status);
               }
               
                if (window.console && console.log) { console.log('sc',$scope,response.data.status);  }
               
         
             });
          };
          
          
          
        }]
    };
});
