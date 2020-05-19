<% if C4P.getAll_TopSlider %>
<div class='topslider'>
    <% loop C4P.getAll_TopSlider %>
    <a href="$Link" class='topslider-slide' id='topslider-slide-$ID'  
        <% if First %> style='display:block' <% end_if %>>
        <img src="$Picture.CroppedImage(680,240).Link">
        <h2 class='topslider-title'>$Title</h2>
        <% if  Text %>
         <div class='topslider-text'>$myText</div>
        
        <% end_if %>
        
    </a>
    <% end_loop %>

    <% if C4P.getAll_TopSlider.moreThanOne %>
    
    <div class='topslider-thumbnails'>
        <% loop C4P.getAll_TopSlider %>
        <a href='#topslider-slide-$ID'  class='topslider-thumbnail <% if First %> topslider-activethumbnail <% end_if %>' seiteid='home' >&nbsp;</a> 
        <% end_loop %>
    </div>

    <% end_if %>
    
</div>

<script type="text/javascript" charset="utf-8">


var topslider_autoslide_timer;

function slider_autoslide () {

  //find next button to the currently selected one
  
  nextthumb=$('.topslider-activethumbnail').next();
  if(nextthumb.length == 0)
  {
    nextthumb=$('.topslider-thumbnails a:first');
  }
  nextthumb.trigger('click');
    
  topslider_autoslide_timer=setTimeout(slider_autoslide,5000);
    
}


$(document).ready(function() {

  $('.topslider-thumbnails').on('click','a',function(event){
  
          if(topslider_autoslide_timer)
          {
            clearTimeout(topslider_autoslide_timer);
          }
          $('.topslider-activethumbnail').removeClass('topslider-activethumbnail');
          $(this).addClass('topslider-activethumbnail');
          event.preventDefault();
          var imgid=$(this).attr('href');
          var seiteid=$(this).attr('seiteid');
          $('.topslider-slide:visible').not(imgid).fadeOut(1800);
          $(imgid).fadeIn(1800);
      
  });
  
  
  topslider_autoslide_timer=setTimeout(slider_autoslide,5000);

});

</script>

<% end_if %>





