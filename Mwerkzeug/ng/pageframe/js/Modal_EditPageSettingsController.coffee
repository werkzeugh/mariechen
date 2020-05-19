define ->
  angular.module('coreModule').registerController 'Modal_EditPageSettingsController', ($scope, params, close, $element, $timeout, validationService, $q,$rootScope)->
    console.log "Modal_EditPageSettingsController inited, params:" , params  if window.console and console.log

    #load angular-validation

    $scope.submitForm=->

      if $scope.form1.$invalid
        $scope.errorVisible=1
        console.log "cannot submit form, because its invalid" , null  if window.console and console.log
        $timeout(->
          $scope.errorVisible=0
        ,2000)
      else
        $scope.close 
          pageData:$scope.model

    $scope.slugRemoteValidationCheck = ->
      deferred = $q.defer()
      if $scope.customURLSegment
        commandArgs={slug:$scope.model.URLSegment,parentId:params.parentPage.id}
        if params.args.mode is 'edit' or params.args.mode is 'rename'
          commandArgs.skipId=params.pageId
        console.log "slugRemoteValidationCheck" , commandArgs,$scope.model  if window.console and console.log
        params.topScope.callPageManager('checkSlug',commandArgs).then (payload)->
          # {isValid:1/0, message:'msg'}
          deferred.resolve(payload)
      else
        deferred.resolve(true)

      return deferred.promise


    $scope.params=params

    $scope.availableClassNames=params.allowedClassNames

    if params.args.mode=='duplicate'
      $scope.customURLSegment=true
    else
      $scope.customURLSegment=false

    $scope.$watch 'customURLSegment', (val)->
      $scope.updateURLSegment()
      if val 
        $scope.blurAndFocus('page_URLSegment')
        $scope.submitCount=0 #reset validation-message


    $scope.$watch 'model.Title', (val)->
      $scope.updateURLSegment()
    $scope.$watch 'model.MenuTitle', (val)->
      $scope.updateURLSegment()

    $scope.updateURLSegment=->
      unless $scope.customURLSegment
        title=if $scope.model.MenuTitle  then $scope.model.MenuTitle else $scope.model.Title
        $scope.model.URLSegment=$scope.generateURLSegmentFrom title

    $scope.generateURLSegmentFrom=(str)->
      return str unless str

      str = str.replace(/^\s+|\s+$/g, '') # trim
      str = str.toLowerCase()

      #replace umlauts
      tr = {"ä":"ae", "ü":"ue", "ö":"oe", "ß":"ss" }
      str=str.replace /[äöüß]/g, (part)->
        tr[part]


      # remove accents, swap ñ for n, etc
      from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;"
      to   = "aaaaeeeeiiiioooouuuunc------"

      l=from.length
      for i in  [ 0 .. l-1 ]
        str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i))


      str = str.replace(/[^a-z0-9 -]/g, '') # remove invalid chars
        .replace(/\s+/g, '-') # collapse whitespace and replace by -
        .replace(/-+/g, '-') # collapse dashes

      return str


    $scope.model=
      ClassName:params.defaultClassName
      URLSegment:''
      Title:''
      MenuTitle:''
      Hidden:0

    #pre-fill all existing model-Properties with values from sourcePage
    if params.sourcePage
      angular.forEach $scope.model, (value, key) ->
        if params.sourcePage.hasOwnProperty(key)
          $scope.model[key]=params.sourcePage[key]


    $scope.close = (result)->
      console.log "closed my stuff" , null  if window.console and console.log
      close result, 500

    $scope.setFocus=(elementId)->
      $timeout( ->
        element=document.getElementById(elementId)
        console.log "➜ set focus to" , element  if window.console and console.log
        element.focus()
       ,500)

    $scope.blurAndFocus=(elementId)->
      $timeout( ->
        element=document.getElementById(elementId)
        console.log "➜ send blur-event to" , element  if window.console and console.log
        jQuery(element).trigger('blur')  #this triggers validation,thats why we blur
        element.focus()
       ,500)



    $scope.submitCount=0
    myValidation = new validationService()
    myValidation.setGlobalOptions({ isolatedScope: $scope, displayOnlyLastErrorMsg: true,debounce: 300 })

    $scope.setFocus('page_Title')

