
<div class='CElement' >
  <% if editMode %>
  <form method='POST' target='previewframe' action='/BE/Pages/ajaxCElement/$CssID/save'>
  <% end_if %>
<table class='dividertable'>
  <tr valign='top' >
    <td class='image' style='vertical-align: top;'>
      <br>
      <% if editMode %>
       <% else %>
       <hr>
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
