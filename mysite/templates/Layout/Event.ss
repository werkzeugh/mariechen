
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
	
</style>

<div class='eventpane'>
	<div class='eventinner group'>
			<div class='eventleft'>
				 	<h1>Persönliche<strong>Einladung</strong></h1>

				 	<h2>$Title</h2>
				 	<h3>$SubTitle</h3>


				 	<div class='date'>$Datum1.GermanDate(l; d.m.Y) $TimeText</div>
				 	<div class='date'>$LocTitle, $LocStreet,<br> $LocZip $LocCity</div>
				 	<div class='typography space'>$InvitationText1</div>

				 	
				 	<div class='space'>Ihr persönlicher Anmeldecode lautet: <strong>$InvitationCode</strong></div>

					<div class='space'><strong>Bitte rasch anmelden:</strong> Bitte melden Sie sich noch heute mit Ihrem persönlichen Anmeldelink an:</div>

					<div class='space'>
										<a href='{$Link}codeentry' class='nicebutton'>Anmelden</a>
					</div>
			</div>
			<div class='eventright'>

					<% if  InvitationPictureID %>
						<% if InvitationPicture %>
							$InvitationPicture.SetWidth(430)
						<% end_if %>

					<% end_if %>

				 	<div class='typography'>$InvitationText2</div>


			</div>
	</div>
</div>
