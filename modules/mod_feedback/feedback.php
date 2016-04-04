<?php
/**
* feedback.php
* script for all actions with feddback
* @package Feedback Package of SEOCMS
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 22.12.2010
* @copyright (c) 2010+ by SEOTM
*/
$ajax = false;
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] ); 
include_once( SITE_PATH.'/include/defines.php' );

$FeedbackLayout = &check_init('FeedbackLayout', 'FeedbackLayout');
$Page->breadcrumb = '<div class="path">
        <a href="/">Главная</a> • Контакты
        </div>';

if( !isset ( $_REQUEST['task'] ) ) $FeedbackLayout->task = NULL;
else $FeedbackLayout->task = $FeedbackLayout->Form->GetRequestTxtData($_REQUEST['task'], 1);


  
if(!isset($_REQUEST['name'])) $FeedbackLayout->name = NULL;
else $FeedbackLayout->name = $FeedbackLayout->Form->GetRequestTxtData($_REQUEST['name'], 1);

if(!isset($_REQUEST['fax'])) $FeedbackLayout->fax = NULL;
else $FeedbackLayout->fax = $FeedbackLayout->Form->GetRequestTxtData($_REQUEST['fax'], 1);

if(!isset($_REQUEST['company'])) $FeedbackLayout->company = NULL;
else $FeedbackLayout->company = $FeedbackLayout->Form->GetRequestTxtData($_REQUEST['company'], 1);

if(!isset($_REQUEST['tel'])) $FeedbackLayout->tel = NULL;
else $FeedbackLayout->tel = $FeedbackLayout->Form->GetRequestTxtData($_REQUEST['tel'], 1);

if(!isset($_REQUEST['e_mail'])) $FeedbackLayout->e_mail = NULL;
else $FeedbackLayout->e_mail = $FeedbackLayout->Form->GetRequestTxtData($_REQUEST['e_mail'], 1);
                                                                               
if(!isset($_REQUEST['question'])) $FeedbackLayout->question = NULL;
else $FeedbackLayout->question = $FeedbackLayout->Form->GetRequestTxtData($_REQUEST['question'], 1);

if(!isset($_REQUEST['to_addr'])) $FeedbackLayout->to_addr = NULL;
else $FeedbackLayout->to_addr = $FeedbackLayout->Form->GetRequestTxtData($_REQUEST['to_addr'], 1);

//=========== kcaptcha ==================
if ( !isset( $_REQUEST['captchacodestr'] ) ) $FeedbackLayout->captchacodestr = NULL;
else $FeedbackLayout->captchacodestr = $FeedbackLayout->Form->GetRequestTxtData($_REQUEST['captchacodestr'], 1);
//=======================================

//print_r($_COOKIE);

if(!isset($_COOKIE['refpage'])) $FeedbackLayout->refpage = NULL;
else $FeedbackLayout->refpage = $FeedbackLayout->Form->GetRequestTxtData($_COOKIE['refpage'], 1);

if(!isset($_COOKIE['serfing'])) $FeedbackLayout->cookie_serfing = NULL;
else $FeedbackLayout->cookie_serfing = $_COOKIE['serfing'];
/*?><h1><?=$FeedbackLayout->multi['_TXT_FORM_NAME'];?></h1><?*/

switch ($FeedbackLayout->task){
  case 'show_form':
   // $FeedbackLayout->show_form();
    break;
  case 'send':
    $err = NULL;
    if ($FeedbackLayout->CheckFields()!=NULL) {
        //$FeedbackLayout->show_form();
         echo $FeedbackLayout->CheckFields();
        break;    
    }
    /*if($FeedbackLayout->is_files==1){
        if( !isset($_FILES['filename']) ) $filename = NULL;
        else $filename = $_FILES['filename'];      
        if ( $filename!=null and $_FILES["filename"]["error"]==0) {
          $FeedbackLayout->fpath = $FeedbackLayout->Form->GetRequestTxtData($_FILES["filename"]["name"], 1);
          $tmp_f_name = $_FILES["filename"]["tmp_name"];
      
          $FeedbackLayout->uploaddir = SITE_PATH.FeedbackUploadFilesPath;
          if ( !file_exists ($FeedbackLayout->uploaddir) ) mkdir($FeedbackLayout->uploaddir,0777); 
          else @chmod($FeedbackLayout->uploaddir,0777);
          $uploaddir1 = $FeedbackLayout->uploaddir.$FeedbackLayout->fpath; 
          if ( !copy($tmp_f_name,$uploaddir1) ) {
              $FeedbackLayout->Err = $FeedbackLayout->Err.$FeedbackLayout->multi['MSG_ERR_FILE_MOVE'].'<br>';
              @chmod($FeedbackLayout->uploaddir,0755); 
              $FeedbackLayout->show_form(); 
              return false;
          }
          @chmod($FeedbackLayout->uploaddir,0755);
        }
        else $FeedbackLayout->fpath = NULL;
    }  */   

    if( !$FeedbackLayout->send_form() ) {
        echo $FeedbackLayout->multi['_TXT_NO_SENT'];
        //$FeedbackLayout->show_form();
    }
    else echo $FeedbackLayout->multi['_TXT_MESS_SENT'];
    break;
  default:
   echo '<div id="form-order">';
   if($ajax){
       echo "<span>Отправить заявку</span>";
   }else{
       echo '<div class="title contact-inside">Связаться с нами</div>';
   }
    $FeedbackLayout->show_form();
   echo '</div>';
    break;      
}
?>