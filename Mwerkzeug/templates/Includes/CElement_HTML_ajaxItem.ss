
<div class='CElement' >
  <% if editMode %>
  <form method='POST' target='previewframe' action='/BE/Pages/ajaxCElement/$CssID/save'>
  <% end_if %>
<table class='dividertable'>
  <tr valign='top' >
    <td class='image' style='vertical-align: top;'>
      <br>
      <% if editMode %>
       <input type='hidden' name='fdata[PictureID]' class='MwFileField' value='$PictureID'> 
       <% else %>
       $Picture.Image.CMSThumbnail
      <% end_if %>
    </td>
    <td class='text'>
      <% if editMode %>
        <textarea name='fdata[Text]' style='width:100%;height:300px'>$Text</textarea>
      <% else %>
       <% if  TextAsHtml %>
         <b>HTML:</b>
         <div>$TextAsHtml</div>
       <% end_if %>
       
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
