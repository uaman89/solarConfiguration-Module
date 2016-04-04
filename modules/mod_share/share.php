<?php
include_once( $_SERVER['DOCUMENT_ROOT'].'/include/defines.php' );
/* @var $Page PageUser */
$Page = &check_init('PageUser', 'PageUser');
/* @var $ModulesPlug ModulesPlug */
$ModulesPlug = &check_init('ModulesPlug', 'ModulesPlug');
$id_module = $ModulesPlug->GetModuleIdByPath( 'mod_share/share.backend.php' );
if(!isset ($Page->Share)) 
    $Page->Share = &check_init('ShareLayout', 'ShareLayout', "'$id_module'");
    
$Page->Share->lang_id = _LANG_ID; 
$Page->Share->module = $id_module;

if( !isset($_REQUEST['brend']) ) $Page->Catalog->brend=NULL;
else $Page->Catalog->brend = $Page->Catalog->Form->GetRequestTxtData($_REQUEST['brend'], 1);

if( !isset($_REQUEST['model']) ) $Page->Catalog->model=NULL;
else $Page->Catalog->model = $Page->Catalog->Form->GetRequestTxtData($_REQUEST['model'], 1);

if( !isset($_REQUEST['size']) ) $Page->Catalog->size=NULL;
else $Page->Catalog->size = $Page->Catalog->Form->GetRequestTxtData($_REQUEST['size'], 1);

if( !isset($_REQUEST['price']) ) $Page->Catalog->price=NULL;
else $Page->Catalog->price = $Page->Catalog->Form->GetRequestTxtData($_REQUEST['price'], 1);

if( !isset( $_REQUEST['share'] ) ) $share_id=0;
else $share_id = $_REQUEST['share']; 
  
if( !isset ( $_REQUEST['q'] ) ) $q = NULL;
else $q = $_REQUEST['q'];  

if( !isset ( $_REQUEST['ajax'] ) ) $ajax = false;
else $ajax = true;  

if( !isset ( $_REQUEST['tag'] ) ) $tag = NULL;
else $tag = urldecode($_REQUEST['tag']);

if( !empty($q) AND !strstr($q, 'share.php') ) {
    $share_id=$Page->Share->GetIdByFolderName($q);
}

if(!isset($Page->Share->start)) $Page->Share->start=0;
//else $Page->Share->start = $Page->Share->Form->GetRequestNumData($_REQUEST['start']);

if(!isset($Page->Share->display)) $Page->Share->display=9;
//else $Page->Share->display = $Page->Share->Form->GetRequestNumData($_REQUEST['display']);

if(!isset($Page->Share->page)) $Page->Share->page=1;
//else $Page->Share->page = $Page->Share->Form->GetRequestTxtData($_REQUEST['page'], 1);
if($Page->Share->page>1) $Page->Share->start = ($Page->Share->page-1)*$Page->Share->display;
if($Page->Share->page=='all') {
    $Page->Share->start = 0;
    $Page->Share->display = 999999;
}



$Page->Share->share_id=$share_id;

$Page->Share->ajax=$ajax;
$Page->Share->share_txt = $Page->Share->GetShare($Page->Share->share_id);
$Page->FrontendPages->page = PAGE_SHARE;
$Page->Share->SetMetaData($Page->FrontendPages->page);

if ( empty($Page->Share->title) ) $Title = $Page->Share->multi["_TXT_SHARE_TITLE"];
else $Title = $Page->Share->title;
if ( empty($Page->Share->description) ) $Description =  $Page->Share->multi["_TXT_META_DESCRIPTION"];
else $Description = $Page->Share->description;
if ( empty($Page->Share->keywords) ) $Keywords = $Page->Share->multi["_TXT_META_KEYWORDS"];
else $Keywords = $Page->Share->keywords;  

$Page->SetTitle($Title);
$Page->SetDescription( $Description );
$Page->SetKeywords( $Keywords );  
//if(!empty($Page->Logon->user_id) && $Page->Share->share==$Page->Share->main_page)
//      Header("Lосаtiоn: /myaccount");
$Page->Catalog->share_id=$share_id;
if(!empty($share_id)) $Page->Catalog->shareLink=$q;

if(!$ajax)
$Page->WriteHeader();

if(empty($share_id) || $Page->Share->IsSubLevels($share_id)){
                $Page->Share->ShowSharesCurrentLevel ();
            }else
                $Page->Share->showContent();

if(!$ajax)
$Page->WriteFooter();     
?>