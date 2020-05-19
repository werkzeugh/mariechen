
<div class='CElement' >
  <% if editMode %>
  <h3>Download:</h3>

  <form method='POST' target='previewframe' action='/BE/Pages/ajaxCElement/$CssID/save'>
    <% end_if %>
    <table class='dividertable'>
      <tr valign='top'>
        <td>

          <% if editMode %>
          <label>Titel:</label>
          <input type='text' name='fdata[Title]' value='$Title'>
          <% end_if %>
          <% if File  %>
<div class='space'>
            <a href='$File.Url' target='_new'>$File.FileIcon $Title</a> ($File.Size)
</div>          <% end_if %>

          <% if editMode %>
          <div class='space'>
            <input type='hidden' name='fdata[FileID]' class='MwFileField' value='$FileID'> 
          </div>          
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

  $('.MwFileField').MwFileField({
    buttonText: 'Datei auswählen',
    removeButtonText: 'Datei-Link entfernen'
  }).change(function () {
    CElement_submit(this);
  });

  $('.editbuttons').hide();
  $('div.CElement form .editbuttons').show();

  </script>
  <% else %>
  <script>
  $('.editbuttons').show();
  </script>
  <% end_if %>

</div>
