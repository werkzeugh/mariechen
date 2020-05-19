<style>
    
tr.active td {
background: #dff0d8 !important;
}    
.versionlist {overflow:auto;
    max-height:600px;}    
</style>

<div style='padding:40px'>
    <h1>Page History</h1>



    
    <div class='row' >
    

        <div class='well span12 group'>Page: $record.Title 
            <small class='pull-right'>Page-ID: $ID</small>
        </div>
     </div>
        <% if  $RestoredVersion %>

        <div class='row' >
    
            <div>&nbsp;</div>
            <div class='alert alert-success span12'>Version $RestoredVersion restored successfully !</div>
            <div>&nbsp;</div>
        </div>
        <% end_if %>
    
    <div class='row' >
    
        <div class='span12 versionlist'>
            <table class='table table-bordered table-condensed table-striped' >
                <tr>
                    <th>Version</th>
                    <th>Date</th>
                    <th>editor</th>
                    <th>changed fields </th>
                    
                </tr>
                <% loop  versions %>

                <tr class='versionrow versionrow-$Version' data-version='$Version'>
                    <td>$Version</td>
                    <td>$Datum.FormattedDate("D, d.m.Y")
                     <small>$Datum.FormattedDate("H:i")</small></td>
                    <td>$Publisher.PublicName</td>
                    <td>
                        
                        <% with Diff %>
                            <% loop ChangedFields %>
                                <span class='label label-tooltip'  title="$Diff">$Title</span>
                            <% end_loop %>
                        <% end_with %>
                        
                    </td>
                    <td>
                        
                        <button class="btn btn-restore btn-mini" type="submit"><i class="icon-white icon-arrow-right"></i> restore</button>
                        
                    </td>
                </tr>

                <% end_loop %>
            </table>
        </div>

    </div>
    
    
    <% if  $RestoredVersion %>
    
        <script type="text/javascript" charset="utf-8">
            $('tr.versionrow-$RestoredVersion').addClass('active');
        </script>
        
    <% end_if %>
    
    <div id='preview'>
        <h3 style='margin-top:10px'>current Version:</h3>
        <iframe id='previewframe' name='previewframe'></iframe>
    </div>

    <form id='dataform' method=POST>
        <input type='hidden' id='rollback2version' name='rollback2version' value=''>
    </form>

    <script type="text/javascript" charset="utf-8">


    jQuery(document).ready(function($) {

              $('.btn-restore').on('click',function() {
                 
                 var version=$(this).closest('tr').data('version');

                 $('#rollback2version').val(version); 
                 
                 $('#dataform').submit(); 
                  
              });

              $('#previewframe').attr('src','$record.PreviewLink');

     });



    </script>
    
</div>

<script type="text/javascript" charset="utf-8">
    
    $('.label-tooltip').tooltip({});
</script>