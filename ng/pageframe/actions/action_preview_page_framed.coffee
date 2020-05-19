define ->
  angular.module('coreModule').registerController 'action_preview_page_framed_controller', ($scope, topScope, params,$rootScope)->

    main=->
      # url='/BE/Pages/show/' + params.pageId; 
      # window.open(url,'_blank');       
      url='/BE/Pages/show/495'
      window.open(url,'rightframe')


    main()


