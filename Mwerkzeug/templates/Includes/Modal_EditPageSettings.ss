<div class="modal fade Modal_EditPageSettings" id="fade">
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

        <form novalidate ng-submit="submitForm()" name="form1" class="form-horizontal">

          <div ng-show="(params.args.mode=='create' || params.args.mode=='paste') && params.args.position">
            <div class="form-group">
              <label for="page_Position" class="col-sm-3 control-label"><% _t('backend.labels.page_position') %></label>
              <div class="col-sm-9">
                <div class="form-control-static">
                  
                  <div ng-show="params.args.position=='before'"><i class='fa fa-arrow-right fa-fw fa-lg'></i> <i class='fa fa-file-o fa-lg'></i> <b>{{model.MenuTitle?model.MenuTitle:model.Title}}</b></div>

                  <div class="node dimmed">
                    <i class='fa fa-fw fa-lg'></i> <i class='{{params.referenceNode.icon}} fa-lg'></i> <b ng-bind-html="params.referenceNode.text | trusted"></b>
                  </div>
                  <div ng-show="params.args.position=='after'"><i class='fa fa-arrow-right fa-fw fa-lg'></i> <i class='fa fa-file-o fa-lg'></i> <b>{{model.MenuTitle?model.MenuTitle:model.Title}}</b></div>
                  <div ng-show="params.args.position=='inside'"><i class='fa fa-fw fa-lg'></i><i class='fa fa-arrow-right fa-fw fa-lg'></i> <i class='fa fa-file-o fa-lg'></i> <b>{{model.MenuTitle?model.MenuTitle:model.Title}}</b></div>
                </div>
              </div>
            </div>
          </div>

          <div ng-show="params.args.mode=='duplicate' && params.args.copyOf">
            <div class="form-group">
              <label for="page_Position" class="col-sm-3 control-label"><% _t('backend.labels.source_page') %></label>
              <div class="col-sm-9">
                <div class="form-control-static">
                      <i class='{{params.sourceNode.icon}} fa-lg'></i> <b ng-bind-html="params.sourceNode.text | trusted"></b>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="page_Title" class="col-sm-3 control-label"><% _t('backend.labels.page_Title') %></label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="page_Title" ng-model="model.Title" name="Title" validation="required" >
            </div>
          </div>

          <div class="form-group">
            <label for="page_MenuTitle" class="col-sm-3 control-label"><% _t('backend.labels.page_MenuTitle') %></label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="page_MenuTitle" ng-model="model.MenuTitle" name="MenuTitle" />
            </div>
          </div>

          <div class="form-group form-group-checkbox">
            <label for="page_URLSegment" class="col-sm-3 control-label"><% _t('backend.labels.page_URLSegment') %></label>
            <div class="col-sm-9">
              <div class="form-control-static">
                <div class='baseUrl'>{{params.baseUrl}} <b>{{model.URLSegment}}</b></div>
                <div class="checkbox">
                  <label>
                    <input type="checkbox"  ng-model="customURLSegment"> <% _t('backend.labels.page_customUrlSegment') %>  <span ng-show="customURLSegment">:</span>
                  </label>
                </div>
              </div>

              <div ng-if="customURLSegment">
                <input type="text" class="form-control show-validationstatus" id="page_URLSegment"  
                ng-model="model.URLSegment" name="URLSegment"
                placeholder="<% _t('backend.labels.url_segment_placeholder') %>"
                validation="pattern=/^[a-z0-9-]+$/:alt=<% _t('backend.labels.validator_rule_slug') %>|remote:slugRemoteValidationCheck|required" 
                debounce="500"
                />
              </div>



            </div>
          </div>


          <div class="form-group form-group-checkbox" ng-show="params.args.mode=='paste' && params.sourcePage.ClassName!='AliasPage' && params.permissions.pasteAsAlias">
            <div class="col-sm-offset-3 col-sm-9">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="pasteAsAlias" ng-model="model.pasteAsAlias"> <% _t('backend.labels.page_pasteAsAlias') %>
                </label>
              </div>
            </div>
          </div>


          <div class="form-group form-group-checkbox" ng-if="model.pasteAsAlias">
            <div class="col-sm-offset-3 col-sm-9">
              <div class="checkbox">
                <label>&nbsp;&nbsp;&nbsp;
                  <input type="checkbox" name="AliasSubPages" ng-model="model.AliasSubPages"> <% _t('backend.labels.page_AliasSubPages') %>
                </label>
              </div>
            </div>
          </div>

          <div class="form-group form-group-checkbox" ng-if="params.args.mode=='paste' && !model.pasteAsAlias ">
            <div class="col-sm-offset-3 col-sm-9">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="includeSubPages" ng-model="model.includeSubPages"> <% _t('backend.labels.page_includeSubPages') %>
                </label>
              </div>
            </div>
          </div>


          <div class="form-group">
            <label for="page_Hidden" class="col-sm-3 control-label"><% _t('backend.labels.page_Hidden') %></label>
            <div class="col-sm-9">

              <label class="radio-inline">
              <input type="radio" name="inlineRadioOptions"  ng-model="model.Hidden" id="inlineRadio1" value="0"> <% _t('backend.labels.page_Hidden_0') %>
              </label>
              <label class="radio-inline">
                <input type="radio" name="inlineRadioOptions"  ng-model="model.Hidden" id="inlineRadio2" value="1"> <% _t('backend.labels.page_Hidden_1') %>
              </label>
            </div>
          </div>


          <div class="form-group" ng-if="params.args.mode=='create'">
            <label for="page_ClassName" class="col-sm-3 control-label"><% _t('backend.labels.page_ClassName') %></label>
            <div class="col-sm-9">
              <select class="form-control" id="page_ClassName" ng-model="model.ClassName" name="ClassName" validation="required"
              ng-options="cn.key as cn.name for cn in availableClassNames" ng-disabled="availableClassNames.length<2"></select>
            </div>
          </div>

          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <button type="submit" class="btn btn-primary btn-lg"> OK </button>
              <span class="alert alert-danger" ng-show="errorVisible && form1.\$invalid"><i class='fa fa-arrow-up'></i> <% _t('backend.texts.please_fix_redfields') %></span>
            </div>
          </div>

        </form>

      </div>
     <!--  <div class="modal-footer">
        <button type="button" ng-click="close('cancel')" class="btn btn-default" >No</button>
        <button type="button" ng-click="close(25)" class="btn btn-primary" >Yes</button>
      </div> -->
    </div>
  </div>
</div>
