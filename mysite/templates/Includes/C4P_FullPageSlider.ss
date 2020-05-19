 <div class="ce ce-fullpageslider align-$Alignment $FontClass $getCssClassString">
     <div class="slides-container tinyfade">
         <% loop $Children %>
         <div class="slide-img" style="background-image:url('$Picture.Link')">
            <% if  ImageID %>
                <div class="overlay">
                    <img src="$Image.Link">
                </div>
            <% end_if %>
         </div>
         <% end_loop %>
     </div>
     
     <div class="downlink">
         <a href="#down" id="downlink"><i class='fal fa-chevron-down'></i></a>
     </div>
 </div>
 <% if  Children.Count >1 %>
 <script>
     var tf = new Tinyfade(".tinyfade", 3000, 1000);
 </script>
 <% end_if %>
 <script>
    jQuery(document).ready(function ($) {
        $("#downlink").on('click',function (e) {
            var href = $(this).attr("href");
            var el = $("#down")[0];
            el.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });

            e.preventDefault();
        });
    });
</script>
<a id="down" />
