<?php

use SilverStripe\Security\Permission;
use SilverStripe\Security\Member;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Session;
use SilverStripe\Security\Group;
use SilverStripe\Core\Convert;
use SilverStripe\Control\Email\Email;
    
class MwUserBEController extends BackendPageController
{

    private static $allowed_actions = [
        'listing',
        'profile',
        'index',
        'groups',
        'loginAs',
        'ehp',
        'groupehp',
    ];

    var $CurrentGroup;
    
    public function init()
    {
        parent::init();
        $this->includePartialBootstrap();
        $this->summitSetTemplateFile("Layout", "MwUserBE_Layout");
        if (!Permission::check("ADMIN")) {
            MwUtils::NiceDie('access denied');
        }
    }

    public function SubLayout()
    {
        return $this->renderWith('Layout/MwUserBE_'.$this->Action);
    }


    public function profile()
    {

      
        $c=array();
        return $c;
    }

    public function index(SilverStripe\Control\HTTPRequest $request)
    {
      
        return array();
    }
  
  
    public function loginAs()
    {
        if (!Permission::check("ADMIN")) {
            return "";
        }
      
        $this->record=DataObject::get_by_id(Member::class, $this->Url_ID);
      
      
        if ($this->record) {
            if (Mwerkzeug\MwSession::get('CurrentBEPortalID')) {
                Mwerkzeug\MwSession::set('CurrentBEPortalID', null);
            }

            Mwerkzeug\MwSession::set('SudoPreviousUser', Member::currentUser()->ID);
            $this->record->v3LogIn();

            echo $this->minimalPageHeader();
            echo "<div class='info'>
                you are now logged in as \"{$this->record->UsernameOrEmail}\"
                <div>&nbsp;</div>  
                to return to the User-Management just log out again.
                    <div>&nbsp;</div>  
                
                continue to
                
                <a href='/'>Frontend</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href='/BE/'>Backend</a>
                    
               </div>";
        }
    }
  
    function listing()
    {
        if ($this->Url_ID) {
            $this->CurrentGroup=DataObject::get_by_id(Group::class, $this->Url_ID);
        }
       
        EHP::includeRequirements();
        return array();
    }
  
    function groups()
    {

        EHP::includeRequirements();

        return array();
    }
  
  
    function UserGroups()
    {
  
        $res= DataObject::get(Group::class, '', 'Sort asc');

        return $res;
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
        return Member::class;
    }

    function ehp()
    {
            echo  $this->EHP->dispatch();
            exit();
    }

    public function EHP_getJoinArguments(&$options)
    {
        
        if ($groupid=$options['listparams']['GroupID']) {
            $joins=array();
            $joins[]=array(
                'innerJoin',
                'Group_Members',
                "Member.ID=MemberID",
            );
            return $joins;
        }
    }

  


    public function EHP_getFilterSQL(&$cond, &$filtervars, $options = null)
    {
        
           
        if ($groupid=$options['listparams']['GroupID']) {
            $cond['GroupID']="GroupID=".Convert::raw2sql($groupid);
        }


           
             
           // foreach ($filtervars as $key => $value) {
           //             if(preg_match('#^HasGot(.*)Mail$#',$key,$m))
           //             {
           //                 if($value=='SENT')
           //                     $cond[$key]=" $key = 1 ";
           //                 elseif($value=='OPENED')
           //                     $cond[$key]=" $key = 1  and {$m[1]}MailLastOpened is not NULL ";
           //                 else
           //                     unset($cond[$key]);
           //             }
           //         }
           //
    }


      
      
