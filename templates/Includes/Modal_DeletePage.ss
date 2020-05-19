<div class="modal fade Modal_DeletePage" id="fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <a href="javascript:void(0)" class="fa fa-times pull-right close" ng-click="close('cancel')"  aria-hidden="true"></a>
        <h4 class="modal-title"><i class='fa fa-trash-o fa-lg'></i>  <% _t('backend.labels.actionmenu_cmd_l_delete_page') %></h4>
      </div>
      <div class="modal-body">

      <% _t('backend.texts.confirm_page_deletion') %>
        

        <div class="align-center">
          <div>&nbsp;</div>
          <i class='{{params.page.icon}} fa-lg'></i> <b ng-bind-html="params.page.text | trusted"></b>
          <div>&nbsp;</div>

          <button type="button" ng-click="close('ok')" class="btn btn-primary btn-danger" ><i class='fa fa-trash-o'></i> <% _t('backend.labels.yes') %></button>
          <button type="button" ng-click="close('cancel')" class="btn btn-default" ><i class='fa fa-times'></i> <% _t('backend.labels.no') %></button>
        </div>
      </div>

    </div>
  </div>
</div>
