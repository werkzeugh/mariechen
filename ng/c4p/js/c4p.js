var CopyClipboardAsJsonCtrl, PasteClipboardAsJsonCtrl, c4pApp, trusted;

c4pApp = angular.module("c4p", ['ui.tree', 'ui.bootstrap', 'pascalprecht.translate']);

angular.module("c4p").controller("c4pMainCtrl", function($scope, $translate) {
  $scope.app = {
    serverinfo: {
      clipboardsize: 0
    }
  };
  $scope.reloadItemById = function(itemId, nextaction) {
    return $scope.$broadcast('reloadItem', {
      itemId: itemId,
      nextaction: nextaction
    });
  };
  $scope.showErrorForItemById = function(itemId, msg) {
    return $scope.$broadcast('showErrorForItem', {
      itemId: itemId,
      msg: msg
    });
  };
  return $scope.renameItemId = function(oldId, newId) {
    return $scope.$broadcast('renameItemId', {
      oldId: oldId,
      newId: newId
    });
  };
});

angular.module("c4p").directive("c4pList", function() {
  return {
    restrict: "E",
    replace: true,
    templateUrl: "/Mwerkzeug/ng/c4p/partials/c4p-list.html",
    scope: {
      c4p_place: '@place',
      c4p_record: '@record',
      language: '@',
      placeconf: '@placeconf',
      app: '='
    },
    link: function(scope, element, attrs) {
      var ref, ref1, ref2, ref3;
      if (attrs.placeconfEncoded) {
        scope.settings.placeconf = scope.$eval(attrs.placeconfEncoded);
      }
      if ((ref = scope.settings) != null ? (ref1 = ref.placeconf) != null ? ref1.max_width : void 0 : void 0) {
        element.css('max-width', scope.settings.placeconf.max_width + "px");
      }
      if ((ref2 = scope.settings) != null ? (ref3 = ref2.placeconf) != null ? ref3.max : void 0 : void 0) {
        if (window.console && console.log) {
          console.log("start watching", null);
        }
        return scope.$watchCollection('items', function(newItems, oldItems) {
          return scope.isAddingPossible = newItems.length < scope.settings.placeconf.max;
        });
      }
    },
    controller: [
      "$scope", "$element", "$attrs", "$filter", "$q", "$http", "$compile", "$timeout", "$modal", "$translate", function($scope, $element, $attrs, $filter, $q, $http, $compile, $timeout, $modal, $translate) {
        var apiUrl, createNewId, filterFilter, getItemArrayForItemOrItemList, getItemIdsForItemList, permissions, selectionRangeStartItem;
        $scope.settings = {};
        $scope.settings.c4p_place = $scope.c4p_place;
        $scope.settings.c4p_record = $scope.c4p_record;
        $scope.settings.lang = $scope.language;
        $translate.use($scope.settings.lang);
        $scope.mainListLoaded = $q.defer();
        $scope.listStatus = 'loading';
        $scope.listMode = 'list';
        $scope.globalEditMode = false;
        $scope.childrenEditGroupname = '_children';
        $scope.items = [];
        $scope.isAddingPossible = true;
        $scope.selectedItems = [];
        $scope.hiddenItems = [];
        $scope.status = {
          selectAll: false,
          showHidden: false
        };
        $scope.showAddArea = null;
        $scope.list = permissions = {};
        apiUrl = '/BE/C4P_Api';
        $scope.refreshListing = function() {
          return $http.post(apiUrl + '/listdata', {
            settings: $scope.settings
          }).then(function(res) {
            if (res.data && res.data.status === "ok") {
              $scope.items = res.data.items;
              $scope.app.serverinfo = res.data.serverinfo;
              $scope.list.permissions = res.data.permissions;
              $scope.listStatus = 'loaded';
              $scope.updateHiddenItems();
              return $scope.mainListLoaded.resolve();
            }
          });
        };
        $scope.refreshListing();
        filterFilter = $filter('filter');
        selectionRangeStartItem = null;
        $scope.$watch("status.selectAll", function(newValue, oldValue) {
          var newval;
          if (window.console && console.log) {
            console.log("selectAll", newValue, oldValue);
          }
          newval = $scope.selectAll;
          if (window.console && console.log) {
            console.log("$scope.status.showHidden ", $scope.status.showHidden);
          }
          angular.forEach($scope.items, function(item) {
            var skipItem;
            skipItem = false;
            if (item.hidden && !$scope.status.showHidden) {
              if (newValue) {
                skipItem = true;
              }
            }
            if (item.locked || !item.permissions["delete"]) {
              skipItem = true;
            }
            if (!skipItem) {
              return item.selected = newValue;
            }
          });
          selectionRangeStartItem = null;
          return $scope.updateSelectedItems();
        });
        $scope.handleSelectBox = function(item, event) {
          var lastSelectedItem, selectMode;
          if (window.console && console.log) {
            console.log("cb", item, event);
          }
          if (event && event.shiftKey) {
            if (selectionRangeStartItem) {
              selectMode = false;
              return angular.forEach($scope.items, function(i) {
                if (i === lastSelectedItem) {
                  return selectMode = true;
                } else if (i === item) {
                  return selectMode = false;
                } else if (selectMode) {
                  return i.selected = true;
                }
              });
            }
          } else {
            if (!item.selected) {
              return lastSelectedItem = item;
            } else {
              return lastSelectedItem = null;
            }
          }
        };
        $scope.updateSelectedItems = function() {
          $scope.selectedItems = filterFilter($scope.items, {
            selected: true
          });
          return $scope.updateHiddenItems();
        };
        $scope.updateHiddenItems = function() {
          return $scope.hiddenItems = filterFilter($scope.items, {
            hidden: true
          });
        };
        $scope.treeOptions = {
          accept: function(sourceNodeScope, destNodesScope, destIndex) {
            var allowed_types, destElement, droppedCtype, sourceElement;
            sourceElement = sourceNodeScope.hasOwnProperty('i') ? sourceNodeScope.i : sourceNodeScope.c;
            droppedCtype = sourceElement != null ? sourceElement.ctype : void 0;
            if (!droppedCtype) {
              return false;
            }
            destElement = destNodesScope.$parent.hasOwnProperty('i') ? destNodesScope.$parent.i : destNodesScope.$parent.c;
            if (destElement) {
              allowed_types = destElement.config._children.allowed_types;
            } else {
              allowed_types = $scope.settings.placeconf.allowed_types;
            }
            if (!allowed_types) {
              return false;
            }
            if (allowed_types.hasOwnProperty(droppedCtype)) {
              return true;
            }
            return false;
          }
        };
        $scope.setListMode = function(newMode) {
          $scope.listMode = newMode;
          if (newMode === 'sort') {
            $scope.sortableItems = angular.copy($scope.items);
          }
          return $scope.saveOrderInProgress = false;
        };
        $scope.saveOrder = function() {
          var sortable_item_ids;
          $scope.saveOrderInProgress = true;
          sortable_item_ids = $scope.sortableItems.map(function(item) {
            return {
              ctype: item.ctype,
              id: item.id
            };
          });
          if (window.console && console.log) {
            console.log("save order", sortable_item_ids);
          }
          return $http.post(apiUrl + '/saveorder', {
            id: $scope.items[0].id,
            items: sortable_item_ids,
            settings: $scope.settings
          }).then(function(res) {
            if (res.data && res.data.status === "ok") {
              $scope.items = $scope.sortableItems;
              $scope.setListMode('list');
              return $scope.reloadPreview(res.data.preview_url);
            }
          });
        };
        $scope.multiAction = function(action, event) {
          $scope.multiActionLoading = true;
          return $scope[action + 'Items'].call(this, $scope.selectedItems, event).then(function() {
            if (window.console && console.log) {
              console.log("then called", null);
            }
            return $scope.multiActionLoading = false;
          });
        };
        $scope.getOtherAllowedTypes = function(item) {
          var ref, typeKey, typeRec;
          if (!item.hasOwnProperty('otherAllowedTypes')) {
            item.otherAllowedTypes = [];
            ref = $scope.settings.placeconf.allowed_types;
            for (typeKey in ref) {
              typeRec = ref[typeKey];
              typeRec.key = typeKey;
              if (item.ctype !== typeKey) {
                item.otherAllowedTypes.push(typeRec);
              }
            }
          }
          return item.otherAllowedTypes;
        };
        $scope.setItemType = function(item, newType) {
          $element.find('#c4p-ctypefield').val(newType);
          $element.find('#c4p-nextactionfield').val('edit');
          $scope.submitItem(item);
        };
        $scope.cancelItem = function(item) {
          var editform;
          $scope.globalEditMode = false;
          item.editable = false;
          item.editready = false;
          editform = $element.find('#editform-' + item.id);
          editform.html('cancelled');
          if (window.console && console.log) {
            console.log("emptied editform", editform);
          }
          if (item.childreneditready) {
            $scope.reloadItem(item);
          }
          item.childreneditready = false;
          if (item.isNew) {
            return $scope.items.splice($scope.items.indexOf(item), 1);
          }
        };
        $scope.editItem = function(item, params, event) {
          var editform;
          if (window.console && console.log) {
            console.log("editItem", item, params);
          }
          if ($scope.globalEditMode || item.locked || (item.is_alias && !params.edit_alias) || (item.permissions && (!item.permissions.edit && !item.permissions.editChildren))) {
            return false;
          }
          if (event && event.altKey && event.metaKey) {
            params = {
              edit_json: true,
              force: true
            };
          }
          if (item.config && item.config._children && !(params != null ? params.force : void 0)) {
            return $scope.childrenEditItem(item, params);
          } else {
            item.submitting = false;
            if (!params) {
              params = {};
            }
            $scope.globalEditMode = true;
            editform = $element.find('#editform-' + item.id);
            item.editable = true;
            item.editready = false;
            item.childreneditready = false;
            return $http.post(apiUrl + '/editform', {
              id: item.id,
              params: params,
              settings: $scope.settings
            }).then(function(res) {
              var elem, linkingFunction;
              if (res.data && res.data.status === "ok") {
                item.editready = true;
                linkingFunction = $compile(res.data.html);
                elem = linkingFunction($scope);
                editform.contents().remove();
                editform.append(elem);
              }
            });
          }
        };
        $scope.childrenEditItem = function(item, params) {
          if (!params) {
            params = {};
          }
          $scope.childrenEditGroupname = '_children';
          if (params.groupname) {
            $scope.childrenEditGroupname = '_children_' + params.groupname;
          }
          item.childreneditready = true;
          $scope.globalEditMode = true;
          return item.editready = false;
        };
        $scope.callActionOnItem = function(item, action, args) {
          var params;
          $scope.globalEditMode = true;
          item.submitting = true;
          params = {
            action: action,
            args: args
          };
          return $http.post(apiUrl + '/customaction', {
            id: item.id,
            params: params,
            settings: $scope.settings
          }).then(function(res) {
            if (res.data && res.data.status === "ok") {
              $scope.globalEditMode = false;
              item.submitting = false;
              return $scope.reloadItem(item);
            }
          });
        };
        $scope.submitItem = function(item, event) {
          var form;
          form = $element.find('#editform-' + item.id + ' form');
          if (form.valid()) {
            item.submitting = true;
            form.submit();
          }
          return true;
        };
        $scope.getItemForId = function(id) {
          var res;
          res = filterFilter($scope.items, {
            id: id
          });
          if (res.length) {
            return res[0];
          } else {
            return null;
          }
        };
        $scope.$on('showErrorForItem', function(event, params) {
          var item;
          item = $scope.getItemForId(params.itemId);
          if (item) {
            return $scope.showErrorForItem(item, params);
          }
        });
        $scope.showErrorForItem = function(item, params) {
          alert(params.msg);
          item.submitting = false;
          item.editready = true;
          if (window.console && console.log) {
            return console.log("item", item);
          }
        };
        $scope.$on('reloadItem', function(event, params) {
          var item;
          item = $scope.getItemForId(params.itemId);
          if (item) {
            return $scope.reloadItem(item, params);
          }
        });
        $scope.reloadItem = function(item, params) {
          $scope.globalEditMode = false;
          item.editable = false;
          item.editready = false;
          item.loading = true;
          return $http.post(apiUrl + '/getitem', {
            id: item.id,
            settings: $scope.settings
          }).then(function(res) {
            var idx;
            if (res.data && res.data.status === "ok") {
              idx = $scope.items.indexOf(item);
              $scope.items[$scope.items.indexOf(item)] = res.data.item;
              $scope.reloadPreview();
              if (params && params.nextaction === 'edit') {
                return $timeout(function() {
                  return $scope.editItem($scope.items[idx]);
                }, 500);
              }
            }
          });
        };
        $scope.$on('renameItemId', function(event, params) {
          var item;
          item = $scope.getItemForId(params.oldId);
          if (item) {
            return item.id = params.newId;
          }
        });
        createNewId = function() {
          var newid, rand, ts;
          ts = Math.round(new Date().getTime() / 1000);
          ts = "" + ts;
          ts = ts.substring(4);
          rand = Math.floor(Math.random() * 1000);
          return newid = "" + ts + rand;
        };
        $scope.duplicateItem = function(item) {
          return $scope.createItem({
            newitem_duplicateof: item
          });
        };
        $scope.getDefaultCtype = function() {
          var firstkey;
          for (firstkey in $scope.settings.placeconf.allowed_types) {
            break;
          }
          return firstkey;
        };
        $scope.addItem = function(nextItem) {
          return $http.post(apiUrl + '/get_allowed_types_to_add', {
            settings: $scope.settings,
            allowedTypes: $scope.settings.placeconf.allowed_types,
            nextItem: nextItem
          }).success(function(data) {
            var key, typeCount;
            if (data.status === 'ok') {
              $scope.allowedTypesForAdd = data.payload;
              typeCount = Object.keys($scope.allowedTypesForAdd).length;
              if (typeCount === 1) {
                return $scope.createItem({
                  ctype: Object.keys($scope.allowedTypesForAdd)[0],
                  before: nextItem
                });
              } else if (typeCount > 1) {
                key = 'item_';
                if (nextItem) {
                  key += nextItem.id;
                }
                return $scope.showAddArea = key;
              } else {
                return alert('sorry, no item can be created here');
              }
            }
          });
        };
        $scope.resetAddItem = function() {
          return $scope.showAddArea = null;
        };
        $scope.createItem = function(params) {
          var editparams, newitem, newitemDefaults;
          $scope.showAddArea = null;
          if (!params) {
            params = {};
          }
          newitem = {};
          newitem.id = createNewId();
          newitem.rec = {};
          newitem.isNew = 1;
          if (params.newitem_duplicateof) {
            newitem.ctype = params.newitem_duplicateof.ctype;
          } else if (params.ctype) {
            newitem.ctype = params.ctype;
          } else {
            newitem.ctype = $scope.getDefaultCtype();
          }
          newitem.html = '...';
          newitemDefaults = {
            id: newitem.id,
            ctype: newitem.ctype
          };
          editparams = {
            'newitem': 1,
            'newitem_defaults': newitemDefaults
          };
          if (params.before) {
            editparams.newitem_before = params.before.id;
            $scope.items.splice($scope.items.indexOf(params.before), 0, newitem);
          } else if (params.newitem_duplicateof) {
            editparams.newitem_duplicateof = params.newitem_duplicateof.id;
            $scope.items.splice($scope.items.indexOf(params.newitem_duplicateof) + 1, 0, newitem);
          } else {
            $scope.items.push(newitem);
          }
          return $timeout(function() {
            return $scope.editItem(newitem, editparams);
          }, 500);
        };
        $scope.reloadPreview = function(url) {
          $scope.updateHiddenItems();
          if (!url) {
            url = angular.element('#previewframe').attr('src');
            url = url.split("?")[0] + '?preview=' + new Date().getTime();
          }
          if (url) {
            angular.element('#previewframe').attr('src', url);
          }
        };
        getItemArrayForItemOrItemList = function(itemOrItemlist) {
          if (angular.isArray(itemOrItemlist)) {
            return itemOrItemlist;
          } else {
            return [itemOrItemlist];
          }
        };
        getItemIdsForItemList = function(itemlist) {
          var ids, item, j, len;
          ids = [];
          for (j = 0, len = itemlist.length; j < len; j++) {
            item = itemlist[j];
            ids.push(item.id);
          }
          return ids;
        };
        $scope.pasteItems = function(event, position) {
          var modalInstance;
          if (position == null) {
            position = {};
          }
          if (event && event.altKey && event.metaKey) {
            angular.element('body').addClass('c4p');
            modalInstance = $modal.open({
              templateUrl: "/Mwerkzeug/ng/c4p/partials/c4p-paste_clipboard_as_json.html",
              controller: PasteClipboardAsJsonCtrl,
              scope: $scope
            });
            return modalInstance.result.then(function(str2paste) {
              $scope.doPasteItems(str2paste, position);
              angular.element('body').removeClass('c4p');
            }, function() {
              angular.element('body').removeClass('c4p');
            });
          } else {
            return $scope.doPasteItems('clipboard', position);
          }
        };
        $scope.doPasteItems = function(jsondata, position) {
          return $http.post(apiUrl + '/get_allowed_types_to_add', {
            settings: $scope.settings,
            allowedTypes: $scope.settings.placeconf.allowed_types,
            nextItem: null
          }).success(function(data) {
            var allowedTypesForAdd;
            if (data.status === 'ok') {
              allowedTypesForAdd = data.payload;
              return $http.post(apiUrl + '/pasteitems', {
                settings: $scope.settings,
                jsondata: jsondata,
                position: position,
                allowedTypesForAdd: allowedTypesForAdd
              }).then(function(res) {
                if (res.data && res.data.msg) {
                  if (res.data.status === 'error') {
                    alert(res.data.msg);
                  }
                  if (res.data.status === 'warning') {
                    if (window.top.callAngularFunction) {
                      window.top.callAngularFunction('showNotification', res.data.msg, 0);
                    }
                  }
                  $scope.refreshListing();
                  return $scope.reloadPreview();
                } else {
                  return alert("an error occured");
                }
              });
            }
          });
        };
        $scope.hideItem = function(item) {
          return $scope.hideItems([item]);
        };
        $scope.unhideItem = function(item) {
          return $scope.unhideItems([item]);
        };
        $scope.copyItem = function(item) {
          return $scope.copyItems([item]);
        };
        $scope.copyaliasItem = function(item) {
          return $scope.copyaliasItems([item]);
        };
        $scope.cutItems = function(items, event) {
          return $scope.copyItems(items, event).then(function() {
            return $scope.removeItems(items, event, true);
          });
        };
        $scope.copyaliasItems = function(items, event) {
          return $scope.copyItems(items, event, {
            'makealias': true
          });
        };
        $scope.copyItems = function(items, event, options) {
          var itemIds;
          items = getItemArrayForItemOrItemList(items);
          itemIds = getItemIdsForItemList(items);
          options = options || {};
          options.storage = 'session';
          if (event && event.altKey && event.metaKey) {
            options.storage = 'text';
          }
          return $http.post(apiUrl + '/copyitems', {
            id: itemIds[0],
            items: itemIds,
            settings: $scope.settings,
            options: options
          }).then(function(res) {
            var modalInstance;
            if (res.data && res.data.status === "ok") {
              if (window.console && console.log) {
                console.log(res.data.num_items + " items added to the clipboard", null);
              }
              $scope.app.serverinfo.clipboardsize = res.data.num_items;
              if (res.data.data) {
                $scope.value2copy = res.data.data;
                angular.element('body').addClass('c4p');
                modalInstance = $modal.open({
                  templateUrl: "/Mwerkzeug/ng/c4p/partials/c4p-copy_clipboard_as_json.html",
                  controller: CopyClipboardAsJsonCtrl,
                  scope: $scope
                });
                modalInstance.result["finally"](function() {
                  angular.element('body').removeClass('c4p');
                  return $scope.value2copy = null;
                });
              }
            } else {
              alert("an error occured");
            }
          });
        };
        $scope.hideItems = function(items, event) {
          var itemIds;
          items = getItemArrayForItemOrItemList(items);
          itemIds = getItemIdsForItemList(items);
          return $http.post(apiUrl + '/hideitems', {
            ids: itemIds,
            settings: $scope.settings
          }).then(function(res) {
            var item, j, len;
            if (res.data && res.data.status === "ok") {
              for (j = 0, len = items.length; j < len; j++) {
                item = items[j];
                $scope.items[$scope.items.indexOf(item)].hidden = true;
              }
              return $scope.reloadPreview(res.data.preview_url);
            } else {
              return alert("an error occured");
            }
          });
        };
        $scope.unhideItems = function(items, event) {
          var itemIds;
          items = getItemArrayForItemOrItemList(items);
          itemIds = getItemIdsForItemList(items);
          return $http.post(apiUrl + '/unhideitems', {
            ids: itemIds,
            settings: $scope.settings
          }).then(function(res) {
            var item, j, len;
            if (res.data && res.data.status === "ok") {
              for (j = 0, len = items.length; j < len; j++) {
                item = items[j];
                $scope.items[$scope.items.indexOf(item)].hidden = false;
              }
              return $scope.reloadPreview(res.data.preview_url);
            } else {
              return alert("an error occured");
            }
          });
        };
        return $scope.removeItems = function(items, event, forceDelete) {
          var confirmMsg, def, itemIds;
          items = getItemArrayForItemOrItemList(items);
          itemIds = getItemIdsForItemList(items);
          confirmMsg = $translate.instant('text_deleteConfirmation', {
            num: itemIds.length
          });
          if (window.console && console.log) {
            console.log(confirmMsg);
          }
          if (forceDelete || confirm(confirmMsg)) {
            return $http.post(apiUrl + '/removeitems', {
              ids: itemIds,
              settings: $scope.settings
            }).then(function(res) {
              var item, j, len;
              if (res.data && res.data.status === "ok") {
                for (j = 0, len = items.length; j < len; j++) {
                  item = items[j];
                  $scope.items.splice($scope.items.indexOf(item), 1);
                }
                $scope.reloadPreview(res.data.preview_url);
                return $scope.updateSelectedItems();
              } else {
                return alert("an error occured");
              }
            });
          } else {
            def = $q.defer();
            def.resolve();
            return def.promise;
          }
        };
      }
    ]
  };
});

