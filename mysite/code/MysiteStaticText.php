<?php

class MysiteStaticEntity extends MwStaticEntity
{

}

class MysiteStaticText extends MwStaticText
{

}

class MysiteStaticTextController extends MwStaticTextController
{


    public function getVisibleScopes()
    {
        $arr=Array();
        $arr['mysite/de']=1;
        $arr['default/de']=1;
        return $arr;
    }


}
