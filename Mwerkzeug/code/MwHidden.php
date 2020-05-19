<?php 
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDatetime;


class MwHidden extends DataExtension {


    private static $db =[
        'Hidden'    => DBBoolean::class,
        'HideOn'    => DBDatetime::class,
        'PublishOn' => DBDatetime::class,
        'Archived'    => DBBoolean::class,
        'ArchiveOn'   => DBDatetime::class,
    ];

        function  checkHiddenTimer()
        {
            static $alreadyRunning;
            if($alreadyRunning)
                return false;

            $alreadyRunning=1;
            
            $now=date('Y-m-d H:i:s');
        
            if ($this->owner->HideOn) {
                if(strstr($this->owner->HideOn,'1970'))
                {
                    $this->owner->HideOn=NULL;
                    $this->owner->write();
                    return; 
                }
                elseif($this->owner->HideOn<$now)
                    $queue[$this->owner->HideOn]='HideOn';
            }
          
            if ($this->owner->PublishOn) {
                if(strstr($this->owner->PublishOn,'1970'))
                {
                    $this->owner->PublishOn=NULL;
                    $this->owner->write();
                }
                elseif($this->owner->PublishOn<$now)
                    $queue[$this->owner->PublishOn]='PublishOn';
            }
            

            
            if($queue)
            {
                ksort($queue);
                foreach ($queue as $ts => $action) {
                    switch ($action) {
                        case 'HideOn':
                        $this->owner->Hidden=1;
                        $this->owner->HideOn=NULL;
                        break;
                        case 'PublishOn':
                        $this->owner->Hidden=0;
                        $this->owner->PublishOn=NULL;
                        break;
                    }
                }
                //save values
                $this->owner->write();
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
          


	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if(!$this->owner->ID && !$this->owner->isChanged('Hidden'))
		{
			//new records get hidden by default
			$this->owner->Hidden=1;
		}
        if($this->owner->HideOn=='1970-01-01 00:00:00')
        {
            if(strstr($this->owner->HideOn,'1970'))
            {
             $this->owner->HideOn=NULL;
             $this->owner->write();
             return;
             }
            $this->owner->HideOn=NULL;
        }
        if($this->owner->PublishOn=='1970-01-01 00:00:00')
        {
            if(strstr($this->owner->PublishOn,'1970'))
            {
             $this->owner->PublishOn=NULL;
             $this->owner->write();
             return;
             }
            $this->owner->PublishOn=NULL;
        }
        if($this->owner->ArchiveOn=='1970-01-01 00:00:00')
        {
            if(strstr($this->owner->ArchiveOn,'1970'))
            {
             $this->owner->ArchiveOn=NULL;
             $this->owner->write();
             return;
             }
            $this->owner->ArchiveOn=NULL;
        }
        
        
	}
    


	
	public function UnHiddenChildren($ClassFilterRegex="none",$showItemsNotInMenu=false)
	{
        
		// taken from public function Children() {
        $cacheKey="$ClassFilterRegex_$showItemsNotInMenu";

		if(!(isset($this->owner->cache['children'][$cacheKey]) && $this->owner->cache['children'][$cacheKey])) { 
			$result = $this->owner->stageChildren($showItemsNotInMenu); 
		 	if(isset($result)) { 
		 		$this->owner->cache['children'][$cacheKey] = new ArrayList(); 
		 		foreach($result as $child) { 
		 			if(/*$child->canView()  &&*/ !$child->Hidden) {
                        if($ClassFilterRegex!="none") {
                            if(!preg_match("#$ClassFilterRegex#",$child->ClassName))
                                continue;
                        }
                        if(is_object($this->owner->cache['children'][$cacheKey])) {
                            $this->owner->cache['children'][$cacheKey]->push($child); 
                        }
		 			}
		 		} 
		 	} 
		} 
		return $this->owner->cache['children'][$cacheKey];
	}



}

