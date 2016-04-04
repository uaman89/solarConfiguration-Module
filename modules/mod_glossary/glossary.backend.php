<?
// ================================================================================================
//    System     : PrCSM05
//    Module     : Glossary
//    Version    : 1.0.0
//    Date       : 18.11.2005
//    Licensed To:
//                 Igor  Trokhymchuk  ihoru@mail.ru
//                 Andriy Lykhodid    las_zt@mail.ru
//
//    Purpose    : Class definition for Glossary
//
// ================================================================================================
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] );
include_once( SITE_PATH.'/modules/mod_glossary/glossary.defines.php' );


if( !isset( $_REQUEST['module'] ) ) $module = NULL;
else $module = $_REQUEST['module'];

//============================================================================================
// START
// Blocking to execute a script from outside (not Admin-part) 
//============================================================================================
$Msg = new ShowMsg();
$goto = "http://".NAME_SERVER."/admin/index.php?logout=1";
//echo '<br>$goto='.$goto;
if ( !isset($_SESSION[ 'session_id']) OR empty($_SESSION[ 'session_id']) OR empty( $module ) ) {
     //$Msg->show_msg( '_NOT_AUTH' );
     //return false;
     ?><script>window.location.href="<?=$goto?>";</script><?;
}

$logon = new  Authorization();
if (!$logon->LoginCheck()) {
    //return false;
    ?><script>window.location.href="<?=$goto?>";</script><?; 
}
//=============================================================================================
// END
//=============================================================================================

if( !isset( $_REQUEST['task'] ) ) $task = 'show';
else $task = $_REQUEST['task'];

if( !isset( $_REQUEST['start'] ) ) $start = 0;
else $start = $_REQUEST['start'];

if( !isset( $_REQUEST['display'] ) ) $display = 10;
else $display = $_REQUEST['display'];

if( !isset( $_REQUEST['sort'] ) ) $sort = NULL;
else $sort = $_REQUEST['sort'];

if( !isset( $_REQUEST['fltr'] ) ) $fltr=NULL;
else $fltr = $_REQUEST['fltr'];

if( !isset( $_REQUEST['srch'] ) ) $srch = NULL;

if( !isset($_REQUEST['id']) ) $id=NULL;
else $id = $_REQUEST['id'];

if( !isset( $_REQUEST['sel'] ) ) $sel = NULL;
else $sel = $_REQUEST['sel'];

 $m = new Glossary();
 $m->module = $module;
 $m->user_id = $logon->user_id;
 $m->task = $task;
 $m->display = $display;
 $m->start = $start;
 $m->sort = $sort;
 $m->fltr = $fltr;
 $m->srch = $srch;
 $m->sel = $sel;
 
 if( !isset( $_REQUEST['fln'] ) ) $m->fln = _LANG_ID;
 else $m->fln = $_REQUEST['fln'];

 $script = 'module='.$m->module.'&display='.$m->display.'&start='.$m->start.'&sort='.$m->sort.'&fltr='.$m->fltr;
 $script = $_SERVER['PHP_SELF']."?$script";


switch( $task ) {

    case 'show':      $m->show(); break;

    case 'new':       $m->edit( NULL, NULL ); break;

    case 'edit':      $m->edit( $_REQUEST['id'], NULL ); break;

    case 'save':
                      if ( $m->save( $id, $cod, $name, $description ) )
                      {
                        echo "<script>window.location.href='$script';</script>";
                      }
                      break;

    case 'delete':
                      if( !isset($_REQUEST['id_del']) ) $id_del=NULL;
                      else $id_del = $_REQUEST['id_del'];
                      if ( !empty($id_del) ) {                       
                        $del = $m->del( $id_del );
                        if ( $del > 0 ) echo "<script>window.alert('".$Msg->get_msg('_SYS_DELETED_OK')." $del');</script>";
                         else $Msg->show_msg('_ERROR_DELETE');
                      }
                      else $Msg->show_msg('_ERROR_SELECT_FOR_DEL');
                      echo "<script>window.location.href='$script';</script>";
                      break;

    case 'cancel':  echo "<script>window.location.href='$script';</script>";
                    break;
}
?>
