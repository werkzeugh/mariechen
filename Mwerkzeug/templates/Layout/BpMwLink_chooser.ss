<style>
body {background:#FFF;margin:0px;}
table.filelist td {width:250px;padding:0px 10px;}

.filelist h2 {font-size:17px;margin-bottom:15px;color:#555;}

.filelist li a {color:#888;display:block;padding:3px 5px;border-bottom:1px dotted #aaa;}

.filelist li a:hover {background:#ddd;}
.filelist li a strong {font-size:12px;color:#222;}

</style>

<div id="tabs">

  <ul>
  <% if not HideRecentItems %>
    <li><a href="#tabs-1">Recent Items</a></li>
<% end_if %>

    <li><a href="#tab-pages">Pages</a></li>
    <li><a href="#tab-files">Files</a></li>
    <li><a href="/BE/MwLink/ajaxChooserExternalUrl?MwLink=$UrlEncodedMwLink">External URL</a></li>
    <li><a href="/BE/MwLink/ajaxChooserEmail?MwLink=$UrlEncodedMwLink">E-Mail</a></li>
    <% if  AdditionalTabs￼ %>
    <% loop AdditionalTabs %>
    <li><a href="/BE/MwLink/ajaxChooser{$Key}?MwLink=$UrlEncodedMwLink">$Title</a></li>
    <% end_loop %>
    <% end_if %>

    <li><a href="#tab-remove">remove Link </a></li>
  </ul>
  <% if not HideRecentItems %>

  <div id="tabs-1">

    <table border="0" cellpadding="0" class='filelist'>
      <tr>

        <td>
          <% if  RecentPages %>

          <h2>Pages</h2>

          <ul>
            <% loop  RecentPages %>
            <li><a href='#' title='$ID $Title' mwlink='$MwLink'  class='mwlink'><strong>$MenuTitle</strong><br> $ReadableUrl</a></li>
            <% end_loop %>
          </ul>
          <% end_if %>
        </td>

        <td>
          <% if  RecentFiles %>
          <h2>Files</h2>

          <ul>
            <% loop  RecentFiles %>
              <li><a href='#' mwlink='$MwLink' class='mwlink'><strong>$Title</strong><br>$ReadableUrl</a></li>
            <% end_loop %>
          </ul>
          <% end_if %>

        </td>

      </tr>
    </table>


  </div>
  <% end_if %>

  <div id="tab-pages" title='/BE/Pages/MwLinkChooser/?MwLink=$UrlEncodedMwLink'  style='min-height:400px'></div>
  <div id="tab-files" title='/BE/MwFileFieldChooser/?MwLink=$UrlEncodedMwLink'></div>

  <div id="tab-remove" >
    <a href='#' class='button mwlink' mwlink=''><span class='tinyicon ui-icon-trash'></span>remove link</a>
  </div>
  <div id="tab-remove" >

  </div>


</div>



<script>


function setMwLink(mwlink) {
    if (window.opener) {
      //popup version (tinymce)
      if (window.opener.LastUsedMwLinkField) {
        window.opener.LastUsedMwLinkField.updateIDFromPopupWindow(mwlink);
        window.close();
      } else alert('error: cannot find file-chooser field in parent window.');
    } else if (parent) {
      //boxy version
//      console.log(parent.LastUsedMwLinkField);
      parent.LastUsedMwLinkField.updateIDFromPopupWindow(mwlink);
    }

  }


$(document).ready(function() {

    $("#tabs").tabs({
        ajaxOptions: {
            error: function(xhr, status, index, anchor) {
                $(anchor.hash).html("Couldn't load this tab. ");
            }
        },
        activate: function(event, ui) {


            var pnl = $(ui.newPanel);
            var url = pnl.attr('title'); // get url to load from title attr

            if (window.console && console.log) { console.log('url',ui);  }

            if ((url) && (pnl.find('iframe').length == 0)) {
                $('<iframe />').attr({
                    frameborder: '0',
                    scrolling: 'yes',
                    src: url,
                    width: '100%',
                    height: '500px'
                }).appendTo(pnl).load(function() { // IFRAME resize code
                    var iframe = $(this); // iframe element
                    var win = this.contentWindow; // child window
                    var element = $(win.document); // element to size to; .document may return 0 depending on sizing of document, may have to use another element
                    $(win.document).ready(function() {
                        iframe.height(element.height()); // resize iframe
                    });
                });
            }
            return true;
        }
    });

    <% if HideRecentItems %>

    setTimeout(function() {
              $("#tabs").tabs("option", "active", 1);
              $("#tabs").tabs("option", "active", 0);
          }, 500);

    <% end_if %>

    $("a.mwlink").live('click', function(e) {
        e.preventDefault();
        var mwlink = $(this).attr('mwlink');
        setMwLink(mwlink);
    });


});

</script>
