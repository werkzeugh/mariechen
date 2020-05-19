<style>


#mainholder {
 width:100%;
 height:100%
}

html, body {
    height: 100%
  }


body.Page-index {
    overflow: hidden;
    margin: 0
}
body.Page-index #wrap {
    width:100%;
    max-width:100%;
    min-width:100%;
    margin:0px;
    padding:0px
}



#footer {
    display:none;
}

#mainholder {
    width: 100%;
    height:100%;
    position:relative;
}
#framewrapper {
    float: left;
    width: 100%;
    height: 100%;
    position: relative;
}
#leftframe_div {
    float: left;
    width: $conf(JsTreeWidth)px;
    margin-left: -100%;
    
}

#rightframe_div {
    margin-left: $conf(JsTreeWidth)px;
    <% if not SkinVersion == 2 %>
        padding-left:10px;
    <% end_if %>

}

#leftframe_div,#rightframe_div {
    height: 100%;
    position: relative
}
#leftframe,#rightframe {
    height: 100%;
    width: 100%
}

#resizeslider-div {
    margin-right:500px;
    margin-left:100px;
    height:20px;
}
#slider {
    height:20px;
    overflow:hidden;
    line-height: 20px;
    width:auto;
    color:#ccc;
    cursor:e-resize;
    position: absolute;
    font-size: 20px;
}

<% if  SkinVersion == 2 %>

    body {background-color: #35434e}
<% end_if %>

</style>


<div id='mainholder' class='bp-page-framed'>
    <div id="resizeslider-div"><div id="slider">⬌</div></div>

    <div id='framewrapper'>
        <div id='rightframe_div'><iframe id='rightframe' name='rightframe' src='/BE/Pages/edit'></iframe></div>
    </div>
    <div id='leftframe_div'><iframe id='leftframe' name='leftframe'   src='/BE/Pages/edit'></iframe></div>
    
</div>

<script type='text/javascript'>

var CurrentPageID;
var setCurrentPageID=function(id)
{
    if(id>0)
    {
     CurrentPageID=id;
     $('.current',$('#leftframe').contents() ).removeClass('current');
     $('#node_'+CurrentPageID,$('#leftframe').contents() ).addClass('current');
    }
};


$(function(){

    var autosize=function(){
        $('#mainholder').css({'height':(($(window).height())-$('#header').height())+'px'});
    };
    
    $(window).resize(function(){
        autosize();
    });

    autosize();
    
    $(document).ready(function($) {
        autosize();

        var slider_w=$( "#slider").width();

        $( "#slider").draggable({ containment: "parent" }).on( "drag", function( event, ui ) {

            var w=ui.offset.left+slider_w;
            $('#leftframe_div').width(w);
            $('#rightframe_div').css('margin-left',w+'px');
        } ).css('left',($conf(JsTreeWidth)-slider_w)+'px');

        // $( "#resizeable-div" ).resizable({ handles: "e",maxWidth: 800,minWidth:100  }).on( "resize", function( event, ui ) {

        //      $('#leftframe_div').width(ui.size.width);
        //      $('#rightframe_div').css('margin-left',ui.size.width+'px');

        // } );


        var navurl='/BE/Pages/treeframe';
        
        if(window.location.hash)
        {
            var editurl=window.location.hash;
            editurl=editurl.replace(/#/,'/BE/Pages/');
            $('#rightframe').attr('src',editurl);
            if(editurl.match('/edit/'))
            {
                navurl=editurl.replace(/\\/edit\\//,'/treeframe/');
            }
        }
        
            $('#leftframe').attr('src',navurl);

     });


    
});
</script>


