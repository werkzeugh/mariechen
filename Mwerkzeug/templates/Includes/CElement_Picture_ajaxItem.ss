<div class='CElement $CType' >

  <% if editMode %>
  <form method='POST' target='previewframe' action='{$celementbaseurl}/ajaxCElement/$CssID/save'>
  <% end_if %>

  <table class='dividertable'>
    <tr valign='top'>
      <td width='100%'>

        <% if editMode %>

        <div class='label-above'>
          <input type='hidden' name='fdata[PictureID]' class='MwFileField' value='$PictureID'> 
          <div class='space' id='fdata_PictureText'>
            <label>Image-Text:</label>
            <input type='text' name='fdata[PictureText]'value='$PictureText'> 
          </div>
          <div class='space'>
            <label>Image-Copyright:</label>
            <input type='text' name='fdata[PictureCopyright]'  id='input_Copyright' value='$PictureCopyright'> 
          </div>

          
        </div>

        <% else %>
      
        $getFrontendHTML

        <% end_if %>

      </td>

      <td class='buttoncol' >
        $editButtons
      </td>
    </tr>
  </table>


  <% if editMode %>

  </form>
  <script type="text/javascript" charset="utf-8">

  $('.MwFileField').MwFileField().change(function () {
    CElement_submit(this);
  });

  $('.editbuttons,.addCElement').hide();
  $('div.CElement form .editbuttons').show();

  </script>

  <% else %>

  <script>
  $('.editbuttons,.addCElement').show();
  </script>

  <% end_if %>

</div>
