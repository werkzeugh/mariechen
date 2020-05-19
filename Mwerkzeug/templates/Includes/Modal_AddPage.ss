<div class="modal fade" id="fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <a href="javascript:void(0)" class="fa fa-times pull-right close" ng-click="close('cancel')"  aria-hidden="true"></a>
        <h4 class="modal-title" >Seite hinzufügen</h4>
      </div>
      <div class="modal-body">
        <p >Seite erstellen</p>
        
        <form class="form form-horizontal span-6">
                <label >Titel der neuen Seite</label>

          <input type="text" class="form-control" ng-model="model.Title">
        </form>

      </div>
      <div class="modal-footer">
        <button type="button" ng-click="close(false)" class="btn btn-default" ><span >Abbrechen</span></button>
        <button type="button" ng-click="close(true)" class="btn btn-primary" ><span >OK</span></button>
      </div>
    </div>
  </div>
</div>
