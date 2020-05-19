<?php

use SilverStripe\View\ViewableData;

class Datum  extends ViewableData {

  var $ts;
  
  public function __construct($_date)
  {
    $this->ts=self::parseDate($_date);
  }
  

  public function forTemplate()
  {
    return $this->__toString();
  }

  public function isEmpty()
  {
    return ($this->ts<100);
  }
  
  
  public function __toString()
  {
    if($this->isEmpty())
     return "";
    else
     $ret=$this->MysqlDate;
     
    return trim(str_replace('00:00:00','',$ret));
  }



  static public function parseDate($_date)
  {
    if(preg_match('#^\d\d\d\d\d\d\d*$#',$_date))
      return $_date;
      
    if($_date=='heute') 
      $_date='today';
    if($_date=='morgen') 
      $_date='tomorrow';

    $ret=strtotime($_date);

    if(!$ret)
      return self::get_unixtime($_date);
    else
      return $ret;
  }
  
  public function getMysqlDate()
  {
      return $this->mysqlDate($this->ts);
  }

  public function getMysqlDay()
  {
      return self::mysqlDay($this->ts);
  }

  public function FormattedDate($format)
  {
    return $this->formatted_date($format,$this->ts);
  }
  
  public  function AgoText()
  {
      return $this->Ago($this->ts);
  }
  
  
  public  function TimeDiffText()
  {
      return $this->TimeDiff($this->ts);
  }
  

  public  function GermanDate($format)
  {
    $format=str_replace(';',',',$format);
    return $this->german_date($format,$this->ts);
  }

  public  function NextDay()
  {
    $newDay=new Datum(Date('Y-m-d',$this->ts+100000));
    return $newDay;
  }

  public  function PrevDay()
  {
    $newDay=new Datum(Date('Y-m-d',$this->ts-30000));
    return $newDay;
  }
  
  public  function Increment($seconds)
  {
    $this->ts+=$seconds;
  }
  
   static public function getDateRangeForKw($year,$kw)
    {
      $time = strtotime("4 January " . $year);
      if (date('w', $time) != 1)
        $time = strtotime("last Monday", $time);
      $montag = strtotime("+" . ($kw - 1) . " weeks", $time);

      $sonntag = $montag + 86400*6; // 6 Tage drauf: Sonntag
      //Dann bekommst du die passenden Daten mit date('d.m.Y', $montag) und date('d.m.Y', $sonntag).
  //    echo "\n<li>getRange for ($jahr,$woche) ".Date('d.m.Y',$montag).'-'.Date('d.m.Y',$sonntag);

      return array('start'=>$montag,'end'=>$sonntag);
    }

    static public function getKw($t)
    {

      $y = date('Y', $t);
      $w = date('w', $t);
      $z = date('z', $t);
      $wjan4 = (367+$w-$z)%7;
      $kw = (int)(1+($z-3+$wjan4)/7) - !$w + !$wjan4;
      if (!$kw)
      {
        $y--;
        $kw = 52 + (int)(1==$wjan4 ||
          (!$wjan4 && 29==date('t', mktime(0,0,0,2,1,$y))));
      }

    //  echo "\n<li>getKw for  <pre>".Date('d.m.Y',$t)."= $y / $kw </pre>";
      return array('year'=>$y,'week'=>$kw);
    }
  

  public  function Round($roundto="day")
  {
    
    if($roundto=="day")
      $this->ts=self::get_unixtime(Date("Y-m-d",$this->ts));
  }
  
  

  static function get_unixtime($_date)
  {

    //creates a unix timestamp from a date like "dd.mm.yyyy hh:mm"
    //creates a unix timestamp from a date like "yyyy-mm-dd hh:mm"
    //creates a unix timestamp from a date like "dd.mm."
    //OR creates a unix timestamp from a time like "hh:mm"

    if (!preg_match('/([0-9][0-9]?)\.([0-9][0-9]?)\.([0-9][0-9][0-9][0-9])( +([0-9][0-9]?):([0-9][0-9]?)(:([0-9][0-9]?))?)?/',$_date,$m))
    {
      preg_match('/([0-9][0-9][0-9][0-9])-([0-9][0-9]?)-([0-9][0-9]?)( +([0-9][0-9]?):([0-9][0-9]?)(:([0-9][0-9]?))?)?/',$_date,$m);

      list($m[1], $m[3]) = array($m[3], $m[1]);
    }

    if (!$m[3])
    {
       //try with time only
       preg_match('/([^h:.]+)[:.h]([^:.]+)(:(\d\d))?/',$_date,$m);

       if (!$m[2])
       {
           return NULL;
       }
       if($m[4])
        $sec=$m[4];
       else
        $sec=0;
       $unixtime=mktime(intval($m[1]),intval($m[2]),$sec,1,1,1970);
       return $unixtime;
    }
    
    $unixtime=mktime(intval($m[5]),intval($m[6]),intval($m[8]),intval($m[2]),intval($m[1]),intval($m[3]));
      return $unixtime;

  }

