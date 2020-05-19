
<div class='bootstrap' style='width:1000px'>

    <div class='row'>
    
        <div class='span3'>
        
            <div style="padding: 8px 0;" class="well">
                <ul class="nav nav-list">
                    <li class="nav-header">Users</li>
                    <% loop UserGroups %>
                        <li class='action-listing id-$ID'><a href="$Top.CurrentBaseURL/listing/$ID"><i class="icon-user"></i>$Title</a></li>
                    <% end_loop %>

                        <li class='action-listing id-'><a href="$CurrentBaseURL/listing"><i class="icon-user"></i>All Users</a></li>
                    <!-- <li class='action-invited muted'><a href="#"><i class="icon-envelope"></i>Invited Users</a></li> -->
                    <li class="nav-header">Groups</li>
                    <li class='action-groups'><a href="$CurrentBaseURL/groups"><i class="icon-list"></i> Manage Groups</a></li>
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

