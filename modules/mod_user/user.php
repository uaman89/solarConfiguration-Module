<?php                                      /* user.php */
// ================================================================================================
// System : SEOCMS
// Module : user.php
// Version : 1.0.0
// Date : 10.01.2006
// Licensed To:
// Igor Trokhymchuk ihoru@mail.ru
// Andriy Lykhodid las_zt@mail.ru
//
// Purpose : script for all actions with External users on the front-end
//
// ================================================================================================
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] );
include_once( SITE_PATH.'/modules/mod_user/userShow.class.php' );
include_once( SITE_PATH.'/modules/mod_user/userBlogLayout.class.php' );


if(!defined("_LANG_ID")) {$pg = new PageUser();}

//------------------ Authorization settings -------------------------
 if (!isset($logon)) $logon = new  UserAuthorize();
//-------------------------------------------------------------------

$Msg = new ShowMsg();
$Msg->SetShowTable(TblModUserSprTxt);
$User = new UserShow($logon->session_id,$logon->user_id);
if(empty($Blog)) $Blog = Singleton::getInstance('userBlogLayout');
//$Blog = new userBlogLayout($logon->session_id,$logon->user_id);
if ( !isset($whattodo)) $whattodo = NULL;

//comments
if ( isset($_REQUEST['task']) ){
    if (   $_REQUEST['task']=='reg'
        OR $_REQUEST['task']=='save_reg_data'
        OR $_REQUEST['task']=='data'
        OR $_REQUEST['task']=='saveprofile'
        OR $_REQUEST['task']=='message'
        OR $_REQUEST['task']=='map'
        OR $_REQUEST['task']=='send'
        OR $_REQUEST['task']=='makelogon'
        OR $_REQUEST['task']=='not_activated'
        OR $_REQUEST['task']=='not_user'
        OR $_REQUEST['task']=='upgrade_account'
        OR $_REQUEST['task']=='upgrade_account_save'
        OR $_REQUEST['task']=='show_profile'
        OR $_REQUEST['task']=='forgotpass'
        OR $_REQUEST['task']=='send_pass'
        OR $_REQUEST['task']=='showprofile'
        OR $_REQUEST['task']=='editprofile'
        OR $_REQUEST['task']=='profile'
        OR $_REQUEST['task']=='update'
        OR $_REQUEST['task']=='up_img'
        OR $_REQUEST['task']=='down_img'
        OR $_REQUEST['task']=='del_img'
        OR $_REQUEST['task']=='edit_email_pass'
        OR $_REQUEST['task']=='set_new_email_pass'
        OR $_REQUEST['task']=='quick_search_form'
        OR $_REQUEST['task']=='quick_search'
        OR $_REQUEST['task']=='complex_search_form'
        OR $_REQUEST['task']=='make_complex_search'
        OR $_REQUEST['task']=='set_to_premium'
        OR $_REQUEST['task']=='show_send_letter_form'
        OR $_REQUEST['task']=='send_letter_to'
        OR $_REQUEST['task']=='send_kiss_to'
        OR $_REQUEST['task']=='show_send_photo_form'
        OR $_REQUEST['task']=='send_photo_to'
        OR $_REQUEST['task']=='show_box'
        OR $_REQUEST['task']=='show_box_letters'
        OR $_REQUEST['task']=='show_box_kisses'
        OR $_REQUEST['task']=='show_box_photos'
        OR $_REQUEST['task']=='show_box_letter_detail'
        OR $_REQUEST['task']=='show_box_kiss_detail'
        OR $_REQUEST['task']=='show_box_photo_detail'
        OR $_REQUEST['task']=='set_deleted_box_letter'
        OR $_REQUEST['task']=='set_deleted_box_letters_arr'
        OR $_REQUEST['task']=='show_my_favorites'
        OR $_REQUEST['task']=='add_my_favorite'
        OR $_REQUEST['task']=='del_my_favorite'
        OR $_REQUEST['task']=='show_letters_chat'
        OR $_REQUEST['task']=='login_checkup'
        OR $_REQUEST['task']=='addImage'
        OR $_REQUEST['task']=='deleteImage'
        OR $_REQUEST['task']=='userBlog'
        OR $_REQUEST['task']=='saveNewBlogRecord'
        OR $_REQUEST['task']=='userBlogEditRecord'
        OR $_REQUEST['task']=='userBlogAbout'
        OR $_REQUEST['task']=='userBlogShow'
        OR $_REQUEST['task']=='userBlogShowAll'
        OR $_REQUEST['task']=='userBlogEntry'
        OR $_REQUEST['task']=='showAllBlogs'
        OR $_REQUEST['task']=='showAllExperts'
        OR $_REQUEST['task']=='chek'
        OR $_REQUEST['task']=='comments'
        OR $_REQUEST['task']=='deleteBlog')
        $task = $_REQUEST['task'];
    else $task='makelogon';
}
//echo '<br>$task='.$task.' $_REQUEST[task]='.$_REQUEST['task'];
if (!isset($task) or $task==NULL) $task='makelogon';
if ( !empty($logon->Err) ) $task = 'makelogon';
//echo '<br>$task='.$task;
if ( !$logon->user_id
     AND $task!='reg'
     AND $task!='save_reg_data'
     AND $task!='data'
     AND $task!='map'
     AND $task!='forgotpass'
     AND $task!='send_pass'
     AND $task!='not_activated'
     AND $task!='not_user'
     AND $task!='show_profile'
     AND $task!='showprofile'
     AND $task!='editprofile'
     AND $task!='saveprofile'
     AND $task!='message'
     AND $task!='send'
     AND $task!='quick_search_form'
     AND $task!='quick_search'
     AND $task!='upgrade_account_save'
     AND $task!='login_checkup'
     AND $task!='addImage'
     AND $task!='deleteImage'
     AND $task!='userBlog'
     AND $task!='chek'
     AND $task!='saveNewBlogRecord'
     AND $task!='userBlogAbout'
     AND $task!='userBlogShowAll'
     AND $task!='userBlogShow'
     AND $task!='userBlogEditRecord'
     AND $task!='userBlogEntry'
     AND $task!='showAllBlogs'
     AND $task!='showAllExperts'
     AND $task!='deleteBlog'
        ) $task = 'makelogon';
