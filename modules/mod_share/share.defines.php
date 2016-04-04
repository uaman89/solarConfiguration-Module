<?
// ================================================================================================
//    System       : CMS
//    Module      : Dynamic Share control
//    Date          : 14.03.2011
//    Licensed To : Yaroslav Gyryn
//    Purpose      : Defines Share
// ================================================================================================
include_once( SITE_PATH.'/include/defines.php' ); 
include_once( SITE_PATH.'/modules/mod_share/share.class.php' );
include_once( SITE_PATH.'/modules/mod_share/backend/share_backend.class.php' );
include_once( SITE_PATH.'/modules/mod_share/ShareLayout.class.php' );

define("MOD_SHARE", true);

define("TblModShare","mod_Share");
define("TblModShareTxt","mod_Share_txt");
define("TblModShareFileImg","mod_share_file_img");
define("TblModShareFileImgSpr","mod_share_file_img_spr");
  

define("Share_Img_Path",SITE_PATH."/images/mod_share/");

define("Share_USE_SHORT_DESCR", 1);
define("Share_USE_SPECIAL_POS", 1);
define("Share_USE_IMAGE", 1);
//define("Share_USE_IS_MAIN", 1);
?>