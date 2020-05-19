<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\FieldType\DBDatetime;

class MwJob extends DataObject
{

    private static $db=array(
        'Controller'           => 'Varchar',
        'Method'               => 'Varchar',
        'ParametersSerialized' => 'Text',
        'RunType'              => "Enum('once,requeue','once')",
        'Status'               => "Enum('pending,running,cancelled,done,failed','pending')",
        'Started'              => DBDatetime::class,
        'Response'             => "Text",
    );
    
    private static $default_sort=" Created desc";
    
    public function getParameters()
    {
        return unserialize($this->ParametersSerialized);
    }

    public function setParameters($val)
    {
        $this->ParametersSerialized=serialize($val);
    }

    public function getRunTime()
    {
        $time1=Datum::get_unixtime($this->Started);
        $age=time()-$time1;
        return $age;
    }

    public function execute()
    {

        echo "\n<li> ".Date('d.m.Y H:i.s')." {$this->Controller} {$this->Method} ({$this->RunType})";


      //     echo "\n<li>mwuits: execute<pre>".htmlspecialchars(print_r($this,1))."</pre>";
        $this->Status='running';
        $this->Started=Datum::mysqlDate(time());
        $this->write();
        if ($this->Controller && $this->Method) {
            $ret=call_user_func_array(array($this->Controller, $this->Method), array($this->Parameters));
        }

        echo $ret;

        if (strstr($ret, 'OK')) {
            $this->Status='done';
        } else {
            $this->Status='failed';
            $this->Response=$ret;
        }
      
        $this->write();

        if ($this->RunType == 'requeue') {
            $this->requeue();
        }

        echo "<li>".Date('d.m.Y H:i.s')." new status of MwJob {$this->ID}: {$this->Status}";
    }
   
    public function requeue()
    {
        echo "\n .... requeuing MwJob {$this->ID}";

        $this->Status='pending';
        $this->Created=Datum::mysqlDate(time());
        $this->write();
    }

    public function timeout()
    {
        $this->Status='failed';
        if ($this->RunType == 'requeue') {
            $this->requeue();
        }
        $this->write();
    }
    
    public static function addJob($Controller, $Method, $p)
    {
        $job= new MwJob;
        $job->Status='pending';
        $job->Controller=$Controller;
        $job->Method=$Method;
        $job->setParameters($p);
     
        if ($setval=$p['RunType']) {
            if ($setval=='requeue') { //delete existing requeuing job for same controller/metho
                if ($existing_job=DataObject::get_one('MwJob', "Controller='$Controller' AND Method='$Method' ")) {
                    $existing_job->delete();
                }
            }
          
            $job->RunType=$setval;
        }
     
        $job->write();

        return $job;
    }
   
    public static function getNextRunningJob()
    {
        return DataObject::get_one('MwJob', "Status='running'", "", "Created desc", 1);
    }
    
    public static function hasRunningJob()
    {
        $rj=MwJob::getNextRunningJob();
        if ($rj->RunTime>120) {
            $rj->timeout();
            echo "\nJob {$rj->ID} timed out after {$rj->RunTime} seconds";
            return self::hasRunningJob();
        }

        return $rj;
    }

    public static function getNextJob()
    {
        return DataObject::get_one('MwJob', "Status='pending'", "", "Created asc", 1);
    }
}

class MwJobController extends Controller
{
  
    var $isCron=1;
  
    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        return array();
    }
   
    public function execute()
    {
     
        $id=Controller::curr()->urlParams['ID'];
        $job=DataObject::get_by_id('MwJob', $id);
        if ($job) {
            $job->execute();
        }
    }
   
    public function Items()
    {
        return DataObject::get('MwJob', "Status in ('pending','running')", "Created asc");
    }

    public function process()
    {
        $GLOBALS['called_via_web']=1;
        $CLIcontroller=new MwJob_Process;
        $CLIcontroller->index();
    }

    public function cronprocess()
    {
        set_time_limit(50);
        $GLOBALS['called_via_web']=1;
        $GLOBALS['called_via_cron']=1;
     
        $CLIcontroller=new MwJob_Process;
        $CLIcontroller->index();
    }
}



class MwJob_Process extends Controller
{
  
    function index(SilverStripe\Control\HTTPRequest $request)
    {
      
      
      
        if ($id=array_get($_GET, 'kill')) {
            $t=DataObject::get_by_id('MwJob', $id);
            
            if ($t && $t->Status=='running') {
                $t->Status='in_progress';
                $t->write();
                echo "<li>Job $id killed";
            } else {
                echo('cannot find task '.$id);
            }

            echo " <a href=\"?nope\">continue</a>";
            die();
        }
      
        while (memory_get_usage() < 30*1024*1024) {
            $rj=MwJob::hasRunningJob();
            if ($rj) {
                echo "\n<li>WARNING: MwJob {$rj->ID} already running since {$rj->RunTime} seconds <a href='?kill={$rj->ID}'>&raquo; kill</a>";
                if (!$GLOBALS['called_via_web']) {
                    sleep(1);
                    flush();
                } else {
                    die("wait for other job to die");
                }
            } else {
              //get next job from queue
                if ($job=MwJob::getNextJob()) {
                    $job->execute();
                    flush();
                } else {
                    echo ".";
                    if (!$GLOBALS['called_via_web']) {
                        sleep(2);
                    }
                    flush();
                    if ($GLOBALS['called_via_cron']) {
                        die("no jobs left to do, exit");
                    }
                }
            }

            if ($GLOBALS['called_via_web'] && !$GLOBALS['called_via_cron']) {
                die("<META HTTP-EQUIV=Refresh CONTENT=\"2\">");
            }
        }
        echo "\n finished loop, memory too high";
    }


    public function handleRequest(SilverStripe\Control\HTTPRequest  $request)
    {
        //fix handleRequests since silverstripe4

        $params = $request->latestParams();
        $actionUrl = implode("/", $params);
        $request->setUrl($actionUrl);

        return parent::handleRequest($request);
    }
}
