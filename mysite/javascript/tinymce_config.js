(function($) {
    $(document).ready(function() {


        function mwFileBrowser(field_name, url, type, win) {

            // alert("Field_Name: " + field_name + "\nURL: " + url + "\nType: " + type + "\nWin: " + win); // debug/testing
            /* If you work with sessions in PHP and your client doesn't accept cookies you might need to carry
          the session name and session ID in the request string (can look like this: "?PHPSESSID=88p0n70s9dsknra96qhuk6etm5").
          These lines of code extract the necessary parameters and add them back to the filebrowser URL again. */

            var cmsURL = '/BE/MwFileChooser/?type=' + escape(type) + '&fileurl=' + escape(url);

            tinyMCE.activeEditor.windowManager.open({
                file: cmsURL,
                title: 'FileBrowser',
                width: 620,
                // Your dimensions may differ - toy around with them!
                height: 500,
                resizable: "yes",
                inline: "yes",
                // This parameter only has an effect if you use the inlinepopups plugin!
                close_previous: "no"
            },
            {
                window: win,
                input: field_name
            });
            return false;
        }

        setup_tinymce = function()
        {
          
          
           // tinymce plain  --- begin 
            $('textarea.tinymce').each(function() {

                $(this).tinymce({
                    // Location of TinyMCE script
                    script_url: '/mysite/thirdparty/tinymce/jscripts/tiny_mce/tiny_mce.js',
                    // General options
                    theme: "advanced",
                    plugins: "safari,table,style,mwlink,contextmenu,paste,noneditable,nonbreaking,inlinepopups",
                    language : "en",
                    content_css: "/mysite/css/typography.css",
                    body_class: "typography",
                    // Theme options
                    theme_advanced_buttons1: "undo,redo,link,unlink,cleanup,bold,italic",//styleselect
                    theme_advanced_buttons2: "",
                    theme_advanced_buttons3: "",
                    theme_advanced_buttons4: "",
                    theme_advanced_toolbar_location: "top",
                    theme_advanced_toolbar_align: "left",
                    theme_advanced_statusbar_location: "none",
                    theme_advanced_resizing: true,
                    theme_advanced_font_sizes : "10px,11px,12px,13px,15px,16px,17px,18px,20px,22px,24px,26px",
                    file_browser_callback: mwFileBrowser,
                    valid_elements: "a[href|target|title],strong/b,span[class],hr,[div[padding|align|class],strike,br,p[padding|align|class],ul,ol,li,em/i," + 
                     "-table[border=0|cellspacing|cellpadding|width|frame|rules|" +
                     "height|align|summary|bgcolor|background|bordercolor|class],-tr[rowspan|width|" +
                     "height|align|valign|bgcolor|background|bordercolor],tbody,thead,tfoot," +
                     "#td[colspan|rowspan|width|height|align|valign|bgcolor|background|bordercolor" +
                     "|scope],#th[colspan|rowspan|width|height|align|valign|scope],caption",
                    // // Drop lists for link/image/media/template dialogs
                    // template_external_list_url : "lists/template_list.js",
                    // external_link_list_url : "lists/link_list.js",
                    // external_image_list_url : "lists/image_list.js",
                    // media_external_list_url : "lists/media_list.js",
                    // style_formats: [
                    // {
                    //     title: 'Ueberschrift',
                    //     block: 'h2'
                    // }              
                    // ],
                    table_styles: "Fullwidth Layout-Table=fullwidthtable;Data-Table=datatable",
                    width: $(this).width(),
                    //take width from textarea width (css)
                    height: $(this).css('height'),
                    //take width from textarea width (css)
                    //document_base_url : "http://[domain name]",
                    relative_urls: false,
                    script_loaded: function()
                    {
                      tinymce.PluginManager.load('mwlink', '/Mwerkzeug/tinymce_plugins/mwlink/editor_plugin.js');
                    }
                });
                //tinymce
            });
            //each
            // tinymce plain  --- end
            
            
            // tinymce plain  --- begin 
             $('textarea.tinymce_minimal').each(function() {

                 $(this).tinymce({
                     // Location of TinyMCE script
                     script_url: '/mysite/thirdparty/tinymce/jscripts/tiny_mce/tiny_mce.js',
                     // General options
                     theme: "advanced",
                     plugins: "safari,table,style,mwlink,contextmenu,paste,noneditable,nonbreaking,inlinepopups",
                     language : "en",
                     content_css: "/mysite/css/typography.css",
                     body_class: "typography",
                     // Theme options
                     theme_advanced_buttons1: "bold",
                     theme_advanced_buttons2: "",
                     theme_advanced_buttons3: "",
                     theme_advanced_buttons4: "",
                     theme_advanced_toolbar_location: "top",
                     theme_advanced_toolbar_align: "left",
                     theme_advanced_statusbar_location: "none",
                     theme_advanced_resizing: true,
                     valid_elements:"strong/b",
                     width: $(this).width(),
                     //take width from textarea width (css)
                     height: $(this).css('height'),
                     //take width from textarea width (css)
                     //document_base_url : "http://[domain name]",
                     relative_urls: false,
                     script_loaded: function()
                    {
                      tinymce.PluginManager.load('mwlink', '/Mwerkzeug/tinymce_plugins/mwlink/editor_plugin.js');
                    }

                 });
                 //tinymce
             });
             //each
             // tinymce plain  --- end
               
            
        };
        // setup_tinymce
        setup_tinymce();
    });
})(jQuery);