c4pApp.filter("getLabel", function($filter) {
  return function(input, dict) {
    var res;
    if (angular.isArray(dict)) {
      res = $filter('filter')(dict, {
        'code': input
      });
      return res.pop().label;
    } else {
      if (dict[input]) {
        return dict[input].label;
      } else {
        return input;
      }
    }
  };
});

angular.module("c4p").directive("c4pPreviewTemplate", function($compile) {
  var linker;
  linker = function(scope, element, attrs) {
    scope.listMode = attrs.listMode;
    scope.item = scope.$eval(attrs.item);
    scope.template = scope.$eval(attrs.template);
    element.html(scope.template).show();
    $compile(element.contents())(scope);
  };
  return {
    restrict: "E",
    replace: true,
    link: linker,
    scope: true
  };
});

angular.module("c4p").directive("c4pSortPreviewTemplate", function($compile) {
  var linker;
  linker = function(scope, element, attrs) {
    element.html(scope.$eval(attrs.template)).show();
    $compile(element.contents())(scope);
  };
  return {
    restrict: "E",
    replace: true,
    link: linker
  };
});

angular.module("c4p").directive("c4pCreateItemTypeChooser", function() {
  return {
    restrict: "EA",
    replace: true,
    scope: false,
    templateUrl: "/Mwerkzeug/ng/c4p/partials/c4p_create_item_type_chooser.html"
  };
});

