app = angular.module("mwcart", []);




app.controller('MainCtrl', function ($scope, $location, $http, mwcartService) {

  $scope.tmp = {};
  $scope.app = {
    cart: null,
    test: 'open space'
  };

  mwcartService.getData().then(function (data) {
    $scope.app.cart = data;
  });


  $scope.app.hasPromocode = function () {

    if (typeof ($scope.app.cart) == 'undefined' || $scope.app.cart == null) {
      return 'undefined';
    }

    return ($scope.app.cart.promocode) ? 'yes' : 'no';
  };


  $scope.removePromoCode = function () {

    var url = $scope.settings.currentUrl + 'ng_remove_promocode';
    $http.post(url, $scope.tmp).then(function (response) {
      window.location.reload();
    });

  };

  $scope.submitForm = function () {

    $('#payload').val($('#cartform').serialize());
    $('#transportform').submit();

  };

  $scope.checkPromoCode = function () {

    var url = $scope.settings.currentUrl + 'ng_check_promocode';
    $http.post(url, $scope.tmp).then(function (response) {

      if (window.console && console.log) {
        console.log('res', response.data);
      }

      if (response.data.status == 'ok') {
        window.location.reload();
      } else {
        $scope.errormsg = response.data.msg;
      }

    });
  };


});



app.factory('mwcartService', function ($http) {

  $http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

  var mwcartService = {

    getData: function (id, standids) {
      var promise = $http.get('/de/cart/ng_cartdata/').then(function (response) {
        return response.data;
      });
      return promise;
    }

  };

  return mwcartService;
});