    public function EHP_initFormFields($record, &$formfields, $params)
    {
      
        $p=array();
        $p['fieldname']='Email';
        $p['label']='E-Mail';
        $p['styles']='width:185px';
        $p['bs_input_prepend']='<i class="icon-envelope"></i>';
        $formfields[$p['fieldname']]=$p;
        
        
        $p=array();
        $p['fieldname']='NewPassword';
        $p['label']='new Password';
        $p['rendertype']='bootstrap';
        $p['styles']='width:185px';
        $p['bs_input_prepend']='<i class="icon-lock"></i>';
        $formfields[$p['fieldname']]=$p;
        

        $p=array();
        $p['fieldname']='FirstName';
        $p['label']='FirstName';
        $formfields[$p['fieldname']]=$p;


        $p=array();
        $p['fieldname']='Username';
        $p['label']='Username';
        $formfields[$p['fieldname']]=$p;


        $p=array();
        $p['fieldname']='Surname';
        $p['label']='Lastname';
        $formfields[$p['fieldname']]=$p;
        

        
        
        
        $p=array();
        $p['fieldname']='GroupIDs';
        $p['label']='Groups';
        $p['type']='checkboxes';
        $p['rendertype']='bootstrap';
        $p['default_value']=$record->GroupIDs;
        if ($this->EHP->Options['listparams']['GroupID']) {
            $p['default_value'][]=$this->EHP->Options['listparams']['GroupID'];
        }
        
        $p['options']=DataObject::get(Group::class, '', 'Sort asc')->map()->toArray();
        $formfields[$p['fieldname']]=$p;
    }

    public function EHP_roweditTpl($record, $params)
    {

        $tpl= '
        <td colspan="11">
            <div class="formgroup">
                <!-- pre --> 
                <ul>
                    <li>
                          $FormField(Email).HTML.RAW
                    </li>
                    <li>
                        $FormField(NewPassword).HTML.RAW
                    </li>
                 </ul>
                 <ul>
                     <li>
                         $FormField(FirstName).HTML.RAW
                     </li>
                     <li>
                           $FormField(Surname).HTML.RAW
                     </li>
                  </ul>
                  
                  <ul>
                      <li>
                            $FormField(GroupIDs).HTML.RAW
                      </li>
                   </ul>
                 <!-- post --> 
            </div>
        </td>
        ';

        return $tpl;
    }


  
  
    function EHP_getJSONColumnDefinitions()
    {
            
        return "        
            'Email':             {'label':'Email','sortable':1,'filter':{type:'auto'}},
            'Groups':            {'label':'Groups','sortable':0},
            'NumVisit':          {'label':'# visits','sortable':1,'filter':{type:'auto'}},
            'LastVisited':       {'label':'last visit','sortable':1},
            'FirstName':         {'label':'FirstName','sortable':1,'filter':{type:'auto'},hide_on_load:1},
            'Surname':           {'label':'Surname','sortable':1,'filter':{type:'auto'},hide_on_load:1},
            'Username':           {'label':'Username','sortable':1,'filter':{type:'auto'},hide_on_load:1},
        ";
    }
  
    // public function EHP_rowTpl()
    // {
    

        
    //   // $groupid=$this->EHP->Options['listparams']['GroupID'] * 1;
        
    //   // return '
    //   //     <td>$Email</td>
    //   //     <td>
    //   //         <% loop Groups %>
    //   //             <% if ID = '.$groupid.'  %><% else %>
    //   //                 <span class="label">$Title</span>
    //   //             <% end_if %>
    //   //         <% end_control %>
    //   //     </td>
    //   //     <td>$NumVisit</td>
    //   //     <td>$LastVisited</td>
    //   // ';

    // }


    public function EHP_columnTemplates()
    {

        $groupid=intval($this->EHP->Options['listparams']['GroupID']);
        
        return array(
            'Groups' => '<% loop Groups %><% if ID = '.$groupid.'  %><% else %><span class="label">$Title</span><% end_if %><% end_loop %>',

        );
    }

  
    public function EHP_onBeforeWrite($record, $ehp)
    {
        //if( $ehp->recordIsNew)
        {
            //check email exists
            $email=Convert::raw2sql(trim($record->Email));
            $id=$record->ID*1;
            $existing=DataObject::get_one(Member::class, "Email='$email' and ID<>$id");
        if ($existing) {
            die('<div class="alert"><strong>Error!</strong> email-address \''.$email.'\' already in use.</div>');
        }
              
        }
    }
    

