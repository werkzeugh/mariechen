<% loop C4P.getAll_MainContent %>

    <div class="celement $CTypeShort">

      <% if CTypeShort = Headline %>
      <% else_if CTypeShort = Intro %>
      <% else_if CTypeShort = Text %>

          <div class='typography space'>
            $Html.RAW
          </div>
          
      <% else_if CTypeShort = ImageLeft %>

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

       <div class='typography'>$Text</div>

      <% end_if %>

    </div> 
    <% end_loop %>
