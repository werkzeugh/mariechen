define(function() {
  var coreModule, trusted;
  coreModule = angular.module("coreModule", ['mc.resizer', 'oc.lazyLoad', 'pascalprecht.translate']);
  coreModule.config([
    '$controllerProvider', function($controllerProvider) {
      return coreModule.registerController = $controllerProvider.register;
    }
  ]);
  coreModule.controller("pageframeMainCtrl", function($scope, $http, $q, $controller, $element, $rootScope, $ocLazyLoad, $injector, $compile, $timeout) {
    var ModalService;
    $scope.app = app;
    $scope.app.clipboard = [];
    $scope.app.getPageInPageTree = function(pageId) {
      return $scope.app.pageTreeRef.get_node(pageId);
    };
    $scope.callPageManager = function(command, args) {
      var defered;
      defered = $q.defer();
      $http.post('/BE/Pages/ng_pagemanager/' + command, args).success(function(data) {
        if (data.status === 'ok') {
          return defered.resolve(data.payload);
        } else {
          return defered.reject(data);
        }
      });
      return defered.promise;
    };
    $scope.getContextMenuItemsForPageTreeItem = function(node) {
      var defered;
      defered = $q.defer();
      $http.post('/BE/Pages/ng_pagemanager/actionmenuItemsForPage', {
        'id': node.id,
        'clipboard': $scope.app.clipboard
      }).success(function(data) {
        if (data.status === 'ok') {
          return defered.resolve(data.payload);
        } else {
          return defered.reject('cannot read the data received');
        }
      });
      return defered.promise;
    };
    $scope.loadActionController = function(name) {
      var defered;
      defered = $q.defer();
      require(['/mysite/ng/pageframe/actions/action_' + name + '.js' + $scope.debugTimestamp()], function() {
        return defered.resolve();
      }, function(err) {
        return require(['/Mwerkzeug/ng/pageframe/actions/action_' + name + '.js' + $scope.debugTimestamp()], function() {
          return defered.resolve();
        });
      });
      return defered.promise;
    };
    $scope.executeController = function(options) {
      var controllerName, controllerScope, inputs, myController;
      controllerName = options.controller;
      if (controllerName) {
        controllerScope = $rootScope.$new();
        inputs = {
          $scope: controllerScope
        };
        if (options.inputs) {
          angular.forEach(options.inputs, function(dummy, inputName) {
            return inputs[inputName] = options.inputs[inputName];
          });
        }
        return myController = $controller(controllerName, inputs);
      }
    };
    $scope.app.failPopup = function(response) {
      if (window.console && console.log) {
        console.log("response failed, show popup", response);
      }
      $scope.app.showModal('ErrorPopup', response);
      return $scope.app.pageTreeRef.refresh();
    };
    $scope.runPageAction = function(args) {
      var command, coreModuleScope, defered, url;
      coreModuleScope = $scope;
      defered = $q.defer();
      if (args.command) {
        command = args.command;
      } else if (args.menuItem.command) {
        command = args.menuItem.command;
      }
      if (command.args) {
        args.args = command.args;
      }
      if (command.name === 'preview_page') {
        url = '/BE/Pages/show/' + args.pageId;
        return window.open(url, '_blank');
      }
      if (command.name === 'preview_page_framed') {
        url = '/BE/Pages/show/' + args.pageId;
        return window.open(url, 'rightframe');
      }
      args.defered = defered;
      $scope.loadActionController(command.name).then(function() {
        return $scope.executeController({
          controller: "action_" + command.name + "_controller",
          inputs: {
            topScope: coreModuleScope,
            params: args
          }
        });
      });
      return defered.promise;
    };
    $scope.showNotification = function(msg, timeout) {
      var closeButton, hideNotificationFunction, timeoutValue;
      if (timeout == null) {
        timeout = 3000;
      }
      timeoutValue = timeout;
      jQuery('#notificationBubble .msg').html(msg);
      jQuery('#notificationBubble').slideDown();
      hideNotificationFunction = function() {
        return jQuery('#notificationBubble').slideUp();
      };
      if (timeoutValue > 0) {
        return $timeout(hideNotificationFunction, timeoutValue);
      } else {
        closeButton = jQuery("<i class='fa fa-times fa-lg pull-right act-as-link'></i>").on('click', hideNotificationFunction);
        jQuery('#notificationBubble .msg').append(closeButton);
        return hideNotificationFunction;
      }
    };
    $scope.setCurrentRightFrameMode = function(newMode) {
      $scope.app.currentRightFrameMode = newMode;
      if (window.console && console.log) {
        return console.log("setCurrentRightFrameMode set to ", $scope.app.currentRightFrameMode);
      }
    };
    $scope.back2edit = function() {
      $scope.setCurrentRightFrameMode('edit');
      return $scope.editPage($scope.app.currentPageTreeId);
    };
    $scope.editPage = function(id) {
      var url;
      if (window.console && console.log) {
        console.log("editPage " + id);
      }
      url = "/BE/Pages/edit/" + id;
      return frames['rightframe'].location = url;
    };
    $scope.selectPage = function(id) {
      var ret, tree;
      tree = $scope.app.pageTreeRef;
      tree.deselect_node(tree.get_selected());
      ret = tree.select_node(id);
      $scope.setCurrentPageTreeId(id);
      if (ret === false) {
        return frames['leftframe'].location = '/BE/Pages/treeframe/' + id;
      }
    };
    $scope.setCurrentPageTreeId = function(id) {
      $scope.app.currentPageTreeId = id;
      frames['leftframe'].jQuery('#reloadForm').prop('action', '/BE/Pages/treeframe/' + id);
      return $scope.reloadNodeInPageTree(id);
    };
    $scope.reloadNodeInPageTree = function(id) {
      return $scope.callPageManager('getNodeData', {
        pageId: id
      }).then(function(nodeData) {
        var anchorClasses, node, node_anchor, oldClasses, tree;
        tree = $scope.app.pageTreeRef;
        if (tree) {
          tree.set_text(id, nodeData.text);
          tree.set_icon(id, nodeData.icon);
          node = tree.get_node(id, true);
          if (node) {
            node_anchor = node.find('> .jstree-anchor');
            oldClasses = node_anchor.attr('class').split(/\s+/);
            anchorClasses = [];
            angular.forEach(oldClasses, function(className) {
              if (className.match(/^jstree-/)) {
                return anchorClasses.push(className);
              }
            });
            if (nodeData.a_attr["class"]) {
              anchorClasses.push(nodeData.a_attr["class"]);
            }
            nodeData.a_attr["class"] = anchorClasses.join(' ');
            return angular.forEach(nodeData.a_attr, function(value, key) {
              return node_anchor.prop(key, value);
            });
          } else {
            if (window.console && console.log) {
              console.log("node not found in tree");
            }
            return frames['leftframe'].jQuery('#reloadForm').trigger('submit');
          }
        }
      });
    };
    $scope.insertPageAtPosition = function(insertData, insertPosition, insertOptions) {
      var defered;
      defered = $q.defer();
      $http.post('/BE/Pages/ng_insert_page_at_position', {
        insertData: insertData,
        insertPosition: insertPosition,
        insertOptions: insertOptions
      }).then(function(response) {
        if (response.data.status === 'ok') {
          if (response.data.payload.ID) {
            $scope.editPage(response.data.payload.ID);
          }
          return defered.resolve(response.data.payload);
        } else {
          return defered.reject('cannot read the data received');
        }
      });
      return defered.promise;
    };
    $scope.debugTimestamp = function() {
      return '?' + jQuery.now();
    };
    $scope.app.loadController = function(name, params) {
      var baseUrl, defered, scriptUrl;
      defered = $q.defer();
      if (window.console && console.log) {
        console.log("loadController", name, params);
      }
      if (!params.lazyloadModules) {
        params.lazyloadModules = [];
      }
      if (name === 'Modal_EditPageSettingsController') {
        params.lazyloadModules.push('angular-validation');
      }
      if (params.baseUrlForController) {
        baseUrl = params.baseUrlForController;
      } else {
        baseUrl = '/Mwerkzeug/ng/pageframe';
      }
      scriptUrl = baseUrl + '/js/' + name + '.js' + $scope.debugTimestamp();
      if (params.lazyloadModules) {
        $ocLazyLoad.load(params.lazyloadModules).then(function() {
          return require([scriptUrl], function() {
            return defered.resolve();
          });
        });
      } else {
        require([scriptUrl], function() {
          return defered.resolve();
        });
      }
      return defered.promise;
    };
    ModalService = null;
    return $scope.app.showModal = function(action, params) {
      var defered;
      defered = $q.defer();
      $ocLazyLoad.load('angularModalService').then(function() {
        var controllerName;
        ModalService = $injector.get('ModalService');
        controllerName = "Modal_" + action + "Controller";
        return $scope.app.loadController(controllerName, params).then(function() {
          return ModalService.showModal({
            templateUrl: "/BE/Pages/ng_pagemanager/translatedTemplate/Modal_" + action + $scope.debugTimestamp(),
            controller: controllerName,
            inputs: {
              params: params
            }
          }).then(function(modal) {
            if (window.console && console.log) {
              console.log("show modal running", null);
            }
            modal.element.modal();
            modal.close.then(function(result) {
              console.log("modal closed with", result);
              if (result !== 'cancel') {
                defered.resolve(result);
              } else {
                defered.reject(result);
              }
            });
          });
        });
      });
      return defered.promise;
    };
  });
  trusted = {};
  coreModule.filter('trusted', [
    '$sce', function($sce) {
      return function(html) {
        return trusted[html] || (trusted[html] = $sce.trustAsHtml(html));
      };
    }
  ]);
  return coreModule.config(function($translateProvider) {
    $translateProvider.useStaticFilesLoader({
      prefix: '/Mwerkzeug/bower_components/angular-validation-ghiscoding/locales/validation/',
      suffix: '.json'
    });
    return $translateProvider.preferredLanguage('de');
  });
});
