<?php


class MwPage {


  static $conf=Array( //defaults:
    'JsTreeWidth'=>'300',
    );  
  
  static public function conf($key)
  {
    return self::$conf[$key];
  }

  static public function setConf($key,$value)
  {
    self::$conf[$key]=$value;
  }
  
}

?>
