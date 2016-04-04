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


 include_once( SITE_PATH.'/modules/mod_glossary/glossary.defines.php' );


 if( !isset( $_REQUEST['task'] ) ) $task = 'showa';
 else $task = $_REQUEST['task'];

 if( !isset( $_REQUEST['l'] ) ) $l = '';
 else $l = $_REQUEST['l'];


 $m = new Glossary();
 if( isset( $_REQUEST['id'] ) ) $m->fltr = ' and id='.$_REQUEST['id'];
 $m->ShowLinks();

 if( $l ) $m->ShowGlossary( $l );
 else $m->ShowPage();

?>
