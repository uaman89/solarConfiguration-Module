<?
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] );

include_once( $_SERVER['DOCUMENT_ROOT'].'/modules/mod_configuration/configuration.defines.php' );

if(!defined("_LANG_ID")) {session_start(); $pg = new PageAdmin();}
if( !isset($_REQUEST['task']) || empty($_REQUEST['task']) ) $task='show';
else $task=$_REQUEST['task'];

if( !isset( $_REQUEST['module'] ) ) $module = NULL;
else $module = $_REQUEST['module'];
//var_dump($_REQUEST);
//die();
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
//=============================================================================================v
$Configuration = new Configuration($logon->user_id, $module);
$Configuration->task   = ( isset( $_REQUEST['task']) ) ? $_REQUEST['task'] : null;
$Configuration->module = ( isset( $_REQUEST['module'] ) ) ? $_REQUEST['module'] : NULL;


//echo '<br>task='.$task;
switch( $Configuration->task )
{

    default:
        $Configuration->showFrom();
        break;

}

?>