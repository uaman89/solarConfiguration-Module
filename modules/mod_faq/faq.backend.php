<?
/**
* faq.backend.php  
* script for all actions with FAQ
* @package FAQ Package of SEOCMS
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 24.02.2011
* @copyright (c) 2010+ by SEOTM
*/
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] ); 
include_once( SITE_PATH.'/modules/mod_faq/faq.class.php' );

if(!defined("_LANG_ID")) {$pg = new PageAdmin();} 

if( !isset( $_REQUEST['module'] ) ) $module = NULL;
else $module = $_REQUEST['module'];

//Blocking to execute a script from outside (not Admin-part) 
if ( !$pg->logon->isAccessToScript($module)) exit;

if( !isset( $_REQUEST['task'] ) ) $task = 'show';
else $task = $_REQUEST['task'];

if( !isset( $_REQUEST['display'] ) ) $display = 10;
else $display = $_REQUEST['display'];

if( !isset( $_REQUEST['start'] ) ) $start = 0;
else $start = $_REQUEST['start'];

if( !isset( $_REQUEST['sort'] ) ) $sort = NULL;
else $sort = $_REQUEST['sort'];

if( !isset( $_REQUEST['fltr'] ) ) $fltr=NULL;
else $fltr = $_REQUEST['fltr'];

if( !isset( $_REQUEST['sel'] ) ) $sel = NULL;
else $sel = $_REQUEST['sel'];

if( isset( $_REQUEST['dorel'] ) )
{
 if( $task == 'new' ) $task = 'new_rel';
 if( $task == 'save' ) $task = 'save_rel';
 if( $task == 'delete' ) $task = 'del_rel';
}

 $m = new FAQ($pg->logon->user_id, $module);

 $m->task = $task;
 $m->display = $display;
 $m->start = $start;
 $m->sort = $sort;
 $m->fltr = $fltr;
 $m->sel = $sel;
 
 if( !isset( $_REQUEST['fln'] ) ) $m->fln = _LANG_ID;
 else $m->fln = $_REQUEST['fln'];

 $script = 'module='.$m->module.'&display='.$m->display.'&start='.$m->start.'&sort='.$m->sort.'&fltr='.$m->fltr.'&fln='.$m->fln;
 $script = $_SERVER['PHP_SELF']."?$script";


switch( $task ) {

    case 'show':      $m->show(); break;

    case 'new':       $m->edit( NULL, NULL ); break;

    case 'edit':      $m->edit( $_REQUEST['id'], NULL ); break;

    case 'save':
                      $id_rel = NULL;
                      if ( $m->save( $id, $cod, NULL, $id_category, $_REQUEST['subject'], $question, $answer, $id_rel, $status, $display1 ) )
                      {
                        echo "<script>window.location.href='$script';</script>";
                      }
                      break;

    case 'delete':
                       $del = $m->del( $id_del );
                       if ( !empty($id_del) ) {
                         $del=$m->del( $id_del );
                         if ( $del > 0 ) echo "<script>window.alert('".$pg->Msg->get_msg('_SYS_DELETED_OK')." $del');</script>";
                         else $pg->Msg->show_msg('_ERROR_DELETE');
                       }
                       else $pg->Msg->show_msg('_ERROR_SELECT_FOR_DEL');
                       echo "<script>window.location.href='$script';</script>";
                       break;
    case 'cancel':
                      echo "<script>window.location.href='$script';</script>";
                      break;

    case 'up':
                    $m->up( $move );
                    echo "<script>window.location.href='$script';</script>";
                    break;

    case 'down':
                    $m->down( $move );
                    echo "<script>window.location.href='$script';</script>";
                    break;

    case 'cancel':  echo "<script>window.location.href='$script';</script>";
                    break;

    case 'show_rel':
                    $m->rel( $cod );
                    break;

    case 'new_rel':
                    $m->rel_edit( NULL, $cod );
                    break;

    case 'edit_rel':
                    $m->rel_edit( $id, $cod );
                    break;

    case 'save_rel':
                    if( $m->rel_save( $id, $id_faq, $id_rel ) )
                       echo "<script>window.location.href='$script&task=show_rel&cod=$id_faq';</script>";
                    break;

    case 'del_rel':
                    $del = $m->rel_del( $id_del );
                         if ( $del > 0 ) echo "<script>window.alert('Deleted OK! ($del records)');</script>";
                         else echo show_error('_ERROR_DELETE');
                    echo "<script>window.location.href='$script&task=show_rel&cod=$cod';</script>";
                    break;

}

?>
