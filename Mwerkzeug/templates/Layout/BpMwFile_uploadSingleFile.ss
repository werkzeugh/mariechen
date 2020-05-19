<!-- http://www.angrymonkeys.com.au/blog/2010/03/28/single-file-upload-using-plupload/ -->

<!-- Load Queue widget CSS and jQuery -->
<% require css(Mwerkzeug/thirdparty/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css) %>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<!-- Load plupload and all it's runtimes and finally the jQuery queue widget -->

<% require javascript(Mwerkzeug/thirdparty/plupload/js/plupload.full.js) %>
<% require javascript(Mwerkzeug/thirdparty/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js) %>

<script>
$(document).ready(function() {

  plupload.addI18n({
    <% if Language == de %>
          'Select files' : 'Datei auswählen',
          'Add files to the upload queue and click the start button.' : 'Datei in die Warteschlange stellen und Start-Button drücken ',
          'Filename' : 'Dateiname',
          'Status' : 'Status',
          'Size' : 'Dateigröße',
          'Add files' : 'Dateien hinzufügen',
          'Start upload':'Start Upload',
          'Stop current upload' : 'Upload abbrechen',
          'Start uploading queue' : 'Warteschlange uploaden',
          'Drag files here.' : 'Datei mit der Maus hierher ziehen.'
    <% else %>
          'Select files' : 'choose file',
          'Add files to the upload queue and click the start button.' : 'Add a file to the upload-queue and click Start'
          
    <% end_if %>
  });
          
 });

</script>


<div id="container" style='margin:3px 8px'>
	<div id="filelist">No runtime found.</div>
	<br />
	<a id="pickfiles" href="#" class='button'><span class='tinyicon ui-icon-folder-open'></span><b id='UploadBrowseButtonText'></b></a>
  <!-- <a id="uploadfiles" href="#">[Upload files]</a> -->
</div>



<script type="text/javascript">


// Custom example logic
$(function() {

   var MwFileField=window.parent.LastUsedMwFileField;
   
   var plupload_options={
		runtimes : 'html5,flash,html4',
		browse_button : 'pickfiles',
		container : 'container',
		max_file_size : '20mb',
		url : '/BE/MwFile/receive/',
		multi_selection: false,
		flash_swf_url : '/Mwerkzeug/thirdparty/plupload/js/plupload.flash.swf',
		filters : [
  		{title : "Image files", extensions : "jpg,jpeg,gif,png,svg"},
  		{title : "Video files", extensions : "mp4,avi,mov,gpp,mv4,flv"},
  		{title : "Zip files", extensions : "zip"},
  		{title : "PDF files", extensions : "pdf"},
		{title : "Other",           'extensions' : "ics"},
  		{title : "MS-Office files", extensions : "doc,docx,xls,xlsx,ppt,pptx,key,pps"}
		]
		//resize : {width : 320, height : 240, quality : 90}
	};
    
   if(MwFileField)
   {
     $('#UploadBrowseButtonText').text(MwFileField.options.texts.UploadBrowseButtonText);
     
     if(MwFileField.options.plupload_options)
     {
         $.extend(plupload_options, MwFileField.options.plupload_options);
     }
   }    
   
   
    if (window.console && console.log) { console.log(plupload_options);  }
	var uploader = new plupload.Uploader(plupload_options);

	uploader.bind('Init', function(up, params) {
		$('#filelist').html("<div style='color:#eee'>(" + params.runtime + ")</div>");
	});



	$('#uploadfiles').click(function(e) {
		uploader.start();
		e.preventDefault();
	});

	uploader.init();

	uploader.bind('FilesAdded', function(up, files) {
		$.each(files, function(i, file) {
			$('#filelist').append(
				'<div id="' + file.id + '">' +
				file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
			'</div>');
		});

		up.refresh(); // Reposition Flash/Silverlight
	});

  uploader.bind('QueueChanged', function(up) {
  if ( up.files.length > 0 && uploader.state != 2) {
  uploader.start();
  }
  });
  

	uploader.bind('UploadProgress', function(up, file) {
		$('#' + file.id + " b").html(file.percent + "%");
	});

	uploader.bind('Error', function(up, err) {
		$('#filelist').append("<div>Error: " + err.code +
			", Message: " + err.message +
			(err.file ? ", File: " + err.file.name : "") +
			"</div>"
		);

		up.refresh(); // Reposition Flash/Silverlight
	});


	uploader.bind('FileUploaded', function(up, file,info) {
	
	  response=eval('(' + info.response + ')');
	  if (window.console && console.log) {
        // firebug logger
        console.log('MwFileItem: ' + response);
    }
    if(response.error || !response.id)
    {
  		$('#' + file.id + " b").html("<span style='color:red'>FAIL</span>");
    }
    else
    {
  	  MwFileField.element.val(response.id);
      $('#' + file.id + " b").html("100%");
	  }
	  MwFileField.redraw();
	  
	
	});
});


</script>






