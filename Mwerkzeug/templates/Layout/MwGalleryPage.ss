<style type="text/css" media="screen">
  
.album_item {
  display: block;
  float: left;
  margin-right:9px;
  margin-bottom:9px;
}

.albumrow {
}

.albumpage {
  position:relative;
  margin:10px;
}


#cboxLoadedContent  {
overflow:hidden
}

#primaryContent {background:#fff !important}

</style>



<div id="content">				
  <% include LeftMenu %>
  <!-- End secondaryContent -->
  <div id="primaryContent" class="basicContent">
    <!-- Begin 2-Spaltiger Content des primaryContent-->
    <div class="twoCols">
      
      
      <div class='albumpage group'>
        <div class='typography space'>$Content.RAW  </div>

          $getPaging(Items)
          <div class='albumrow group'>
            
            <% loop ItemsForPage %>
                <a class='album_item pos-$Pos' href="$ZoomImage.Link" rel='colorbox' title='$CombinedTitle' $HoverCode>$Thumbnail</a>
                </a>
                <% if Modulus(6) %><% else %>
                  </div><div class='albumrow group'>
                <% end_if %>
            <% end_loop %>
          </div>
          $getPaging(Items)
          

      </div>



      <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
          $("a[rel='colorbox']").colorbox({current:"Image {current} of {total}",slideshow:true,slideshowSpeed:4000,slideshowAuto:false,slideshowStart:"start slideshow",slideshowStop:"stop slideshow"});
         });
      </script>
      
      
    </div>
    <!-- End twoCols -->


  </div>
  <!-- END primaryContent -->	

</div>
<!-- END content -->









