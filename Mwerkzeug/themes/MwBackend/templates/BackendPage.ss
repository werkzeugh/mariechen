<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<% base_tag %>

<% if SkinVersion==2 %>

<% else %>
    <link rel="stylesheet" type="text/css" media="screen, projection" href="$ThemeDir/css/screen.css" />
    <% require css(Mwerkzeug/thirdparty/jqueryui/css/south-street/jquery-ui-1.10.3.custom.css) %>
<% end_if %>

$MetaTags

</head>
<body class="mysite BE $ClassName $ClassName-$Action">

<script type="text/javascript" charset="utf-8">(function($) { $(document).ready(function() { // Silverstripe jQuery header
  
	// Add .last class to certain lists
	$(document).ready(function(){
		$("ul li:last").addClass("last");
		});
 
  }); })(jQuery);                                                                             // Silverstripe jQuery footer </script>

<% include BackendPage_Header %>

<div id="wrap" class="group">
	
	<div id="layout">		
	 
	 $Layout
	 
	</div> <!-- /.layut -->

	<div id="footer">
   $Now.Format(D - d.m.Y) $TimeNow.Nice24
	</div> <!-- /footer -->
</div> <!-- /wrap -->

<!-- <(°) -->


</body>
</html>
