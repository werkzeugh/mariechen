define ->
  angular.module('coreModule').registerController 'action_edit_page_permissions_controller', ($scope, topScope, params,$rootScope)->

    main=->
      # url='/BE/Pages/show/' + params.pageId; 
      # window.open(url,'_blank');       
      url='/BE/Pages/edit/'+params.pageId+'/99_C4P_Place_PagePermissions'
      window.open(url,'rightframe')


    main()


