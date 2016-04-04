<?
/**
* poll.backend.php  
* script for all actions with Polls
* @package Poll Package of SEOCMS
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 24.02.2011
* @copyright (c) 2010+ by SEOTM
*/
include_once( SITE_PATH.'/modules/mod_poll/poll.defines.php' );

if(!defined("_LANG_ID")) {$pg = new PageAdmin();} 

if( !isset( $_REQUEST['module'] ) ) $module = NULL;
else $module = $_REQUEST['module'];

//Blocking to execute a script from outside (not Admin-part) 
if ( !$pg->logon->isAccessToScript($module)) exit;

if( !isset($_REQUEST['task']) ) $task='show';
else $task=$_REQUEST['task'];

if(!isset($_REQUEST['sort'])) $sort=NULL;
else $sort=$_REQUEST['sort'];

if(!isset($_REQUEST['start'])) $start = 0;
else $start=$_REQUEST['start'];

if(!isset($_REQUEST['display'])) $display = 20;
else $display=$_REQUEST['display'];

if( !isset( $_REQUEST['fltr_status'] ) ) $fltr_status = NULL;
else $fltr_status = $_REQUEST['fltr_status'];

if( !isset( $_REQUEST['fltr_type'] ) ) $fltr_type = NULL;
else $fltr_type = $_REQUEST['fltr_type'];

if( !isset( $_REQUEST['poll_id'] ) ) $poll_id = NULL;
else $poll_id = $_REQUEST['poll_id'];

if( !isset( $_REQUEST['sel'] ) ) $sel = NULL;
else $sel = $_REQUEST['sel'];

if( !isset( $_REQUEST['id'] ) ) $id = NULL;
else $id = $_REQUEST['id'];

if( !isset( $_REQUEST['start_date'] ) ) $start_date = NULL;
else $start_date = $_REQUEST['start_date'];

if( !isset( $_REQUEST['end_date'] ) ) $end_date = NULL;
else $end_date = $_REQUEST['end_date'];

if( !isset( $_REQUEST['id_del'] ) ) $id_del = NULL;
else $id_del = $_REQUEST['id_del'];

if( !isset( $_REQUEST['count'] ) ) $count = NULL;
else $count = $_REQUEST['count'];


$m = new PollCtrl($pg->logon->user_id, $module);
$m->task = $task;
$m->display = $display;
$m->start = $start;
$m->sort = $sort;
$m->fltr_status = $fltr_status;
$m->fltr_type = $fltr_type;
$m->poll_id = $poll_id;
$m->sel = $sel;
$m->id = $id;
$m->count = $count;
$m->start_date = $start_date; 
$m->end_date = $end_date; 
 
$script = 'module='.$m->module.'&display='.$m->display.'&start='.$m->start.'&sort='.$m->sort.'&fltr_status='.$m->fltr_status.'&fltr_type='.$m->fltr_type;
if( $poll_id ) $script = $script.'&poll_id='.$poll_id;
$script = $_SERVER['PHP_SELF']."?$script";
$m->script = $script;

 if( isset( $_REQUEST['alt'] ) )
 {
  if( $task == 'new' ) $task = 'new_alt';
  if( $task == 'save' ) $task = 'save_alt';
  if( $task == 'delete' ) $task = 'del_alt';
 }

 if( isset( $_REQUEST['answ'] ) )
 {
  if( $task == 'delete' ) $task = 'del_answer';
 }

 if( isset( $_REQUEST['ip'] ) )
 {
  if( $task == 'delete' ) $task = 'del_ip';
 }

///echo 'task='.$task;
 switch( $task )
 {

    case 'show':      $m->show();
                      break;

    case 'new':       $m->edit( $id, NULL );
                      break;

    case 'edit':      $m->edit( $_REQUEST['id'], NULL );
                      break;

    case 'save':
                      if( $m->save() ) echo "<script>window.location.href='$script';</script>";
                      break;
                      
    case 'savereturn':
                      if( $m->save() ){$m->edit( $_REQUEST['id'], NULL );}
                      break; 

    case 'delete':
                      $del = $m->del( $id_del );
                      if ( $del > 0 ) echo "<script>window.alert('Deleted OK! ($del records)');</script>";
                      else $pg->Msg->show_msg('_ERROR_DELETE');
                      echo "<script>window.location.href='$script';</script>";
                      break;

    case 'show_alt':
                      $m->show_alt();
                      break;

    case 'new_alt':
                      $m->edit_alt(  NULL, NULL  );
                      break;

    case 'edit_alt':
                      $m->edit_alt(  $_REQUEST['id'], NULL  );
                      break;

    case 'save_alt':
                      if( $m->save_alt() ) echo "<script>window.location.href='$script&task=show_alt';</script>";
                      break;

    case 'del_alt':
                      $del = $m->del_alt( $id_del );
                      if ( $del > 0 ) echo "<script>window.alert('Deleted OK! ($del records)');</script>";
                      else $pg->Msg->show_msg('_ERROR_DELETE');
                      echo "<script>window.location.href='$script&task=show_alt';</script>";
                      break;

    case 'up':
                    $m->up( $move );
                    echo "<script>window.location.href='$script&task=show_alt';</script>";
                    break;

    case 'down':
                    $m->down( $move );
                    echo "<script>window.location.href='$script&task=show_alt';</script>";
                    break;

    case 'show_answer':
                      $m->show_answer();
                      break;

    case 'del_answer':
                      $del = $m->del_answer( $id_del );
                      if ( $del > 0 ) echo "<script>window.alert('Deleted OK! ($del records)');</script>";
                      else $pg->Msg->show_msg('_ERROR_DELETE');
                      echo "<script>window.location.href='$script&task=show_answer';</script>";
                      break;

    case 'show_ip':
                      $m->show_ip();
                      break;

    case 'del_ip':
                      $del = $m->del_ip( $id_del );
                      if ( $del > 0 ) echo "<script>window.alert('Deleted OK! ($del records)');</script>";
                      else $pg->Msg->show_msg('_ERROR_DELETE');
                      echo "<script>window.location.href='$script&task=show_ip';</script>";
                      break;

    default:        $m->show();
                    break;
 } //--- end switch
?>
