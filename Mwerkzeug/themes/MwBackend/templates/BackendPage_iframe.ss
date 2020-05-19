<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 
  $MetaTags
<link rel="stylesheet" type="text/css" media="screen, projection" href="$ThemeDir/css/screen.css" />
<% require css(Mwerkzeug/thirdparty/jqueryui/css/south-street/jquery-ui-1.10.3.custom.css) %>
 
</head>
<body class="action-$Action">
  <style type="text/css" media="screen">
    .hide_in_frame {display:none;}
  </style>
  
  $preHTML
  <div class='right iframe'>
    $Layout
  </div>
  $postHTML
<% include layout_js %>
  
</body>
</html>
