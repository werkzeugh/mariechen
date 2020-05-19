<div class="modal fade Modal_DevtoolExecuteCmd" id="fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <a href="javascript:void(0)" class="fa fa-times pull-right close" ng-click="close('cancel')"  aria-hidden="true"></a>
        <h4 class="modal-title" ng-show="params.args.mode=='create'"><i class='fa fa-plus fa-lg'></i>  <% _t('backend.headlines.create_page') %></h4>
        <h4 class="modal-title" ng-show="params.args.mode=='duplicate'"><i class='fa fa-copy fa-lg'></i>  <% _t('backend.labels.actionmenu_cmd_l_duplicate_page') %></h4>
        <h4 class="modal-title" ng-show="params.args.mode=='edit'"><i class='fa fa-cog fa-lg'></i>  <% _t('backend.labels.actionmenu_cmd_l_page_settings') %></h4>
        <h4 class="modal-title" ng-show="params.args.mode=='rename'"><i class='fa fa-edit fa-lg'></i>  <% _t('backend.labels.actionmenu_cmd_l_rename_page') %></h4>
        <h4 class="modal-title" ng-show="params.args.mode=='paste'"><i class='fa fa-plus fa-lg'></i>  <% _t('backend.labels.actionmenu_cmd_l_paste_page') %></h4>

      </div>
      <div class="modal-body">

        devtool execute <b>{{params.menuItem.command.args.cmd}}</b> on <b>"{{params.referencePage.url}}"</pre>

        <a href="{{iframeUrl}}" target="devToolresultFrame">{{iframeUrl}}</a>
        <iframe style="width:500px;height:700px;border:1px solid #ddd"  name="devToolresultFrame"<>
        ng-src="{{iframeUrl}}"></iframe>

      </div>
     <!--  <div class="modal-footer">
        <button type="button" ng-click="close('cancel')" class="btn btn-default" >No</button>
        <button type="button" ng-click="close(25)" class="btn btn-primary" >Yes</button>
      </div> -->
    </div>
  </div>
</div>
