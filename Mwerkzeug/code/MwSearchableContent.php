<?php 
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;





class MwSearchableContent extends DataObject
{
	
	private static $db=Array(
			'Text'        => 'Text',
			'Link'        => 'Varchar(120)',
			'MwLink'        => 'Varchar(120)',
		);

    private static $indexes = [
        'MwSearchableContentMwLinkIdx' => [
            'type' => 'unique',
            'columns' => ['MwLink'],
        ],
        'MwSearchableContentIdx'=> [
            'type' => 'fulltext',
            'columns' => ['Text'],
        ],
        'MwSearchableContentLinkIdx'=>['Link'],
    ];

	static function set4Object($content,$object)
  {

    if(is_subclass_of( $object, SiteTree::class))
    {
      $id=$object->ID;
      $so=DataObject::get_one('MwSearchableContent',"ID='$id'");

    }
    else
    {
      $mwlink=$object->MwLink;
      $so=DataObject::get_one('MwSearchableContent',"MwLink='$mwlink'");

    }

    if(!$so)
    {
      $so	= new MwSearchableContent();
      if($mwlink)
        $so->MwLink=$mwlink;
      else	    
        $so->ID=$id;	
    }

    $cc = $object->Title."\n\n";
    $cc .= strip_tags($content);

    $so->Text =$cc;
    $so->Link =$object->Link();
    $so->write();

    return $so;
  }
}









