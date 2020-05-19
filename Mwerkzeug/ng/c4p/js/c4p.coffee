
c4pApp=angular.module("c4p",  ['ui.tree','ui.bootstrap','pascalprecht.translate'])

angular.module("c4p").controller "c4pMainCtrl", ($scope,$translate)->
  $scope.app =
    serverinfo:
      clipboardsize:0


  $scope.reloadItemById= (itemId,nextaction)->
    # console.log "reload Item" , itemId  if window.console and console.log
    $scope.$broadcast 'reloadItem', {itemId:itemId, nextaction: nextaction}

  $scope.showErrorForItemById= (itemId,msg)->
    # console.log "reload Item" , itemId  if window.console and console.log
    $scope.$broadcast 'showErrorForItem', {itemId:itemId,msg:msg}

  $scope.renameItemId= (oldId,newId)->
    $scope.$broadcast 'renameItemId', {oldId:oldId, newId: newId}



angular.module("c4p").directive "c4pList", ->
  restrict: "E"
  replace: true
  templateUrl: "/Mwerkzeug/ng/c4p/partials/c4p-list.html"
  scope: 
    c4p_place: '@place'
    c4p_record: '@record'
    language: '@'
    placeconf: '@placeconf'
    app: '='

  link: (scope, element, attrs) ->
    # console.log "scope" , scope ,attrs if window.console and console.log
    if attrs.placeconfEncoded
        scope.settings.placeconf=scope.$eval(attrs.placeconfEncoded)


    if scope.settings?.placeconf?.max_width
      element.css('max-width', scope.settings.placeconf.max_width+"px")            

    if scope.settings?.placeconf?.max
      console.log "start watching" , null  if window.console and console.log

      scope.$watchCollection 'items', (newItems,oldItems)->
        scope.isAddingPossible=(newItems.length < scope.settings.placeconf.max)

  controller: ["$scope", "$element", "$attrs", "$filter", "$q", "$http", "$compile", "$timeout","$modal", "$translate", ($scope, $element, $attrs, $filter, $q, $http, $compile, $timeout, $modal, $translate ) ->

      $scope.settings={}
      $scope.settings.c4p_place=$scope.c4p_place
      $scope.settings.c4p_record=$scope.c4p_record
      $scope.settings.lang=$scope.language
      $translate.use($scope.settings.lang)

      # console.log "❖ c4p-list loaded with settings,attrs" , $scope.settings, $attrs if window.console and console.log

      $scope.mainListLoaded=$q.defer()
      $scope.listStatus='loading'
      $scope.listMode='list'
      $scope.globalEditMode=false
      $scope.childrenEditGroupname='_children'

      $scope.items=[]
      $scope.isAddingPossible=true
      $scope.selectedItems=[]
      $scope.hiddenItems=[]
      $scope.status={selectAll:false,showHidden:false}
      $scope.showAddArea=null
      $scope.list=
        permissions={}

    
      apiUrl='/BE/C4P_Api';
     
      $scope.refreshListing=->

        $http.post(apiUrl+'/listdata', {settings:$scope.settings}).then (res) ->
          if res.data and res.data.status is "ok"
            $scope.items= res.data.items
            $scope.app.serverinfo=res.data.serverinfo
            $scope.list.permissions=res.data.permissions
            $scope.listStatus='loaded'
            $scope.updateHiddenItems()
            $scope.mainListLoaded.resolve()


      $scope.refreshListing()

      filterFilter=$filter('filter')


      selectionRangeStartItem=null

      $scope.$watch "status.selectAll", (newValue, oldValue) ->
        console.log "selectAll" , newValue, oldValue if window.console and console.log
        newval=$scope.selectAll
        console.log "$scope.status.showHidden " , $scope.status.showHidden   if window.console and console.log
        angular.forEach $scope.items, (item) ->
          skipItem=false
          if item.hidden and not $scope.status.showHidden 
            if newValue
              skipItem=true
          if item.locked  || !item.permissions.delete
            skipItem=true            
          item.selected=newValue unless skipItem

        selectionRangeStartItem=null  
        $scope.updateSelectedItems()


      $scope.handleSelectBox = (item, event) ->
        console.log "cb" , item, event  if window.console and console.log
        if event and event.shiftKey 
          if selectionRangeStartItem
            #select all between here and selectionRangeStartItem
            selectMode=false
            angular.forEach $scope.items, (i) ->
              if i is lastSelectedItem
                selectMode=true
              else if i is item
                selectMode=false
              else if selectMode
                i.selected=true
        else
          unless item.selected
            lastSelectedItem=item
          else            
            lastSelectedItem=null




      $scope.updateSelectedItems = ->
        $scope.selectedItems=filterFilter($scope.items,{selected:true})
        $scope.updateHiddenItems()

      $scope.updateHiddenItems = ->
        $scope.hiddenItems=filterFilter($scope.items,{hidden:true})


      $scope.treeOptions = 
        accept: (sourceNodeScope, destNodesScope, destIndex)->

          # console.log "try to drop " ,sourceNodeScope, destNodesScope if window.console and console.log

          sourceElement = if sourceNodeScope.hasOwnProperty 'i' then sourceNodeScope.i else sourceNodeScope.c
          droppedCtype=sourceElement?.ctype
          return false unless droppedCtype

          destElement = if destNodesScope.$parent.hasOwnProperty 'i' then destNodesScope.$parent.i else destNodesScope.$parent.c

          if(destElement)
            allowed_types=destElement.config._children.allowed_types
          else
            allowed_types=$scope.settings.placeconf.allowed_types 

          return false unless allowed_types

          # console.log "allowed_types,droppedCtype " , allowed_types,droppedCtype  if window.console and console.log


          if allowed_types.hasOwnProperty droppedCtype
            return true

          return false




      $scope.setListMode =(newMode)->
        $scope.listMode=newMode
        if newMode is 'sort'
          $scope.sortableItems=angular.copy($scope.items)
        $scope.saveOrderInProgress=false

      $scope.saveOrder=->
        # console.log "save order" , null  if window.console and console.log
        $scope.saveOrderInProgress=true
        sortable_item_ids=$scope.sortableItems.map (item) -> {ctype: item.ctype, id:item.id}
        console.log "save order" , sortable_item_ids  if window.console and console.log
        
        $http.post(apiUrl+'/saveorder', {id:$scope.items[0].id,items:sortable_item_ids,settings:$scope.settings}).then (res) ->
          if res.data and res.data.status is "ok"
            $scope.items=$scope.sortableItems
            $scope.setListMode ('list')
            $scope.reloadPreview(res.data.preview_url)


      $scope.multiAction=(action, event)->
        $scope.multiActionLoading=true
        $scope[action+'Items'].call(this,$scope.selectedItems,event).then ->
          console.log "then called" , null  if window.console and console.log
          $scope.multiActionLoading=false


       


      $scope.getOtherAllowedTypes=(item)-> 
        unless item.hasOwnProperty 'otherAllowedTypes'
          item.otherAllowedTypes=[]
          for typeKey, typeRec of $scope.settings.placeconf.allowed_types
            typeRec.key=typeKey
            unless item.ctype is typeKey
              item.otherAllowedTypes.push typeRec

        item.otherAllowedTypes


      $scope.setItemType=(item, newType)->
        # console.log "set item type" , item,newType  if window.console and console.log
        $element.find('#c4p-ctypefield').val(newType)
        $element.find('#c4p-nextactionfield').val('edit')

        $scope.submitItem(item)
        return

      $scope.cancelItem=(item)->
        $scope.globalEditMode=false
        item.editable=false
        item.editready=false

        editform =$element.find('#editform-'+item.id)
        editform.html('cancelled')
        console.log "emptied editform" , editform  if window.console and console.log


        if item.childreneditready
          $scope.reloadItem(item)
        item.childreneditready=false
        if item.isNew 
          $scope.items.splice($scope.items.indexOf(item),1)


      $scope.editItem=(item, params, event)->
        console.log "editItem" , item, params  if window.console and console.log
        return false if $scope.globalEditMode || item.locked || (item.is_alias && !params.edit_alias) || (item.permissions && (!item.permissions.edit && !item.permissions.editChildren))
        
        if event and event.altKey and event.metaKey
          params={edit_json:true,force:true}

        if item.config && item.config._children && !params?.force
          $scope.childrenEditItem(item,params)
        else

          item.submitting=false

          params={} unless params
          # console.log "editItem" , item,params  if window.console and console.log
          $scope.globalEditMode=true

          editform =$element.find('#editform-'+item.id)
          item.editable=true
          item.editready=false
          item.childreneditready=false

          $http.post(apiUrl+'/editform', {id:item.id,params:params,settings:$scope.settings}).then (res) ->
            if res.data and res.data.status is "ok"
              item.editready=true
              linkingFunction = $compile res.data.html
              elem = linkingFunction $scope
              editform.contents().remove()
              editform.append elem
              return

      $scope.childrenEditItem=(item, params)->
        params={} unless params
        #console.log "childrenEditItem" , item,params  if window.console and console.log
        $scope.childrenEditGroupname='_children'
        if params.groupname 
          $scope.childrenEditGroupname='_children_'+params.groupname 

        item.childreneditready=true
        $scope.globalEditMode=true

        item.editready=false

      $scope.callActionOnItem=(item,action,args)->
        $scope.globalEditMode=true
        item.submitting=true

        params=
          action:action
          args:args

        $http.post(apiUrl+'/customaction', {id:item.id,params:params,settings:$scope.settings}).then (res) ->
            if res.data and res.data.status is "ok"
              $scope.globalEditMode=false
              item.submitting=false
              $scope.reloadItem(item)


      $scope.submitItem=(item,event)->
        # console.log "submit item" , item,event  if window.console and console.log
  
        form=$element.find('#editform-'+item.id+' form')

        if form.valid()
          item.submitting=true
          form.submit()

        return true



      $scope.getItemForId=(id)->
        res=filterFilter($scope.items,{id:id})
        if res.length
          return res[0]
        else
          return null


      $scope.$on 'showErrorForItem', (event, params)->

        # console.log "try to reload Item in scope" , params  if window.console and console.log
        item =$scope.getItemForId(params.itemId)
        if item
          $scope.showErrorForItem(item,params)

      $scope.showErrorForItem=(item,params)->
        alert(params.msg)
        item.submitting=false
        item.editready=true
        console.log "item" , item  if window.console and console.log


      $scope.$on 'reloadItem', (event, params)->

        # console.log "try to reload Item in scope" , params  if window.console and console.log
        item =$scope.getItemForId(params.itemId)
        if item
          $scope.reloadItem(item,params)

      $scope.reloadItem=(item,params)->
         $scope.globalEditMode=false
         item.editable=false
         item.editready=false
         item.loading=true
         $http.post(apiUrl+'/getitem', {id:item.id,settings:$scope.settings}).then (res) ->
          if res.data and res.data.status is "ok"
            idx=$scope.items.indexOf(item)
            $scope.items[$scope.items.indexOf(item)]=res.data.item
            # console.log "reloaded" , params  if window.console and console.log

            $scope.reloadPreview()

            if params && params.nextaction is 'edit'
              $timeout( ->
                $scope.editItem($scope.items[idx])
              ,500)

      $scope.$on 'renameItemId', (event, params)->
        item =$scope.getItemForId(params.oldId)
        if item
          item.id=params.newId


      createNewId=()->
        ts=Math.round(new Date().getTime() / 1000) 
        ts="#{ts}"
        ts=ts.substring(4)
        rand=Math.floor(Math.random() * 1000);
        newid="#{ts}#{rand}"

      $scope.duplicateItem=(item)->
        $scope.createItem({newitem_duplicateof:item})

      $scope.getDefaultCtype= ->
        for firstkey of $scope.settings.placeconf.allowed_types 
          break
        firstkey

      $scope.addItem=(nextItem)->

        $http.post(apiUrl+'/get_allowed_types_to_add',{settings:$scope.settings,allowedTypes:$scope.settings.placeconf.allowed_types,nextItem:nextItem}).success (data)->
          if data.status is 'ok'
            $scope.allowedTypesForAdd=data.payload
            typeCount= Object.keys($scope.allowedTypesForAdd).length
            if typeCount is 1
              $scope.createItem({ctype:Object.keys($scope.allowedTypesForAdd)[0],before:nextItem})
            else if typeCount > 1
              key='item_'
              if nextItem
                key+=nextItem.id
              $scope.showAddArea=key
            else
              alert 'sorry, no item can be created here'

      $scope.resetAddItem= ->
        $scope.showAddArea=null



      $scope.createItem=(params)->

        $scope.showAddArea=null
        params={} unless params
        # console.log "additem" , params  if window.console and console.log
        newitem ={}
        newitem.id=createNewId()
        newitem.rec={}
        newitem.isNew=1
        if params.newitem_duplicateof
          newitem.ctype=params.newitem_duplicateof.ctype
        else if params.ctype
          newitem.ctype=params.ctype
        else
          newitem.ctype=$scope.getDefaultCtype()
        newitem.html='...'

        newitemDefaults=
          id:newitem.id
          ctype:newitem.ctype

        editparams={'newitem':1,'newitem_defaults':newitemDefaults}

        if params.before 
          editparams.newitem_before=params.before.id
          $scope.items.splice($scope.items.indexOf(params.before),0,newitem)
        else if params.newitem_duplicateof 
          editparams.newitem_duplicateof=params.newitem_duplicateof.id
          $scope.items.splice($scope.items.indexOf(params.newitem_duplicateof)+1,0,newitem)
        else        
          $scope.items.push(newitem)
      
        $timeout( ->
          $scope.editItem(newitem,editparams)
        ,500)


      $scope.reloadPreview = (url)->
        $scope.updateHiddenItems()
        unless url
          url=angular.element('#previewframe').attr('src')
          url=url.split("?")[0]+'?preview='+new Date().getTime();
        if url
          angular.element('#previewframe').attr('src',url)
        return

      getItemArrayForItemOrItemList = (itemOrItemlist)->
        if angular.isArray itemOrItemlist
          return itemOrItemlist
        else
          return [itemOrItemlist]

      getItemIdsForItemList = (itemlist)->
        ids=[]
        for item in itemlist
          ids.push(item.id)
        return ids

      $scope.pasteItems=(event, position={})->

        if event and event.altKey and event.metaKey
          angular.element('body').addClass('c4p') 
          modalInstance = $modal.open
            templateUrl: "/Mwerkzeug/ng/c4p/partials/c4p-paste_clipboard_as_json.html"
            controller:PasteClipboardAsJsonCtrl
            scope:$scope
          modalInstance.result.then (str2paste)-> 
            $scope.doPasteItems(str2paste,position)
            angular.element('body').removeClass('c4p')
            return
          , ->
            angular.element('body').removeClass('c4p')
            return
        else
          $scope.doPasteItems('clipboard',position)
  
      $scope.doPasteItems=(jsondata,position)->

        $http.post(apiUrl+'/get_allowed_types_to_add',{settings:$scope.settings,allowedTypes:$scope.settings.placeconf.allowed_types,nextItem:null}).success (data)->
          if data.status is 'ok'
            allowedTypesForAdd=data.payload
            $http.post(apiUrl+'/pasteitems', {settings:$scope.settings,jsondata:jsondata,position:position,allowedTypesForAdd:allowedTypesForAdd}).then (res) ->
              if res.data and res.data.msg 
                if res.data.status is 'error'
                  alert res.data.msg
                if res.data.status is 'warning'
                  if window.top.callAngularFunction
                    window.top.callAngularFunction('showNotification',res.data.msg,0)
                $scope.refreshListing()
                $scope.reloadPreview()
              else
                alert "an error occured"
          
      $scope.hideItem=(item)->
        $scope.hideItems([item])

      $scope.unhideItem=(item)->
        $scope.unhideItems([item])

      $scope.copyItem=(item)->
        $scope.copyItems([item])

      $scope.copyaliasItem=(item)->
        $scope.copyaliasItems([item])


      $scope.cutItems=(items, event)->
        $scope.copyItems(items,event).then ->
          $scope.removeItems(items,event,true)


      $scope.copyaliasItems=(items, event)->
        $scope.copyItems(items,event, {'makealias':true})
        

      $scope.copyItems=(items, event, options)->
        items=getItemArrayForItemOrItemList(items)
        itemIds=getItemIdsForItemList(items)
        options=options or {}

        options.storage='session'

        if event and event.altKey and event.metaKey
          # add c4p-class to body, so the modal will be visible
          options.storage='text'

        $http.post(apiUrl+'/copyitems', {id:itemIds[0],items:itemIds,settings:$scope.settings,options:options}).then (res) ->
          if res.data and res.data.status is "ok"
            console.log "#{res.data.num_items} items added to the clipboard" , null  if window.console and console.log
            $scope.app.serverinfo.clipboardsize=res.data.num_items
            if res.data.data
              $scope.value2copy=res.data.data
              angular.element('body').addClass('c4p') 
              modalInstance = $modal.open
                templateUrl: "/Mwerkzeug/ng/c4p/partials/c4p-copy_clipboard_as_json.html"
                controller:CopyClipboardAsJsonCtrl
                scope:$scope
              modalInstance.result.finally -> 
                angular.element('body').removeClass('c4p')
                $scope.value2copy=null
          else
            alert "an error occured"
          return

      $scope.hideItems=(items, event)->
        items=getItemArrayForItemOrItemList(items)
        itemIds=getItemIdsForItemList(items)
        $http.post(apiUrl+'/hideitems', {ids:itemIds,settings:$scope.settings}).then (res) ->
          if res.data and res.data.status is "ok"
            for item in items
              $scope.items[$scope.items.indexOf(item)].hidden=true
            $scope.reloadPreview(res.data.preview_url)
          else
            alert "an error occured"

      $scope.unhideItems=(items, event)->
        items=getItemArrayForItemOrItemList(items)
        itemIds=getItemIdsForItemList(items)
        $http.post(apiUrl+'/unhideitems', {ids:itemIds,settings:$scope.settings}).then (res) ->
          if res.data and res.data.status is "ok"
            for item in items
              $scope.items[$scope.items.indexOf(item)].hidden=false
            $scope.reloadPreview(res.data.preview_url)
          else
            alert "an error occured"
        

      $scope.removeItems=(items, event, forceDelete)->
        items=getItemArrayForItemOrItemList(items)
        itemIds=getItemIdsForItemList(items)

        confirmMsg=$translate.instant 'text_deleteConfirmation', { num: itemIds.length }
        console.log confirmMsg  if window.console and console.log

        if forceDelete or confirm confirmMsg
          $http.post(apiUrl+'/removeitems', {ids:itemIds,settings:$scope.settings}).then (res) ->
            if res.data and res.data.status is "ok"
              for item in items
                $scope.items.splice($scope.items.indexOf(item),1)
              $scope.reloadPreview(res.data.preview_url)
              $scope.updateSelectedItems()
            else
              alert "an error occured"
        else
          def=$q.defer()
          def.resolve()
          return def.promise



  ]

