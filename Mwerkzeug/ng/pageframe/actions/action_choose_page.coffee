define ->
  angular.module('coreModule').registerController 'action_choose_page_controller', ($scope, topScope, params, $q)->
    

    console.log "action_choose_page_controller:" , params  if window.console and console.log

    orFail=topScope.app.failPopup
    params.topScope=topScope
    commandArgs=
      pageId:params.pageId

    main=->
      topScope.callPageManager('getNodeData',commandArgs).then doShowModal, orFail


    doShowModal=(nodeData)->
      params.page=nodeData
      topScope.app.showModal('ChoosePage',params).then doChoosePage

    doChoosePage=(data)->
      if data is 'ok'
        console.log "choose page" , null  if window.console and console.log

        mwlink="mwlink://SiteTree-"+params.pageId
        params.onChoose.resolve(mwlink)


    main()





