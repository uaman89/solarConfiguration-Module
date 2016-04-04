<?
/**
* share.backend.php
* script for all actions with dynamic pages
* @package Pages Package of SEOCMS
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 24.02.2011
* @copyright (c) 2010+ by SEOTM
*/
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] );
include_once(SITE_PATH.'/modules/mod_share/share.defines.php');

if(!defined("_LANG_ID")) {$pg = new PageAdmin();}

if( !isset( $_REQUEST['module'] ) ) $module = NULL;
else $module = $_REQUEST['module'];

//Blocking to execute a script from outside (not Admin-part)
if ( !$pg->logon->isAccessToScript($module)) exit;


if( !isset( $_REQUEST['task'] ) ) $task = 'show';
else $task = $_REQUEST['task'];

if( isset($_REQUEST['item_img']) and ( !empty($_REQUEST['item_img'])) ) {
    $task='delitemimg';
    $item_img = $_REQUEST['item_img'];
}
else $item_img = NULL;

if( !isset( $_REQUEST['display'] ) ) $display = 20;
else $display = $_REQUEST['display'];

if( !isset( $_REQUEST['start'] ) ) $start = 0;
else $start = $_REQUEST['start'];

if( !isset( $_REQUEST['sort'] ) ) $sort = NULL;
else $sort = $_REQUEST['sort'];

if( !isset( $_REQUEST['UseEndDate'] ) ) $UseEndDate = 0;
else $UseEndDate = 1;

if( !isset( $_REQUEST['UseManufac'] ) ) $UseManufac = 0;
else $UseManufac = 1;

if( !isset( $_REQUEST['UseCateg'] ) ) $UseCateg = 0;
else $UseCateg = 1;

if( !isset( $_REQUEST['Active'] ) ) $Active = 0;
else $Active = 1;

if( !isset( $_REQUEST['ShareEnd'] ) ) $ShareEnd = NULL;
else $ShareEnd = $_REQUEST['ShareEnd'];

if( !isset( $_REQUEST['ShareBegin'] ) ) $ShareBegin = NULL;
else $ShareBegin = $_REQUEST['ShareBegin'];

if( !isset( $_REQUEST['manufacId'] ) ) $manufacId = NULL;
else $manufacId = $_REQUEST['manufacId'];

if( !isset( $_REQUEST['CategId'] ) ) $CategId = NULL;
else $CategId = $_REQUEST['CategId'];

if (!empty($CategId)) { // $ltr2=categ=51 => $flrt2=51
    $arr_fltr2_tmp = explode("=", $CategId);
    if (isset($arr_fltr2_tmp[1]))
        $CategId = $arr_fltr2_tmp[1];
}

if( !isset( $_REQUEST['fltr'] ) ) $fltr=NULL;
else $fltr = $_REQUEST['fltr'];

if( !isset( $_REQUEST['textarea_editor'] ) ) $textarea_editor=NULL;
else $textarea_editor = $_REQUEST['textarea_editor'];



if( !isset( $_REQUEST['id_categ'] ) ) $id_categ=NULL;
else $id_categ = $_REQUEST['id_categ'];

if( !isset( $_REQUEST['id'] ) ) $id=NULL;
else $id = $_REQUEST['id'];

if( !isset( $_REQUEST['title'] ) ) $title = NULL;
else $title = $_REQUEST['title'];

if( !isset( $_REQUEST['description'] ) ) $description = NULL;
else $description = $_REQUEST['description'];

if( !isset( $_REQUEST['body'] ) ) $body = NULL;
else $body = $_REQUEST['body'];

if( !isset( $_REQUEST['keywords'] ) ) $keywords = NULL;
else $keywords = $_REQUEST['keywords'];

if( !isset( $_REQUEST['name'] ) ) $name = NULL;
else $name = $_REQUEST['name'];

if( !isset( $_REQUEST['name_old'] ) ) $name_old = NULL;
else $name_old = $_REQUEST['name_old'];

if( isset( $_REQUEST['visible'] ) AND $_REQUEST['visible']=='visible' ) $visible = 1;
else $visible = 0;

