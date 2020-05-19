<div class='CElement $CType' >
  <% if editMode %>
  <form method='POST' target='previewframe' action='/BE/Pages/ajaxCElement/$CssID/save'>
    <% end_if %>

    <% include CElement_ajaxItem_TypeChooser %>
    
    <table class='dividertable'>
      <tr valign='top'>
        <td width='80%'>
          <% include CElement_ajaxItem_Image %>
        </td>
        <td class='buttoncol' >
          $editButtons
        </td>
      </tr>
    </table>

  <% if editMode %>
  </form>
  <% else %>
   $addbuttons
  <% end_if %>

  <% include CElement_ajaxItem_script %>

</div>