c4pApp.filter "getLabel", ($filter)->
  (input, dict) ->
    if angular.isArray(dict)
      res=$filter('filter')(dict,{'code':input})
      return res.pop().label
    else
      if dict[input]
        dict[input].label
      else
        input


angular.module("c4p").directive "c4pPreviewTemplate", ($compile) ->

 
  linker = (scope, element, attrs) ->
    scope.listMode=attrs.listMode
    scope.item=scope.$eval(attrs.item)
    scope.template=scope.$eval(attrs.template)
    element.html(scope.template).show()
    $compile(element.contents()) scope
    return

  restrict: "E"
  replace: true
  link: linker
  scope: true
    # item: "="
    # template: "="
    # listMode: "@"


# same directive w/o extra scope for being able to sort the stuff
angular.module("c4p").directive "c4pSortPreviewTemplate", ($compile) ->
 
  linker = (scope, element, attrs) ->
    element.html(scope.$eval(attrs.template)).show()
    $compile(element.contents()) scope
    return

  restrict: "E"
  replace: true
  link: linker
 


angular.module("c4p").directive "c4pCreateItemTypeChooser", ->
  restrict: "EA"
  # transclude: true
  replace:true
  scope:false
  templateUrl: "/Mwerkzeug/ng/c4p/partials/c4p_create_item_type_chooser.html"



