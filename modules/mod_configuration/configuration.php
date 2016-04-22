<?
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] );

include_once( $_SERVER['DOCUMENT_ROOT'].'/modules/mod_configuration/configuration.defines.php' );

if(!defined("_LANG_ID")) {session_start(); $pg = new PageAdmin();}

$module = ( !isset( $_REQUEST['module'] ) ) ? NULL : $_REQUEST['module'];

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
$Configuration = new ConfigurationOrder($logon->user_id, $module);
$Configuration->task   = ( isset( $_REQUEST['task']) ) ? $_REQUEST['task'] : null;
$Configuration->module = ( isset( $_REQUEST['module'] ) ) ? $_REQUEST['module'] : NULL;
$Configuration->orderId = ( isset( $_REQUEST['order_id'] ) ) ? $_REQUEST['order_id'] : NULL;
$Configuration->delete = ( isset( $_REQUEST['delete'] ) && is_array( $_REQUEST['delete'])  ) ? $_REQUEST['delete'] : NULL;

//var_dump($_REQUEST);
//echo '<br>$Configuration->task:'.$Configuration->task;
switch( $Configuration->task ) {
    case 'new':
    case 'edit':
        $Configuration->showConfigurationOrder();
        break;
    case 'getOrderData':
        exit ( $Configuration->getJsonOrderDataById() );

    case 'save':
        $request = file_get_contents("php://input");
        $postdata = json_decode($request, 1);
        $Configuration->save($postdata);
        break;
    case 'delete':
        //var_dump($Configuration->delete);
        $Configuration->deleteOrders();
        break;
    default:
        $Configuration->showConfigurationOrderList();
}
?>