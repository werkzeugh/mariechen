<style type="text/css" media="screen">

.article {
  margin: 20px 0px;
}
.articleleft {
  float: left;
  width: 668px;
  margin-left: 11px;
}

.articleheader h1{
  font-size:26px;
  font-weight:normal;
  margin:20px 0px;
}

.articleright {
  float: left;
  width: 211px;
  margin-left: 21px;
  font-size:13px;
}


.articleright a {
  color:#333;
}

.articleright h2 {
  text-transform: uppercase;
  color: #a88266;
  font-size: 13px;
  border-bottom: 1px solid #716255;
  margin:5px 0px;
}

.articleheader {
  margin-left: 11px;
  margin-top:10px;
}

.rightcolelement {
  margin-bottom:10px;
}


.fade_gallery {position:relative;height:270px;}

.fade_gallery .fadeimg {
  display: none;
  position: absolute;
  top: 0px;
  left: 0px;
  width:100%;
}


.fade_gallery .fadeimg .imgtext  {
  position:absolute;
  right:10px;
  bottom:0px;
  width:220px;
  color:#A88266;
  font-size:13px;

}
.mainmargin {margin:0px;}


</style>


<div class='group'>

  <div class="leftmenu">
   
    <div class="leftmenu_inner">

      <ul class="menu">
        <% loop pageNavItems %>    
   
          <li class="$LinkingMode"><a href="$Link">$Title</a></li>
   
        <% end_loop %>
      </ul>

    </div>

  </div>






  
<div class='article group'>


  <div class='articleleft'>
  <h1>$Title</h1>



    <div class='introtext'>
      <b>$IntroText</b>
    </div>
    
    <% loop C4P.getAll_MainContent %>

    <div class="$CTypeShort">

      <% if CTypeShort = Headline %>
      <% else_if CTypeShort = Intro %>
      <% else_if CTypeShort = Text %>

          <div class='typography space'>
            $Html.RAW
          </div>
          
      <% else_if CTypeShort = TextImage %>

        <div class='group'>
          <div class='imagecol' style='width:{$ColWidth}px;float:left'>
            <% include Article_content_image %>
          </div>
          <div class='typography space' style='margin-left:{$ColWidth}px'>
            <div style='margin-left:10px'>$Html.RAW</div>
          </div>
        </div>
      
      <% else_if CTypeShort = ImageRight %>

        <div class='group'>
          <div class='imagecol' style='width:{$ColWidth}px;float:right'>
            <% include Article_content_image %>
          </div>
          <div class='typography space' style='margin-right:{$ColWidth}px'>
            <div style='margin-right:10px'>$Html.RAW</div>
          </div>
        </div>
      
      <% else_if CTypeShort = ImageOnly %>

        <div class='imagecol'>
          <% include Article_content_image %>
        </div>
      
      <% else_if CTypeShort = PlainHtml %>

       <div class='typography'>$Text.RAW</div>

      <% end_if %>

    </div> 
    <% end_loop %>

  </div> 

  <div class='articleright'>
    
    <% loop C4P.getAll_RightContent %>

     <div class="$CTypeShort rightcolelement">

       <% if CTypeShort = Headline %>
         <h1>$Title</h1>
       <% else_if CTypeShort = Text %>

           <div class='typography'>
             $Html.RAW
           </div>

       <% else_if CTypeShort = ImageOnly %>

         <div class='imagecol'>
           <% include Article_content_image %>
         </div>

       <% else_if CTypeShort = PlainHtml %>

        <div class='typography'>$Text.RAW</div>

      <% else_if CTypeShort = Downloads %>
      
          <% with Parent %>
        
              <% if C4P.getAll_Downloads %>

              <h2>Downloads</h2>
              <% loop C4P.getAll_Downloads %>

              <div class = 'space'>
                $FileLink
              </div>
              <% end_loop %>

              <% end_if %>
      
          <% end_with %>
      
       <% end_if %>

     </div> 
     <% end_loop %>
    
    
    </div> 


  </div>

</div> 


