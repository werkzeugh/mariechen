<?php
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDatetime;

// upgrade from old:
// update SiteTree_Live s1 set ConfigData = (select ConfigData from  _obsolete_FrontendPage_Live where ID=s1.ID);
// not enabled by default

class MwSiteTreeConfigExtension extends DataExtension
{
    private static $db = array(
        'ConfigData'           => 'Text'
      );

    //call from FrontendPage-class like this:

    // public function update($incoming)
    // {
    //     $this->MwSiteTreeConfigExtension_update($incoming);

    //     return parent::update($incoming);
    // }
    // public function __get($fieldname)
    // {
    //     $val=$this->MwSiteTreeConfigExtension__get($fieldname);
    //     if ($val=="__parent__") {
    //         return parent::__get($fieldname);
    //     } else {
    //         return $val;
    //     }
    // }




    public function MwSiteTreeConfigExtension_update($incoming)
    {

        //filter Config-values
        foreach ($incoming as $key => $value) {
            if (preg_match('#^Config_(.*)$#', $key, $m)) {
                $this->owner->setConfigField($m[1], $value);
                unset($incoming[$key]);
            }
        }
    }

    public function MwSiteTreeConfigExtension__get($fieldname)
    {
        if (strstr($fieldname, 'Config_') && preg_match('#^Config_(.*)$#', $fieldname, $m)) {
            $fname=$m[1];
            return $this->owner->getConfigField($fname);
        }

        return "__parent__";
    }

   
   

    public function getConfigField($name, $defaultValue = null)
    {
        if (!isset($this->owner->fields)) {
            $this->owner->initConfigFields();
        }

        return $this->owner->fields[$name];
    }

    public function initConfigFields()
    {
        $this->owner->fields=json_decode($this->owner->ConfigData, 1);
    }

    public function setConfigField($name, $value)
    {
        //add field & value to mainrecord->EVTData (does not save !)
        $data=json_decode($this->owner->ConfigData, 1);
        if (!is_array($data)) {
            $data=array();
        }
        $data[$name]=$value;

        $this->owner->ConfigData=json_encode($data);
    }
}
