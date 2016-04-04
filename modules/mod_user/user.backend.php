<?php
/**
* user.backend.php
* script for all actions with External Users
* @package User Package of SEOCMS
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 24.02.2011
* @copyright (c) 2010+ by SEOTM
*/
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] );
include_once( SITE_PATH.'/modules/mod_user/user.defines.php' );


if(!defined("_LANG_ID")){$pg = &check_init('PageAdmin', 'PageAdmin');}

if( !isset( $_REQUEST['module'] ) ) $module = NULL;
else $module = $_REQUEST['module'];

//Blocking to execute a script from outside (not Admin-part)
if ( !$pg->logon->isAccessToScript($module)) exit;

if( !isset($_REQUEST['task']) || empty($_REQUEST['task']) ) $task='show';
else $task=$_REQUEST['task'];

if( !isset( $_REQUEST['fln'] ) ) $fln = _LANG_ID;
else $fln = $_REQUEST['fln'];

if( !isset( $_REQUEST['fltr'] ) ) $fltr = NULL;
else $fltr = $_REQUEST['fltr'];

if( !isset( $_REQUEST['expertTitle'] ) ) $expertTitle = NULL;
else $expertTitle = $_REQUEST['expertTitle'];

if( !isset( $_REQUEST['ShowInTop'] ) ) $ShowInTop = 0;
else $ShowInTop = $_REQUEST['ShowInTop'];

if( !isset( $_REQUEST['fltr2'] ) ) $fltr2 = NULL;
else $fltr2 = $_REQUEST['fltr2'];

if( !isset( $_REQUEST['srch'] ) ) $srch = NULL;
else $srch = $_REQUEST['srch'];

if(!isset($_REQUEST['srch_dtfrom'])) $srch_dtfrom=NULL;
else $srch_dtfrom=$_REQUEST['srch_dtfrom'];

if(!isset($_REQUEST['srch_dtto'])) $srch_dtto=NULL;
else $srch_dtto=$_REQUEST['srch_dtto'];

if(!isset($_REQUEST['srch_country'])) $srch_country=NULL;
else $srch_country=$_REQUEST['srch_country'];

if(!isset($_REQUEST['srch_alias'])) $srch_alias=NULL;
else $srch_alias=$_REQUEST['srch_alias'];


if(!isset($_REQUEST['sort'])) $sort=NULL;
else $sort=$_REQUEST['sort'];

if(!isset($_REQUEST['start'])) $start=0;
else $start=$_REQUEST['start'];

if(!isset($_REQUEST['display']) || ( isset($srch) and empty($srch)) ) $display=20;
else $display=$_REQUEST['display'];


if( !isset($_REQUEST['id']) ) $id=NULL;
else $id = $_REQUEST['id'];

if(!isset($_REQUEST['login_multi_use'])) $login_multi_use = NULL;
else $login_multi_use =$_REQUEST['login_multi_use'];

if(!isset($_REQUEST['enrol_date'])) $enrol_date =NULL;
else $enrol_date =$_REQUEST['enrol_date'];

if( !isset($_REQUEST['user_status']) ) $user_status=NULL;
else $user_status = $_REQUEST['user_status'];

if( !isset($_REQUEST['old_user_status']) ) $old_user_status=NULL;
else $old_user_status = $_REQUEST['old_user_status'];

if( !isset($_REQUEST['group_id']) ) $group_id=NULL;
else $group_id = $_REQUEST['group_id'];

if( !isset($_REQUEST['old_group_id']) ) $old_group_id=NULL;
else $old_group_id = $_REQUEST['old_group_id'];

if( !isset($_REQUEST['id']) ) $id=NULL;
else $id = $_REQUEST['id'];

if(!isset($_REQUEST['login'])) $login=NULL;
else $login=$_REQUEST['login'];

if ( !isset($_REQUEST['old_login'])) $old_login = NULL;
else  $old_login = $_REQUEST['old_login'];

if ( !isset($_REQUEST['email'])) $email = NULL;
else  $email = $_REQUEST['email'];

