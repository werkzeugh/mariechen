<div class="product-detail topcontainer">


            <h2>
                {$Product.getTranslated('Title')}
            </h2>
            <div class="price">
                {$Product.Price} &euro;
            </div>

            {$ShortText}

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

    

            <% if  $Product.getTranslated('Weight') %>
            <div>
            <label class="label-Weight">$trans('Weight','Gewicht'):</label>
                <div class="factblock">
                    {$Product.Weight_de}<% if  CurrentLanguage=="en" %><div>{$Product.Weight}</div><% end_if %>
                </div>
            </div>
            <% end_if %>
            <div>&nbsp;</div>


            <h1>
                {$Product.getTranslated('Config_GoogleTitle')}
            </h1>

     
            <div class="description bodytext typography">
                $Product.Text
            </div>


        </div>
    

