<div class="modal fade" id="fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <a href="javascript:void(0)" class="fa fa-times pull-right close" ng-click="close('cancel')"  aria-hidden="true"></a>
        <h4 class="modal-title" style="color:red;font-size: 1.5em;"><i class='fa fa-exclamation-triangle fa-lg'></i> <% _t('backend.headlines.error') %></h4>
      </div>
      <div class="modal-body">
        Neuer Lagerstandstyp:
      </div>
      <div class="modal-footer align-center">
        <button type="button" ng-click="close('Typ1')" class="btn btn-lg btn-primary" >Typ1</button>
        <button type="button" ng-click="close('Typ2')" class="btn btn-lg btn-primary" >Typ2</button>
        <button type="button" ng-click="close('Typ3')" class="btn btn-lg btn-primary" >Typ3</button>
      </div>
    </div>
  </div>
</div>
