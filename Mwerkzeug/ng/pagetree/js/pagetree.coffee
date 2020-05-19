
pagetreeApp=angular.module("pagetree", [])

angular.module("pagetree").controller "pagetreeMainCtrl", ($scope, $http, $q)->
  $scope.selectedTreeNode=null

  $scope.actionForm=jQuery('#actionform')
  $scope.reloadForm=jQuery('#reloadForm')

  $scope.app=
    settings:{}
    currentClickMode:'edit'

  $scope.app.topWindow=window.top
  

  $scope.init=(settings)->
    $scope.app.settings=angular.extend($scope.app.settings,settings)

    if $scope.app.settings.treeGroups
      $scope.app.tabList=$scope.app.settings.treeGroups

      if $scope.app.settings.treeGroupName
        $scope.app.activeTabKey=$scope.app.settings.treeGroupName
      else
        $scope.app.activeTabKey=$scope.app.tabList[0].key

      $scope.app.setTreeGroupName $scope.app.activeTabKey

    console.log "pagetreeMainCtrl init called" , $scope.app.settings  if window.console and console.log

  $scope.app.callTopWindowAngularFunction=(functionName, data)->
    promise=$scope.app.topWindow.callAngularFunction(functionName,data)

  $scope.opentest=->
    url='/BE/Pages/show/495'
    window.open(url,'_blank');       

  $scope.viewPage=(id)->
    if id>0
      url='/BE/Pages/preview/'+id
      $scope.actionForm.attr('action',url)
      # console.log "submit" , url, $scope.actionForm[0] if window.console and console.log
      $scope.actionForm.submit()

  $scope.editPage=(id)->
    if id>0
      url='/BE/Pages/edit/'+id
      $scope.actionForm.attr('action',url)
      # console.log "submit" , url, $scope.actionForm[0] if window.console and console.log
      $scope.actionForm.submit()

  $scope.choosePage=(id)->
    defered=$q.defer()
    $scope.app.callTopWindowAngularFunction('runPageAction',{pageId:id,onChoose:defered,command:{name:'choose_page'}}).then (response)->
      # console.log "choosePage-response" , response  if window.console and console.log

    defered.promise.then (mwlink)->
      # console.log "setMwLink" , mwlink  if window.console and console.log
      window.parent.setMwLink(mwlink)

  $scope.app.setCurrentClickMode=(newMode)->
    console.log "setCurrentClickMode" , $scope.app  if window.console and console.log
    $scope.app.currentClickMode=newMode
    if $scope.app.topWindow.app.currentPageTreeId
      $scope.app.clickPage($scope.app.topWindow.app.currentPageTreeId)
    return true

  $scope.back2edit=()->
    $scope.editPage $scope.app.topWindow.app.currentPageTreeId


  $scope.app.clickPage=(id)->
    $scope.app.topWindow.app.currentPageTreeId=id

    if $scope.app.settings.mode is 'linkchooser'
      $scope.choosePage(id)
    else
      console.log "clickPage" , id, $scope.app.currentClickMode  if window.console and console.log
      if $scope.app.currentClickMode is 'preview'
        $scope.viewPage(id)
      else 
        $scope.editPage(id)


  $scope.app.choosePortal=(key)->
    $scope.app.setTreeGroupName(key)
    $scope.app.reload()
    return 

  $scope.app.setTreeGroupName=(key)->
    jQuery('#treeGroupName').val(key)

  $scope.app.reload=()->
    $scope.reloadForm.submit()


pagetreeApp.directive 'jstreeChooser', ($timeout, $http) ->
  restrict: 'A'
  templateUrl: '/Mwerkzeug/ng/pagetree/partials/pagetree-chooser.html'
  scope:
    app: '='
    labelEditMode :'@'
    labelPreviewMode :'@'
    labelClickMode: '@'

  controller: ["$scope", "$element", "$attrs", "$filter", "$q", "$http", ($scope, $element, $attrs, $filter, $q, $http ) ->



  ]


