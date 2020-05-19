<?php 
use SilverStripe\Control\Director;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ViewableData;



class MwGeo extends ViewableData 
{
	
    static $conf;


    static public function setConf($key,$value)
    {
      self::$conf[$key]=$value;
    }


    static public function conf($key)
    {
      return self::$conf[$key];
    }

  static public function reverseGeocode($lat,$lng)
    {
        $coords=Array('lat' => $lat,'lng' => $lng);

        if($coords['lat'])
        {
          
          $url="http://maps.google.com/maps/geo?output=xml&oe=utf-8&ll={$coords[lat]},{$coords[lng]}&key=".self::getGoogleApiKey();
          $locresponse=file_get_contents($url);
          
          include_once(Director::baseFolder().'/Mwerkzeug/thirdparty/phpQuery/phpQuery/phpQuery.php');
          $doc=phpQuery::newDocumentXML($locresponse);  
          phpQuery::selectDocument($doc);

          foreach(pq('address') as $a) {
            $adress = pq($a)->text();
            if(trim($adress))
            { 
              $locname=$adress;
              break;
            }
          }
        }

        if(trim($locname))
          return $locname;
    }
  
  static public function geocode($adress)
  {

  	$url="http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($adress)."&sensor=false";

    // echo "<li>checkloc $url";
  	$locresponse=file_get_contents($url);
  	if($locresponse)
  	{
  		$data=json_decode($locresponse,true);

      if($geo=$data['results'][0]['geometry']['location'])
      {
          return $geo;                      
      }
  		
  	}

    return Array();
       
  }

   static function getGoogleApiKey($srv=NULL)
    {
      if(!$srv)
        $srv=array_get($_SERVER,'HTTP_HOST');
      
      if(!($res=self::conf('GmapApiV3Key')))
      {
          $keys['content.freshconcepts.at.dev']='ABQIAAAArrdl9GIJG270_9_GDZAmFBQWBzy_a-a_aQSsUmvjqGyV19KJnxTsOI-TlfyEYIMF3qXZIt251jXKNA';
          $keys['content.freshconcepts.at']='ABQIAAAArrdl9GIJG270_9_GDZAmFBSQMW1bad9NPdbf59HxI5AkEZBn5hQ4WRcpOUFFdePp5cGT8Z949TeQBA';
          $keys['www.nachz.at.dev']='ABQIAAAArrdl9GIJG270_9_GDZAmFBR3smWK1ePa0EphLiHTQjFp2GhBGhSjo9eq9ia6OgVNdQ3_8YmPuBoUnQ';
          $keys['www.nachz.at']='ABQIAAAArrdl9GIJG270_9_GDZAmFBTkBpH4B3_ibpvPfk1eOJcRBEy8hBREj4c-A97-nOHby65Ippz9F3hcAQ';
          $keys['clusterwien.at.dev']='ABQIAAAArrdl9GIJG270_9_GDZAmFBRUGJQYDWloWV5wQXPM9nLzPIqQghQtP_5p8wf6osUxK7_anKmZYT-q_w';
          $keys['clusterwien.at']='ABQIAAAArrdl9GIJG270_9_GDZAmFBTl44ChmQSNoxZ2oCjwH52QAr1bLhSq0weRq52hoJA_tyVtqI5ePsv6JA';
          $res=$keys[$srv];
      }
     
      return $res;
    }

    static function getStaticMap($locs,$w=200,$h=200,$p=Array()) {

        $key=self::getGoogleApiKey();
      
          if(is_array($locations) || get_class($locations)==ArrayList::class )
          {
            $locations=$locs;
          }
          else
          {
            $locations=Array($locs);
          }

          foreach ($locations as $loc) {
            if($loc->Lat && $loc->Lng)
            {
                $markers[]="{$loc->Lat},{$loc->Lng}";
            }
          }


          if($markers)
          {
              if(is_numeric($p['zoom']))
                  $addon.="&zoom={$p['zoom']}";
            
                $url="http://maps.googleapis.com/maps/api/staticmap?size={$w}x{$h}&maptype=roadmap{$addon}&sensor=false&markers=".implode('%7C',$markers);
          $html="
            <img src=$url class='gmapimg'>
            ";
           }
          return $html;
            
        }


}





 ?>
