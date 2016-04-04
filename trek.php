<?php
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] );
include_once( SITE_PATH.'/include/defines.php' );
$tmp_db = new DB();

if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
else $ip = NULL;

if (!isset($_REQUEST['idc'])) $idc = NULL;
else $idc = $_REQUEST['idc'];

if (!isset($_REQUEST['ids'])) $ids = NULL;
else $ids = $_REQUEST['ids'];

if (!isset($_REQUEST['cs'])) $cs = NULL;
else $cs = $_REQUEST['cs'];

foreach ($_REQUEST as $key => $val):
    if ($string == '') {
        if ($val == '') {
            $string .= $key;
        } else {
            $string .= $key . '=' . $val;
        }
    } else {
        if ($val == '') {
            $string .= '&' . $key;
        } else {
            $string .= '&' . $key . '=' . $val;
        }
    }
endforeach;

$url = $ip;

$tag = $string;
$tag = explode('&', $tag);
$tag[count($tag) - 1] = "";
$string = implode('&', $tag);

$r = 0;
for ($i = 0; $i < strlen($string); $i++) {
    $r = $r ^ ord($string[$i]);
}

if($r==$cs) {
    $params = array(
        'idc' => $idc,
        'ids' => $ids,
        'server' => '0',
        'cs' => $cs
    );
}else{
    $params = array(
        'idc' => $idc,
        'ids' => $ids,
        'server' => '1',
        'cs' => $cs
    );
}

//$url = 'demo:demo@solar.seotm.biz/test.php';

if( $curl = curl_init() ) {
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    $out = curl_exec($curl);
    echo $out;
    curl_close($curl);
}