//echo '<br>$task='.$task;

//echo '<br> whattodo='.$whattodo;
if ( $whattodo=='1' ) $task='reg';

if ( isset($_REQUEST['save_reg_data']) ) $task = 'save_reg_data';
if ( isset($_REQUEST['update']) ) $task = 'update';
if ( isset($_REQUEST['send_pass']) ) $task = 'send_pass';
if ( isset($_REQUEST['set_new_pass']) ) $task = 'set_new_pass';

if(!isset($_REQUEST['sort'])) $sort=NULL;
else $sort=$_REQUEST['sort'];

if(!isset($_REQUEST['start'])) $start=0;
else $start=$_REQUEST['start'];

if(!isset($_REQUEST['map_id'])) $map_id=0;
else $map_id=$_REQUEST['map_id'];

if(!isset($_REQUEST['display'])) $display=20;
else $display=$_REQUEST['display'];

if(!isset($_REQUEST['start'])) $Blog->start=0;
else $Blog->start = $Blog->Form->GetRequestNumData($_REQUEST['start']);

if(!isset($_REQUEST['display'])) $Blog->display=4;
else $Blog->display = $Blog->Form->GetRequestNumData($_REQUEST['display']);

if(!isset($_REQUEST['page'])) $Blog->page=1;
else $Blog->page = $Blog->Form->GetRequestTxtData($_REQUEST['page'], 1);
if($Blog->page>1) $Blog->start = ($Blog->page-1)*$Blog->display;
if($Blog->page=='all') {
    $Blog->start = 0;
    $Blog->display = 999999;
}

