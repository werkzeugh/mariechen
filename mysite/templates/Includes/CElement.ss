<div class='CElement $CType'
 <% if CSSHeight %> style="height:{$CSSHeight}px;overflow:hidden"<% end_if %>
>


  <% if CType == UmaVideo %>

  <% require javascript(mysite/thirdparty/jquery-fancybox/fancybox/jquery.fancybox-1.3.4.pack.js) %>

  <% require css(mysite/thirdparty/jquery-fancybox/fancybox/jquery.fancybox-1.3.4.css) %>


  <% if Picture %>
     <a href="$VideoLink"  class='videopopup' video_w="$VideoW" video_h="$VideoH">$SizedPicture</a>
    <% if PictureText %>
      <div class='imagetext'>$PictureText</div>
    <% end_if %>
  
  <% end_if %>


  <% else_if CType == UmaPicture %>
  
    <% if Picture %>
      <a href='#' class='slideshow1_link'>$SizedPicture</a>
      <% if PictureText %>
        <div class='imagetext'>$PictureText</div>
      <% end_if %>
    
      <% if SlideShowItems %>
      
          <div id='slideshowlinks' >    
          <% loop  SlideShowItems %>
              <a href = "$Picture.Link" rel="slideshow1" title="$PictureText <% if PictureCopyright %>$NicePictureCopyright<% end_if %>" >$Top.translate(start slideshow,Slideshow starten)</a> 
          <% end_loop %>
          </div>

          <script type="text/javascript" charset="utf-8">
            $(document).ready(function() {

        			$("a[rel='slideshow1']").colorbox();

              $(".slideshow1_link").click(function(event) {
                event.preventDefault();
                
                $("a[rel='slideshow1']:first").trigger('click');
              });
              

             });

          </script>

      <% end_if %>
    
    
    <% end_if %>
    

  <% else %>

    <div class='$Style'>
      $Text
    </div>
  
  <% end_if %>
</div>