if( isset( $_REQUEST['publish'] ) AND $_REQUEST['publish']=='publish' ) $publish = 1;
else $publish = 0;

if( !isset( $_REQUEST['descr'] ) ) $descr=NULL;
else $descr = $_REQUEST['descr'];

if( !isset( $_REQUEST['short'] ) ) $short = NULL;
else $short = $_REQUEST['short'];

if( !isset( $_REQUEST['id_del'] ) ) $id_del=NULL;
else $id_del = $_REQUEST['id_del'];

if( !isset( $_REQUEST['skidka'] ) ) $skidka=NULL;
else $skidka = $_REQUEST['skidka'];

if( !isset( $_REQUEST['level'] ) ) $level=0;
else $level = $_REQUEST['level'];

if( !isset( $_REQUEST['move'] ) ) $move=NULL;
else $move = $_REQUEST['move'];

if( !isset($_REQUEST['img']) ) $img=NULL;
else $img = $_REQUEST['img'];

if( !isset( $_REQUEST['replace_to'] ) ) $replace_to = NULL;
else $replace_to = $_REQUEST['replace_to'];

if( !isset( $_REQUEST['sel'] ) ) $sel = NULL;
else $sel = $_REQUEST['sel'];

if( isset( $_REQUEST['ctrlscript'] ) AND $_REQUEST['ctrlscript']=='ctrlscript' ) $ctrlscript = 1;
else $ctrlscript = 0;

if( isset( $_REQUEST['special_pos'] ) AND $_REQUEST['special_pos']=='special_pos' ) $special_pos = 1;
else $special_pos = 0;


if( !isset( $_REQUEST['textarea_editor'] ) ) $textarea_editor = NULL;
else $textarea_editor = $_REQUEST['textarea_editor'];

if( !isset( $_REQUEST['lang_id'] ) ) $lang_id = NULL;
else $lang_id = $_REQUEST['lang_id'];

if( !isset($_REQUEST['id_tag']) ) $id_tag=NULL;
else $id_tag = $_REQUEST['id_tag'];

if( !isset($_REQUEST['main_page']) ) $main_page=0;
else $main_page=1;

if( $task=='savereturn') {$task='save'; $action='return';}
else $action=NULL;
if( $task=='to_publish') {$task='save'; $publish=1;}

$ShareBackend = new ShareBackend($pg->logon->user_id, $module);
$ShareBackend->task = $task;
$ShareBackend->action = $action;
$ShareBackend->display = $display;
$ShareBackend->start = $start;
$ShareBackend->sort = $sort;
$ShareBackend->fltr = $fltr;
$ShareBackend->textarea_editor = $textarea_editor;

$ShareBackend->id = $id;
$ShareBackend->level = $level;
$ShareBackend->visible = $visible;
$ShareBackend->publish = $publish;
$ShareBackend->skidka = $skidka;
$ShareBackend->Active = $Active;

$ShareBackend->descr = $descr;
$ShareBackend->UseEndDate = $UseEndDate;
$ShareBackend->UseManufac = $UseManufac;
$ShareBackend->ShareEnd = $ShareEnd;
$ShareBackend->ShareBegin = $ShareBegin;
$ShareBackend->manufacId = $manufacId;
$ShareBackend->UseCateg = $UseCateg;
$ShareBackend->CategId = $CategId;

$ShareBackend->short = $short;
$ShareBackend->title = $title;
$ShareBackend->description = $description;
$ShareBackend->keywords = $keywords;
$ShareBackend->body = $body;
$ShareBackend->move = $move;
$ShareBackend->img = $img;
$ShareBackend->sel = $sel;
$ShareBackend->ctrlscript = $ctrlscript;
$ShareBackend->special_pos = $special_pos;
$ShareBackend->textarea_editor = $textarea_editor;
$ShareBackend->id_tag = $id_tag;
$ShareBackend->main_page = $main_page;
$ShareBackend->name = $ShareBackend->PrepareLink($name, $ShareBackend->ctrlscript);
$ShareBackend->name_old = $name_old;