if ( !isset($_REQUEST['alias'])) $alias = NULL;
else  $alias = $_REQUEST['alias'];


if ( !isset($_REQUEST['oldpass'])) $oldpass = NULL;
else  $oldpass = $_REQUEST['oldpass'];

if ( !isset($_REQUEST['password'])) $password = NULL;
else  $password = $_REQUEST['password'];

if ( !isset($_REQUEST['password2'])) $password2 = NULL;
else  $password2 = $_REQUEST['password2'];

if ( !isset($_REQUEST['name'])) $name = NULL;
else  $name = $_REQUEST['name'];

if ( !isset($_REQUEST['secondname'])) $secondname = NULL;
else  $secondname = $_REQUEST['secondname'];

if ( !isset($_REQUEST['surname'])) $surname = NULL;
else  $surname = $_REQUEST['surname'];

if ( !isset($_REQUEST['country'])) $country = NULL;
else  $country = $_REQUEST['country'];

if ( !isset($_REQUEST['adr'])) $adr = NULL;
else  $adr = $_REQUEST['adr'];

if ( !isset($_REQUEST['city'])) $city = NULL;
else  $city = $_REQUEST['city'];

if ( !isset($_REQUEST['phone'])) $phone = NULL;
else  $phone = $_REQUEST['phone'];

if ( !isset($_REQUEST['phone_mob'])) $phone_mob = NULL;
else  $phone_mob = $_REQUEST['phone_mob'];

if ( !isset($_REQUEST['fax'])) $fax = NULL;
else  $fax = $_REQUEST['fax'];

if ( !isset($_REQUEST['www'])) $www = NULL;
else  $www = $_REQUEST['www'];

if ( isset($_REQUEST['subscr']) AND $_REQUEST['subscr']!='0') $subscr = 1;
else $subscr=0;

if(!isset($_REQUEST['check_up'])) $check_up = NULL;
else $check_up =$_REQUEST['check_up'];

if ( !isset($_REQUEST['bonuses'])) $bonuses = NULL;
else  $bonuses = $_REQUEST['bonuses'];

if ( !isset($_REQUEST['discount'])) $discount = NULL;
else  $discount = $_REQUEST['discount'];

if ( !isset($_REQUEST['sys_user_id'])) $sys_user_id = NULL;
else  $sys_user_id = $_REQUEST['sys_user_id'];

if ( !isset($_REQUEST['state'])) $state  = NULL;
else  $state  = $_REQUEST['state'];
if ( !isset($_REQUEST['aboutMe'])) $aboutMe  = NULL;
else  $aboutMe = $_REQUEST['aboutMe'];
if(!isset($_REQUEST['userImage'])) $userAvatar = NULL;
else $userAvatar =$_REQUEST['userImage'];
if(!isset($_REQUEST['expertImg'])) $expertImg= NULL;
else $expertImg =$_REQUEST['expertImg'];
if(!isset($_REQUEST['expertImgHeader'])) $expertImgHeader=NULL;
else $expertImgHeader=$_REQUEST['expertImgHeader'];
if(!isset($_REQUEST['expertImg'])) $expertImg=NULL;
else $expertImg=$_REQUEST['expertImg'];

if( $task=='savereturn') {$task='save'; $action='return';}
else $action=NULL;

//$User = new UserCtrl($pg->logon->user_id, $module, 10, $sort, $start, '100%');
$User = check_init('UserCtrl', 'UserCtrl',"'".$pg->logon->user_id."','".$module."','10','".$sort."','".$start."','100%'");

$User->expertImgHeader=$expertImgHeader;
$User->expertImg=$expertImg;
$User->task = $task;
$User->display = $display;
$User->sort = $sort;
$User->start = $start;
$User->fln = $fln;
$User->ShowInTop = $ShowInTop;

