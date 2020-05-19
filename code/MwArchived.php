<?php 
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDatetime;


class MwArchived extends DataExtension {


    private static $db = array(
						'Archived'    => DBBoolean::class,
						'ArchiveOn'   => DBDatetime::class,
			);


    	public function onBeforeWrite()
    	{
    		parent::onBeforeWrite();
            if($this->owner->ArchiveOn=='1970-01-01 00:00:00')
            {
                $this->owner->ArchiveOn=NULL;
            }
    	}
    


        function  checkArchivedTimer()
        {
            $now=date('Y-m-d H:i:s');
        
            if ($this->owner->ArchiveOn) {
                if(strstr($this->owner->ArchiveOn,'1970'))
                {
                 $this->owner->ArchiveOn=NULL;
                 $this->owner->write();
                }
                elseif($this->owner->ArchiveOn<$now)
                {
                    $this->owner->Archived=1;
                    $this->owner->ArchiveOn=NULL;
                    $this->owner->write();
                }
            }
            
        } 

}