  static function toHour( $timeval)
  {

    list($h,$m,$s)=explode(':',$timeval);

    if($h<6)
      $h+=24;

    return $h+$m/60+$s/3600;
  }

  static  function mysqlDate($value)
  {
    return self::german_date('Y-m-d H:i:s',$value);
  }

  static  function mysqlDay($value)
  {
    return self::german_date('Y-m-d',$value);
  }

  static function toTime( $hour)
  {

     if($hour>=24)
        $hour-=24;

    $fullhour=floor($hour);
    $parthour=$hour-$fullhour;
    $minutes=60*$parthour;

    return sprintf("%02d:%02d",$fullhour,$minutes);
  }


  static function formatted_date($_format,$_timestamp=NULL)
    {

      if ($_timestamp)
      {
        if(!is_numeric($_timestamp))
          $_timestamp=self::get_unixtime($_timestamp);
        $ret=Date($_format,$_timestamp);
      }
      else
        $ret=Date($_format);
      return $ret;
    }


  static function german_date($_format,$_timestamp=NULL)
  {

    $_format = str_replace(';',',',$_format); //u can use ; instead of , in .ss-Templates now
    
    $ret = self::formatted_date($_format,$_timestamp);

    $trans = array(
      "Mon" => "Mo",
      "Tue" => "Di",
      "Wed" => "Mi",
      "Thu" => "Do",
      "Fri" => "Fr",
      "Sat" => "Sa",
      "Sun" => "So",
      "Monday" => "Montag",
      "Tuesday" => "Dienstag",
      "Wednesday" => "Mittwoch",
      "Thursday" => "Donnerstag",
      "Friday" => "Freitag",
      "Saturday" => "Samstag",
      "Sunday" => "Sonntag",
      "January" => "Jänner",
      "February" => "Februar",
      "March" => "März",
      "April" => "April",
      "May" => "Mai",
      "June" => "Juni",
      "July" => "Juli",
      "August" => "August",
      "September" => "September",
      "October" => "Oktober",
      "November" => "November",
      "December" => "Dezember",
      "Jan" => "Jan",
      "Feb" => "Feb",
      "Mar" => "März",
      "Apr" => "April",
      "May" => "Mai",
      "Jun" => "Juni",
      "Jul" => "Juli",
      "Aug" => "Aug",
      "Sep" => "Sep",
      "Oct" => "Okt",
      "Nov" => "Nov",
      "Dec" => "Dez"
      );

    $ret=strtr($ret,$trans);


    return $ret;
  }


  public function SecondsAgo()
  {
	 return time() - $this->ts;
  }

	/**
	 * Returns the number of seconds/minutes/hours/days or months since the timestamp
	 */
 static function Ago($_timestamp) {


   if ($_timestamp)
   {
     if(!is_numeric($_timestamp))
       $_timestamp=self::get_unixtime($_timestamp);
   }


   if(time() > $_timestamp) {
       return str_replace('{difference}',self::TimeDiff($_timestamp),
     _t(
       'Date.TIMEDIFFAGO',
       '{difference} ago'
       ) );
   } else {
     return str_replace('{difference}',self::TimeDiff($_timestamp),
     _t(
       'Date.TIMEDIFFAWAY',
       '{difference} away'
       ) );
   }
 }


	static function TimeDiff($_timestamp) {

			$ago = abs(time() - $_timestamp);

			if($ago < 60) {
				$span = $ago;
				return ($span != 1) ? "{$span} "._t("Date.SECS", " secs") : "{$span} "._t("Date.SEC", " sec");
			}
			if($ago < 3600) {
				$span = round($ago/60);
				return ($span != 1) ? "{$span} "._t("Date.MINS", " mins") : "{$span} "._t("Date.MIN", " min");
			}
			if($ago < 86400) {
				$span = round($ago/3600);
				return ($span != 1) ? "{$span} "._t("Date.HOURS", " hours") : "{$span} "._t("Date.HOUR", " hour");
			}
			if($ago < 86400*30) {
				$span = round($ago/86400);
				return ($span != 1) ? "{$span} "._t("Date.DAYS", " days") : "{$span} "._t("Date.DAY", " day");
			}
			if($ago < 86400*365) {
				$span = round($ago/86400/30);
				return ($span != 1) ? "{$span} "._t("Date.MONTHS", " months") : "{$span} "._t("Date.MONTH", " month");
			}
			if($ago > 86400*365) {
				$span = round($ago/86400/365);
				return ($span != 1) ? "{$span} "._t("Date.YEARS", " years") : "{$span} "._t("Date.YEAR", " year");
			}

	}


}

?>