$User->expertTitle = $expertTitle;
$User->srch = $srch;
$User->fltr = $fltr;
$User->fltr2 = $fltr2;
$User->srch_dtfrom = $srch_dtfrom;
$User->srch_dtto = $srch_dtto;
$User->srch_country = $srch_country;
$User->srch_alias = $srch_alias;
$User->state=$state;
$User->aboutMe=$aboutMe;
$User->id = $id;
$User->login_multi_use=$login_multi_use;
$User->enrol_date=strip_tags(trim($enrol_date));
$User->group_id = $group_id;
$User->old_group_id = $old_group_id;
$User->login = addslashes(strip_tags(trim($login)));
$User->old_login = strip_tags(trim($old_login));
$User->email = strip_tags(trim($email));
$User->email2=$User->email;
$User->email=strip_tags(trim($alias));
$User->userAvatar = addslashes(strip_tags(trim($userAvatar)));
$User->oldpass=$oldpass;
$User->password=$password;
$User->password2=$password2;

$User->name=strip_tags(trim($name));
$User->secondname=strip_tags(trim($secondname));
$User->surname=strip_tags(trim($surname));
$User->country=strip_tags(trim($country));
$User->adr=strip_tags(trim($adr));
$User->city=strip_tags(trim($city));
$User->phone=strip_tags(trim($phone));
$User->phone_mob=strip_tags(trim($phone_mob));
$User->fax=strip_tags(trim($fax));
$User->www=strip_tags(trim($www));
$User->subscr=strip_tags(trim($subscr));
$User->user_status=strip_tags(trim($user_status));
$User->old_user_status=strip_tags(trim($old_user_status));
$User->bonuses=strip_tags(trim($bonuses));
$User->discount=strip_tags(trim($discount));


