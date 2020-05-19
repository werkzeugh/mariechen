<?php

/**
* 
* PHP Button Image Pluggable Set 
*
* Copyright (C) 2006 Matsuda Shota
* http://sgssweb.com/
* admin@sgssweb.com
*
* ------------------------------------------------------------------------
*
*/



require_once 'package.fig.php';



class ButtonImagePluggableSet
	extends GMIPluggableSet
{
	var $defaultVariables = array(
		"text" => "",
		"width" => 50,
		"bgcolor" => 0x333333,
		"rightImage" => "{image: '../lib/img/ButtonRightInactive.png'}",
		"fillImage" => "{image: '../lib/img/ButtonFillInactive.png'}",
		"leftImage" => "{image: '../lib/img/ButtonLeftInactive.png'}"
	);
	
	function ButtonImagePluggableSet() {
		parent::GMIPluggableSet();
	}
	
	function getExpression() {
		return "autoresize both;".
			   "padding 0,10;".
			   "color {bgcolor};".
			   "fill;".
			   "image {leftImage},0,0;".
			   "translate {leftImage.width},0;".
			   "patternrect {fillImage},0,0,{width},20;".
			   "image {rightImage},{width},0;".
			   "font '../lib/font/ttf/MyriadSemibold.ttf',8.5,8.5;".
			   "color 0,0,0,50; string {text},0,5,{width},center;".
			   "color 0xffffff;".
			   "string {text},0,4,{width},center;";
	}
	
	function getVariables() {
		return array_merge($this->defaultVariables, $_GET);
	}
}



// remove slashes inserted by "magic quotes"
if (get_magic_quotes_gpc()) {
	$_GET = array_map("strip_text_slashes", $_GET);
	$_POST = array_map("strip_text_slashes", $_POST);
	$_COOKIE = array_map("strip_text_slashes", $_COOKIE);
}
function strip_text_slashes($arg) {
	if(!is_array($arg)) {
		$arg = stripslashes($arg);
	}
	else if (is_array($arg)) {
		$arg = array_map("strip_text_slashes", $arg);
	}
	return $arg;
}



$pluggableSet = new ButtonImagePluggableSet();
$fig = new FontImageGenerator();
$fig->setPluggableSet($pluggableSet);
$fig->execute();

?>
