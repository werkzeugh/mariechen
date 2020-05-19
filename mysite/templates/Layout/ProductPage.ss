<div class="product-detail">


    <div class="detail-thumbnails">

        <div class="thumbnail-container">
            <a href="javascript:history.back()"><i class='fal fa-arrow-left'></i></a>
            <bagdetail-thumbnails class="vueapp-bagdetail">

            </bagdetail-thumbnails>
        </div>
    </div>
    <div class="detail-left">

        <bagdetail-imagelist class="vueapp-bagdetail">
            <% loop  Product.getImagesWithTagIds() %>
            <img src="$img.Link" width="10" tags="$tagIdString" dbid="$img.ID" bigsrc="$bigimg.Link"
                smallsrc="$smallimg.Link" />
            <% end_loop %>
        </bagdetail-imagelist>
    </div>

    <div class="detail-right">
        <div class="product-info">
            <h2>
                {$Product.getTranslated('Title')}
            </h2>
            <h1>
                {$Product.getTranslated('Config_GoogleTitle')}
            </h1>
            <div class="price">
                {$Product.Price} &euro;
            </div>

            <% if  $Product.getTranslated('Material') %>
            <div>&nbsp;</div>
            <label class="label-material">$trans('Material','Material'):</label>
            {$Product.getTranslated('Material')}
            <% end_if %>

            <% if  $Product.getTranslated('Dimensions') %>
            <br>
            <label class="label-Dimensions">$trans('Dimensions','Maße'):</label>
            <div class="factblock">
               {$Product.Dimensions_de}<% if  CurrentLanguage=="en" %><div>{$Product.Dimensions}</div><% end_if %>
            </div>
            <% end_if %>

            <% if  $Product.getTranslated('StrapLength') %>
            <div>
            <label class="label-Dimensions">$trans('Strap-Length','Riemenlänge'):</label>
                <div class="factblock">
                {$Product.StrapLength_de}<% if  CurrentLanguage=="en" %><div>{$Product.StrapLength}</div><% end_if %>
                </div>
            </div>
            <% end_if %>

            <% if  $Product.getTranslated('Weight') %>
            <div>
            <label class="label-Weight">$trans('Weight','Gewicht'):</label>
                <div class="factblock">
                    {$Product.Weight_de}<% if  CurrentLanguage=="en" %><div>{$Product.Weight}</div><% end_if %>
                </div>
            </div>
            <% end_if %>
            <div>&nbsp;</div>

            <label class="label-color">$trans('Colour','Farbe'):</label>

            <bagdetail-variants class="vueapp-bagdetail" current-variant-id="$currentVariantId">
                <% loop Product.getUnHiddenProductVariantsWithColors %>
                <a href="$variant.Link" name="$variant.Title" dbid="$variant.ID" imgs="$imgIdString" instock="$variant.InStock"
                    colorstr="$colorString">$variant.Title</a>
                <% end_loop %>
            </bagdetail-variants>

            <div class="description bodytext typography">
                $Product.Text
            </div>


        </div>
    </div>







</div>

{$getCodeForProductDetailWidgets.RAW}
