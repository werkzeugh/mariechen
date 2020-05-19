<!-- Load Queue widget CSS and jQuery -->


<% require css(Mwerkzeug/thirdparty/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css) %>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->
<% require javascript(Mwerkzeug/thirdparty/plupload/js/plupload.full.js) %>
<% require javascript(Mwerkzeug/thirdparty/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js) %>

<% if Language == de %>
<script>
$(document).ready(function() {

  plupload.addI18n({
          'Select files' : 'Dateien zum Upload auswählen',
          'Add files to the upload queue and click the start button.' : 'Dateien in die Warteschlange stellen und Start-Button drücken ',
          'Filename' : 'Dateiname',
          'Status' : 'Status',
          'Size' : 'Dateigröße',
          'Add files' : 'Dateien hinzufügen',
          'Start upload':'Start Upload',
          'Stop current upload' : 'Upload abbrechen',
          'Start uploading queue' : 'Warteschlange uploaden',
          'Drag files here.' : 'Dateien mit der Maus hierher ziehen.'
  });
          
 });

</script>
<% end_if %>

<form >
  <div style='background:#ddd;margin:8px;padding:8px'>
    <label>Copyright-Info for uploaded Files: </label>
    &copy; <input type='text' id='UploaderCopyright' style='width:200px'>
  </div>
  <div id="uploader">
    <p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p>
  </div>
</form>


<script type="text/javascript">
// Convert divs to queue widgets when the DOM is ready
$(function() {
	$("#uploader").pluploadQueue({
		// General settings
		'runtimes' : 'html5,flash,html4',
		'url' : <% if UploadURL %>'$UploadURL'<% else %>'/BE/MwFile/receive/$CurrentDirectory.ID'<% end_if %>,
		'max_file_size' : '100mb',
		'chunk_size' : '1000mb',
		'unique_names' : false,
    'rename' : true,
		// Resize images on clientside if we can
		//resize : {width : 320, height : 240, quality : 90},

		// Specify what files to browse for
		filters : [
		  <% if FiltersJSON %>
        $FiltersJSON
		  <% else %>
  			{'title' : "Image files",     'extensions' : "jpg,jpeg,gif,png,svg"},
  			{'title' : "Video files",     'extensions' : "mp4,avi,mov,gpp,mv4,flv"},
  			{'title' : "Zip files",       'extensions' : "zip"},
        {'title' : "Text files",      'extensions' : "txt"},
  			{'title' : "PDF files",       'extensions' : "pdf"},
  			{'title' : "Other",           'extensions' : "ics"},
  			{'title' : "MS-Office files", 'extensions' : "doc,docx,xls,xlsx,ppt,pps,key,pptx"}
		  <% end_if %>
		],

		// Flash settings
		'flash_swf_url' : '/Mwerkzeug/thirdparty/plupload/js/plupload.flash.swf',
		
		init: {
		        StateChanged: function(up) {
		                  if (up.state == plupload.STARTED)
		                  {
		                    var copyright = $('#UploaderCopyright').val();
                        
                        if(!up.settings.original_url) 
                        {
                          up.settings.original_url=up.settings.url;
                        }
                        up.settings.url = up.settings.original_url + '&Copyright=' + escape(copyright);
		                    //console.log('set copyright to:'+copyright+'for url'+up.settings.url);
		                  }
              
                      if (up.state == plupload.STOPPED)
                       {
                         if( typeof(onPluploadComplete)=='function' )
                         {
                           onPluploadComplete();
                         }
                       }
                  }  
		}

	});

	// Client side form validation
	$('form').submit(function(e) {
		var uploader = $('#uploader').pluploadQueue();

		// Validate number of uploaded files
		if (uploader.total.uploaded == 0) {
			// Files in queue upload them first
			if (uploader.files.length > 0) {
				// When all files are uploaded submit form
				uploader.bind('UploadProgress', function() {
					if (uploader.total.uploaded == uploader.files.length)
						$('form').submit();
				});

				uploader.start();
			} else
				alert('You must at least upload one file.');

			e.preventDefault();
		}
	});
});
</script>