angular.module("c4p").directive("c4pChildPreview", function($compile) {
  var linker;
  linker = function(scope, element, attrs) {
    element.removeAttr(attrs.$attr.c4pChildPreview);
    if (scope.listMode === 'sort') {
      element.hide();
    } else {
      element.attr('ng-repeat', 'i in item._children');
      element.attr('ng-bind-html', 'i.html | trusted');
      $compile(element)(scope);
    }
  };
  return {
    restrict: "A",
    replace: true,
    link: linker
  };
});

angular.module("c4p").directive("c4pChildlist", function($compile) {
  return {
    restrict: "E",
    scope: false,
    link: function(scope, elm, attr) {
      var tplHtml;
      tplHtml = "<div class='c4p-childlist'><c4p-list app='app' place='" + attr.place + "' record='" + attr.record + "' placeconf-encoded='" + attr.placeconfig + "'>child-link directive here</c4p-list></div>";
      elm.html(tplHtml);
      $compile(elm.contents())(scope);
    }
  };
});

trusted = {};

c4pApp.filter('trusted', [
  '$sce', function($sce) {
    return function(html) {
      return trusted[html] || (trusted[html] = $sce.trustAsHtml(html));
    };
  }
]);

CopyClipboardAsJsonCtrl = function($scope, $modalInstance) {
  $scope.str2copy = angular.toJson($scope.value2copy, true);
  $scope.cancel = function() {
    $modalInstance.dismiss("cancel");
  };
};