pagetreeApp.directive 'jstree', ($timeout, $http) ->
  {
    restrict: 'A'
    require: '?ngModel'
    scope:
      selectedNode: '=?'
      selectionChanged: '='
      app: '='
      context: '@'

    link: (scope, element, attrs) ->

      expandAndSelect = (ids) ->
        ids = ids.slice()

        expandIds = ->
          if ids.length == 1
            treeElement.jstree 'deselect_node', treeElement.jstree('get_selected')
            treeElement.jstree 'select_node', ids[0]
          else
            treeElement.jstree 'open_node', ids[0], ->
              ids.splice 0, 1
              expandIds()
              return
          return

        expandIds()
        return


      scope.selectedNode = scope.selectedNode or {}
      jsTreeSettings=  
        core:
          multiple:false
          themes:
            dots:false
            icons:true
            stripes:true
          check_callback : (operation, node, node_parent, node_position, more) ->
            # operation can be 'create_node', 'rename_node', 'delete_node', 'move_node' or 'copy_node'
            # in case of 'rename_node' node_position is filled with the new node name
            # if operation is 'rename_node' then true else false
            return true

          data: (obj,callback) ->
            # show loading indicator
            savedIcon=obj.icon
            treeElement.jstree('set_icon',obj,'fa fa-spinner fa-spin')
            # callback.call(this,[{
            #   icon:'fa fa-spinner fa-spin'
            #   text:'... loading'
            #   }])

            queryParams=[]
            queryParams.push 'tgn='+scope.app.activeTabKey  if scope.app.activeTabKey 
            queryParams.push 'id='+obj.id
            queryParams.push 'curr='+scope.app.settings.idOfCurrentPage

            $http.post('/BE/Pages/ajaxTreeData_v2?'+queryParams.join('&')).success (data)->
              treeElement.jstree('set_icon',obj,savedIcon)
              angular.forEach data, (node) ->
                state={}
                if scope.app.settings.parentIdsOfCurrentPage and scope.app.settings.parentIdsOfCurrentPage.indexOf(node.id) > -1
                  state.opened=true
                  setstate=true
                if scope.app.settings.idOfCurrentPage is node.id
                  state.selected=true
                  setstate=true

                if setstate 
                  node.state=state

                node.a_attr.href='/BE/Pages/edit/'+node.id

              callback.call(this,data) 
              

        plugins: ['themes','json_data','ui','contextmenu','dnd','state','wholerow']
        state:
          filter: (state)->
            if scope.app.settings.idOfCurrentPage > 0 and state?.core?.selected
              delete state.core.selected
            # console.log "statefilter" , state  if window.console and console.log
            state
        dnd:
          copy:false
          always_copy:true
          inside_pos:'first'
          check_while_dragging:false
          is_draggable:(nodes)->
            if nodes[0].icon.match('draggable') or window.event.shiftKey 
              return true
            return false

        contextmenu:
          select_node:false
          items: (obj, callback)->
            contextMenuAlreadyOpen=jQuery('.jstree-contextmenu').length
            if contextMenuAlreadyOpen
              $.vakata.context.hide()
              return

            if scope.app.settings.mode is 'linkchooser'
              return 
            scope.app.callTopWindowAngularFunction('getContextMenuItemsForPageTreeItem',obj).then (response)->
              response.payload.then (actionmenuItems)->
                menuItems=
                  headline:
                    label:"#{obj.text}:"
                    _disabled:true
                    action: ->
                      null
                angular.forEach actionmenuItems, (menuItem) ->
                  
                  if menuItem.submenu and menuItem.submenu.length>0
                    newSubHeadline=
                      label:menuItem.label
                      _disabled:true
                      action: ->
                        null
                    menuItem.submenu.unshift newSubHeadline
                      
                  angular.forEach menuItem.submenu, (subMenuItem) ->
                    subMenuItem.action =->
                      scope.app.callTopWindowAngularFunction('runPageAction',{pageId:obj.id,menuItem:subMenuItem})

                  menuItem.action =->
                    scope.app.callTopWindowAngularFunction('runPageAction',{pageId:obj.id,menuItem:menuItem})
                  menuItems[menuItem.key]=menuItem

                console.log "menuItems=" , menuItems  if window.console and console.log
                callback.call(this, menuItems)


            loadingMenu=
              headline:
                label:"#{obj.text}:"
                _disabled:true

              loading:
                label:"...loading"
                icon:'fa fa-spinner fa-spin'
                _disabled:true

    


      jsTreeSettings=angular.extend(jsTreeSettings,scope.app.settings.jstree)
      treeElement = $(element)
      tree = treeElement.jstree(jsTreeSettings)

      ajax_again="to-be-refreshed"

      if window.parent.setPageTreeRef
        window.parent.setPageTreeRef(jQuery.jstree.reference(element))
  
      topScope=
        app:window.parent.app

      tree.bind 'open_node.jstree close_node.jstree', (e, data) ->
        currentNode = data.instance.get_node(data.node,1)
        if e.type == 'close_node'
          currentNode.addClass ajax_again
        if e.type == 'open_node'
          if currentNode.hasClass(ajax_again)
            data.instance.refresh_node data.node


      tree.bind 'copy_node.jstree', (e, data) ->
        console.log "copy node" , data  if window.console and console.log
        
        menuItem={command:{name:'move_page'}}
        args={pageId:data.original.id,menuItem:menuItem,moveInfo:{newparent:data.parent,position:data.position}};
        
        data.instance.delete_node data.node # delete copied node in all cases
        
        scope.app.callTopWindowAngularFunction('runPageAction',args).then (response)->
          response.payload.then (response)->
            console.log "runPageAction res" , response  if window.console and console.log
            if response.success  #if success, really move node
              data.instance.move_node data.original, data.parent, data.position
 


      tree.bind 'set_state.jstree', (e) ->
        # console.log "set state called" , e  if window.console and console.log
      tree.bind 'activate_node.jstree', (e,data)->

        # console.log "activate_node called with event:" , e  if window.console and console.log
        n = data.instance.get_selected true
        if n
          n = n[0]
          console.log "clicked node=" ,n  if window.console and console.log
          scope.selectedNode.id = n.id
          scope.selectedNode.path = n.a_attr.path
          scope.selectedNode.text = n.text

          if n.a_attr.class?.match('expand_only')
            console.log "expand node" , n  if window.console and console.log
            data.instance.toggle_node n
          else
            scope.app.clickPage(n.id)

          if topScope.app.lastPageIdMarkedForDragging
            topScope.app.unmarkPageForDragging(topScope.app.lastPageIdMarkedForDragging)
            topScope.app.lastPageIdMarkedForDragging=null


                # $timeout ->
        #   n = treeElement.jstree('get_selected', true)
        #   if n
        #     n = n[0]
        #     scope.selectedNode.id = n.id
        #     scope.selectedNode.path = n.a_attr.path
        #     scope.selectedNode.text = n.text
        #     if scope.selectionChanged
        #       $timeout ->
        #         scope.selectionChanged scope.selectedNode
        #         return
        #   return
        # return

      # scope.$watch 'selectedNode.id', ->
      #   selectedIds = treeElement.jstree('get_selected')
      #   if selectedIds.length == 0 and scope.selectedNode.id or selectedIds.length != 1 or selectedIds[0] != scope.selectedNode.id
      #     if selectedIds.length != 0
      #       treeElement.jstree 'deselect_node', treeElement.jstree('get_selected')
      #     if scope.selectedNode.id
      #       treeElement.jstree 'select_node', scope.selectedNode.id
      #   return
      
      return

  }

# ---
# generated by js2coffee 2.0.3
