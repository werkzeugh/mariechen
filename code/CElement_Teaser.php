<?php


class CElement_Teaser extends CElement
{
  public function init()
  {
    $this->CType='Teaser';
  }

  public function getTeaserPos()
  {
    return str_replace('Teasers', '', $this->Fieldname);
  }

  function getLink()
  {
    if($this->MwLink)
    {
      return MwLink::getObjectForMwLink($this->MwLink)->Link();
    }
    else 
    return "#";
    
  }
  

  public function CMSThumbnail()
  {
    if(!$this->PictureID)
      return NULL;

    $pic=$this->Picture;
    if(!$pic)
      return NULL;

    $img=$pic->Image();
    if(!$img)
      return NULL;

    $defaultConfig=Array();
    $defaultConfig['Function'] = 'SetWidth';
    $defaultConfig['Args']     = Array('150');
    $pageconfig=$this->Parent->getConfig("TeaserPos.{$this->TeaserPos}.TeaserSettings.CMSThumbnail");

    $config=MwUtils::array_merge_recursive_distinct($defaultConfig, $pageconfig);

    //call $img->$config['function'] ( $config['args'][0],$config['args'][1],... )

  

    if($config['Function'] && $config['Args'])
    {
       $im=call_user_func_array(array($img, $config['Function']), $config['Args'] );
       return $im;
    }
    else
      return NULL;

  }


}


