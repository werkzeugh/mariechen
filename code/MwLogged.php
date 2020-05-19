<?php 
use SilverStripe\Security\Member;
use SilverStripe\ORM\DataExtension;


class MwLogged extends DataExtension {


    private static $has_one = array(
        'CreatedBy' => Member::class,
        'LastEditedBy' => Member::class,
    );
       
 

    	public function onBeforeWrite()
    	{
    		parent::onBeforeWrite();
            $mid=Member::currentUserID();
            if($mid)
            {
                if(!$this->owner->ID || !$this->owner->CreatedByID)
                {
                    $this->owner->CreatedByID=$mid;
                }

                $this->owner->LastEditedByID=$mid;
            
            }

    	}




}

