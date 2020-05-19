
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
       <div class='space'>
         <label>Image-Copyright:</label>
         <input type='text' name='fdata[PictureCopyright]'  id='input_Copyright' value='$PictureCopyright'> 
       </div>
       <% else %>
       $Picture.Image.CMSThumbnail
      <% end_if %>
    </td>
    <td class='text'>
      <% if editMode %>
        <div class='space'><label class='above'>Title</label><input name='fdata[Title]' value="$Title"></div>
        
        <textarea class='tinymce' name='fdata[Text]'>$Text</textarea>
      <% else %>
       <div><strong>$Title</strong></div>
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