PasteClipboardAsJsonCtrl = function($scope, $modalInstance) {
  $scope.fdata = {
    str2paste: 'paste here'
  };
  $scope.ok = function() {
    if (window.console && console.log) {
      console.log("do", $scope.fdata.str2paste);
    }
    $modalInstance.close($scope.fdata.str2paste);
  };
  $scope.cancel = function() {
    $modalInstance.dismiss("cancel");
  };
};

angular.module('c4p').config(function($translateProvider) {
  $translateProvider.translations('en', {
    label_addItem: 'add new element',
    label_duplicateItem: 'duplicate',
    label_copyItemToClipboard: 'copy Item',
    label_copyAsAliasItem: 'copy As Alias-Item',
    label_hideItem: 'hide',
    label_unhideItem: 'unhide',
    label_removeItem: 'remove',
    label_cancelEditing: 'cancel editing',
    label_editChildren: 'edit child-items',
    label_editItem: 'edit item',
    label_cancel: 'cancel',
    label_hide: 'hide',
    label_unHide: 'un-hide',
    label_delete: 'delete',
    label_cut: 'cut',
    label_copy: 'copy',
    label_showHidden: 'show {num, plural, one {# hidden item} other {# hidden items} }',
    label_selectAll: 'select all',
    label_save: 'save',
    label_close: 'close',
    label_addItemHere: 'add item here',
    label_reorderItems: 're-order items',
    hl_chooseTheItemYouWantToAdd: 'choose the item you want to add',
    label_paste_n_items: 'paste {num, plural,one {1 item} other {# items} } from clipboard',
    hl_perform_actions: 'perform actions on {num, plural, one {selected item} other {# selected items} } :',
    text_deleteConfirmation: 'do you really want to delete {num, plural, one {this item} other {the selected items} }'
  });
  $translateProvider.translations('de', {
    label_addItem: 'neues Element hinzufügen',
    label_duplicateItem: 'duplizieren',
    label_copyItemToClipboard: 'kopieren',
    label_copyAsAliasItem: 'als Alias kopieren',
    label_hideItem: 'verstecken',
    label_unhideItem: 'einblenden',
    label_removeItem: 'löschen',
    label_cancelEditing: 'Bearbeitung abbrechen',
    label_editChildren: 'Sub-Elemente bearbeiten',
    label_editItem: 'Element bearbeiten',
    label_cancel: 'abbrechen',
    label_hide: 'verstecken',
    label_unHide: 'einblenden',
    label_delete: 'löschen',
    label_cut: 'ausschneiden',
    label_copy: 'kopieren',
    label_save: 'speichern',
    label_close: 'schliessen',
    label_addItemHere: 'neues Element einfügen',
    label_showHidden: '{num, plural, one {# verstecktes Element} other {# versteckte Elemente} } anzeigen',
    label_selectAll: 'alle auswählen',
    label_paste_n_items: '{num, plural,one {1 Element} other {# Elemente} } aus Zwischenablage einfügen',
    label_reorderItems: 'Elemente umsortieren',
    hl_chooseTheItemYouWantToAdd: 'wählen sie das Element das eingefügt werden soll',
    hl_perform_actions: 'für {num, plural,one {ausgewähltes Element} other {# ausgewählte Elemente} } :',
    text_deleteConfirmation: 'möchten Sie {num, plural, one {das ausgewählte Element} other {die ausgewählten Elemente} } wirklich löschen ?'
  });
  return $translateProvider.useMessageFormatInterpolation();
});
