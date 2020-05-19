var pagetreeApp;

pagetreeApp = angular.module("pagetree", []);

angular.module("pagetree").controller("pagetreeMainCtrl", function($scope, $http, $q) {
  $scope.selectedTreeNode = null;
  $scope.actionForm = jQuery('#actionform');
  $scope.reloadForm = jQuery('#reloadForm');
  $scope.app = {
    settings: {},
    currentClickMode: 'edit'
  };
  $scope.app.topWindow = window.top;
  $scope.init = function(settings) {
    $scope.app.settings = angular.extend($scope.app.settings, settings);
    if ($scope.app.settings.treeGroups) {
      $scope.app.tabList = $scope.app.settings.treeGroups;
      if ($scope.app.settings.treeGroupName) {
        $scope.app.activeTabKey = $scope.app.settings.treeGroupName;
      } else {
        $scope.app.activeTabKey = $scope.app.tabList[0].key;
      }
      $scope.app.setTreeGroupName($scope.app.activeTabKey);
    }
    if (window.console && console.log) {
      return console.log("pagetreeMainCtrl init called", $scope.app.settings);
    }
  };
  $scope.app.callTopWindowAngularFunction = function(functionName, data) {
    var promise;
    return promise = $scope.app.topWindow.callAngularFunction(functionName, data);
  };
  $scope.opentest = function() {
    var url;
    url = '/BE/Pages/show/495';
    return window.open(url, '_blank');
  };
  $scope.viewPage = function(id) {
    var url;
    if (id > 0) {
      url = '/BE/Pages/preview/' + id;
      $scope.actionForm.attr('action', url);
      return $scope.actionForm.submit();
    }
  };
  $scope.editPage = function(id) {
    var url;
    if (id > 0) {
      url = '/BE/Pages/edit/' + id;
      $scope.actionForm.attr('action', url);
      return $scope.actionForm.submit();
    }
  };
  $scope.choosePage = function(id) {
    var defered;
    defered = $q.defer();
    $scope.app.callTopWindowAngularFunction('runPageAction', {
      pageId: id,
      onChoose: defered,
      command: {
        name: 'choose_page'
      }
    }).then(function(response) {});
    return defered.promise.then(function(mwlink) {
      return window.parent.setMwLink(mwlink);
    });
  };
  $scope.app.setCurrentClickMode = function(newMode) {
    if (window.console && console.log) {
      console.log("setCurrentClickMode", $scope.app);
    }
    $scope.app.currentClickMode = newMode;
    if ($scope.app.topWindow.app.currentPageTreeId) {
      $scope.app.clickPage($scope.app.topWindow.app.currentPageTreeId);
    }
    return true;
  };
  $scope.back2edit = function() {
    return $scope.editPage($scope.app.topWindow.app.currentPageTreeId);
  };
  $scope.app.clickPage = function(id) {
    $scope.app.topWindow.app.currentPageTreeId = id;
    if ($scope.app.settings.mode === 'linkchooser') {
      return $scope.choosePage(id);
    } else {
      if (window.console && console.log) {
        console.log("clickPage", id, $scope.app.currentClickMode);
      }
      if ($scope.app.currentClickMode === 'preview') {
        return $scope.viewPage(id);
      } else {
        return $scope.editPage(id);
      }
    }
  };
  $scope.app.choosePortal = function(key) {
    $scope.app.setTreeGroupName(key);
    $scope.app.reload();
  };
  $scope.app.setTreeGroupName = function(key) {
    return jQuery('#treeGroupName').val(key);
  };
  return $scope.app.reload = function() {
    return $scope.reloadForm.submit();
  };
});

pagetreeApp.directive('jstreeChooser', function($timeout, $http) {
  return {
    restrict: 'A',
    templateUrl: '/Mwerkzeug/ng/pagetree/partials/pagetree-chooser.html',
    scope: {
      app: '=',
      labelEditMode: '@',
      labelPreviewMode: '@',
      labelClickMode: '@'
    },
    controller: ["$scope", "$element", "$attrs", "$filter", "$q", "$http", function($scope, $element, $attrs, $filter, $q, $http) {}]
  };
});

