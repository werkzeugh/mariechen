<script type="text/javascript" charset="utf-8">

var editMode = '$editMode';

if (editMode === '1') {
    setup_tinymce();
    $('.MwFileField').MwFileField().change(function() {
        CElement_submit(this);
    });
    
    $('.MwLinkField').MwLinkField({});
    
    $('.editbuttons,.addCElement').css('visibility', 'hidden');
    $('div.CElement form .editbuttons').css('visibility', 'visible');
}
 else
 {
    $('.editbuttons,.addCElement').css('visibility', 'visible');
}

</script>

