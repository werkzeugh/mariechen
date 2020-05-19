
<div class='CElement' >
  <% if editMode %>
  <form method='POST' target='previewframe' action='/BE/Pages/ajaxCElement/$CssID/save'>
  <% end_if %>
<table class='dividertable'>
  <tr valign='top'>
    <td class='image'>
      <br>
      <% if editMode %>
       <input type='hidden' name='fdata[PictureID]' class='MwFileField' value='$PictureID'> 
       <div class='space' id='fdata_PictureText'>
         <label>Bild-Text:</label>
         <input type='text' name='fdata[PictureText]'value='$PictureText'> 
       </div>
       <div class='space'>
         <label>Bild-Copyright:</label>
         <input type='text' name='fdata[PictureCopyright]'  id='input_Copyright' value='$PictureCopyright'> 
       </div>
       <% else %>
       $Picture.Image.CMSThumbnail
      <% end_if %>
    </td>
    <td class='text'>
      <% if editMode %>
        <textarea class='tinymce' name='fdata[Text]'>$Text</textarea>
      <% else %>
       <div class='typography'>$Text</div>
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
  setup_tinymce();
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
