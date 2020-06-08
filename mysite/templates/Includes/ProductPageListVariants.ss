<style>
    #main_savelink {
        display: none
    }
    .is-hidden {
     opacity:0.5;
    }
    .zero {
     font-weight:bold;
     color:red;
     border:1px solid red;
     padding:0.2em .5em;
     display:inline-block;
     border-radius:4px;
    }
</style>



tags from product: 
<eb-tag-viewer class='vueapp-eb_backend' tagids='$ProductTagsIdString'></eb-tag-viewer>
<div>&nbsp;</div>

<form method="POST">

 <eb-row-sorter class="vueapp-eb_backend" name="SortedIds"> </eb-row-sorter>






    <table class="variantlist table  table-bordered table-striped taggable-items" id="variantlist"> 

        <thead>
            <tr>
                <th><input type="checkbox" class="taggable-toggle" name="taggable_toggle" value=""></th>
                <th>Name</th>
                <th>Preis</th>
                <th>Lagerstand</th>
                <th>Tags</th>
                <th>imgs</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="js-sortable">
            <% loop Variants %>
    
            <tr class="js-sortable-tr" data-id="$ID">
                <td class="taggable-cb-td">
                    <input type="checkbox" class="taggable-cb" name="taggable_ids[]" value="SiteTree-$ID">
                </td>
                <td class="<% if  $Hidden %>is-hidden<% end_if %>">
                    <a href="$EditLink" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a> $Title
                    
                </td>
                <td>
                   $Price
                </td>
                <td>
       
                    $InStock
                </td>
                <td>
    
                    <eb-tag-viewer class="vueapp-eb_backend" record="SiteTree-$ID" editable="1"
                        types="colors,usage,material">
                    </eb-tag-viewer>
    
                </td>
                 <td class="imagelist">
    
                    <% loop $getImages %>
                        <img src="$CroppedImage(310,390).Link" class="<% if  $isListImage  %>
is-list-image<% end_if %>">
                    <% end_loop %>

    
                </td>
                <td class="js-sortable-handle">
                    <i class='fa fa-bars fa-lg'></i>
                </td>
            </tr>
            <% end_loop %>
        </tbody>
    </table>

<% include Tagabble_Items_Form %>

  
</form>
<style>
.imagelist img { 
    border:2px solid transparent;
    max-height:80px;
}
.imagelist img.is-list-image { 
  border-color:grey;
}

.variantlist {
    width:auto;
    min-width:700px;
}

</style>
<div>&nbsp;</div>


<a class="btn btn-sm" type="button"  href="/ex/bags/update_stock">➜ Lagerstand mit Texcom abgleichen</a>


