<?php

use SilverStripe\Control\Email\Email;
use SilverStripe\View\Requirements;
use SilverStripe\Security\Permission;
use SilverStripe\ORM\DataObject;

class MwMailLog extends DataObject
{

    private static $db=array(
        'From'    => 'Varchar(255)',
        'To'      => 'Varchar(255)',
        'Subject' => 'Varchar(255)',
        'Headers' => 'Varchar(255)',
        'Body'    => 'Text',
  
    );
  
    public function getDatum()
    {
        return new Datum($this->Created);
    }
  
    public function getShortText()
    {
        return MwUtils::ShortenText(strip_tags($this->Body), 100);
    }
  
    public static function makeEmailFromArray($emails)
    {
        
        if (is_array($emails)) {
            $emails2=[];
            foreach ($emails as $email => $name) {
                $emails2[]="\"$name\" <$email>";
            }
            return implode(",", $emails2);
        } else {
            return $emails;
        }
    }
    static function add(Email $Email)
    {
      //logs email to mail-log database
      // use as:    $mail->send(); MwMailLog::add($mail); // log this mail

    
        if (!$Email || (new \ReflectionClass($Email))->getShortName()!='Email') {
            return;
        }
    
        $m=new MwMailLog();
        $m->From=self::makeEmailFromArray($Email->getFrom());
        $m->To=self::makeEmailFromArray($Email->getTo());
        $m->Subject=$Email->getSubject();
        $m->Body=$Email->getBody();
        $m->write();
    

    
    
        $url="http://".array_get($_SERVER, 'HTTP_HOST')."/BE/MailLog/detail/{$m->ID}";
        Requirements::customScript("if (window.console && console.info) { console.info('mail sent and logged to %o','$url');}");
    
        if (Permission::check('ADMIN')) {
            $mailinfo=$m->toMap();
            unset($mailinfo['Body']);
            Requirements::customScript("\$.ajax({'type':'POST','data':".json_encode($mailinfo).",'url':'$url','dataType':'html'})");
        }

        
     // if(array_get($_GET,'d') || 1 ) { $x=$m; $x=htmlspecialchars(print_r($x,1));echo "\n<li>ArrayList: <pre>$x</pre>"; }
    }
}


class MwMailLogController extends BackendPageController
{

    private static $allowed_actions = [
        'ehp',
        'detail',
        'previewMailTemplate',
        'sendmail',
    ];


    function index(SilverStripe\Control\HTTPRequest $request)
    {
      
        $this->includePartialBootstrap(array('scripts' => 'modal'));
      
        EHP::includeRequirements();

      
        return array();
    }

    function detail()
    {

        Requirements::clear();
        $mail=DataObject::get_by_id('MwMailLog', $this->urlParams['ID']);
        echo $mail->Body;
        die();
    }

    public function sendmail()
    {
        $recipient=array_get($_POST, 'email');
        $mailrec=DataObject::get_by_id('MwMailLog', $_POST['id']);
      
        $mail = Mwerkzeug\MwEmail::create($mailrec->From, $recipient, $mailrec->Subject, $mailrec->Body);
        $mail->setTemplate(null);
        $mail->send();
        if (array_get($_GET, 'd') || 1) {
            $x=$mail;
            $x=htmlspecialchars(print_r($x, 1));
            echo "\n<li>ArrayList: <pre>$x</pre>";
        }

      
      
        return "ok";
    }


  // include ehp stuff ---------- BEGIN

    public function getEHP()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new EHP($this);
        }
        return $this->cache[__FUNCTION__];
    }

    public function EHP_getRecordClass()
    {
        return 'MwMailLog';
    }

    function ehp()
    {
            echo  $this->EHP->dispatch();
            exit();
    }



    public function EHP_Items($options = null)
    {
              return DataObject::get($this->EHP->RecordClass)
                      ->where($this->EHP->getFilterSQL())
                      ->sort("Created desc");
    }
     
    // public function EHP_multi_action($action,$itemids)
    // {
    //     if(preg_match('#^send_([a-z_0-9]+)$#i',$action,$m))
    //     {
    //
    //         $mailtype=$m[1];
    //         $n=0;
    //         foreach ($itemids as $id) {
    //             $p=DataObject::get_by_id('Person',$id);
    //             if($p->sendMail($mailtype))
    //             {
    //                 $n++;
    //                 $msg.='<script>
    //                     $("#ehp").EHP("reloadItem",'.$id.');
    //                     </script>';
    //             }
    //         }
    //
    //         $msg.="'$mailtype' was sent to $n persons.";
    //         return $msg;
    //
    //
    //     }
    //     return FALSE;
    // }

   
  
    public function previewMailTemplate()
    {
       
        $data="<b>Lorem ipsum dolor sit amet, consetetur</b> 
       <div>&nbsp;</div>
       sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
       <div>&nbsp;</div>
       Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
       <div>&nbsp;</div>
       Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.";

        $tplname=null;
       
        if (strstr(array_get($_GET, 'tplname'), 'Mail')) {
            $tplname=array_get($_GET, 'tplname');
        }


        $html=MysiteMail::makeNiceHTML($data, $tplname);

        die($html);
    }
  
    public function EHP_rowTpl()
    {
        return '
        <td>$Datum.FormattedDate("d.m.Y H:i:s")</td><td>$From</td><td class="to">$To</td><td><a href="/BE/MailLog/detail/$ID"><% if Subject %>$Subject<% else %>--- EMPTY MAIL-SUBJECT ---<% end_if %>
</a></td><td>$ShortText</td>
      ';
    }


    public function EHP_roweditTpl()
    {
        return '
        <td>editDatum.FormattedDate("d.m.Y H:i:s")</td><td>$From</td><td>$To</td><td><a href="/BE/MailLog/detail/$ID">$Subject</a></td><td>$ShortText</td>
      ';
    }


    public function EHP_rowButtons()
    {
         return "<a class=\"btn btn-small resend-btn\" title=\"send to other e-mail-address\"><i class=\"icon-envelope\"></i></a>";
    }
    
    // include ehp stuff ---------- END
}