    public function EHP_onAfterWrite($record, $ehp)
    {
        if ($ehp->recordIsNew) {
            //set group ids on new records
            $record->GroupIDs=$ehp->fdata['GroupIDs'];
        }
    }
    
     

    public function EHP_rowButtons()
    {
         return implode("\n", array(
             $this->EHP->defaultButton('inlineedit'),
             $this->EHP->defaultButton('hide_unhide'),
             $this->EHP->defaultButton('delete'),
             "<a href='#' class='button loginas'>login as</a>",
         ));
    }



    public function EHP_multi_action($action, $itemids)
    {
        if (preg_match('#^send_([a-z_0-9]+)$#i', $action, $m)) {
            $mailtype=$m[1];
            $n=0;
            foreach ($itemids as $id) {
                $p=DataObject::get_by_id('Person', $id);
                if ($p->sendMail($mailtype)) {
                    $n++;
                    $msg.='<script>
                          $("#ehp").EHP("reloadItem",'.$id.');
                      </script>';
                }
            }

            $msg.="'$mailtype' was sent to $n persons.";
            return $msg;
        }
        return false;
    }
      // include ehp stuff ---------- END
 

      // include Groupehp stuff ---------- BEGIN

    public function getGROUP_EHP()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new EHP($this, 'GROUP_EHP');
        }
        return $this->cache[__FUNCTION__];
    }

    public function GROUP_EHP_getRecordClass()
    {
        return Group::class;
    }

    function groupehp()
    {
            echo  $this->GROUP_EHP->dispatch();
            exit();
    }
      
      
      
      
    public function GROUP_EHP_initFormFields($record, &$formfields, $params)
    {
      
        $p=array();
        $p['fieldname']='Email';
        $p['label']='E-Mail';
        $formfields[$p['fieldname']]=$p;

        $p=array();
        $p['fieldname']='FirstName';
        $p['label']='FirstName';
        $formfields[$p['fieldname']]=$p;

        $p=array();
        $p['fieldname']='Surname';
        $p['label']='Lastname';
        $formfields[$p['fieldname']]=$p;
    }

    public function GROUP_EHP_roweditTpl($record, $params)
    {
        return '
              <td colspan="11">
                  <div class="formgroup">
                      <ul>
                          <li>
                              $FormField(Title).HTML.RAW
                          </li>
                          <li>
                                $FormField(Code).HTML.RAW
                          </li>
                       </ul>
                      <ul>
                          <li>
                                $FormField(Description).HTML.RAW
                          </li>
                       </ul>
                  </div>
              </td>
              ';
    }


  
  
    public function GROUP_EHP_rowTpl()
    {
        return '
                <td>$Title.RAW</td>
                <td>$Code.RAW</td>
                <td>$Description.RAW</td>
            ';
    }
     

    public function GROUP_EHP_rowButtons($record = null)
    {
        // if ($record->Code=='administrators') {
        //       return "";
        // }
                
         return implode("\n", array($this->EHP->defaultButton('inlineedit'), $this->EHP->defaultButton('delete')));
    }



    public function GROUP_EHP_multi_action($action, $itemids)
    {
        if (preg_match('#^send_([a-z_0-9]+)$#i', $action, $m)) {
            $mailtype=$m[1];
            $n=0;
            foreach ($itemids as $id) {
                $p=DataObject::get_by_id('Person', $id);
                if ($p->sendMail($mailtype)) {
                    $n++;
                    $msg.='<script>
                                $("#ehp").EHP("reloadItem",'.$id.');
                            </script>';
                }
            }

            $msg.="'$mailtype' was sent to $n persons.";
            return $msg;
        }
        return false;
    }
            
            // include ehp stuff ---------- END
}
