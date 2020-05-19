<div class="">
 
 
    <% if Level(3) %>
    <% include BreadCrumbs %>
    <% end_if %>
    
    <% include sessionMessage %>




    <h2> $Title</h2>
    <% if publish_date %>
    <b>$publish_date</b>
    <% end_if %>
  
      <% if Image1 %>
       $Image1.SetWidth(500)
      <% end_if %>
    
    $Content.RAW  
    $Plain_Html.RAW

  

    $Form.RAW

   
</div>



