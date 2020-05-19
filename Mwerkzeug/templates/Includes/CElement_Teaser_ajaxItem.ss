<% if abgelaufen %>

<% else %>

<div class='CElement $CType' >
  <% if editMode %>
  <form method='POST' target='previewframe' action='/BE/Pages/ajaxCElement/$CssID/save'>
    <% end_if %>

    <% include CElement_ajaxItem_TypeChooser %>
    
    <table class='dividertable'>
      <tr valign='top'>
        <td width='40%'>
          <% if editMode %>
          <label>Picture:</label>
           <input type='hidden' name='fdata[PictureID]' class='MwFileField' value='$PictureID'> 
           
             <div class='field field_Title'>
               <label>Sichtbar bis (DD.MM.YYYY)</label>
               <input type='text'name='fdata[EndDate]' style='width:200px' value="$EndDate">
             </div>
             
           <% else %>
           $CMSThumbnail
           <% if EndDate  %>
            sichtbar bis: $EndDate
           <% end_if %>

           
       
           
          <% end_if %>
        </td>
        <td width='40%'>
          <% if editMode %>
          <div class='field field_Link'>
            <label>Link</label>
            <input type='hidden' name='fdata[MwLink]' style='width:400px' class='MwLinkField' value="$MwLink">
          </div>
          
          <div class='field field_Title'>
            <label>Title</label>
            <input type='text'name='fdata[Title]' style='width:400px' value="$Title">
          </div>

          <div class='field field_Text'>
            <label>Text</label>
            <textarea  name='fdata[Text]' style='width:400px'>$Text</textarea>
          </div>

          <% else %>
            <div class='field field_Title'><strong>$Title</strong></div>
            <div class='field field_Text'>$Text</div>
          <% end_if %>
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

<% end_if %>