angular.module("c4p").directive "c4pChildPreview", ($compile) ->

  linker = (scope, element, attrs) ->
    element.removeAttr(attrs.$attr.c4pChildPreview)
    if scope.listMode is 'sort'
      element.hide()
    else
      element.attr('ng-repeat', 'i in item._children');
      element.attr('ng-bind-html','i.html | trusted');
      $compile(element) scope
    return

  restrict: "A"
  replace: true
  link: linker


angular.module("c4p").directive "c4pChildlist", ($compile)->
  restrict: "E"
  scope:false
  link: (scope, elm, attr) ->
    tplHtml="<div class='c4p-childlist'><c4p-list app='app' place='"+attr.place+"' record='"+attr.record+"' placeconf-encoded='"+attr.placeconfig+"'>child-link directive here</c4p-list></div>"
    elm.html tplHtml
    # console.log "link:" , tplHtml  if window.console and console.log
    $compile(elm.contents()) scope
    return


trusted ={}
c4pApp.filter 'trusted',['$sce', ($sce)->
        return (html)->
          return trusted[html] || (trusted[html]=$sce.trustAsHtml(html));
]



CopyClipboardAsJsonCtrl = ($scope, $modalInstance) ->

  $scope.str2copy=angular.toJson($scope.value2copy, true)

  $scope.cancel = ->
    $modalInstance.dismiss "cancel"
    return

  return


