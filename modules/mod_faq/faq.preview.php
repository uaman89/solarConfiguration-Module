<?php
// ================================================================================================
//    System     : PrCSM05
//    Module     : FAQ
//    Version    : 1.0.0
//    Date       : 11.01.2005
//    Licensed To:
//                 Igor  Trokhymchuk  ihoru@mail.ru
//                 Andriy Lykhodid    las_zt@mail.ru
//
//    Purpose    : FAQ - module
//
// ================================================================================================

include_once( SITE_PATH.'/admin/modules/sys_spr/sys_spr.class.php' );

$msg = new ShowMsg();
session_start();
//------------------ Authorization settings -------------------------
$session_id=mosGetParam( $_SESSION, 'session_id', '' );
if ($session_id=="") {
     $msg->show_msg('_NOT_AUTH');
     echo "<script language='JavaScript'>window.location.href='logout.php';</script>";
}

$logon = new  Authorization($session_id);
if (!$logon->LoginCheck()) echo "<script>window.location.href='logout.php';</script>\n";;
//--------------------------------------------------------------------

//----------------------- languages settings --------------------------
$Lang = new SysLang();
if (empty($_SESSION["lang_pg"])) $_SESSION["lang_pg"]=1;

if(isset($_REQUEST['lang_pg'])){
   $_SESSION["lang_pg"] =$_REQUEST['lang_pg'];
   define( "_LANG_ID", $_SESSION["lang_pg"] );
}
if (isset($_SESSION["lang_pg"])) define( "_LANG_ID", $_SESSION["lang_pg"] );
else define( "_LANG_ID", 1 );


if (!isset($pg)) $pg = new PageAdmin();

$pg->WriteHeader();

?>

        <script>
                var form = window.opener.document.form_faq
                var subject = window.opener.document.form_faq.elements['subject[<?=_LANG_ID?>]'].value;
                var question = window.opener.document.form_faq.elements['question[<?=_LANG_ID?>]'].value;
                var answer = window.opener.document.form_faq.elements['answer[<?=_LANG_ID?>]'].value;
        </script>
<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0">
        <tr>
                <td class="contentheading" colspan="2" align=center><h3><script>document.write(subject);</script></h3></td>
        </tr>
        <tr>
                <script>document.write("<td valign=\"top\" height=\"90%\" colspan=\"2\"><br>" + question + "</td>");</script>
        </tr>

        <tr>
                <script>document.write("<td valign=\"top\" height=\"90%\" colspan=\"2\"><br><br>" + answer + "</td>");</script>
        </tr>
        <tr>    <td> <td> <br>
        <tr>
                <td align="right"><a href="#" onClick="window.close()">Close</a>&nbsp;&nbsp;</td>
                <td align="left"><a href="javascript:;" onClick="window.print(); return false">Print</a></td>
        </tr>
</table>
<?
$pg->WriteFooter();
?>