$User->script_ajax = "module=$User->module&display=$User->display&start=$User->start&sort=$User->sort&id=$User->id&fln=$User->fln&fltr=$User->fltr&fltr2=$User->fltr2&srch=$User->srch&srch_dtfrom=$User->srch_dtfrom&srch_dtto=$User->srch_dtto&srch_alias=$User->srch_alias";
$User->script="index.php?".$User->script_ajax;
//echo $task;die();
//phpinfo();
switch( $task ) {
    case 'showblogs':
        $Blog->ShowContent();
        break;
    case 'show':
        $User->show();
        break;
    case 'edit':
        if (!$User->edit()) echo "<script>window.location.href='$User->script';</script>";
        break;
    case 'new':
        $User->edit();
        break;
    case 'save':
        if ( $User->CheckFields( $User->id )!=NULL ) {

          $User->edit();
          return false;
        }
        if (!empty($User->password) and !empty($User->password2)){

            if ( $User->CheckPassFields($User->login)!=NULL ) {

                $User->edit();
                return false;
            }
        }

        // save external user to external users
        $User->user_id=$id;
        if(empty($User->user_id)){

            $User->user_id=$User->SaveToSysUser($User->group_id);
            if ( !$User->user_id) $pg->Msg->show_msg('MSG_NOT_ADD_TO_SYSUSER',TblModUserSprTxt);
        }else{

             if ( !$User->user_id) $pg->Msg->show_msg('MSG_NOT_ADD_TO_SYSUSER',TblModUserSprTxt);
        }

        if ( $User->SaveUser() ){

            // save external user to system users (if add new user $User->id now is not equal to NULL, because it = new id of created user)
            if ($User->id==NULL) {

                //if ( !$User->SendProfile() ) $pg->Msg->show_msg('MSG_PROFILE_NOT_SENT',TblModUserSprTxt);
            }
            else {

                //Don't use here GetSysUserIdByUserId!!! use old_email because login can be changing!
                $res = $User->update_user($User->GetSysUserIdByUserLogin($User->old_email), $User->group_id, $User->email, $User->enrol_date, $User->login_multi_use);
                if(!$res) $pg->Msg->show_msg('MSG_NOT_UPD_TO_SYSUSER',TblModUserSprTxt);

                if ( !empty($User->group_id) ) {
                    $res = $User->SetUserGroup($User->id, $User->group_id);
                }

                // change password for user with new login $User->email
                if (!empty($User->password) and !empty($User->password2)){
                    if ( !$User->change_pass($User->login, $User->password) ) {
                        $User->TextMessage=$User->Err.$pg->Msg->show_text('MSG_PASSWORD_NOT_CHANGE').'<br>';
                        $User->edit();
                        return false;
                    }
                }

                //if change login
                if ( !empty($User->id) AND $User->old_email!=$User->email){
                    if ( !$User->ChangeUserLogin($User->old_email, $User->email) ){
                        $pg->Msg->show_msg('MSG_LOGIN_NOT_CHANGED');
                        $User->edit();
                        return false;
                    }
                }

                //if user status is activated then send email about this to the user.
                if( $User->user_status==3 AND $User->old_user_status!=$User->user_status ){
                    $User->SendHTMLActivation();
                }
                //is set status "Not User" the logout this user from the system.
                elseif($User->user_status==1){
                    $pg->logon->LogoutUserFromSystem($User->id);
                }
            }

            //--- if change status for subsription to News START ---
            if($User->subscr==1){
                $News = new NewsLayout();
                $News->subscriber = $User->email;
                $News->subscr_pass = '';
                $News->categories = 'all';
                $News->SubscrSave();
            }
            else{
                $News = new NewsLayout();
                $News->subscriber = $User->email;
                $News->SubscrDel();
            }
            $resImg="";$resImgExpert="";$resImgExpertHeader="";
          if($User->userAvatar!=null)
                $resImg=$User->saveUserAvatar();
          if($User->expertImg!=null)
                $resImgExpert=$User->saveUserAvatarExpert();
          if($User->expertImgHeader!=null)
                $resImgExpertHeader=$User->saveUserAvatarHeader();
            $err="";
           if(!empty($resImg)){
               $err.=$resImg."<br/>";
           }
           if(!empty($resImgExpert)){
               $err.=$resImgExpert."<br/>";
           }
           if(!empty($resImgExpertHeader)){
               $err.=$resImgExpertHeader;
           }
           if(!empty($err)) echo "<script>$.fancybox('".$err."');</script>";
            //--- if change status for subsription to News END ---

           if( $ShareBackend->action=='return' ) $User->edit();
           else echo "<script>window.location.href='$User->script';</script>";
        }
        break;
    case 'delete':
        if( !isset($_REQUEST['id_del']) ) $id_del=NULL;
        else $id_del = $_REQUEST['id_del'];
        if ( !empty($id_del) ) {
         $del=$User->del( $id_del );
         if ( $del > 0 ) echo "<script>window.alert('".$pg->Msg->get_msg('_SYS_DELETED_OK')." $del');</script>";
         else $pg->Msg->show_msg('_ERROR_DELETE');
        }
        else $pg->Msg->show_msg('_ERROR_SELECT_FOR_DEL');
        echo "<script>window.location.href='$User->script';</script>";
        break;
    case 'cancel':
        echo "<script>window.location.href='$User->script';</script>";
        break;


   /*-------------- Search of users start -----------------*/
   case 'complex_search_form':
        $User->ShowComplexSearchForm();
        break;
   case 'make_complex_search':
        if ( $User->CheckFieldsSearch()!=NULL ) $User->ShowComplexSearchForm();
        else $User->ShowSearchResult( $User->MakeComplexSearch() );
        break;
   /*-------------- Search of users end -----------------*/

    case 'login_checkup':
        //echo '<br>$make_check='.$make_check;
        if( empty($User->email) ){
            ?><span style="font-size:10px; color:red;"><?php  echo $User->Msg->show_text('_EMPTY_LOGIN_FIELD');?></span><?php
            return false;
        }
        //echo '<br>$User->old_login='.$User->old_login.' $User->login='.$User->login.' $check_up='.$check_up;
        if( $User->old_login!=$User->login OR $check_up ){
            if( !$User->unique_login($User->login) ) {
                ?><span style="font-size:10px; color:red;"><?php  echo $User->Msg->show_text('MSG_LOGIN_EXIST');?></span><?php
            }
            else {?><span style="font-size:10px; color:green;"><?php  echo $User->Msg->show_text('MSG_LOGIN_FREE');?></span><?php }
        }
        break;
    case 'show_dispatch_form':
        $User->ShowDispathForm();
        break;
}

?>