if(!isset($_REQUEST['login'])) $login=NULL;
else $login=$_REQUEST['login'];

if ( !isset($_REQUEST['alias'])) $alias = NULL;
else  $alias = $_REQUEST['alias'];

if( !isset($_REQUEST['id']) ) $id=NULL;
else $id = $_REQUEST['id'];

if ( !isset($_REQUEST['activate_user'])) $activate_user = NULL;
else  $activate_user = $_REQUEST['activate_user'];

if ( !isset($_REQUEST['name'])) $name = NULL;
else  $name = $_REQUEST['name'];

if ( !isset($_REQUEST['country'])) $country = NULL;
else  $country = $_REQUEST['country'];

if ( !isset($_REQUEST['adr'])) $adr = NULL;
else  $adr = $_REQUEST['adr'];

if ( !isset($_REQUEST['city'])) $city = NULL;
else  $city = $_REQUEST['city'];

if ( !isset($_REQUEST['phone'])) $phone = NULL;
else  $phone = $_REQUEST['phone'];

if ( !isset($_REQUEST['letter'])) $letter = NULL;
else  $letter = $_REQUEST['letter'];

if ( !isset($_REQUEST['phone_mob'])) $phone_mob = NULL;
else  $phone_mob = $_REQUEST['phone_mob'];

if ( !isset($_REQUEST['fax'])) $fax = NULL;
else  $fax = $_REQUEST['fax'];

if ( !isset($_REQUEST['www'])) $www = NULL;
else  $www = $_REQUEST['www'];

if ( !isset($_REQUEST['state'])) $state = "m";
else  $state = $_REQUEST['state'];

if ( isset($_REQUEST['subscr']) AND $_REQUEST['subscr']!='0') $subscr = 1;
else $subscr=0;

if ( !isset($_REQUEST['old_email'])) $old_email = NULL;
else  $old_email = $_REQUEST['old_email'];

if ( !isset($_REQUEST['email'])) $email = NULL;
else  $email = $_REQUEST['email'];

if ( !isset($_REQUEST['email2'])) $email2 = NULL;
else  $email2 = $_REQUEST['email2'];

if ( !isset($_REQUEST['oldpass'])) $oldpass = NULL;
else  $oldpass = $_REQUEST['oldpass'];

if ( !isset($_REQUEST['password'])) $password = NULL;
else  $password = $_REQUEST['password'];

if ( !isset($_REQUEST['password2'])) $password2 = NULL;
else  $password2 = $_REQUEST['password2'];

if(!isset($_REQUEST['check_up'])) $check_up = NULL;
else $check_up =$_REQUEST['check_up'];

if(!isset($_REQUEST['wichField'])) $wichField = NULL;
else $wichField =$_REQUEST['wichField'];

if(!isset($_REQUEST['val'])) $val = NULL;
else $val =$_REQUEST['val'];

if(!isset($_REQUEST['day'])) $day = NULL;
else $day =$_REQUEST['day'];

if(!isset($_REQUEST['month'])) $month = NULL;
else $month =$_REQUEST['month'];

if(!isset($_REQUEST['year'])) $year = NULL;
else $year =$_REQUEST['year'];

if(!isset($_REQUEST['bonuses'])) $bonuses = NULL;
else $bonuses =$_REQUEST['bonuses'];

if(!isset($_REQUEST['userImage'])) $userAvatar = NULL;
else $userAvatar =$_REQUEST['userImage'];

if(!isset($_REQUEST['headerBlog'])) $headerBlog = NULL;
else $headerBlog =$_REQUEST['headerBlog'];

if(!isset($_REQUEST['blogContent'])) $blogContent = NULL;
else $blogContent =$_REQUEST['blogContent'];

if(!isset($_REQUEST['recordId'])) $recordId = NULL;
else $recordId =$_REQUEST['recordId'];

if(!isset($_REQUEST['userId'])) $userId = NULL;
else $userId =$_REQUEST['userId'];

