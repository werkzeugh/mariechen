  

<div>&nbsp;</div>
<style>
.table td  label { display:flex; align-items:center;}
label > img { margin-left:2em }
</style>




<% if  ProductImages %>
<h2>
    choose which Product-Images are relevant for this variant
</h2>
<div>&nbsp;</div>
    <table class="table table-bordered table-striped taggable-items">
        <thead>
            <tr>
                <th>Image</th>
                <th>Filename</th>
            </tr>
        </thead>
        <tbody >
          <% loop VariantImages %>
            <tr class="" data-id="$ID">
                <td>
                 <label>
                     <input type="checkbox" name="fdata[ImageIds][]" value="$ID" checked>
                        $SetFittedSize(100,100)
                 </label>
                </td>
                <td>
                    $Title
                </td>
            </tr>
            <% end_loop %>
            <% loop RestImages %>
            <tr class="" data-id="$ID">
                <td>
                 <label>
                     <input type="checkbox" name="fdata[ImageIds][]" value="$ID" >
                        $SetFittedSize(100,100)
                 </label>
                </td>
                <td>
                    $Title
                </td>
            </tr>
            <% end_loop %>
        </tbody>
    </table>
    <% else %>
        please add some images on product-level first
    <% end_if %>


