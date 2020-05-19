<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<head>
    <% base_tag %>
    $MetaTags
</head>
<body class='page-$ID'>

<style>
  
  #emailinput {width:400px;font-size:18px;}
  
</style>



<div class='space'><label><strong>E-Mail Adress</strong></label></div>
<input type='text' id='emailinput' value='$CurrentValue'>

<div class='space'>
  <a href='#' class='button uselink_email' style='font-size:16px'><span class='tinyicon ui-icon-arrowthick-1-e'></span>OK</a>
</div>
<script>

$("a.uselink_email").live('click',function(e){
   e.preventDefault();
   var email = $.trim($('#emailinput').val());
   
   var mwlink = "mwlink://email:" + email;
   setMwLink(mwlink);
   
 });
 
</script>