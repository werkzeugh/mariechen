<?php

use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\DataObject;

class MysiteFileExtension extends DataExtension
{
    private static $db =array(
        'Hidden' => DBBoolean::class,
        'Sort'=>'Int',
     );

    public function getSortedChildren()
    {
        $where = "ParentID={$this->owner->ID} and Deleted=0 ";
        $files = DataObject::get('MwFile', $where, "Sort asc");
        return $files;
    }
}