//$result = file_get_contents($url, false, stream_context_create(array(
//    'http' => array(
//        'method'  => 'POST',
//        'header'  => 'Content-type: application/x-www-form-urlencoded',
//        'content' => http_build_query($params)
//    )
//)));
//echo '$result='.$result.'<br><br><br><br>';
if(isset($_REQUEST['sensor'])) {
//    echo 'sensor';
    if (!isset($_REQUEST['idc'])) $idc = NULL;
    else $idc = $_REQUEST['idc'];

    if (!isset($_REQUEST['ids'])) $ids = NULL;
    else $ids = $_REQUEST['ids'];

    if (!isset($_REQUEST['sensor'])) $sensor = NULL;
    else $sensor = $_REQUEST['sensor'];

    if (!isset($_REQUEST['stat'])) $stat = NULL;
    else $stat = $_REQUEST['stat'];

    if (!isset($_REQUEST['azim'])) $azim = NULL;
    else $azim = $_REQUEST['azim'];

    if (!isset($_REQUEST['tilt'])) $tilt = NULL;
    else $tilt = $_REQUEST['tilt'];

    if (!isset($_REQUEST['long'])) $long = NULL;
    else $long = $_REQUEST['long'];

    if (!isset($_REQUEST['lat'])) $lat = NULL;
    else $lat = $_REQUEST['lat'];

    if (!isset($_REQUEST['temp'])) $temp = NULL;
    else $temp = $_REQUEST['temp'];

    if (!isset($_REQUEST['wind'])) $wind = NULL;
    else $wind = $_REQUEST['wind'];

    if (!isset($_REQUEST['snow'])) $snow = NULL;
    else $snow = $_REQUEST['snow'];

    if (!isset($_REQUEST['irrd'])) $irrd = NULL;
    else $irrd = $_REQUEST['irrd'];

    if (!isset($_REQUEST['time'])) $time = NULL;
    else $time = $_REQUEST['time'];

    if (!isset($_REQUEST['inst'])) $inst = NULL;
    else $inst = $_REQUEST['inst'];

    if (!isset($_REQUEST['num'])) $num = NULL;
    else $num = $_REQUEST['num'];

    if (!isset($_REQUEST['cs'])) $cs = NULL;
    else $cs = $_REQUEST['cs'];

    $wind_protection = substr($wind, 0, strrpos($wind, '|'));
    $wind_speed = substr(stristr($wind, '|'), 1);

    $q = "SELECT * FROM `mod_user_stantion` WHERE `idc`='" . $idc . "' AND `ids`='" . $ids . "'";
    $res = $tmp_db->db_Query($q);
    $mas = $tmp_db->db_FetchAssoc();
    if (!$mas) {
        $q = "INSERT INTO `mod_user_stantion` SET
                  `idc`='" . $idc . "',
                  `ids`='" . $ids . "',
                  `sensor`='" . $sensor . "',
                  `stat`='" . $stat . "',
                  `azim`='" . $azim . "',
                  `tilt`='" . $tilt . "',
                  `long` = '" . $long . "',
                  `lat` = '" . $lat . "',
                  `temp`='" . $temp . "',
                  `snow_protection`='" . $snow . "',
                  `irrd`='" . $irrd . "',
                  `time`='" . $time . "',
                  `inst`='" . $inst . "',
                  `num`='" . $num . "',
                  `cs`='" . $cs . "',
                  `wind_speed`='" . $wind_speed . "',
                  `wind_protection`='" . $wind_protection . "'
                  ";
    } else {
        $q = "UPDATE `mod_user_stantion` SET
                  `sensor`='" . $sensor . "',
                  `stat`='" . $stat . "',
                  `azim`='" . $azim . "',
                  `tilt`='" . $tilt . "',
                  `long` = '" . $long . "',
                  `lat` = '" . $lat . "',
                  `temp`='" . $temp . "',
                  `snow_protection`='" . $snow . "',
                  `irrd`='" . $irrd . "',
                  `time`='" . $time . "',
                  `inst`='" . $inst . "',
                  `num`='" . $num . "',
                  `cs`='" . $cs . "',
                  `wind_speed`='" . $wind_speed . "',
                  `wind_protection`='" . $wind_protection . "' WHERE `idc`='" . $idc . "' AND `ids`='" . $ids . "'
                  ";
    }


//echo $q;

    $uploaddir = SITE_PATH . '/SunFlower/id' . $idc;
    if (!file_exists($uploaddir)) mkdir($uploaddir, 0777);
    else  @chmod($uploaddir, 0777);

    $uploaddir = SITE_PATH . '/SunFlower/id' . $idc . '/s' . $ids;
    if (!file_exists($uploaddir)) mkdir($uploaddir, 0777);
    else @chmod($uploaddir, 0777);



    $uploaddir = SITE_PATH . '/SunFlower/id' . $idc . '/s' . $ids . '/sensor.txt';
    $fp = fopen($uploaddir, "a+");
    fwrite($fp, $string);
    fwrite($fp, "\r\n");
    fclose($fp);



}elseif(isset($_REQUEST['tracker'])){
//    echo 'treker';
    if(!isset($_REQUEST['idc'])) $idc= NULL;
    else $idc =$_REQUEST['idc'];

    if(!isset($_REQUEST['ids'])) $ids= NULL;
    else $ids =$_REQUEST['ids'];

    if(!isset($_REQUEST['tracker'])) $tracker= NULL;
    else $tracker =$_REQUEST['tracker'];

    if(!isset($_REQUEST['idt'])) $idt= NULL;
    else $idt =$_REQUEST['idt'];

    if(!isset($_REQUEST['statAzim'])) $statAzim= NULL;
    else $statAzim =$_REQUEST['statAzim'];

    if(!isset($_REQUEST['statTilt'])) $statTilt= NULL;
    else $statTilt =$_REQUEST['statTilt'];

    if(!isset($_REQUEST['inst'])) $inst= NULL;
    else $inst =$_REQUEST['inst'];

    if(!isset($_REQUEST['cs'])) $cs= NULL;
    else $cs =$_REQUEST['cs'];

    $q = "SELECT * FROM `mod_user_tracker` WHERE `idc`='".$idc."' AND `ids`='".$ids."' AND `idt`='".$idt."'";
//echo $q;
    $res =$tmp_db->db_Query($q);
    $mas = $tmp_db->db_FetchAssoc();
    if(!$mas){
        $q = "INSERT INTO `mod_user_tracker` SET
                  `idc`='".$idc."',
                  `ids`='".$ids."',
                  `tracker`='".$tracker."',
                  `idt`='".$idt."',
                  `statAzim`='".$statAzim."',
                  `statTilt`='".$statTilt."',
                  `inst` = '".$inst."',
                  `cs` = '".$cs."'
                  ";


    }else{
        $q = "UPDATE `mod_user_tracker` SET
                  `idc`='".$idc."',
                  `ids`='".$ids."',
                  `tracker`='".$tracker."',
                  `statAzim`='".$statAzim."',
                  `statTilt`='".$statTilt."',
                  `inst` = '".$inst."',
                  `cs` = '".$cs."' WHERE `idc`='".$idc."' AND `ids`='".$ids."' AND `idt`='".$idt."'
                  ";

    }

    $uploaddir = SITE_PATH.'/SunFlower/id'.$idc;
    if ( !file_exists ($uploaddir) ) mkdir($uploaddir,0777);
    else  @chmod($uploaddir,0777);

    $uploaddir = SITE_PATH.'/SunFlower/id'.$idc.'/s'.$ids;
    if ( !file_exists ($uploaddir) ) mkdir($uploaddir,0777);
    else @chmod($uploaddir,0777);

//    foreach($_REQUEST as $key=>$val):
//        if($string==''){$string .= $key.'='.$val;}else{$string .= '&'.$key.'='.$val;}
//    endforeach;

    $uploaddir = SITE_PATH.'/SunFlower/id'.$idc.'/s'.$ids.'/t'.$idt.'.txt';
    $fp = fopen($uploaddir, "a+");
    fwrite($fp, $string);
    fwrite($fp, "\r\n");
    fclose($fp);

}

$res = $tmp_db->db_Query($q);
if (!$res OR !$tmp_db->result)
    return false;



?>