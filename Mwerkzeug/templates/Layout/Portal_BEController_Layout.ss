<div class='bootstrap' style='width:1000px'>

    <div class='row'>
    
        <div class='span3'>
        
            <div style="padding: 8px 0;" class="well">
                <ul class="nav nav-list">
                    <li class="nav-header">Portale</li>
                    <% loop PortalGroups %>
                        <li class='action-listing id-$ID'><a href="$Top.CurrentBaseURL/listing/$ID"><i class="icon-user"></i>$Title</a></li>
                    <% end_loop %>

                </ul>
            </div>
            
            <script type="text/javascript" charset="utf-8">
            
              var active_navitems=$('.nav-list .action-$Action');
              
              if(active_navitems.length>1)
              {
                  active_navitems=$('.nav-list .action-{$Action}.id-{$Url_ID}');
              }
              active_navitems.addClass('active');
              $('i',active_navitems).addClass('icon-white');
            </script>
        
        </div>
        <div class='span9'>

            $SubLayout
            
        </div>
    
    </div>    
</div>

