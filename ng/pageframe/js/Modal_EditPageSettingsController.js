define(function() {
  return angular.module('coreModule').registerController('Modal_EditPageSettingsController', function($scope, params, close, $element, $timeout, validationService, $q, $rootScope) {
    var myValidation;
    if (window.console && console.log) {
      console.log("Modal_EditPageSettingsController inited, params:", params);
    }
    $scope.submitForm = function() {
      if ($scope.form1.$invalid) {
        $scope.errorVisible = 1;
        if (window.console && console.log) {
          console.log("cannot submit form, because its invalid", null);
        }
        return $timeout(function() {
          return $scope.errorVisible = 0;
        }, 2000);
      } else {
        return $scope.close({
          pageData: $scope.model
        });
      }
    };
    $scope.slugRemoteValidationCheck = function() {
      var commandArgs, deferred;
      deferred = $q.defer();
      if ($scope.customURLSegment) {
        commandArgs = {
          slug: $scope.model.URLSegment,
          parentId: params.parentPage.id
        };
        if (params.args.mode === 'edit' || params.args.mode === 'rename') {
          commandArgs.skipId = params.pageId;
        }
        if (window.console && console.log) {
          console.log("slugRemoteValidationCheck", commandArgs, $scope.model);
        }
        params.topScope.callPageManager('checkSlug', commandArgs).then(function(payload) {
          return deferred.resolve(payload);
        });
      } else {
        deferred.resolve(true);
      }
      return deferred.promise;
    };
    $scope.params = params;
    $scope.availableClassNames = params.allowedClassNames;
    if (params.args.mode === 'duplicate') {
      $scope.customURLSegment = true;
    } else {
      $scope.customURLSegment = false;
    }
    $scope.$watch('customURLSegment', function(val) {
      $scope.updateURLSegment();
      if (val) {
        $scope.blurAndFocus('page_URLSegment');
        return $scope.submitCount = 0;
      }
    });
    $scope.$watch('model.Title', function(val) {
      return $scope.updateURLSegment();
    });
    $scope.$watch('model.MenuTitle', function(val) {
      return $scope.updateURLSegment();
    });
    $scope.updateURLSegment = function() {
      var title;
      if (!$scope.customURLSegment) {
        title = $scope.model.MenuTitle ? $scope.model.MenuTitle : $scope.model.Title;
        return $scope.model.URLSegment = $scope.generateURLSegmentFrom(title);
      }
    };
    $scope.generateURLSegmentFrom = function(str) {
      var from, i, l, to, tr, _i, _ref;
      if (!str) {
        return str;
      }
      str = str.replace(/^\s+|\s+$/g, '');
      str = str.toLowerCase();
      tr = {
        "ä": "ae",
        "ü": "ue",
        "ö": "oe",
        "ß": "ss"
      };
      str = str.replace(/[äöüß]/g, function(part) {
        return tr[part];
      });
      from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
      to = "aaaaeeeeiiiioooouuuunc------";
      l = from.length;
      for (i = _i = 0, _ref = l - 1; 0 <= _ref ? _i <= _ref : _i >= _ref; i = 0 <= _ref ? ++_i : --_i) {
        str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
      }
      str = str.replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
      return str;
    };
    $scope.model = {
      ClassName: params.defaultClassName,
      URLSegment: '',
      Title: '',
      MenuTitle: '',
      Hidden: 0
    };
    if (params.sourcePage) {
      angular.forEach($scope.model, function(value, key) {
        if (params.sourcePage.hasOwnProperty(key)) {
          return $scope.model[key] = params.sourcePage[key];
        }
      });
    }
    $scope.close = function(result) {
      if (window.console && console.log) {
        console.log("closed my stuff", null);
      }
      return close(result, 500);
    };
    $scope.setFocus = function(elementId) {
      return $timeout(function() {
        var element;
        element = document.getElementById(elementId);
        if (window.console && console.log) {
          console.log("➜ set focus to", element);
        }
        return element.focus();
      }, 500);
    };
    $scope.blurAndFocus = function(elementId) {
      return $timeout(function() {
        var element;
        element = document.getElementById(elementId);
        if (window.console && console.log) {
          console.log("➜ send blur-event to", element);
        }
        jQuery(element).trigger('blur');
        return element.focus();
      }, 500);
    };
    $scope.submitCount = 0;
    myValidation = new validationService();
    myValidation.setGlobalOptions({
      isolatedScope: $scope,
      displayOnlyLastErrorMsg: true,
      debounce: 300
    });
    return $scope.setFocus('page_Title');
  });
});