if(!isset($_REQUEST['aboutMe'])) $aboutMe = NULL;
else $aboutMe =$_REQUEST['aboutMe'];

if(!isset($_REQUEST['wichForm'])) $wichForm= NULL;
else $wichForm =$_REQUEST['wichForm'];

if(!isset($_REQUEST['expertImg'])) $expertImg= NULL;
else $expertImg =$_REQUEST['expertImg'];

if(!isset($_REQUEST['idOfDelete'])) $idOfDelete= NULL;
else $idOfDelete =$_REQUEST['idOfDelete'];

if(!isset($_REQUEST['surname'])) $surname= NULL;
else $surname =$_REQUEST['surname'];

if(!isset($_REQUEST['secondname'])) $secondname= NULL;
else $secondname =$_REQUEST['secondname'];

if(!isset($_REQUEST['pass'])) $pass= NULL;
else $pass =$_REQUEST['pass'];

if(!isset($_REQUEST['newpass1'])) $newpass1= NULL;
else $newpass1 =$_REQUEST['newpass1'];

if(!isset($_REQUEST['newpass2'])) $newpass2= NULL;
else $newpass2 =$_REQUEST['newpass2'];

if(!isset($_REQUEST['message'])) $message= NULL;
else $message =$_REQUEST['message'];

//var_dump($_REQUEST);die();
$User->surname=$surname;
$User->secondname=$secondname;
$User->pass=$pass;
$User->newpass1=$newpass1;
$User->newpass2=$newpass2;

$User->task = $task;
$User->val=$val;

$User->wichField=$wichField;
$User->sort = $sort;
$User->start = $start;
$User->display = $display;
$User->aboutMe=$aboutMe;
$Blog->headerBlog=$headerBlog;
$Blog->blogContent=$blogContent;
$Blog->recordId=$recordId;
$Blog->userId=$userId;
$Blog->idOfDelete=$idOfDelete;

$User->userAvatar = addslashes(strip_tags(trim($userAvatar)));
$User->expertImg=addslashes(strip_tags(trim($expertImg)));
$User->map_id =$map_id;
//echo '$User->map_id='.$User->map_id;
$User->id = $id;
$User->name=addslashes(strip_tags(trim($name)));
$User->message=addslashes(strip_tags(trim($message)));
$User->state=addslashes(strip_tags(trim($state)));
$User->country=addslashes(strip_tags(trim($country)));
$User->adr=addslashes(strip_tags(trim($adr)));

$User->year=addslashes(strip_tags(trim($year)));
$User->month=addslashes(strip_tags(trim($month)));
$User->day=addslashes(strip_tags(trim($day)));

$User->city=addslashes(strip_tags(trim($year.$month.$day)));
$User->phone=addslashes(strip_tags(trim($phone)));
$User->phone_mob=addslashes(strip_tags(trim($phone_mob)));
$User->fax=addslashes(strip_tags(trim($fax)));
$User->www=addslashes(strip_tags(trim($www)));
$User->subscr=addslashes(strip_tags(trim($subscr)));
$User->user_status=2;
$User->bonuses=addslashes(strip_tags(trim($bonuses)));
$User->old_email = $old_email;
$User->login = addslashes(strip_tags(trim($login)));
$User->email = addslashes(strip_tags(trim($email)));
$User->alias = addslashes(strip_tags(trim($alias)));
$Blog->letter=$letter;

$User->oldpass=$oldpass;
$User->password=$password;
$User->password2=$password2;
//echo "login=".$User->login." ".$User->password." ".$User->city." ".$User->email;
$User->whattodo=$whattodo;
//echo '<br>$referer_page='.$referer_page;
if (isset($_REQUEST['referer_page'])) $User->referer_page = $_REQUEST['referer_page'];
else {
    if(strstr($_SERVER['REQUEST_URI'], 'referer_page=')){
         $start = strpos($_SERVER['REQUEST_URI'], 'referer_page=');
         $User->referer_page = substr($_SERVER['REQUEST_URI'], ($start+strlen('referer_page=')) );
    }
    elseif( isset($_SERVER["HTTP_REFERER"])) $User->referer_page = $_SERVER["HTTP_REFERER"];
    else $User->referer_page = NULL;
}
$User->referer_page = str_replace('AND', '&', $User->referer_page);
//echo '<br/>$User->referer_page='.$User->referer_page;

