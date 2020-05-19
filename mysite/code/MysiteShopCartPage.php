<?php


use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Control\Controller;

class MysiteShopCartPage extends MwShopCartPage
{

}




class MysiteShopCartPageController extends MwShopCartPageController
{
    
    private static $allowed_actions = [
       'add_designed_shape',
       'step0'
         ];


    // public function index($request)
    // {
    //     $cart=$this->Shop->Cart;
    // }
    
    public function add_designed_shape($request)
    {

        ['did'=>$did, 'article'=> $article]=$_GET;

        if ($did && strstr($article, "^")) {
            $this->Shop->addToCart([0=>[
                'articleid'=>$article."^".$did,
                'amount'=>0,
            ]]);
        }


        return $this->redirect($this->CartUrl());
    }

    public function step0()
    {

        // return Controller::curr()->redirect($this->dataRecord->Link().'step1');
        
        if (Member::currentUser()) {
            return Controller::curr()->redirect($this->dataRecord->Link().'step1');
        }
        
        $this->FormHelper->init();
        
        
        return array();
    }
}

class MysiteShopCartPageBEController extends MwShopCartPageBEController
{




}
