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
<% if  not $record.getImageFolder  %>
please create a Folder named '<b>$record.getImageFolderPath</b>' in the File-Management Module, and place the images
there
<div>&nbsp;</div>
NOTE: for now, you have to right-click the "products"-Folder to create a sub-folder there
<div>&nbsp;</div>
<a href="/BE/MwFile/listing/48/" class="btn btn-primary" target="_top">➜ go to File-Manager</a>

<% else %>
images are placed in a Folder named '<b>$record.getImageFolderPath</b>' in the File-Management Module

<a href="/BE/MwFile/listing/{$record.getImageFolder.ID}/" class="btn btn-default btn-xs" target="_top">➜ go to
    File-Manager</a>
<div>&nbsp;</div>
<div>&nbsp;</div>
<form method="POST">

    <eb-row-sorter class="vueapp-eb_backend" name="SortedIds"> </eb-row-sorter>



    <table class="table table-bordered table-striped taggable-items">
        <thead>
            <tr>
<!--                <th><input type="checkbox" class="taggable-toggle" name="taggable_toggle" value=""></th> -->
                <th>Img</th>
                <th>Title</th>
                <th></th>
            </tr>
        </thead>

        <tbody class="js-sortable">
            <% loop Images %>

            <tr class="js-sortable-tr" data-id="$ID">
                <!--<td class="taggable-cb-td">
                    <input type="checkbox" class="taggable-cb" Img="taggable_ids[]" value="MwFile-$ID">
                </td> -->
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

<% end_if %>
