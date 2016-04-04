<?php
if (!defined("SITE_PATH")) define( "SITE_PATH", $_SERVER['DOCUMENT_ROOT'] );
include_once( SITE_PATH.'/include/defines.php' );
include_once( SITE_PATH.'/modules/mod_asked/asked.defines.php' );

$Page = new PageUser();

$Asked = &check_init('AskedLayout', 'AskedLayout');


if ( !isset($_REQUEST['task']) ) $Asked->task = 'all';
else $Asked->task = $_REQUEST['task'];

if ( !isset($_REQUEST['asked_author']) ) $Asked->author = NULL;
else $Asked->author = $_REQUEST['asked_author'];

if ( !isset($_REQUEST['asked_email']) ) $Asked->email = NULL;
else $Asked->email = $_REQUEST['asked_email'];

if ( !isset($_REQUEST['asked_category']) ) $Asked->asked_category = NULL;
else $Asked->asked_category = $_REQUEST['asked_category'];

if ( !isset($_REQUEST['question']) ) $Asked->question = NULL;
else $Asked->question = $_REQUEST['question'];

if ( !isset($_REQUEST['date']) ) $Asked->date = NULL;
else $Asked->date = $_REQUEST['date'];

if ( !isset($_GET['flag']) ) $Asked->flag = 0;
else $Asked->flag = 1;

if(!isset($_REQUEST['start'])) $Asked->start=0;
else $Asked->start= $Asked->Form->GetRequestTxtData($_REQUEST['start'], 1);

if(!isset($_REQUEST['display'])) $Asked->display=10;
else $Asked->display = $Asked->Form->GetRequestTxtData($_REQUEST['display'], 1);

if(!isset($_REQUEST['page'])) $Asked->page=1;
else $Asked->page = $Asked->Form->GetRequestTxtData($_REQUEST['page'], 1);

// $str_cat - for mod_rewrite
$Asked->category =null;
if( !isset( $_REQUEST['str_cat'] ) ) $Asked->str_cat = NULL;
else{
    $Asked->str_cat = $Asked->Form->GetRequestTxtData($_REQUEST['str_cat'], 1);
    $Asked->category = $Asked->Spr->GetCodByTranslit(TblModAskedCat, $Asked->str_cat, $Asked->lang_id);
    if( empty($Asked->category) ) $Page->Set_404();
    else $Asked->fltr .= " AND `".TblModAsked."`.`category`=".$Asked->category;
}

if($Asked->page>1) $Asked->start = ($Asked->page-1)*$Asked->display;
if(strval($Asked->page)=='all') {
    $Asked->start = 0;
    $Asked->display = 999999;
}

$title_content =  $Asked->multi['TXT_QUESTION_AND_ANSWER'];

$title = $title_content.' | ' . META_TITLE;
$Description = $title_content.' ' . META_DESCRIPTION;
$Keywords = $title_content.' ' . META_KEYWORDS;

if(isset ($Page->FrontendPages))
    $FrontendPages = &$Page->FrontendPages;
else
    $FrontendPages = &check_init('FrontendPages', 'FrontendPages');

$FrontendPages->page = PAGE_ASKED;

$Page->SetTitle( $title );
$Page->SetDescription( $Description );
$Page->SetKeywords( $Keywords );


ob_start();
$Page->Form->WriteContentHeader($title_content, false,false);
switch($Asked->task) {
  case 'all':
      $Asked->ShowAnswersByPages();
      break;

  case 'show_form':
        $Asked->ShowForm();
        break;

  case 'add':
      if ($Asked->CheckFields() != '') {
          $Asked->ShowForm();
          //$Page->WriteFooter();
      }
      else{
        $Asked->SaveAsked();
        $Asked->ShowForm(1);
        $Asked->ShowAnswersByPages();
        //echo "<script>window.location.href='$script';</script>";
      }
      break;
}
$Page->Form->WriteContentFooter();
$Page->content = ob_get_clean();
$Page->out();
?>
