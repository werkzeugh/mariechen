<div class='main-left-wrap'>

    <div class='main-leftcol'>


        <div class='main-left-inner'>

            <% if SubNavItems %>

            <div class='main-leftnav-box'>
                <ul class='main-leftnav'>
                    <% loop SubNavItems %>
                    <li class="main-leftnav-item " id='main-leftnav-item-$ID' >
                        <a href="$LeftnavLink" data-dbid='$ID' >$MenuTitle</a>
                    </li>
                
                    <% if UnHiddenChildren %>
                    <ul  class='main-leftnav-$ID' id='main-leftnav-subitems-$ID' >
                        <% loop UnHiddenChildren %>
                        <li class="main-leftnav-item" id='main-leftnav-item-$ID'>
                            <a href="$LeftnavLink" data-dbid='$ID' >$MenuTitle</a>
                        </li>
                        
                        <% if UnHiddenChildren %>
                        <ul  class='main-leftnav-$ID'   id='main-leftnav-subitems-$ID'>
                            <% loop UnHiddenChildren %>
                            <li class="main-leftnav-item" id='main-leftnav-item-$ID'>
                                <a href="$LeftnavLink" data-dbid='$ID'>$MenuTitle</a>
                            </li>
                            <% end_loop %>
                        </ul>
                        <% end_if %>
                        
                        <% end_loop %>
                    </ul>
                
                    <% end_if %>
                    <% end_loop %>
            
                </ul>    
            </div>

            <% else %>
            <h3>$MenuTitle</h3>
            <% end_if %>
    
        </div>

        <div class='left-teaser'>

            <% loop C4P.getFirst_LeftSlider %>

            <a href="$Link">
                <h3>$Title</h3>
            </a>

            <% end_loop %>

        </div>

  


    </div>
</div>

<% if  URLSegment!='home' %>
<% if   HomePage.C4P.getAll_LeftSlider  %> 
<div class='main-left-wrap left-slider-wrap'>
    <div class='left-slider'>
        <ul>
            <% loop HomePage.C4P.getAll_LeftSlider  %>
            <li>
                <a href='$Link'>
                    <div class='imgdiv'>$Picture.CroppedImage(238,120)</div>
                    <div class='title'>$Title</div>
                    <div class='text'>$Text</div>
                </a>
            </li>
            <% end_loop %>
        </ul>
    </div>

</div>
<% end_if %>
<% end_if %>

<script type="text/javascript">


$('.left-slider').unslider({
    delay: 5000
});

<% loop  Parents %>

$('#main-leftnav-item-$ID').addClass('active');
$('#main-leftnav-item-$ID').closest('ul').show();
$('#main-leftnav-subitems-$ID').closest('ul').show();

<% end_loop %>


$('.main-leftnav').on('click','a',function(e)
{
    if($(this).attr('href')=="#")
    {
        e.preventDefault();
    }

    $('#main-leftnav-subitems-'+$(this).data('dbid')).slideDown(500,function(e)
    {
        resizeCols();
    });
});

</script>
