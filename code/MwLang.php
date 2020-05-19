<?php

use SilverStripe\Control\Director;

//i18n class for laravel-style language tables


class MwLang {


   static public function engine()
  {
    return singleton('MwLangEngine');
  }

  static public function get($messageKey)
  {
      return self::engine()->get($messageKey);
  }

  

}

class MwLangEngine {

  var $lang=NULL;
  var $trans=Array();

  public function __construct()
  {
    $this->lang=MwUtils::getCurrentLanguageFromLocale();
    //load language files:

  }

  public function get($messageKey)
  {
    $keyParts=explode('.',$messageKey);
    $lastPart=array_pop($keyParts);
    $messageDir=implode('.',$keyParts);

    $this->loadLanguageFileForKeyParts($messageDir);

    $ret=$this->trans[$messageDir][$lastPart];
    if($ret) {
      return $ret;
    } else {
      return "##{$messageKey}##";      
      
    }

  }

  public function loadLanguageFileForKeyParts($messageDir)
  {

    if(!isset($this->trans[$messageDir])) {
      $filename=Director::baseFolder()."/Mwerkzeug/lang/".$this->lang.'/'.str_replace('.','/',$messageDir).'.php';
      if (file_exists($filename)) {
        $this->trans[$messageDir]=include($filename);
        if(!is_array($this->trans[$messageDir])) {
          $this->trans[$messageDir]=array();
        }
      }
    }

  }

}
