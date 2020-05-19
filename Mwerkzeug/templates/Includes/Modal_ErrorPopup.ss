<div class="modal fade" id="fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <a href="javascript:void(0)" class="fa fa-times pull-right close" ng-click="close('cancel')"  aria-hidden="true"></a>
        <h4 class="modal-title" style="color:red;font-size: 1.5em;"><i class='fa fa-exclamation-triangle fa-lg'></i> <% _t('backend.headlines.error') %></h4>
      </div>
      <div class="modal-body">
        <p>{{params.msg}}</p>
      </div>
      <div class="modal-footer align-center">
        <button type="button" ng-click="close('cancel')" class="btn btn-lg btn-primary" >OK</button>
      </div>
    </div>
  </div>
</div>