PasteClipboardAsJsonCtrl = ($scope, $modalInstance) ->

  $scope.fdata=
    str2paste:'paste here'

  $scope.ok = ->
    console.log "do" , $scope.fdata.str2paste  if window.console and console.log
    $modalInstance.close $scope.fdata.str2paste
    return

  $scope.cancel = ->
    $modalInstance.dismiss "cancel"
    return

  return



angular.module('c4p').config ($translateProvider) ->
  $translateProvider.translations 'en', 
    label_addItem:  'add new element'
    label_duplicateItem: 'duplicate'
    label_copyItemToClipboard: 'copy Item'
    label_copyAsAliasItem: 'copy As Alias-Item'
    label_hideItem: 'hide'
    label_unhideItem: 'unhide'
    label_removeItem: 'remove'
    label_cancelEditing: 'cancel editing'
    label_editChildren: 'edit child-items'
    label_editItem: 'edit item'
    label_cancel: 'cancel'
    label_hide: 'hide'
    label_unHide: 'un-hide'
    label_delete: 'delete'
    label_cut: 'cut'
    label_copy: 'copy'
    label_showHidden: 'show {num, plural, one {# hidden item} other {# hidden items} }'
    label_selectAll: 'select all'
    label_save: 'save'
    label_close: 'close'
    label_addItemHere: 'add item here'
    label_reorderItems: 're-order items'
    hl_chooseTheItemYouWantToAdd:'choose the item you want to add'
    label_paste_n_items: 'paste {num, plural,one {1 item} other {# items} } from clipboard'
    hl_perform_actions:'perform actions on {num, plural, one {selected item} other {# selected items} } :'
    text_deleteConfirmation: 'do you really want to delete {num, plural, one {this item} other {the selected items} }'


    
  $translateProvider.translations 'de', 
    label_addItem:  'neues Element hinzufügen'
    label_duplicateItem: 'duplizieren'
    label_copyItemToClipboard: 'kopieren'
    label_copyAsAliasItem: 'als Alias kopieren'
    label_hideItem: 'verstecken'
    label_unhideItem: 'einblenden'
    label_removeItem: 'löschen'
    label_cancelEditing: 'Bearbeitung abbrechen'
    label_editChildren: 'Sub-Elemente bearbeiten'
    label_editItem: 'Element bearbeiten'
    label_cancel: 'abbrechen'
    label_hide: 'verstecken'
    label_unHide: 'einblenden'
    label_delete: 'löschen'
    label_cut: 'ausschneiden'
    label_copy: 'kopieren'
    label_save: 'speichern'    
    label_close: 'schliessen'
    label_addItemHere: 'neues Element einfügen'
    label_showHidden: '{num, plural, one {# verstecktes Element} other {# versteckte Elemente} } anzeigen'
    label_selectAll: 'alle auswählen'
    label_paste_n_items: '{num, plural,one {1 Element} other {# Elemente} } aus Zwischenablage einfügen'
    label_reorderItems: 'Elemente umsortieren'
    hl_chooseTheItemYouWantToAdd:'wählen sie das Element das eingefügt werden soll'
    hl_perform_actions:'für {num, plural,one {ausgewähltes Element} other {# ausgewählte Elemente} } :'
    text_deleteConfirmation: 'möchten Sie {num, plural, one {das ausgewählte Element} other {die ausgewählten Elemente} } wirklich löschen ?'

  $translateProvider.useMessageFormatInterpolation();
  # $translateProvider.preferredLanguage('de');


