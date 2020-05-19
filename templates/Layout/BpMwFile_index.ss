
$includeRequirementsForMwFileItem.RAW

<% include BpMysiteFile_index %>

<table>
  <tr valign='top'><td style='position:relative'>
    <div class='group'>
      <% include BpMwFile_Foldertree %>
    </div>       
  </td>    
  <td style='position:relative'>

    <h1>$CurrentDirectory.SQLFilename/</h1>

    <% if Action = upload %>
       <script type="text/javascript" charset="utf-8">
         var onPluploadComplete = function()
         {
           $("#uploader").html('upload complete, please wait ...');
           document.location.href='/BE/MwFile$ChooserMode/listing/$CurrentDirectory.ID';
         };
       </script>
       <% include BpMwFile_UploadWidget %>
    <% else %>
    <div align='right'>
        

            <% if AllowUploadInFilechooser || Action = 'listing' %>
              <a href='/BE/MwFile$ChooserMode/upload/$CurrentDirectory.ID' class='iconbutton upload'><span class='tinyicon ui-icon-plus'></span><% _t('backend.labels.bp_mw_file_uploadbutton') %></a>
            <% end_if %>
      &nbsp;
      <% if ListMode == icons %>
       <a href='#' class='listmode_list iconbutton' title='list-mode'><span></span></a>
      <% else %>
       <a href='#' class='listmode_icons iconbutton' title='thumbnail-mode'><span></span></a>
      <% end_if %>


    </div>

        <ul class='thumbnails group listmode_$ListMode'>
          <% loop Files  %>

            <% if Title %>
              <li class='MwFileItem ajaxtarget $EvenOdd' filename='$Filename' fileurl='$Link' dbid='$ID'>
                <% include BpMwFileItem %>
              </li>
            <% end_if %>

          <% end_loop %>
        </ul>

    <% end_if %>

  </td>
</tr>
</table>

<script>
var lastFileLi;

$(document).ready(function() {


  $('a.listmode_list').click(function(event){
    event.preventDefault();
    window.location.href='$CurrentURL?listmode=list';
  });

  $('a.listmode_icons').click(function(event){
    event.preventDefault();
    window.location.href='$CurrentURL?listmode=icons';
  });

  <% if CurrentDirectory %>
  $('#jsTree #$CurrentDirectory.ID').addClass('current');
  <% end_if %>

  <% if ChooserMode %>

  $('.MwFileItem').MwFileItem({
    ChooserMode:'$ChooserMode',
    chooseFile: function(self){
      url=self.element.attr('fileurl');
      id=self.element.attr('dbid');
      if(parent && parent.setMwLink)
      {
         parent.setMwLink('mwlink://MwFile-'+id); 
      }
      else
      {
        FileBrowserDialogue.mySubmit(url,id);
      }
    }
    });

  
  <% else %>
  $('.MwFileItem').MwFileItem();
  <% end_if %>


});


</script>
