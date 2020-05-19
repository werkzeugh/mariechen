
<style type="text/css">

.eventpane {width:900px;
	background:#F3F3F4;
	-moz-box-shadow: 0px 0px 12px #aaa;
	-webkit-box-shadow: 0px 0px 12px #aaa;
	box-shadow: 0px 0px 12px #aaa;
	margin:20px auto;
}

.eventleft{float:left;width:430px;}
.eventright{float:right;width:430px;}

.eventinner {padding:20px;}

h1 {font-size:25px;line-height:35px; color:$Color(1);font-weight:normal;}
h1 strong{font-size:50px; display:block;font-weight:normal;}
h2 {font-size:25px; color:$Color(1);font-weight:bold;}
h3 {font-size:15px; color:$Color(1);font-weight:normal;}

.date,.loc {color:$Color(1);font-weight:bold; font-size:18px;line-height:22px;}


/* nicebutton */
.nicebutton {
	color: #e8f0de;
	border: solid 1px #538312;
	background: #64991e;
	background: -webkit-gradient(linear, left top, left bottom, from(#7db72f), to(#4e7d0e));
	background: -moz-linear-gradient(top,  #7db72f,  #4e7d0e);
	filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#7db72f', endColorstr='#4e7d0e');
}
.nicebutton:hover {
	background: #538018;
	background: -webkit-gradient(linear, left top, left bottom, from(#6b9d28), to(#436b0c));
	background: -moz-linear-gradient(top,  #6b9d28,  #436b0c);
	filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#6b9d28', endColorstr='#436b0c');
}
.nicebutton:active {
	color: #a9c08c;
	background: -webkit-gradient(linear, left top, left bottom, from(#4e7d0e), to(#7db72f));
	background: -moz-linear-gradient(top,  #4e7d0e,  #7db72f);
}
	
.error {color:#900;font-weight:bold;background:yellow;padding:3px;margin:10px;}

  .formular input[type='text'],
  .formular input[type='password'],
  .formular select
   {
    margin:3px 0px;
    width:100%;
    font-size:1em;
    color:#555;
  }

  .formular {width:550px;}
  .formular .column {
    float:left;
    width:auto;
    margin-right:20px;
    margin-top:5px;
  }

  .formular .column label{
    display:block;
    float:none;
    width:auto;
  }
  
  
  .formgroup {
    margin-bottom:20px;
    border-top:5px solid #186F8B;
    padding-top:8px;
  }
  h2 {position:relative;}
  h2 .button {position:absolute;right:-50px; top:30px;}
  
  .formitem-Interessensgebiete label {
    display:block;
    float:none;
  }
</style>

<div class='eventpane'>
	<div class='eventinner group'>
			<div class='eventleft'>
				 	<h1>Ihre<strong>Anmeldung</strong></h1>

				 	<h2>$EventTitle</h2>
				 	<h3>$SubTitle</h3>

				 	<div class='date'>$Datum1.GermanDate(l; d.m.Y) $TimeText</div>
				 	<div class='date'>$LocTitle, $LocStreet,<br> $LocZip $LocCity</div>


				 	<% loop Person %>
					 	<div class='space'><strong>Willkommen $Title $Firstname $Lastname</strong></div>
					<% end_loop %>
				 	
				 	<div class='space'>
				 		<input type='checkbox'>Ich komme gerne.
				 		<input type='checkbox'>Ich komme in Begleitung von <input type='text' size=5>Personen
				 		<input type='checkbox'>Ich kann leider nicht kommen.
				 	</div>

				 	<form method='POST' class='formular'>

				 		<% if ErrorTxt %>
					 		<div class='error'>$ErrorTxt</div>
				 		<% end_if %>

				 		 <div class='group'>
					          <div class='column' style='width:140px'>$FormField(Gender).HTML.RAW</div>
					          <div class='column' style='width:140px'>$FormField(Title).HTML.RAW</div>
				        </div>

				 		 <div class='group'>
					          <div class='column' style='width:140px'>$FormField(Firstname).HTML.RAW</div>
					          <div class='column' style='width:140px'>$FormField(Lastname).HTML.RAW</div>
				        </div>
							
						<div class='space'>
							<a href='{$Link}codeentry' class='nicebutton submit'>Weiter</a>
						</div>

				 	</form>

			</div>
			
	</div>
</div>