if(isset($Page->Catalog)) $Catalog = &$Page->Catalog;
else $Catalog = new CatalogLayout();
$scriptact = $_SERVER['PHP_SELF'];
$User->script = $_SERVER['PHP_SELF']."?task=$User->task&amp;display=$User->display&amp;start=$User->start&amp;sort=$User->sort";
//echo '<br>$User->script='.$User->script;

//echo '<br> $User->task='.$User->task.' $login='.$login;die;

$ModulesPlug = new ModulesPlug();
$id_module = $ModulesPlug->GetModuleIdByPath( '/modules/mod_user/user.backend.php' );
$Blog->module = $id_module;
//echo '<br> $User->task='.$User->task;
//var_dump($User->task);die();
//idc=111111&ids=3&sensor=&stat=0&azim=90&tilt=45&long=45.1237&lat=33.5&temp=25&wind=5|0&snow=0&irrd=500&time=010215|1705&inst=010215&num=500&cs=84
switch( $User->task ) {
    case "deleteBlog":
        $Blog->deleteBlog();
        break;
    case "showAllExperts":
         $Blog->showAllUsers(true);
        break;
    case "showAllBlogs":
        $Blog->showAllUsers();
        break;
    case "userBlogEntry":
        $Blog->ShowCurrentArticle();
        break;
    case "userBlogAbout":
        $Blog->ShowUserInfo();
        break;
    case "userBlogShowAll":
            $Blog->showUserBlogRecords();
        break;
    case "userBlogEditRecord":
            $Blog->addNewRecordShowRedactor();
        break;
    case "saveNewBlogRecord":
            $Blog->saveNewBlogRecord();
        break;
    case "userBlog":
        //echo $logon->user_id;
        if ( $logon->session_id==NULL OR empty($logon->login) ) {
            echo "<script language='JavaScript'>window.location.href='$scriptact';</script>";
        }else{
            $Blog->addNewRecordShowRedactor();
        }
        break;
    case "deleteImage":
        if(is_file($_SERVER["DOCUMENT_ROOT"].$_REQUEST['fileDel'])) unlink($_SERVER["DOCUMENT_ROOT"].$_REQUEST['fileDel']);
                echo '<script type="text/javascript">';
                    echo 'window.parent.del('.$wichForm.');';
                echo '</script>';
        break;
    case "addImage":
        $err="";
        $data="";
        $file="";
            if( isset($_FILES["image"]["name"]) AND count($_FILES["image"]["name"])>0 ) {
                    if( $Catalog->CheckImages(true)==NULL){
                        $folder = $_SERVER["DOCUMENT_ROOT"]."/uploads/tmpAva/";
                        $ext = substr($_FILES['image']['name'],1 + strrpos($_FILES['image']['name'], "."));
                        $file=time().".".$ext;
                        if(is_uploaded_file($_FILES['image']['tmp_name'])){
                         if(move_uploaded_file($_FILES['image']['tmp_name'],$folder.$file)){
                             $data=$_SERVER["DOCUMENT_ROOT"]."/uploads/tmpAva/".$file;
                          }else $err.="Під час завантаження файлу виникла помилка";
                        }else $err.="Файл не завантажений. Спробуйте пізніше.";
                    }else $err.=$Catalog->Err;
            }
            echo $err;
            echo '<script type="text/javascript">';

                echo 'window.parent.response("'.$err.'","/uploads/tmpAva/'.$file.'","'.$file.'","'.$wichForm.'");';

            echo '</script>';
            if($err!="") exit;
             $dir = @opendir($_SERVER["DOCUMENT_ROOT"]."/uploads/tmpAva/");
              while (($file = readdir($dir)) !== false) {
               // текущее время
                   if ($file != "." && $file != "..") {
                    $time_sec=time();
                    // время изменения файла
                    $time_file=filemtime($_SERVER["DOCUMENT_ROOT"]."/uploads/tmpAva/".$file);
                    // тепрь узнаем сколько прошло времени (в секундах)
                    $time=$time_sec-$time_file;
                    if($time>900) unlink($_SERVER["DOCUMENT_ROOT"]."/uploads/tmpAva/".$file);
                   }
              }
        break;

    case 'makelogon':

        if($logon->user_id){
            echo "Авторизация прошла успешно!!!";
            ?><script>setTimeout("window.location.href = '<?php  echo _LINK?>//myaccount/';", 1500);</script><?php
        }else{
//            $User->BadPass();
            echo "Неправильная пара логин пароль!!!";
        }

//        $User->Err = $logon->Err;
//        $User->LoginPage();
        break;
    case 'reg':
        if($logon->user_id)
        {
        ?>
        <h1 class="bgrnd"><?php  echo $User->Msg->show_text('TXT_TITLE_REGISTRATION_PAGE');?></h1>
        <div class="body">
            <?php echo "<script>window.location.href='"._LINK."myaccount/';</script>";?>
         <?php $User->Form->ShowTextMessages($User->Msg->show_text('MSG_USER_IS_LOGGED_ON'));?>
         <div align="center"><a href="<?php  echo _LINK;?>myaccount/" class="ch1"><?php  echo $logon->multi['_TXT_YOUR_PROFILE'];?></a></div>
        </div>
        <?php
        }
        else{
            $User->showRegJS();
            $User->ShowRegForm();
        }
        break;
    case 'login_checkup':
        //phpinfo();
        if( empty($User->email) ){
            ?><span style="font-size:9px; color:red;"><?php  echo $User->Msg->show_text('_EMPTY_LOGIN_FIELD', TblSysMsg);?></span><?php
            return false;
        }
        if( $User->old_email!=$User->email OR $check_up ){
            if( !$User->unique_login($User->email) ) {
                ?><span style="font-size:9px; color:red;"><?php  echo $User->Msg->show_text('MSG_LOGIN_EXIST', TblSysMsg);?></span><?php
            }
            else {?><span style="font-size:9px; color:green;"><?php  echo $User->Msg->show_text('MSG_LOGIN_FREE', TblSysMsg);?></span><?php }
        }
        break;
    case 'save_reg_data':
        if($logon->user_id)
        {
        ?>
        <div class="body">
         <?php  echo "<script>window.location.href='"._LINK."';</script>";?>

        </div>
        <?php
        return false;
        }
        if ( $User->CheckFields()!=NULL ) {
            $User->ShowRegForm();
            return false;
        }
        $User->user_id=$User->SaveToSysUser();
        if ( $User->user_id ){

            $res = $User->SaveUser();
            if($res){
            //--- if change status for subsription to News START ---
                $News = new NewsLayout();
                $News->subscriber = $User->email;
                $News->subscr_pass = '';
                $News->categories = 'all';
                $News->SubscrSave();
            //--- if change status for subsription to News END ---
            if($User->userAvatar!=null)
                $resImg=$User->saveUserAvatar();

            $err="";
           if(!empty($resImg)){
               $err.=$resImg."<br/>";
           }
           if(!empty($resImgExpert)){
               $err.=$resImgExpert;
           }
           if(!empty($err)) echo "<script>$.fancybox('".$err."');</script>";
           if(empty($resImg)){
            if($User->user_status==2) $User->SetActivateCode($User->login);
            //$User->user_id=$User->SaveToSysUser();
            //setcookie('lang_pg',$_GET['lang_pg'],time()+60*60*24*30, '/');
            //if ( $User->user_id ) $UserStat->UpdateStat($new_stat_id, $User->user_id);
            $res = $User->SendHTML();
            $User->ShowRegFinish($res);
            $logon->user_valid( $User->login, $User->password, 1 );
            //-------- For internet-shop ------------

               echo "<script>setTimeout(function(){ window.location.href='"._LINK."';}, 8000) </script>";
           }else echo "<script>$.fancybox('".$resImg."');</script>";
        }else echo "Помилка реєстрації";
        }
        else {
           // echo '<br/>Error registration';
        }
        break;
    case 'not_activated':
        $User->ShowTextMessages($Msg->show_text('MSG_YOU_ARE_NOT_ACTIVATED'));
        break;
    case 'not_user':
        $User->ShowTextMessages($Msg->show_text('MSG_YOU_ARE_NOT_USER'));
        break;
    /*---------------- Forgot Password ------------------*/
    case 'forgotpass':
        $User->ForgotPass();
        break;
    case 'send_pass':
        if ( $User->CheckEmailFields('forgotpass')!=NULL ) {
            $User->ForgotPass();
            return false;
        }
        if ( $User->unique_email($User->email) ){
            // if it is needed to encode password then set new password, else just get current password
            if ( $User->IsEncodePass($User->email) ) $User->SetNewPass($User->email);
            else $User->password = $User->GetUserPassword($User->email);
            if ( !$User->SendNewPass() ){
               $User->Err=$Msg->show_text('MSG_NEW_PASS_NOT_SENT');
               $User->LoginPage();
               return false;
            }
        }else{
            $User->Err="Користувач з такою електронною поштою не зареєстрованый.";
            $User->LoginPage();
            return false;
        }
        $User->TextMessages=$Msg->show_text('MSG_NEW_PASS_SENT_OK');
        $User->LoginPage();
        break;
    /*---------------- Profile --------------------*/
    case 'profile':
        if ( $logon->session_id==NULL OR empty($logon->login) )
            echo "<script language='JavaScript'>window.location.href='$scriptact';</script>";
        //echo 'EditProfile';
        $User->id = $User->GetUserIdByEmail($logon->login);
        $User->Show_JS();
        $User->EditProfile();
        break;
    case 'showprofile':
        if ( $logon->session_id==NULL OR empty($logon->login) )
            echo "<script language='JavaScript'>window.location.href='$scriptact';</script>";
        $User->id = $User->GetUserIdByEmail($logon->login);
        $User->ShowProfile();
        break;
    case 'comments':
        if ( $logon->session_id==NULL OR empty($logon->login) )
            echo "<script language='JavaScript'>window.location.href='$scriptact';</script>";
        //echo 'EditProfile';
        $User->id = $User->GetUserIdByEmail($logon->login);
        $User->Show_JS();
        $User->ShowCommentsBlock();
        break;


    case 'update':
        if ( $logon->session_id==NULL OR empty($logon->login) ) echo "<script language='JavaScript'>window.location.href='$scriptact';</script>";
        // check fields in editing.
        // give parameter $logon->user_id - it means editing of the profile, not creating new one.
        $User->user_id = $logon->user_id;
        if ( $User->SaveUser() ) {
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
            //--- if change status for subsription to News END ---
            if($User->userAvatar!=null)
                $resImg=$User->saveUserAvatar();
            if($User->expertImg!=null)
                $resImgExpert=$User->saveUserAvatarExpert();
            $err="";
           if(!empty($resImg)){
               $err.=$resImg."<br/>";
           }
           if(!empty($resImgExpert)){
               $err.=$resImgExpert;
           }
           if(!empty($err)) echo "<script>$.fancybox('".$err."');</script>";
            $User->TextMessages=$Msg->show_text('MSG_PROFILE_UPDATE_OK');
            $User->EditProfile();
        }
        break;
   /*-------------- Change Password -----------------*/
  case 'edit_email_pass':
        if ( $logon->session_id==NULL OR empty($logon->login) )
            echo "<script language='JavaScript'>window.location.href='$scriptact';</script>";
        $User->ShowChangeEmailPass();
        break;

  case 'set_new_email_pass':

        if ( $logon->session_id==NULL OR empty($logon->login) )
            echo "<script language='JavaScript'>window.location.href='$scriptact';</script>";

        if ( !$User->CheckPassword($logon->login, $User->oldpass) ) {
            $User->Err = $User->Err.$User->Msg->show_text('MSG_INCORRECT_OLD_PASSWORD').'<br>';
            $User->ShowChangeEmailPass();
            return false;
        }
        if ( $User->CheckEmailFields()!=NULL ) {
            $User->ShowChangeEmailPass();
            return false;
        }
        if (!empty($User->password) and !empty($User->password2)){
            if ( $User->CheckPassFields($logon->login)!=NULL ) {
                $User->ShowChangeEmailPass();
                return false;
            }
        }

        if( $User->email!=$logon->login ){
            if ( $User->ChangeLogin($logon->login, $User->email)) {
                //$User->change_email($logon->user_id, $User->id_user, $User->email) ) {
                $logon->login = $User->email;
                //$User->ControlUserWithERP($logon->user_id, $logon->user_type);
                $User->TextMessages=$User->TextMessages.$Msg->show_text('MSG_EMAIL_CHANGE_OK').'<br>';
            }
            else $User->TextMessages=$User->TextMessages.$Msg->show_text('MSG_EMAIL_NOT_CHANGE').'<br>';
        }
        // change password for user with new login $User->email
        if (!empty($User->password) and !empty($User->password2)){
            if ( $User->change_pass($logon->login, $User->password) )
                $User->TextMessages=$User->TextMessages.$Msg->show_text('MSG_PASSWORD_CHANGE_OK').'<br>';
            else
                $User->TextMessages=$User->TextMessages.$Msg->show_text('MSG_PASSWORD_NOT_CHANGE').'<br>';
        }
        //$logon->logout();

        if (empty($User->password)) $password=$User->oldpass;
        else $password=$User->password;
        //echo '<br>$logon->login='.$logon->login;
        $logon->user_valid($User->email, $password);
        //echo '<br>$User->$Logon->login='.$User->$Logon->login;
        echo "<script language='JavaScript'>window.location.href='"._LINK."myaccount/';</script>";
        //$User->id = $User->id_user;
        //$User->EditProfile();
        break;
    /*---------------- Show another Profile --------------------*/
    case 'chek':
        $User->checkAjaxFields();
      break;
    case 'show_profile':
        //echo '<br>$logon->session_id='.$logon->session_id.' $logon->login='.$logon->login;
        //if ( $logon->session_id==NULL OR empty($logon->login) ) echo "<script language='JavaScript'>window.location.href='$scriptact';</script>";
        if($User->profile==$User->id_user) $User->ShowMyProfile();
        else $User->ShowProfile();
        break;
    case 'data':
        $User->ShowData();
        break;
    case 'map':
        $User->ShowMap();
        break;
    case 'editprofile':
        $User->EditProfile();
        break;
    case 'saveprofile':
//        var_dump($User);
        if($User->CheckPass()){
            $User->SaveUserFromCabinet();
            $User->ShowProfile();
        }else{
            echo '<span class="red">Не правильно введен пароль!</span>';
            $User->EditProfile();
        }
        break;
    case 'message':
        $User->ShowFeedMessage();
        break;
    case 'send':

        if( !$User->SendMail() ) {
            $User->send_result = "<br /><center><h2 class='err'>Cообщение не отправлено!</h2></center>";
        }
        else $User->send_result = "<br /><center><h2>Сообщение отправлено!</h2></center>";
        $User->ShowFeedMessage();
        break;
    default:
        $User->LoginPage();
        break;
}

?>
