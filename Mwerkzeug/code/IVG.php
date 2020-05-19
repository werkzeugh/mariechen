<?php
use SilverStripe\Assets\Image;
use SilverStripe\View\ViewableData;

//ArrayList 2011-12-18
// Image / Video / Gallery - Object

class IVG extends ViewableData
{

    var $data;
    public function __construct($data = null)
    {
        if (is_numeric($data)) {
            //if only a number id given, treat number as Image-ID
            $data=array(
                'Type'    => 'Image',
                'ImageID' => $data,
            );
        } elseif (strstr($data, '{')) {
            $this->data=json_decode($data);
        }
    }


    public function isValid()
    {
        if (!$this->data['Type']) {
            return false;
        }
        
        return true;
    }



    public function forTemplate()
    {

        if ($img=$this->Image()) {
            return $img->forTemplate();
        }
    }


    public function getOptionsForIVGField()
    {
        // returns array of options, to be used for IVGField


        $o=array();
        $o['availableTypes']=array(
            'image',
            'video',
            'gallery',
        );

        return $o;
    }
}
