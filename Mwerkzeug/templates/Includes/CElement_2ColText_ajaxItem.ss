<div class='CElement $CType' >
  <% if editMode %>
  <form method='POST' target='previewframe' action='/BE/Pages/ajaxCElement/$CssID/save'>
  <% end_if %>
<table class='dividertable'>
  <tr valign='top'>
    <td width='50%'>
      <% if editMode %>
        <div class='space'><label class='above'>Title left</label><input name='fdata[Title]' value="$Title" style='width:200px'></div>
        <textarea class='tinymce' name='fdata[Text]' style='width:200px'>$Text</textarea>
      <% else %>
       <div><strong>$Title</strong></div>
       <div class='typography'>$Text</div>
      <% end_if %>

    </td>
    <td width='50%'>
      <% if editMode %>
        <div class='space'><label class='above'>Title right</label><input name='fdata[Title2]' value="$Title2" style='width:200px'></div>
        <textarea class='tinymce' name='fdata[Text2]' style='width:200px'>$Text2</textarea>
      <% else %>
       <div><strong>$Title2</strong></div>
       <div class='typography'>$Text2</div>
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
