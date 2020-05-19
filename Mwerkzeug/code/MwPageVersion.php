<?php

use SilverStripe\Versioned\DataDifferencer;
use SilverStripe\Security\Member;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ViewableData;
    
    
class  MwPageVersion extends ViewableData
{
    
    var $version;
    var $versionbefore;
    var $cache;
    
    public function __construct($v,$before_v)
    {
        $this->version=$v;
        $this->versionbefore=$before_v;
        $this->failover=$this->version;
    }
    
    
  
    public function Datum($value='')
    {
        return new Datum($this->LastEdited);
    }
    
    public function Diff()
    {
        if($this->versionbefore)
        {
            $diff=new DataDifferencer( $this->versionbefore,$this->version);
            $diff->ignoreFields('AuthorID', 'Status',"LastEdited");
            
            return $diff;
        }
    }
    
	function Publisher() {
			
		return DataObject::get_by_id(Member::class, $this->record['PublisherID']);
	}
    
    
        static public function compareJSON($from,$to)
        {

            $len1=strlen($from);
            $len2=strlen($to);
            
            return "content-size changed: $len1 ⇢ $len2";
        }
    
    
}