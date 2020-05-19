 <section class="ce ce-imgslider align-$Alignment $FontClass $getCssClassString">
     <div class="ce-outer">
         <div class="ce-inner">
             <div class="responsive-swiper-shiv"></div>

             <div class="swiper-container" id="$CssID">
                 <div class="swiper-wrapper">
                     <% loop $Children %>
                     <div class="swiper-slide" style="background-image:url('$Picture.Link')">
                         $nl2br(Text)
                     </div>
                     <% end_loop %>
                 </div>

                 <!-- <div class="swiper-pagination"></div> -->

                 <!-- If we need navigation buttons -->
                 <div class="swiper-button-prev"></div>
                 <div class="swiper-button-next"></div>

                 <!-- If we need scrollbar -->
                 <!-- <div class="swiper-scrollbar"></div> -->
             </div>
         </div>
     </div>
 </section>
 <% if  Children.Count >1 %>
 <script>
     $(document).ready(function () {
         //initialize swiper when document ready
         var mySwiper = new Swiper('#{$CssID}', {
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
