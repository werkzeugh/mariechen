
<form id='c4p-itemform' class="c4p-itemform" 
method='POST' target='previewframe' action='/BE/C4P_Api/editsave/'>

<div class="c4p-validationerrors">
  <ul>
    
  </ul>

</div>

<input type="hidden" name="settings[c4p_record]" value="$c4p_record">
<input type="hidden" name="settings[c4p_place]" value="$c4p_place">
<input type="hidden" name="id" value="$id">

<input type="hidden" name="fdata[CType]" value="{$CType}" id="c4p-ctypefield">
<input type="hidden" name="params[nextaction]" id='c4p-nextactionfield' value="">

<% if params.newitem %> 
  <input type="hidden" name="params[newitem]" value="$params.newitem">
  <input type="hidden" name="params[newitem_before]" value="$params.newitem_before">
  <input type="hidden" name="params[newitem_duplicateof]" value="$params.newitem_duplicateof">
<% end_if %>

<% if  params.edit_json %> 
  <textarea style="width:100%;height:200px" name="fdata[record_as_json]">
    {$recordAsJson.RAW}
  </textarea>
  $C4PLink
<% else %>


  <% if hasTwoCols %>
  <table style='width:100%'>
    <tr valign='top'>  
      <td><div style='position:relative;margin-right:4px'>$AllFormFields(left)</div></td>
      <td><div style='position:relative;margin-left:4px'>$AllFormFields(right)</div></td>
    </tr>
  </table>
  <% else_if hasTabs %>


    <ul class="nav nav-tabs" id="editTabs_$ID">
      <% loop Tabs %>
       <li><a href="#$Key" data-toggle="tab">$TabTitle</a></li>
      <% end_loop %>

    </ul>

    <div class="tab-content">
      <% loop Tabs %>
      <div class="tab-pane" id="$Key" style='position:relative;padding:10px 5px 10px 5px'>
        <% if hasTwoCols %>
        <table style='width:100%'>
          <tr valign='top'>  
            <td><div style='position:relative;margin-right:4px'>$AllFormFieldsLeft.RAW</div></td>
            <td><div style='position:relative;margin-left:4px'>$AllFormFieldsRight.RAW</div></td>
          </tr>
        </table>
        <% else %>
          $AllFormFields.RAW
        <% end_if %>

      </div>
      <% end_loop %>
    </div>

    <script>
        jQuery('#editTabs_$ID a:first').tab('show');
    </script>


  <% else  %>
      $AllFormFields.RAW
  <% end_if %>

<% end_if %>

</form>
  
  
<script type="text/javascript" charset="utf-8">

      setup_tinymce(); // jshint ignore:line

      jQuery('.MwFileField').MwFileField().change(function() {
         if (window.console && console.log) { console.log('need code to submit after file upload',null);  }
      });

      $("#c4p-itemform").validate({
           errorPlacement: function() {
             //dummy
           },
          ignore: "",
          errorLabelContainer: ".c4p-validationerrors ul",
          errorContainer: ".c4p-validationerrors",
          errorClass: "alert alert-danger",
          highlight: function(element, errorClass) {
              $(element).closest('.form-group').addClass('has-error');
          },
          unhighlight: function(element, errorClass) {
              $(element).closest('.form-group').removeClass('has-error');
          },
          rules: {
              $getJSValidationRules.RAW // jshint ignore:line
          },// jshint ignore:line
          messages: { // jshint ignore:line
              $getJSValidationMessages.RAW // jshint ignore:line
          }// jshint ignore:line
      });


</script>





