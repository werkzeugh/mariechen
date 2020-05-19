define ->
  coreModule=angular.module("coreModule", ['mc.resizer','oc.lazyLoad','pascalprecht.translate'])

  coreModule.config ['$controllerProvider',($controllerProvider)->
    coreModule.registerController=$controllerProvider.register
  ]

  coreModule.controller "pageframeMainCtrl", ($scope, $http, $q, $controller, $element, $rootScope, $ocLazyLoad,  $injector, $compile, $timeout)->

    # console.log "pageframeMainCtrl loaded app=" , app  if window.console and console.log

    $scope.app=app
    $scope.app.clipboard=[]

    $scope.app.getPageInPageTree=(pageId)->
      $scope.app.pageTreeRef.get_node(pageId)

    $scope.callPageManager=(command, args)->
      defered=$q.defer()
      $http.post('/BE/Pages/ng_pagemanager/'+command,args).success (data)->
        if data.status is 'ok'
          defered.resolve(data.payload)
        else
          defered.reject(data)
      return defered.promise

    $scope.getContextMenuItemsForPageTreeItem=(node)->
      defered=$q.defer()
      $http.post('/BE/Pages/ng_pagemanager/actionmenuItemsForPage',{'id':node.id,'clipboard':$scope.app.clipboard}).success (data)->
        if data.status is 'ok'
          defered.resolve(data.payload)
        else
          defered.reject('cannot read the data received')

      return defered.promise

    $scope.loadActionController=(name)->
      defered=$q.defer()

      # require ['/Mwerkzeug/ng/pageframe/actions/action_'+name+'.js'+$scope.debugTimestamp()], ->
      #   defered.resolve() , (err)->

      require ['/mysite/ng/pageframe/actions/action_'+name+'.js'+$scope.debugTimestamp()], 
        ->
          defered.resolve()
        (err) ->
          require ['/Mwerkzeug/ng/pageframe/actions/action_'+name+'.js'+$scope.debugTimestamp()], 
            ->
              defered.resolve()
        
      return defered.promise

    $scope.executeController=(options)->
      controllerName = options.controller
      if controllerName
        controllerScope = $rootScope.$new()
        inputs =
          $scope: controllerScope
        #  If we have provided any inputs, pass them to the controller.
        if options.inputs
          angular.forEach options.inputs, (dummy,inputName) ->
            inputs[inputName] = options.inputs[inputName]
        # console.log "init controller" , controllerName, inputs  if window.console and console.log
        myController = $controller(controllerName, inputs)

    $scope.app.failPopup=(response)->
      console.log "response failed, show popup" , response  if window.console and console.log
      $scope.app.showModal('ErrorPopup',response)
      $scope.app.pageTreeRef.refresh()

    $scope.runPageAction=(args)->
      coreModuleScope=$scope
      defered=$q.defer()

      if args.command
        command=args.command
      else if args.menuItem.command
        command=args.menuItem.command

      if command.args
         args.args=command.args

      #quick actions w/o controllers here:
      if command.name is 'preview_page'
        url='/BE/Pages/show/'+args.pageId
        return window.open(url,'_blank')     

      if command.name is 'preview_page_framed'
        url='/BE/Pages/show/'+args.pageId
        return window.open(url,'rightframe')       

      args.defered=defered
      
      $scope.loadActionController(command.name).then ->
        $scope.executeController
            controller: "action_#{command.name}_controller"
            inputs: 
              topScope:coreModuleScope
              params:args

      # $http.post('/BE/Pages/ng_pagemanager/runPageAction',args).success (data)->
      #   if data.status is 'ok'
      #     $scope.showModal('YesNo',args)
      #     defered.resolve(data.payload)
      #   else
      #     defered.reject('cannot read the data received')

      return defered.promise

    $scope.showNotification=(msg,timeout=3000)->
      timeoutValue=timeout
      jQuery('#notificationBubble .msg').html(msg)
      jQuery('#notificationBubble').slideDown()
      hideNotificationFunction=->
        jQuery('#notificationBubble').slideUp()
      if(timeoutValue>0)
        $timeout( hideNotificationFunction
        ,timeoutValue)
      else
        closeButton=jQuery("<i class='fa fa-times fa-lg pull-right act-as-link'></i>").on('click',hideNotificationFunction)
        jQuery('#notificationBubble .msg').append(closeButton)
        return hideNotificationFunction

    $scope.setCurrentRightFrameMode=(newMode)->
      $scope.app.currentRightFrameMode=newMode;
      console.log "setCurrentRightFrameMode set to " , $scope.app.currentRightFrameMode  if window.console and console.log

    $scope.back2edit=()->
      $scope.setCurrentRightFrameMode 'edit'
      $scope.editPage $scope.app.currentPageTreeId


    $scope.editPage=(id)->
      console.log "editPage #{id}" if window.console and console.log
      url="/BE/Pages/edit/"+id
      frames['rightframe'].location=url  
      
    $scope.selectPage=(id)->
      tree=$scope.app.pageTreeRef
      tree.deselect_node(tree.get_selected())
      ret=tree.select_node(id)
      $scope.setCurrentPageTreeId id

      if ret is false
        frames['leftframe'].location='/BE/Pages/treeframe/'+id
      
    $scope.setCurrentPageTreeId=(id)->
      $scope.app.currentPageTreeId=id
      frames['leftframe'].jQuery('#reloadForm').prop('action','/BE/Pages/treeframe/'+id)
      $scope.reloadNodeInPageTree(id)

    $scope.reloadNodeInPageTree=(id)->
      $scope.callPageManager('getNodeData',{pageId:id}).then (nodeData)->
        tree=$scope.app.pageTreeRef
        if tree
          tree.set_text(id,nodeData.text)
          tree.set_icon(id,nodeData.icon)
          node=tree.get_node(id,true)
          if node
            node_anchor=node.find('> .jstree-anchor')
            oldClasses=node_anchor.attr('class').split(/\s+/)

            anchorClasses=[]  
            angular.forEach oldClasses, (className) ->
              anchorClasses.push className if className.match /^jstree-/ #fetch existing jstree-classes

            anchorClasses.push nodeData.a_attr.class if nodeData.a_attr.class

            nodeData.a_attr.class=anchorClasses.join ' '

            angular.forEach nodeData.a_attr, (value, key) ->
              node_anchor.prop(key,value)
          else
            console.log "node not found in tree"   if window.console and console.log
            frames['leftframe'].jQuery('#reloadForm').trigger('submit')


    $scope.insertPageAtPosition=(insertData, insertPosition, insertOptions)->
      #position: append,prepend,before,after
      #newPage: className, or 'templateId'
      #oldPage: id
      # console.log "insertPageAtPosition(insertData, insertPosition, insertOptions)" , insertData, insertPosition, insertOptions  if window.console and console.log
      defered=$q.defer()
      $http.post('/BE/Pages/ng_insert_page_at_position',{insertData:insertData, insertPosition:insertPosition, insertOptions:insertOptions}).then (response)->
        if response.data.status is 'ok'
          $scope.editPage(response.data.payload.ID) if response.data.payload.ID
          defered.resolve(response.data.payload)
        else
          defered.reject('cannot read the data received')

      defered.promise


    $scope.debugTimestamp=->
      # return ''
      '?'+jQuery.now()

    $scope.app.loadController=(name,params)->
      defered=$q.defer()
      
      console.log "loadController" ,name, params  if window.console and console.log
      params.lazyloadModules=[] unless params.lazyloadModules

      if name is 'Modal_EditPageSettingsController'
        params.lazyloadModules.push 'angular-validation'

      if params.baseUrlForController
        baseUrl=params.baseUrlForController
      else   
        baseUrl='/Mwerkzeug/ng/pageframe'
      
      scriptUrl=baseUrl+'/js/'+name+'.js'+$scope.debugTimestamp();

      if params.lazyloadModules
        $ocLazyLoad.load(params.lazyloadModules).then ->
          require [scriptUrl], ->
            defered.resolve()
      else
          require [scriptUrl], ->
            defered.resolve()

      return defered.promise

    ModalService=null
    $scope.app.showModal=(action,params)->

      defered=$q.defer()
      $ocLazyLoad.load('angularModalService').then ->

        ModalService = $injector.get('ModalService')

        controllerName="Modal_#{action}Controller"

        $scope.app.loadController(controllerName,params).then ->
          ModalService.showModal(
            templateUrl: "/BE/Pages/ng_pagemanager/translatedTemplate/Modal_"+action+$scope.debugTimestamp()
            controller: controllerName
            inputs: 
              params:params
          ).then (modal) ->
            console.log "show modal running" , null  if window.console and console.log
            #it's a bootstrap element, use 'modal' to show it
            modal.element.modal()
            modal.close.then (result) ->
              console.log "modal closed with", result
              unless result is 'cancel'
                defered.resolve(result)
              else
                defered.reject(result)
              return
            return

      return defered.promise

  trusted={}
  coreModule.filter 'trusted',['$sce', ($sce)->
    return (html)->
      return trusted[html] || (trusted[html]=$sce.trustAsHtml(html))
  ]


  coreModule.config ($translateProvider) ->
    # $translateProvider.translations 'en', 
    #   hl_addPage:  'add new page'
    #   label_titleOfNewPage:  'title of new page'
    #   label_cancel:  'cancel'
    #   label_ok:  'OK'
    #   'headlines.error':  'Error'
      
    # $translateProvider.translations 'de', 
    #   hl_addPage:  'neue Seite anlegen'
    #   label_titleOfNewPage:  'Name der neuen Seite'
    #   label_cancel:  'Abbrechen'
    #   label_ok:  'OK'
    #   'headlines.error':  'Fehler'

    $translateProvider.useStaticFilesLoader
      prefix: '/Mwerkzeug/bower_components/angular-validation-ghiscoding/locales/validation/',
      suffix: '.json'
    

    $translateProvider.preferredLanguage('de');
