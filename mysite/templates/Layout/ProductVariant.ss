<div class="product-detail topcontainer">


  <div class="responsive-swiper-shiv"></div>

             <div class="swiper-container" id="prodimgs">
                 <div class="swiper-wrapper">
                     <% loop $getImages %>
                     <div class="swiper-slide">
                        <img src="$SetSize(1024,1024).Link">
                     </div>
                     <% end_loop %>
                 </div>

                 <div class="swiper-button-prev"></div>
                 <div class="swiper-button-next"></div>

             </div>


 <% if  $getImages.Count >1 %>
 <script>
     $(document).ready(function () {
         //initialize swiper when document ready
         var mySwiper = new Swiper('#prodimgs', {
             // Optional parameters
             //  direction: 'vertical',
             navigation: {
                 nextEl: '.swiper-button-next',
                 prevEl: '.swiper-button-prev',
             },
             grabCursor: true,
             pagination: {
                 el: '.swiper-pagination',
                 type: 'progressbar',
             },
             loop: true
         })
     });
 </script>
 <% end_if %>


            <div class="titlebar">
                <h2>
                    <strong>$Product.Title,</strong> $Title  $PriceStr
                </h2>
                <div class="m-btn">
                    In den Warenkorb
                </div> 
            </div>


            {$MyShortText}


            <div class="product-list">
                <% loop getOtherProductVariants %>
                <a href="$Link" class="product-box">
                    <span class="img"><img src="$getMainImage.SetSize(300,300).Link"></span>
                    <span class="m-btn">
                        <span class="p-title">$Product.Title</span>
                        <span class="title">$Title</span>
                        <span class="price">$PriceStr</span>
                    </span>
                </a>
                <% end_loop %>
            </div>


            <h1>
                {$Product.getTranslated('Config_GoogleTitle')}
            </h1>


            <div class="description bodytext typography">
                $Product.Text
            </div>


        </div>
    

