(function ($) {
  $(document).ready(function () {

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
      }, {
        window: win,
        input: field_name
      });
      return false;
    }


    var ts = new Date().getTime();

    var tinymce_options = {
      // Location of TinyMCE script
      script_url: '/Mwerkzeug/thirdparty/tinymce/tiny_mce.js',
      // General options
      theme: "advanced",
      plugins: "mwlink,safari,table,fullscreen,style,contextmenu,paste,noneditable,nonbreaking,inlinepopups,codemagic,lists",
      language: "en",
      autoresize_min_height: 200,
      autoresize_max_height: 600,
      content_css: "/mysite/css/typography.css?" + ts,
      body_class: "typography",
      // Theme options
      theme_advanced_buttons1: "bullist,hr,fullscreen,|,undo,redo,mwlink,unlink,cleanup,codemagic,bold,italic,|,styleselect", //forecolor,fontsizeselect,styleselect
      theme_advanced_buttons2: "", // "tablecontrols" was here
      theme_advanced_buttons3: "",
      theme_advanced_buttons4: "",
      theme_advanced_toolbar_location: "top",
      theme_advanced_toolbar_align: "left",
      theme_advanced_statusbar_location: "none",
      theme_advanced_resizing: true,
      paste_auto_cleanup_on_paste: true,
      paste_remove_styles: true,
      paste_remove_styles_if_webkit: true,
      paste_strip_class_attributes: true,
      paste_preprocess: function (pl, o) {
        //keep bold,italic,underline and paragraphs
        o.content = strip_tags(o.content, 'h1,h2,h3,h4,a,strong,b,span,header,footer,hr,strike,br,p,ul,ol,li,cite,blockquote,em,i,table,tr,tbody,thead,tfoot,td,th,caption');
      },
      theme_advanced_font_sizes: "10px,11px,12px,13px,15px,16px,17px,18px,20px,22px,24px,26px",
      // file_browser_callback: mwFileBrowser,
      valid_elements: "h1[class],h2[class],h3[class],h4[class],a[href|target|title|class|name],strong/b,span[class|style],header,footer,hr,[div[padding|align],strike,br,#p[padding|align|class],ul,ol,li,cite,blockquote,em,i[class]," +
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
      style_formats: [{
          title: 'Überschrift',
          block: 'h2'
        },
        {
          title: 'Sub-Überschrift',
          block: 'h3'
        },
        {
          title: 'Kleiner Text',
          inline: 'span',
          classes: 'size-s'
        }
      ],
      table_styles: "Fullwidth Layout-Table=fullwidthtable;Data-Table=datatable",
      relative_urls: false,
      //document_base_url : "http://[domain name]",
      script_loaded: function () {
        tinymce.PluginManager.load('mwlink', '/Mwerkzeug/tinymce_plugins/mwlink/editor_plugin.js');
      }
    };





    var tinymce_minimal_options = {
      // Location of TinyMCE script
      script_url: '/Mwerkzeug/thirdparty/tinymce/tiny_mce.js',
      // General options
      theme: "advanced",
      plugins: "safari,fullscreen,table,style,mwlink,contextmenu,paste,noneditable,nonbreaking,inlinepopups,autoresize",
      language: "en",
      autoresize_min_height: 200,
      autoresize_max_height: 600,
      content_css: "/mysite/css/typography.css?" + ts,
      body_class: "typography",
      // Theme options
      theme_advanced_buttons1: "bold,fullscreen",
      theme_advanced_buttons2: "",
      theme_advanced_buttons3: "",
      theme_advanced_buttons4: "",
      theme_advanced_toolbar_location: "top",
      theme_advanced_toolbar_align: "left",
      theme_advanced_statusbar_location: "none",
      theme_advanced_resizing: true,
      valid_elements: "strong/b",
      //document_base_url : "http://[domain name]",
      relative_urls: false,
      script_loaded: function () {
        tinymce.PluginManager.load('mwlink', '/Mwerkzeug/tinymce_plugins/mwlink/editor_plugin.js');
      }

    };




    setup_tinymce = function () {
      $('textarea.tinymce').each(function () {

        var use_options;
        var my_options = tinymce_options;

        //take width from textarea width (css):
        my_options.width = $(this).width();
        my_options.height = $(this).height();


        var instance_options = $(this).data('tinymce_options');

        //hook for local changes 2 tinymce_options:
        if (typeof (handle_local_tinymce_options) == "function") {
          use_options = handle_local_tinymce_options(my_options, this, instance_options);
        } else {
          use_options = my_options;
        }

        console.log('#log 9280', my_options);

        $(this).tinymce(use_options);
        //tinymce
      });
      //each



      // tinymce plain  --- begin 
      $('textarea.tinymce_minimal').each(function () {

        var use_options;
        var my_options = tinymce_minimal_options;


        if ($(this).hasClass('tinymce_mail')) {
          my_options.theme_advanced_buttons1 += ",mwlink,unlink,undo,redo";
          my_options.valid_elements += ",#p,#div,a[href|target|title],";
        }

        //take width from textarea width (css):
        my_options.width = $(this).width();
        my_options.height = $(this).height();
        //hook for local changes 2 tinymce_options:
        if (typeof (handle_local_tinymce_minimal_options) == "function") {
          use_options = handle_local_tinymce_minimal_options(my_options, this);
        } else {
          use_options = my_options;
        }

        $(this).tinymce(use_options);

        //tinymce
      });
      //each
      // tinymce minimal  --- end


    };

    // Strips HTML and PHP tags from a string 
    // https://stackoverflow.com/questions/4122451/tinymce-paste-as-plain-text
    // returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
    // example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
    // returns 2: '<p>Kevin van Zonneveld</p>'
    // example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
    // returns 3: '<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>'
    // example 4: strip_tags('1 < 5 5 > 1');
    // returns 4: '1 < 5 5 > 1'
    function strip_tags(str, allowed_tags) {

      var key = '',
        allowed = false;
      var matches = [];
      var allowed_array = [];
      var allowed_tag = '';
      var i = 0;
      var k = '';
      var html = '';
      var replacer = function (search, replace, str) {
        return str.split(search).join(replace);
      };
      // Build allowes tags associative array
      if (allowed_tags) {
        allowed_array = allowed_tags.match(/([a-zA-Z0-9]+)/gi);
      }
      str += '';

      // Match tags
      matches = str.match(/(<\/?[\S][^>]*>)/gi);
      // Go through all HTML tags
      for (key in matches) {
        if (isNaN(key)) {
          // IE7 Hack
          continue;
        }

        // Save HTML tag
        html = matches[key].toString();
        // Is tag not in allowed list? Remove from str!
        allowed = false;

        // Go through all allowed tags
        for (k in allowed_array) { // Init
          allowed_tag = allowed_array[k];
          i = -1;

          if (i != 0) {
            i = html.toLowerCase().indexOf('<' + allowed_tag + '>');
          }
          if (i != 0) {
            i = html.toLowerCase().indexOf('<' + allowed_tag + ' ');
          }
          if (i != 0) {
            i = html.toLowerCase().indexOf('</' + allowed_tag);
          }

          // Determine
          if (i == 0) {
            allowed = true;
            break;
          }
        }
        if (!allowed) {
          str = replacer(html, "", str); // Custom replace. No regexing
        }
      }
      return str;
    }


    // setup_tinymce
    setup_tinymce();
  });
})(jQuery);
