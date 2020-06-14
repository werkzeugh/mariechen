</form>
<style>
    #main_savelink {
        display: none
    }
</style>


<div>&nbsp;</div>
<h2>
    Product-Images
</h2>
<div>&nbsp;</div>

 <vbe-imgfolder class='vueapp-vbe' path='$record.getImageFolderPath' button-text="Upload New Images..."></vbe-imgfolder>
<div>&nbsp;</div>
<form method="POST">

    <eb-row-sorter class="vueapp-eb_backend" name="SortedIds"> </eb-row-sorter>



    <table class="table table-bordered table-striped taggable-items">
        <thead>
            <tr>
                <th><input type="checkbox" class="taggable-toggle" name="taggable_toggle" value=""></th>
                <th>Img</th>
                <th>Title</th>
                <th></th>
            </tr>
        </thead>

        <tbody class="js-sortable">
            <% loop Images %>

            <tr class="js-sortable-tr" data-id="$ID">
                <td class="taggable-cb-td">
                    <input type="checkbox" class="taggable-cb" Img="taggable_ids[]" value="MwFile-$ID">
                </td> 
                <td>
                    $SetFittedSize(100,100)
                </td>
                <td>
                    $Title
                </td>

               
                <td class="js-sortable-handle">
                    <i class='fa fa-bars fa-lg'></i>
                </td>
            </tr>
            <% end_loop %>
        </tbody>
    </table>
</form>

