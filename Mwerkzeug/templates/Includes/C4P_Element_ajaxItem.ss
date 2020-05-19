<div class='CElement $CssClass C4P $CType' >
    <% if editMode %>
    <form method='POST' target='previewframe' action='/BE/CElement/ajaxCElement/$CssID/save'>
        <% if isChild %>
          <input type='hidden' name='args[child_id]' value='$ChildID'>
        <% end_if %>

  <% end_if %>
  
    <table class='dividertable'>
      <tr valign='top'>
            <% if editMode %>
              <% if hasTwoCols %>
                <td width='50%'>
                  <div style='position:relative'>$AllFormFields(left)</div>
                </td>
                <td width='50%'>
                  <div style='position:relative'>$AllFormFields(right)</div>
                </td>
              <% else_if hasTabs %>
                  <td width='100%'>
                      

                      <ul class="nav nav-tabs" id="editTabs_$ID">
                          <% loop Tabs %>
                              <li><a href="#$Key" data-toggle="tab">$TabTitle</a></li>
                          <% end_loop %>

                      </ul>
 
                      <div class="tab-content">
                          <% loop Tabs %>
                          <div class="tab-pane" id="$Key" style='position:relative;padding:0px 5px 10px 5px'>$AllFormFields</div>
                          <% end_loop %>
                      </div>
 
                      <script>
                        $(function () {
                          $('#editTabs_$ID a:first').tab('show');
                        })
                      </script>



                  </td>
               <% else  %>
               <td width='80%'>
                   $AllFormFields()
                 </td>
              <% end_if %>
            <% else %>
              <td width='80%' class='previewcol'>
                $getBEPreviewHTML
              </td>
            <% end_if %>
            <td class='buttoncol' rowspan='2' >
              $editButtons
            </td>
      </tr>
      <% if editMode %>
      <tr>
        <% if hasTwoCols %>
          <td colspan='2'>
        <% else %>
          <td>
        <% end_if %>
            
          $getChildrenEditHTML
          
          <% include C4P_Element_ajaxItem_settings %>
          
          </td>
      <% end_if %>
      </tr>
      
    </table>

  <% if editMode %>
    </form>
  <% end_if %>
</div>
  
  <script type="text/javascript" charset="utf-8">

  var editMode = '$editMode';

  if (editMode === '1') {
      setup_tinymce();
      $('.MwFileField').MwFileField().change(function() {
          CElement_submit(this);
      });


      //$('.MwLinkField').MwLinkField({});

      $('.editbuttons,.addCElement').css('visibility', 'hidden');
      $('div.CElement form .editbuttons').css('visibility', 'visible');
  }
   else
   {
      $('.editbuttons,.addCElement').css('visibility', 'visible');
  }

</script>


