$CustomSettingsHTML

<div class='c4p_settings'>
       
    <label>Status:</label> 
    <input type='radio' value='1' name='fdata[Hidden]' <% if Hidden %>checked<% end_if %> > hidden
    <input type='radio' value='0' name='fdata[Hidden]' <% if Hidden %><% else %>checked<% end_if %> > published 

    <div class='space'>
        <label class='open_extended'><span>▶</span> <u>timers...</u></label>

        <div class='extended' style='display:none' >
            <label>set Status to 'hidden' on:</label> 
            <input type='text' value='$NiceDateTime(HideOn)' class='c4p_datepicker' name='fdata[HideOn]' title="Format: YYYY-MM-DD (hh:mm)" style="width:120px">
            <label>set Status to 'published' on:</label> 
            <input type='text' value='$NiceDateTime(PublishOn)' class='c4p_datepicker' name='fdata[PublishOn]' title="Format: YYYY-MM-DD (hh:mm)" style="width:120px">
        </div>
    </div>

</div>
<script type="text/javascript" charset="utf-8">
    

jQuery('.c4p_settings .open_extended').click(function()
{
    var panel=$('.extended',$(this).parent());
    $('span',this).text(panel.is(":visible")?'▶':'▼');
    
    panel.slideToggle(200,function(){
        
        jQuery('.c4p_datepicker',this).datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            showOn: "button",
            yearRange: '-1:+4',
            minDate:'0',
            dateFormat: 'yy-mm-dd 00:00',
            buttonImage: "/Mwerkzeug/images/calendar.gif",
            buttonImageOnly: true
        });
        
    });
    
});    

<% if hasTimers %>
jQuery('.c4p_settings .open_extended').click();
<% end_if  %>

</script>


