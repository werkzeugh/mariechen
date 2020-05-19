<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<head>
<style type="text/css" >
 
body.mailpreviewpage {background:#ddd;}
.mailpreview {
    margin:20px;
    border:1px solid #aaa;
    min-height:500px;
}
     
</style> 

</head>
<body class='mailpreviewpage page-$ID $ClassName'>

e-Mail-Voransicht:


<div class='mailpreview'>

  $MailHTML

</div>


<form action='$CurrentURL/testsend' method='POST'>
    send a test-Email to:
     <input type='text' name='email' size=30>
     
     <input type='submit' value='send test-email'>
</form>