//echo 'level = '.$level;
$ShareBackend->script_ajax = 'module='.$ShareBackend->module.'&display='.$ShareBackend->display.'&start='.$ShareBackend->start.'&sort='.$ShareBackend->sort.'&fltr='.$ShareBackend->fltr."&level=".$ShareBackend->level;
$ShareBackend->script = "index.php?".$ShareBackend->script_ajax;

//echo '<br>ShareBackend->task='.$ShareBackend->task;
switch($ShareBackend->task){
	case 'show':
        $ShareBackend->show();
		break;
    case 'edit':
        $ShareBackend->edit();
        break;
    case 'new':
        $ShareBackend->edit();
        break;
    case 'ajax_refresh_urlname':
        //phpinfo();
        $PageLayout = new ShareLayout();
        if( isset($ShareBackend->name[$ShareBackend->lang_id]) )$pname = $ShareBackend->name[$ShareBackend->lang_id];
        else $pname='';
        $ShareBackend->ShowURLName($PageLayout, $ShareBackend->ctrlscript, $pname);
        break;
    case 'ajax_refresh_editor':
        //phpinfo();
        $PageLayout = new ShareLayout();
        $ShareBackend->EditShareContentHtml($lang_id);
        break;
    case 'save':
        //phpinfo();
        if($ShareBackend->is_image==1){
            if ( $ShareBackend->SavePicture()!=NULL ){
              $ShareBackend->edit();
              return false;
            }
        }
       if($ShareBackend->UseEndDate==1 && strtotime($ShareBackend->ShareBegin)>=strtotime($ShareBackend->ShareEnd)){
           $ShareBackend->Err=$ShareBackend->multi['TXT_SHARE_BEGIN_END'];
           $ShareBackend->edit();
           return false;
       }
        if( empty($ShareBackend->name) ) {
            //generate translit for empty URL-name only if this is not main page of the site
            if($ShareBackend->main_page==0) $ShareBackend->name=$ShareBackend->GenerateTranslit($ShareBackend->level, $ShareBackend->id, $ShareBackend->descr);
        }


        if( $ShareBackend->CheckFields()==NULL ){
            $ShareBackend->save();
            $ShareBackend->UploadFile->SaveFiles($ShareBackend->id);
            $ShareBackend->UploadImages->SaveImages($ShareBackend->id);
            //$ShareBackend->UploadVideo->SaveVideos($ShareBackend->id)

            if( $ShareBackend->action=='return' ) $ShareBackend->edit();
            else $ShareBackend->show();
        }
        else{
            if( $ShareBackend->action=='return' ) $ShareBackend->edit();
            else $ShareBackend->show();
        }
		break;
	case 'delete':
        $del = $ShareBackend->delShares( $id_del );
        if ( $del == 0 ) $pg->Msg->show_msg('_ERROR_DELETE');
        //else echo "<script>window.alert('Deleted OK! ($del records)');</script>";
        //echo "<script>window.location.href='".$ShareBackend->script."';</script>";
        $ShareBackend->show();
	    break;
    case 'cancel':
        //echo "<script>window.location.href='".$ShareBackend->script."';</script>";
        $ShareBackend->show();
        break;
	case 'up':
        $ShareBackend->up(TblModShare, 'level', $ShareBackend->level);
        $ShareBackend->showHTML();
        //echo "<script>window.location.href='".$ShareBackend->script."';</script>";
        break;
    case 'down':
        $ShareBackend->down(TblModShare, 'level', $ShareBackend->level);
        $ShareBackend->showHTML();
        //echo "<script>window.location.href='".$ShareBackend->script."';</script>";
        break;
    case 'replace':
        //phpinfo();
        $ShareBackend->Form->Replace(TblModShare, 'move', $ShareBackend->id, $replace_to);
        $ShareBackend->showHTML();
        break;
    case 'delitemimg':
        if ( !$ShareBackend->DelItemImage($item_img)){
            $ShareBackend->Err = $ShareBackend->multi['MSG_IMAGE_NOT_DELETED']."<br>";
        }
        $ShareBackend->edit();
        //echo "<script>window.location.href='$sys_spr->script';</script>";
        break;
}
?>