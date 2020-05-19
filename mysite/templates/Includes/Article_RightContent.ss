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

        <div class='typography'>$Text</div>

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