pagetreeApp.directive('jstree', function($timeout, $http) {
  return {
    restrict: 'A',
    require: '?ngModel',
    scope: {
      selectedNode: '=?',
      selectionChanged: '=',
      app: '=',
      context: '@'
    },
    link: function(scope, element, attrs) {
      var ajax_again, expandAndSelect, jsTreeSettings, topScope, tree, treeElement;
      expandAndSelect = function(ids) {
        var expandIds;
        ids = ids.slice();
        expandIds = function() {
          if (ids.length === 1) {
            treeElement.jstree('deselect_node', treeElement.jstree('get_selected'));
            treeElement.jstree('select_node', ids[0]);
          } else {
            treeElement.jstree('open_node', ids[0], function() {
              ids.splice(0, 1);
              expandIds();
            });
          }
        };
        expandIds();
      };
      scope.selectedNode = scope.selectedNode || {};
      jsTreeSettings = {
        core: {
          multiple: false,
          themes: {
            dots: false,
            icons: true,
            stripes: true
          },
          check_callback: function(operation, node, node_parent, node_position, more) {
            return true;
          },
          data: function(obj, callback) {
            var queryParams, savedIcon;
            savedIcon = obj.icon;
            treeElement.jstree('set_icon', obj, 'fa fa-spinner fa-spin');
            queryParams = [];
            if (scope.app.activeTabKey) {
              queryParams.push('tgn=' + scope.app.activeTabKey);
            }
            queryParams.push('id=' + obj.id);
            queryParams.push('curr=' + scope.app.settings.idOfCurrentPage);
            return $http.post('/BE/Pages/ajaxTreeData_v2?' + queryParams.join('&')).success(function(data) {
              treeElement.jstree('set_icon', obj, savedIcon);
              angular.forEach(data, function(node) {
                var setstate, state;
                state = {};
                if (scope.app.settings.parentIdsOfCurrentPage && scope.app.settings.parentIdsOfCurrentPage.indexOf(node.id) > -1) {
                  state.opened = true;
                  setstate = true;
                }
                if (scope.app.settings.idOfCurrentPage === node.id) {
                  state.selected = true;
                  setstate = true;
                }
                if (setstate) {
                  node.state = state;
                }
                return node.a_attr.href = '/BE/Pages/edit/' + node.id;
              });
              return callback.call(this, data);
            });
          }
        },
        plugins: ['themes', 'json_data', 'ui', 'contextmenu', 'dnd', 'state', 'wholerow'],
        state: {
          filter: function(state) {
            var ref;
            if (scope.app.settings.idOfCurrentPage > 0 && (state != null ? (ref = state.core) != null ? ref.selected : void 0 : void 0)) {
              delete state.core.selected;
            }
            return state;
          }
        },
        dnd: {
          copy: false,
          always_copy: true,
          inside_pos: 'first',
          check_while_dragging: false,
          is_draggable: function(nodes) {
            if (nodes[0].icon.match('draggable') || window.event.shiftKey) {
              return true;
            }
            return false;
          }
        },
        contextmenu: {
          select_node: false,
          items: function(obj, callback) {
            var contextMenuAlreadyOpen, loadingMenu;
            contextMenuAlreadyOpen = jQuery('.jstree-contextmenu').length;
            if (contextMenuAlreadyOpen) {
              $.vakata.context.hide();
              return;
            }
            if (scope.app.settings.mode === 'linkchooser') {
              return;
            }
            scope.app.callTopWindowAngularFunction('getContextMenuItemsForPageTreeItem', obj).then(function(response) {
              return response.payload.then(function(actionmenuItems) {
                var menuItems;
                menuItems = {
                  headline: {
                    label: obj.text + ":",
                    _disabled: true,
                    action: function() {
                      return null;
                    }
                  }
                };
                angular.forEach(actionmenuItems, function(menuItem) {
                  var newSubHeadline;
                  if (menuItem.submenu && menuItem.submenu.length > 0) {
                    newSubHeadline = {
                      label: menuItem.label,
                      _disabled: true,
                      action: function() {
                        return null;
                      }
                    };
                    menuItem.submenu.unshift(newSubHeadline);
                  }
                  angular.forEach(menuItem.submenu, function(subMenuItem) {
                    return subMenuItem.action = function() {
                      return scope.app.callTopWindowAngularFunction('runPageAction', {
                        pageId: obj.id,
                        menuItem: subMenuItem
                      });
                    };
                  });
                  menuItem.action = function() {
                    return scope.app.callTopWindowAngularFunction('runPageAction', {
                      pageId: obj.id,
                      menuItem: menuItem
                    });
                  };
                  return menuItems[menuItem.key] = menuItem;
                });
                if (window.console && console.log) {
                  console.log("menuItems=", menuItems);
                }
                return callback.call(this, menuItems);
              });
            });
            return loadingMenu = {
              headline: {
                label: obj.text + ":",
                _disabled: true
              },
              loading: {
                label: "...loading",
                icon: 'fa fa-spinner fa-spin',
                _disabled: true
              }
            };
          }
        }
      };
      jsTreeSettings = angular.extend(jsTreeSettings, scope.app.settings.jstree);
      treeElement = $(element);
      tree = treeElement.jstree(jsTreeSettings);
      ajax_again = "to-be-refreshed";
      if (window.parent.setPageTreeRef) {
        window.parent.setPageTreeRef(jQuery.jstree.reference(element));
      }
      topScope = {
        app: window.parent.app
      };
      tree.bind('open_node.jstree close_node.jstree', function(e, data) {
        var currentNode;
        currentNode = data.instance.get_node(data.node, 1);
        if (e.type === 'close_node') {
          currentNode.addClass(ajax_again);
        }
        if (e.type === 'open_node') {
          if (currentNode.hasClass(ajax_again)) {
            return data.instance.refresh_node(data.node);
          }
        }
      });
      tree.bind('copy_node.jstree', function(e, data) {
        var args, menuItem;
        if (window.console && console.log) {
          console.log("copy node", data);
        }
        menuItem = {
          command: {
            name: 'move_page'
          }
        };
        args = {
          pageId: data.original.id,
          menuItem: menuItem,
          moveInfo: {
            newparent: data.parent,
            position: data.position
          }
        };
        data.instance.delete_node(data.node);
        return scope.app.callTopWindowAngularFunction('runPageAction', args).then(function(response) {
          return response.payload.then(function(response) {
            if (window.console && console.log) {
              console.log("runPageAction res", response);
            }
            if (response.success) {
              return data.instance.move_node(data.original, data.parent, data.position);
            }
          });
        });
      });
      tree.bind('set_state.jstree', function(e) {});
      tree.bind('activate_node.jstree', function(e, data) {
        var n, ref;
        n = data.instance.get_selected(true);
        if (n) {
          n = n[0];
          if (window.console && console.log) {
            console.log("clicked node=", n);
          }
          scope.selectedNode.id = n.id;
          scope.selectedNode.path = n.a_attr.path;
          scope.selectedNode.text = n.text;
          if ((ref = n.a_attr["class"]) != null ? ref.match('expand_only') : void 0) {
            if (window.console && console.log) {
              console.log("expand node", n);
            }
            data.instance.toggle_node(n);
          } else {
            scope.app.clickPage(n.id);
          }
          if (topScope.app.lastPageIdMarkedForDragging) {
            topScope.app.unmarkPageForDragging(topScope.app.lastPageIdMarkedForDragging);
            return topScope.app.lastPageIdMarkedForDragging = null;
          }
        }
      });
    }
  };
});
