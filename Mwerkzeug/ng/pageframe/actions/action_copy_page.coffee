define ->
  angular.module('coreModule').registerController 'action_copy_page_controller', ($scope, topScope, params)->

    console.log "action_copy_page_controller" , params  if window.console and console.log
    
    topScope.app.clipboard=[params.pageId]

    topScope.showNotification 'page copied'





