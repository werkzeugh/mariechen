<?php

use SilverStripe\i18n\i18n;
use SilverStripe\Control\Director;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;



class MwUtils
{

  static public function getCurrentLanguageFromLocale()
  {

   $lang='en';
   if($locale=i18n::get_locale()){
    $lang=mb_substr($locale,0,2);
  }

  return $lang;

}


static public function minimalPageHeader($CSSclass='bootstrap')
{


        return '
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
                "http://www.w3.org/TR/html4/loose.dtd">
             <html><head>
                 <link href="https://fonts.googleapis.com/css?family=PT+Sans:400,700italic" rel="stylesheet" type="text/css">
                 <link href="https://fonts.googleapis.com/css?family=Ubuntu:400,700" rel="stylesheet" type="text/css">

                 <link rel="stylesheet" href="/Mwerkzeug/css/minimal.css" type="text/css" charset="utf-8">
                 <link rel="stylesheet" href="/Mwerkzeug/bootstrap/css/partial_bootstrap.css" type="text/css" charset="utf-8">
                 </head><body class="'.$CSSclass.'">
                 ';
               }

               public function NiceDie($txt)
               {

                die(self::minimalPageHeader()."<div class='info'>$txt</div>");
              }


              static function generateURLSegment($title){
                $trans=Array('ö'=>'oe','ä'=>'ae','ü'=>'ue','Ö'=>'OE','Ä'=>'AE','Ü'=>'UE','ß'=>'ss');
                $t = strtr($title, $trans);
                $t = mb_strtolower($t);
                $t = str_replace('&amp;','-',$t);
                $t = preg_replace('#[^A-Za-z0-9]+#','-',$t);
                $t = preg_replace('#-+#','-',$t);
                if(!$t) {
                  $t = "page-$this->ID";
                }
                return $t;
              }


              static function array_merge_recursive_distinct ( array &$array1, array &$array2 )
              {

                $merged = $array1;

                foreach ($array2 as $key=>&$value) {
                  if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]) ) {
                    $merged[$key] = self::array_merge_recursive_distinct($merged[$key], $value);
                  } else {
                    if($value==="__REMOVE_FROM_ARRAY__") {
                      unset($merged[$key]);
                    } else {
                      $merged[$key] = $value;
                    }
                  }
                }

                return $merged;
              }



              static public function jsonIsValid($jsonstr)
              {
                $data = json_decode($jsonstr,1);

                if ($data === null && trim($jsonstr) ) {
                  return FALSE;
                }

                return TRUE;

              }


              public function tidyJSON($value)
              {
                include_once(Director::baseFolder().'/Mwerkzeug/thirdparty/tidyjson/TidyJSON.php');
                return TidyJSON::tidy($value);
              }

  /**
   * Indents a flat JSON string to make it more human-readable.
   *
   * @param string $json The original JSON string to process.
   *
   * @return string Indented version of the original JSON string.
   */
/*
  static public function tidyJSON($json) {

    $json = str_replace(Array("\n","\r"),"",$json);

    $result = '';
    $pos = 0;
    $strLen = mb_strlen($json);
    $indentStr = ' ';
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;

    for($i = 0; $i <= $strLen; $i++) {

    // Grab the next character in the string
    $char = mb_substr($json, $i, 1);

    // Are we inside a quoted string?
    if($char == '"' && $prevChar != '\\') {
    $outOfQuotes = !$outOfQuotes;
    }
    // If this character is the end of an element,
    // output a new line and indent the next line
    else if(($char == '}' || $char == ']') && $outOfQuotes) {
    $result .= $newLine;
    $pos --;
    for ($j=0; $j<$pos; $j++) {
    $result .= $indentStr;
    }
    }
    // Add the character to the result string
    $result .= $char;

    // If the last character was the beginning of an element,
    // output a new line and indent the next line
    if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
    $result .= $newLine;
    if ($char == '{' || $char == '[') {
    $pos ++;
    }
    for ($j = 0; $j < $pos; $j++) {
    $result .= $indentStr;
    }
    }
    $prevChar = $char;
    }

    return $result;

  }*/



  static function convertDataList2ArrayList($dl)
  {

    $al=new ArrayList();
    foreach ($dl as $record) {
      $al->push($record);
    }
    return $al;

  }


  static function convertArray2ArrayList($arr)
  {

    $dos=new ArrayList();


    foreach ($arr as $key => $value) {

      $dos->push(self::convertArray2ArrayList2($value));

    }


    return $dos;

  }

  static function convertArray2ArrayList2($arr)
  {

    if(is_object($arr))
      return $arr;

    if(!is_array($arr))
      return new ArrayData(Array('Value'=>$arr));

    $a=array();
    foreach ($arr as $key => $value) {
      if (is_numeric($key)) {
        $key='item_'.$key;
      }

      if(is_array($value) && mb_stristr($key,'items'))
        $a[$key]=self::convertArray2ArrayList($value);
     elseif(is_array($value))
       $a[$key]=new ArrayData($value);
     else
       $a[$key]=$value;
   }
   return new ArrayData($a);

 }

 static public function ShortenText($text,$length=60)
 {
   $text=strip_tags($text);

   //no need to trim, already shorter than trim length
   if (mb_strlen($text) > $length-4) {

     //find last space within length
     $text2check=mb_substr($text, 0, $length);
     $max_last_space=0;
     foreach (array(' ','/','-') as $char) {
       $last_space = mb_strrpos($text2check, $char);
       if($last_space && $last_space > $max_last_space) {
         $max_last_space=$last_space;
       }
     }
     if(!$max_last_space) {
       $max_last_space=$length-1;
     }
     $text = mb_substr($text, 0, $max_last_space+1).'...';
   }
   return $text;

 }

 static function mb_substr_replace($output, $replace, $posOpen, $posClose) { 
        return mb_substr($output, 0, $posOpen).$replace.mb_substr($output, $posClose+1); 
 }

static function ShortenTextInTheMiddle($longString,$len=18)
{
  if(mb_strlen($longString)<=$len+1)
    return $longString;
  $separator = '...';
  $separatorlength = mb_strlen($separator) ;
  $maxlength = $len - $separatorlength;
  $start = $maxlength / 2 ;
  $trunc =  mb_strlen($longString) - $maxlength;
  return substr_replace($longString, $separator, $start, $trunc);
}

 // convertArray2ArrayList doppelt ... weg


static public function isValidEmail($email)
{
 return preg_match('#^[^@]+@[^@]+\.[^@]+$#',trim($email));
}



    /**
    * check if a data is serialized or not
    *
    * @param mixed $data    variable to check
    * @return boolean
    */
    function isSerialized($data){
      if (trim($data) == "") {
        return false;
      }
      if (preg_match("/^(i|s|a|o|d)(.*);/si",$data)) {
        return true;
      }
      return false;
    }


  }


  ?>
