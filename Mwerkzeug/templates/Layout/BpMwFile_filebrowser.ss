<style>

.thumbnails li {
  display:block;
  width:100px;
  height:100px;
  border:1px solid #bbb;
  padding:10px;
  margin:6px;
  float:left;
}

.thumbnails .filename {
  width:100px;
  display:block;
  overflow:hidden;
  font-size:10px;
  line-height:20px;
}
    
</style>


<table>
  <tr valign='top'><td style='position:relative'>

    <div class='group'>
      <% include BpMwFile_Foldertree %>
    </div>       

  </td>    
  <td style='position:relative'>

    <h1>Files in: $CurrentDirectory.SQLFilename/</h1>
    
    <% if Action = upload %>
     
     <% include BpMwFile_UploadWidget %>
     
    <% else %>

    $ChooserMode
    
    <ul class='thumbnails group'>
    <% loop Files  %>
    
    <% if Title %>
    <li Title='$Title'>
      <% if Image %>
      <a href='$Image.Link'>$Image.CMSThumbnail</a>
      <% end_if %>
      <div class='filename' >$Title</div>
      <% if Top.ChooserMode %>
      <a href='#'>aaa&rarr;</a>
      <% end_if %>
      
    <% end_if %>
    
      
    </li>

    <% end_loop %>
    </ul>
    
    
    <% end_if %>
    
    
    
    
  </td>
</tr>
</table>

<script>
    $(document).ready(function() {
    
            <% if CurrentDirectory %>
              $('#jsTree #$CurrentDirectory.ID').addClass('current');
            <% end_if %>

     });

</script>