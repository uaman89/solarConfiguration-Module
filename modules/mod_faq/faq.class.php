<?
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

include_once( SITE_PATH.'/modules/mod_faq/faq.defines.php' );   

// ================================================================================================
//    Class             : FAQ
//    Version           : 1.0.0
//    Date              : 11.02.2005
//    Constructor       : Yes
//    Parms             :
//    Returns           : None
//    Description       : FAQ Module
// ================================================================================================
//    Programmer        :  Andriy Lykhodid
//    Date              :  04.02.2005
//    Reason for change :  Creation
//    Change Request Nbr:  N/A
// ================================================================================================

class FAQ {

 var $Right;
 var $Form;
 var $Msg;
 var $Spr;

 var $display;
 var $sort;
 var $start;

 var $user_id;
 var $module;

 var $fln;     // filter of languages
 var $fltr;    // filter of group FAQ

 var $width;
 var $sel = NULL;

// ================================================================================================
//    Function          : FAQ (Constructor)
//    Version           : 1.0.0
//    Date              : 11.02.2005
//    Parms             :
//    Returns           :
//    Description       : FAQ
// ================================================================================================

function __construct($user_id=NULL, $module=NULL)
{
 $this->user_id = $user_id;
 $this->module = $module;
 
 $this->db = DBs::getInstance();
 $this->Right =  &check_init("RightsFAQ", "Rights", "'".$this->user_id."','".$this->module."'");
 $this->Form = new Form( 'form_faq' );         /* create Form object as a property of this class */
 $this->Msg = new ShowMsg();                   /* create ShowMsg object as a property of this class */
 $this->Msg->SetShowTable(TblModFaqSprTxt);
 $this->Spr = new SysSpr( NULL,NULL,NULL,NULL,NULL,NULL,NULL ); /* create SysSpr object as a property of this class */
 $this->width = '750';
}


// ================================================================================================
// Function : show()
// Version : 1.0.0
// Date : 11.02.2005
// Parms :
// Returns :     true,false / Void
// Description : Show FAQs
// ================================================================================================
// Programmer : Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function show()
{
 $db = DBs::getInstance();
 $frm = new Form('fltr');
 $script = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort.'&fltr='.$this->fltr.'&fln='.$this->fln;
 $script = $_SERVER['PHP_SELF']."?$script";

 if( !$this->sort ) $this->sort='display';
 if( strstr( $this->sort, 'display' ) )$this->sort = $this->sort.' desc';
 $q = "SELECT * FROM ".TblModFaq." where 1 ";
 if( $this->fln ) $q = $q." and lang_id=$this->fln";
 if( $this->fltr ) $q = $q." and $this->fltr";
 $q = $q." order by $this->sort";

 $res = $this->Right->Query( $q, $this->user_id, $this->module );
 if( !$res )return false;

 $rows = $this->Right->db_GetNumRows();

 /* Write Table Part */
 AdminHTML::TablePartH();

 $script1 = 'module='.$this->module.'&fltr='.$this->fltr;
 $script1 = $_SERVER['PHP_SELF']."?$script1";

 echo '<TR><TD COLSPAN=11>';
 /* Write Links on Pages */
 $this->Form->WriteLinkPages( $script1, $rows, $this->display, $this->start, $this->sort );

 echo '<TR><TD COLSPAN=6>';

 /* Write Form Header */
 $this->Form->WriteHeader( $script );
 $this->Form->WriteTopPanel( $script );

 echo '<td colspan=2>';
 $arr = NULL;
 $arr[''] = 'All';
 $q = "select * from ".TblModFaqCat;
 $res = $db->db_Query( $q );
 $rows1 = $db->db_GetNumRows();
 for( $i = 0; $i < $rows1; $i++ )
 {
    $row1 = $db->db_FetchAssoc();
    $arr['id_category='.$row1['cod']] = $this->Spr->GetNameById( TblModFaqCat, $row1['id'] );
 }
 $this->Form->SelectAct( $arr, 'id_category', $this->fltr, "onChange=\"location='$script'+'&fltr='+this.value\"" );


 echo '<td colspan=2>';
 $this->Form->WriteSelectLangChange( $script, $this->fln );

 $script2 = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&task=show&fltr='.$this->fltr;
 $script2 = $_SERVER['PHP_SELF']."?$script2";
 if ($this->sel==1){
 $scr = 2;
 }
 else $scr = 1;
?>
 <TR>
 <td class="THead"><a href="<?=$script2?>&amp;sel=<?=$scr?>">*</a></Th>
 <td class="THead"><A HREF=<?=$script2?>&sort=id><?=$this->Msg->show_text('_FLD_ID')?></A></Th>
 <td class="THead"><A HREF=<?=$script2?>&sort=cod><?=$this->Msg->show_text('_FLD_CODE')?></A></Th>
 <td class="THead"><A HREF=<?=$script2?>&sort=subject><?=$this->Msg->show_text('_FLD_FAQ_SUBJECT')?></A></Th>
 <td class="THead"><A HREF=<?=$script2?>&sort=question><?=$this->Msg->show_text('_FLD_FAQ_QUESTION')?></A></Th>
 <td class="THead"><A HREF=<?=$script2?>&sort=answer><?=$this->Msg->show_text('_FLD_FAQ_ANSWER')?></A></Th>
 <td class="THead"><A HREF=<?=$script2?>&sort=id_category><?=$this->Msg->show_text('_FLD_FAQ_CATEGORY')?></A></Th>
 <?/*<td class="THead"><?=$this->Msg->show_text('_FLD_FAQ_RELATED')?></Th*/?>
 <td class="THead"><A HREF=<?=$script2?>&sort=status><?=$this->Msg->show_text('_FLD_FAQ_STATUS')?></A></Th>
 <td class="THead"><A HREF=<?=$script2?>&sort=lang_id><?=$this->Msg->show_text('_FLD_LANGUAGE')?></A></Th>
 <td class="THead"><A HREF=<?=$script2?>&sort=display><?=$this->Msg->show_text('_FLD_FAQ_DISPLAY')?></Th>
 <?

 $up = 0;
 $down = 0;
 $cod = 0;
 $row_arr = NULL;

 $a = $rows;
 $j = 0;
 for( $i = 0; $i < $rows; $i++ )
 {
   $row = $this->Right->db_FetchAssoc();
   if( $i >= $this->start && $i < ( $this->start+$this->display ) )
   {
     $row_arr[$j] = $row;
     $j = $j + 1;
   }
 }

 $style1 = 'TR1';
 $style2 = 'TR2';
 for( $i = 0; $i < count( $row_arr ); $i++ )
 {
   $row = $row_arr[$i];
  if ( (float)$i/2 == round( $i/2 ) )
  {
    echo '<TR CLASS="'.$style1.'">';
  }
  else echo '<TR CLASS="'.$style2.'">';

   echo '<TD>';
   $this->Form->CheckBox( "id_del[]", $row['id'], $this->sel);

   echo '<TD>';
   $this->Form->Link( $script."&task=edit&id=".$row['id'], stripslashes( $row['id'] ), $this->Msg->show_text('_TXT_EDIT_DATA') );

   echo '<TD align=center>'.$row['cod'].'</TD>';

   echo '<TD align=center>'.stripslashes($row['subject']).'</TD>';

   echo '<TD align=center>';
   if( trim( $row['question'] )!='' ) $this->Form->ButtonCheck();

   echo '<TD align=center>';
   if( trim( $row['answer'] )!='' ) $this->Form->ButtonCheck();

   echo '<TD align=center>';
   $id_category = $row['id_category'];
   $category = $this->Spr->GetNameByCod( TblModFaqCat, $id_category );
   $this->Form->Link( $script."&task=show&fltr=id_category=$id_category", $category );

   $col_rel = NULL;
   $q1 = "select * from ".TblModFaqRel." where id_faq='".$row['cod']."'";
   $res1 = $db->Query( $q1, $this->user_id, $this->module );
   if( $res1 )
   {
     $tmp = $db->db_FetchAssoc();
     $col_rel = $db->db_GetNumRows();
   }
   /*
   echo '<TD align=center>';
   $script2 = 'module='.$this->module.'&sort=&fltr='.$this->fltr.'&task=show_rel&cod='.$row['cod'];
   $script2 = $_SERVER['PHP_SELF']."?$script2";
   $value = $this->Msg->show_text('_LNK_EDIT').'('.$col_rel.')';
   $this->Form->Link( $script2, $value );
   */
   echo '<TD align=center>';
   if( $row['status'] =='i')echo $this->Msg->show_text('_FLD_FAQ_INACTIVE');
   if( $row['status'] =='e')echo $this->Msg->show_text('_FLD_FAQ_EXPIRED');
   if( $row['status'] =='a')echo $this->Msg->show_text('_FLD_FAQ_ACTIVE');

   echo '<TD align=center>'.$this->Spr->GetNameByCod( TblSysLang, $row['lang_id'] );

   echo '<TD align=center>';
   if( $up!=0 )
   {
   ?>
    <a href=<?=$script?>&task=up&move=<?=$row['display']?>>
    <?=$this->Form->ButtonUp( $row['id'] );?>
    </a>
   <?
   }

   if( $i!=($rows-1) )
   {
   ?>
     <a href=<?=$script?>&task=down&move=<?=$row['display']?>>
     <?=$this->Form->ButtonDown( $row['id'] );?>
     </a>
   <?
   }

   $up=$row['id'];
   $a=$a-1;
 } //-- end for

 $this->Form->WriteFooter();
 AdminHTML::TablePartF();
}


// ================================================================================================
// Function : count_lang_rows()
// Version : 1.0.0
// Date : 11.02.2005
// Parms :    $row_arr, $i_
// Returns : true,false / Void
// Description : count_lang_rows
// ================================================================================================
// Programmer : Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function count_lang_rows( $row_arr, $i_ )
{
 $count_lang_rows = 0;
 $tmp_cod = $row_arr[$i_]['cod'];
 for( $i_; $i_ < count( $row_arr ); $i_++ )
 {
   $tmp = $row_arr[$i_];
   if( $tmp['cod']==$tmp_cod ) $count_lang_rows = $count_lang_rows + 1;
   else return $count_lang_rows;
 }
 return $count_lang_rows;
}


// ================================================================================================
// Function : GetRowByCODandLANGID()
// Version : 1.0.0
// Date : 11.02.2005
// Parms :   $cod, $ln
// Returns : true,false / Void
// Description : edit/add records in FAQ module
// ================================================================================================
// Programmer : Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function GetRowByCODandLANGID( $cod, $ln )
{
 $db = new Rights;
 $Row = NULL;

 $q = "SELECT * FROM ".TblModFaq." where cod='$cod' and lang_id='$ln'";

 $res = $db->Query( $q, $this->user_id, $this->module );
 if( !$res )  return $Row;

 $Row = $db->db_FetchAssoc();
 return $Row;
}



// ================================================================================================
// Function : edit()
// Version : 1.0.0
// Date : 11.02.2005
//
// Parms :
//                 $id   / id of editing record / Void
//                 $mas  / array of form values
// Returns : true,false / Void
// Description : edit/add records in FAQ module
// ================================================================================================
// Programmer : Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function edit( $id, $mas=NULL )
{
 $Panel = new Panel();
 $ln_sys = new SysLang();

 $fl = NULL;

 if( $mas )
 {
  $fl = 1;
 }

 $script = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort.'&fltr='.$this->fltr;
 $script = $_SERVER['PHP_SELF']."?$script";

 //$Panel->WritePanelHead( "EditPanel_" );

 if( $id!=NULL and ( $mas==NULL ) )
 {
   $q="SELECT * FROM ".TblModFaq." where id='$id'";
   $res = $this->Right->Query( $q, $this->user_id, $this->module );
   if( !$res ) return false;
   $mas = $this->Right->db_FetchAssoc();
 }
 if( $id!=NULL ) $txt = $this->Msg->show_text('_TXT_EDIT_DATA');
 else $txt = $this->Msg->show_text('_TXT_ADD_DATA');


 AdminHTML::PanelSubH( $txt );

 /* Write Form Header */
 $this->Form->WriteHeader( $script );
 //$this->Form->IncludeHTMLTextArea();
 
 $settings=SysSettings::GetGlobalSettings();
 $this->Form->textarea_editor = $settings['editer']; //'tinyMCE'; 
 $this->Form->IncludeSpecialTextArea( $settings['editer']); 

 

 echo '<table border=0 width="100%"><tr><td>';
 /* Write Simple Panel*/
 AdminHTML::PanelSimpleH();
 ?>
<table border=0 width=100%>
<tr><td width=120>
<table class="EditTable" width="100%">
 <TR><TD><?=$this->Msg->show_text('_FLD_ID')?>
 <TD>
<?
   if( $id!=NULL )
   {
    echo $mas['id'];
    $this->Form->Hidden( 'id', $mas['id'] );
   }else $this->Form->Hidden( 'id', '' );

?>
 <TR><TD><?=$this->Msg->show_text('_FLD_CODE')?>
 <TD>
<?
 if( $mas['cod'] )
 {
  echo  $mas['cod'];
  $this->Form->Hidden( 'cod', $mas['cod'] );
 }
 else
 {
   $q = "select * from ".TblModFaq." order by `cod` desc";
   $res = $this->Right->Query( $q, $this->user_id, $this->module );
   $tmp = $this->Right->db_FetchAssoc();
   echo  ($tmp['cod']+1);
   $this->Form->Hidden( 'cod', ($tmp['cod']+1) );
 }
?>
<TR><TD><?=$this->Msg->show_text('_FLD_FAQ_DISPLAY')?>
<td>
<? if( $id!=NULL )
 {
  echo $mas["display"];
  echo '<input type="hidden" name=display1 VALUE="'.$mas["display"].'">';
 }else
 {
      $q="select * from ".TblModFaq." order by `display` desc";
      $res = mysql_query($q);
      $tmp = mysql_fetch_array( $res );
      echo  ($tmp['display']+1);
      echo '<INPUT TYPE=hidden class="textbox0" NAME=display1 SIZE=50 VALUE="'.($tmp['display']+1).'">';
 }
?>
</table>
 <td>
 <table border=0 class="EditTable">
 <TR><TD><b><?=$this->Msg->show_text('_FLD_FAQ_CATEGORY')?></b>
     <TD>
<?
 if( $id!=NULL or ( $mas!=NULL ) ) $this->Spr->ShowInComboBox( TblModFaqCat, 'id_category', $mas['id_category'], 0 );
 else $this->Spr->ShowInComboBox( TblModFaqCat, 'id_category', '', 0 );
?>
<TR><TD><b><?=$this->Msg->show_text('_FLD_FAQ_STATUS')?></b>
     <TD>
<?
 $arr = NULL;
 $arr['a'] = $this->Msg->show_text('_FLD_FAQ_ACTIVE');
 $arr['e'] = $this->Msg->show_text('_FLD_FAQ_EXPIRED');
 $arr['i'] = $this->Msg->show_text('_FLD_FAQ_INACTIVE');
 if( !$mas['status'] ) $mas['status'] = 'i';
 $this->Form->Select( $arr, 'status', $mas['status'], NULL );

 echo '</table>';
 echo '</table>';
 /* Write Simple Panel Footer*/
 AdminHTML::PanelSimpleF();

 echo '<td rowspan=2 valign=top height=100% width="100%">';
  /* Write Simple Panel*/
// AdminHTML::PanelSimpleH();

// echo '<table valign=top border=0><tr><td align=center>';
 // calls function to read image from directory
 //$pathA = SITE_PATH.'/images/mod_faq';
 //$pathL = SITE_PATH.'/images/mod_faq';
 //$path = 'http://'.NAME_SERVER.'/images/mod_faq/';
 //$server_path = 'http://'.NAME_SERVER;
 //$images = array();
 //$images['/'] = '/';
 //$folders = array();
 //$folders[] = '/';
 //FileManeger::ReadImages( $pathA, '/', $folders, $images );

 //$property = 'name="imgs" size="10" multiple="multiple"';
 //$javascript = "onchange=\"previewImage( 'form_faq', 'imgs', 'view_imagefiles', '$path', '$server_path' )\"";
 //$this->Form->Sel( $images['/'], NULL, $property, $javascript);
//echo '<tr><td align=center>';
 //$this->Form->Img( "$server_path/images/blank.png", 'View', "name=view_imagefiles width=150 height=150 border=1" );

//echo '<tr><td>URL:<br>';
 //$this->Form->TextBox( 'img_url', '', 30 );
//echo '</table>';
  /* Write Simple Panel Footer*/
// AdminHTML::PanelSimpleF();

 echo '<tr><td valign=top>';

    $Panel->WritePanelHead( "SubPanel_" );

    $ln_arr = $ln_sys->LangArray( _LANG_ID );
    while( $el = each( $ln_arr ) )
    {
     $lang_id = $el['key'];
     $lang = $el['value'];
     $mas_s[$lang_id] = $lang;

     $Panel->WriteItemHeader( $lang );
        echo "\n <table border=0 class='EditTable' width='100%'>";
        echo "\n <tr>";
        echo "\n <td><b>".$this->Msg->show_text('_FLD_FAQ_SUBJECT').":</b>";
        echo "\n <td>";

        $row = NULL;
        if( $fl ) $this->Form->TextBox( 'subject['.$lang_id.']', $mas['subject'][$lang_id], 60 );
        else
        {
         if( $id )
         {
          $row = $this->GetRowByCODandLANGID( $mas['cod'], $lang_id );
         }
         $this->Form->TextBox( 'subject['.$lang_id.']', $row['subject'], 60 );
        }
        echo "\n <td rowspan=3>";

        echo "\n <tr>";
        echo "\n <td colspan=2><b>".$this->Msg->show_text('_FLD_FAQ_QUESTION').':</b>';
        echo "\n <br>";
        if( $fl ) $this->Form->SpecialTextArea( $this->textarea_editor, 'question', stripslashes($mas['question'][$lang_id]), 12, 95, 'style="width:100%;"', $lang_id  );
        else $this->Form->SpecialTextArea( $this->textarea_editor, 'question', stripslashes($row['question']), 12, 95, 'style="width:100%;"', $lang_id  );
        //$this->Form->HTMLTextArea( 'question['.$lang_id.']', stripslashes($row['question']), 12, 80 );
        
//       $this->EditPageContentHtml($lang_id);

        echo "\n <tr>";
        echo "\n <td colspan=2><b>".$this->Msg->show_text('_FLD_FAQ_ANSWER').':</b>';
        echo "\n <br>";
        if( $fl ) $this->Form->SpecialTextArea( $this->textarea_editor, 'answer', stripslashes($mas['answer'][$lang_id]), 12, 95, 'style="width:100%;"', $lang_id  );
        else $this->Form->SpecialTextArea( $this->textarea_editor, 'answer', stripslashes($row['answer']), 12, 95, 'style="width:100%;"', $lang_id  );

        echo   "\n </table>";
        $Panel->WriteItemFooter();
      }
    $Panel->WritePanelFooter();


 echo '<tr><td colspan=2 align=center>';
  /* Write Simple Panel*/
 AdminHTML::PanelSimpleH();

 echo '<table  border=0><tr><td valign=middle>';
 $this->Form->WriteSavePanel( $script );
 echo '<td>';
 $this->Form->WriteCancelPanel( $script );
 echo '</table>';

  /* Write Simple Panel Footer*/
 AdminHTML::PanelSimpleF();

 echo '</table>';

 $this->Form->WriteFooter();
 AdminHTML::PanelSubF();

}


// ================================================================================================
// Function : save()
// Version : 1.0.0
// Date : 11.02.2005
// Parms : $id, $cod, $lang_id, $id_category, $subject_, $question_, $answer_, $id_rel, $status, $display
// Returns : true,false / Void
// Description : Store data to the table faq
// ================================================================================================
// Programmer : Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function save( $id, $cod, $lang_id, $id_category, $subj, $question_, $answer_, $id_rel, $status, $display )
{
 $ln_sys = new SysLang();

 if (empty($id_category)) {
     $this->Msg->show_msg('MSG_CATEGORY_EMPTY');
     $this->edit( $id, $_REQUEST );
     return false;
 }
 if (empty( $subj[_LANG_ID] )) {
     $this->Msg->show_msg('MSG_SUBJECT_EMPTY');
     $this->edit( $id, $_REQUEST );
     return false;
 }
 if (empty( $question_[_LANG_ID] )) {
     $this->Msg->show_msg('MSG_QUESTION_EMPTY');
     $this->edit( $id, $_REQUEST );
     return false;
 }
 if (empty( $answer_[_LANG_ID] )) {
     $this->Msg->show_msg('MSG_ANSWER_EMPTY');
     $this->edit( $id, $_REQUEST );
     return false;
 }

 $ln_arr = $ln_sys->LangArray( _LANG_ID );

 while( $el = each( $ln_arr ) )
 {
   $subject = addslashes( $subj[ $el['key'] ] );

   $question = addslashes( $question_[ $el['key'] ] );
   $answer = addslashes( $answer_[ $el['key'] ] );
   $lang_id = $el['key'];

   $q = "SELECT * FROM `".TblModFaq."` WHERE `cod`='$cod' and lang_id='$lang_id'";
   $res = $this->Right->Query( $q, $this->user_id, $this->module );
   if( !$res ) return false;
   $rows = $this->Right->db_GetNumRows();
   if( $rows>0 )   //--- update
   {
      $q = "UPDATE `".TblModFaq."` set
           `id_category`='$id_category',
           `subject`='$subject',
           `question`='$question',
           `answer`='$answer',
           `id_rel`='$id_rel',
           `status`='$status',
           `display`='$display'
            where cod='$cod' and lang_id='".$el['key']."'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res ) return false;
   }
   else          //--- insert
   {
     $q = "INSERT INTO `".TblModFaq."` values(NULL, '$cod', '$lang_id', '$id_category', '$subject', '$question', '$answer', '$id_rel', '$status', '$display')";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res ) return false;
   }
 } //--- end while

 return true;
}


// ================================================================================================
// Function : del()
// Version : 1.0.0
// Date : 11.02.2005
// Parms :
// Returns :      true,false / Void
// Description :  Remove data from the table
// ================================================================================================
// Programmer :  Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function del( $id_del )
{
    $kol = count( $id_del );
    $del = 0;
    for( $i=0; $i<$kol; $i++ )
    {
     $u = $id_del[$i];
     $q = "select * from ".TblModFaq." where id='$u'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     $row = $this->Right->db_FetchAssoc();
     $cod = $row['cod'];

     $q="DELETE FROM `".TblModFaq."` WHERE id='$u'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );

     $q="DELETE FROM `".TblModFaqRel."` WHERE id_news='$cod'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     if( !$res )return false;
     if ( $res )
      $del=$del+1;
     else
      return false;
    }
  return $del;
}


// ================================================================================================
// Function : up()
// Version : 1.0.0
// Date : 11.02.2005
// Parms :
// Returns :      true,false / Void
// Description :  Up FAQ
// ================================================================================================
// Programmer :  Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function up( $move )
{
 $q="select * from ".TblModFaq." where display='$move'";
 $res = $this->Right->Query( $q, $this->user_id, $this->module );
 if( !$res )return false;
 $rows = $this->Right->db_GetNumRows();
 $row = $this->Right->db_FetchAssoc();
 $move_down = $row['display'];
 $id_down = $row['cod'];


 $q="select * from ".TblModFaq." where display>'$move' order by display";
 $res = $this->Right->Query( $q, $this->user_id, $this->module );
 if( !$res )return false;
 $rows = $this->Right->db_GetNumRows();
 $row = $this->Right->db_FetchAssoc();
 $move_up = $row['display'];
 $id_up = $row['cod'];

 if( $move_down!=0 AND $move_up!=0 )
 {
 $q="update ".TblModFaq." set
     display='$move_down' where cod='$id_up'";
 $res = $this->Right->Query( $q, $this->user_id, $this->module );

 $q="update ".TblModFaq." set
     display='$move_up' where cod='$id_down'";
 $res = $this->Right->Query( $q, $this->user_id, $this->module );
 }
}


// ================================================================================================
// Function : down()
// Version : 1.0.0
// Date : 11.02.2005
// Parms :
// Returns :      true,false / Void
// Description :  Down FAQ
// ================================================================================================
// Programmer :  Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function down( $move )
{
 $q="select * from `".TblModFaq."` where display='$move'";
 $res = $this->Right->Query( $q, $this->user_id, $this->module );
 if( !$res )return false;
 $rows = $this->Right->db_GetNumRows();
 $row = $this->Right->db_FetchAssoc();
 $move_up = $row['display'];
 $id_up = $row['cod'];


 $q="select * from `".TblModFaq."` where display<'$move' order by display desc";
 $res = $this->Right->Query( $q, $this->user_id, $this->module );
 if( !$res )return false;
 $rows = $this->Right->db_GetNumRows();
 $row = $this->Right->db_FetchAssoc();
 $move_down = $row['display'];
 $id_down = $row['cod'];

 if( $move_down!=0 AND $move_up!=0 )
 {
 $q="update `".TblModFaq."` set
     display='$move_down' where cod='$id_up'";
 $res = $this->Right->Query( $q, $this->user_id, $this->module );

 $q="update `".TblModFaq."` set
     display='$move_up' where cod='$id_down'";
 $res = $this->Right->Query( $q, $this->user_id, $this->module );
 }
}




// ================================================================================================
// Function : rel()
// Version : 1.0.0
// Date : 11.02.2005
// Parms :        $cod=NULL
// Returns :      true,false / Void
// Description :  Related FAQ
// ================================================================================================
// Programmer :  Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function rel( $cod=NULL )
{
 $db = new Rights;

 $script = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort;
 $script = $_SERVER['PHP_SELF']."?$script&cod=$cod";

 if( !$this->sort ) $this->sort = 'id_rel';
 $q="SELECT * FROM ".TblModFaqRel;
 if( $cod )$q=$q." where id_faq='$cod'";
 $q=$q." order by `$this->sort`";

 $res = $this->Right->Query( $q, $this->user_id, $this->module ); if( !$res )return false;
 $rows = $this->Right->db_GetNumRows();

 /* Write Form Header */
 $this->Form->name = 'relart_form';
 $this->Form->WriteHeader( $script );
 $this->Form->Hidden( 'cod', $cod );
 $this->Form->Hidden( 'dorel', '1' );
?>
 <TABLE BORDER=0 CELLPADDING=5 CLASS="TableBG_2">
 <tr><th colspan=8>
<?
 /* Write Links on Pages */
 $this->Form->WriteLinkPages( $script, $rows, $this->display, $this->start, $this->sort );
?>
 <tr><td colspan=4>
<?
 $this->Form->WriteTopPanel2( $script, 'new_rel' );

 $script2 = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&task=shownews_relart';
 $script2 = $_SERVER['PHP_SELF']."?$script2&dorel=1";

 echo '<td colspan=4>';
 if( $cod )
 {
   $q = "SELECT * FROM ".TblModFaq." where 1 ";
   $q = $q." and cod=$cod and lang_id="._LANG_ID;
   $q = $q." group by cod";
   $res = $db->Query( $q, $this->user_id, $this->module );
   $row = $db->db_FetchAssoc();
   echo '<b>'.$row['cod'].'-'.$row['subject'].'</b>';
 }

?>
 <TR>
 <Th>*</Th>
 <Th><A HREF=<?=$scrip?>&sort=id><?=$this->Msg->show_text('_FLD_ID')?></A></Th>
 <Th><A HREF=<?=$scrip?>&sort=cod><?=$this->Msg->show_text('_FLD_CODE')?></A></Th>
 <Th><A HREF=<?=$scrip?>&sort=id_category><?=$this->Msg->show_text('_FLD_FAQ_CATEGORY')?></A></Th>
 <Th><?=$this->Msg->show_text('_FLD_FAQ_SUBJECT')?></Th>
 <Th><A HREF=<?=$scrip?>&sort=status><?=$this->Msg->show_text('_FLD_FAQ_STATUS')?></A></Th>
 <?
 $a = $rows;
 for( $i = 0; $i < $rows; $i++ )
 {
   $row = $this->Right->db_FetchAssoc();
   if( $i >= $this->start && $i < ( $this->start+$this->display ) )
   {
     if ( (float)$i/2 == round( $i/2 ) ) echo '<TR CLASS="col2">';
     else echo '<TR CLASS="col1">';

   echo '<TD>';
   $this->Form->CheckBox( "id_del[]", $row['id'] );

   echo '<TD>';
   $this->Form->Link( $script."&task=edit_rel&id=".$row['id'].'&cod='.$row['id_faq'], $this->Msg->show_text('_LNK_EDIT'), $this->Msg->show_text('_LNK_EDIT') );

   echo '<TD align=center>'.$row['id_rel'].'</TD>';

   $q = "select * from ".TblModFaq." where cod='".$row['id_rel']."' and lang_id="._LANG_ID;
   $res1 = $db->Query( $q, $this->user_id, $this->module );
   if( !$res1 ) return false;
   $mas_f = $db->db_FetchAssoc();

   $category = $this->Spr->GetNameByCod( TblModFaqCat, $mas_f['id_category'] );
   echo '<TD align=center>'.$category.'</TD>';
   echo '<td>'.stripslashes($mas_f['subject']).'</td>';

   echo '<TD align=center>';
    if( $mas_f['status'] =='i')echo 'Inactive';
    if( $mas_f['status'] =='e')echo 'Expired';
    if( $mas_f['status'] =='a')echo 'Active';
   echo '</TD>';
 }
}
?>
</TABLE>
<?
 $this->Form->WriteFooter();
}



// ================================================================================================
// Function : rel_edit()
// Version : 1.0.0
// Date : 11.02.2005
// Parms :        $id, $cod
// Returns :      true,false / Void
// Description :  relart_edit news
// ================================================================================================
// Programmer :  Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function rel_edit( $id, $cod, $mas = NULL )
{

 $script = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort;
 $script = $_SERVER['PHP_SELF']."?$script&dorel=1";

 if( $id!=NULL and ( $mas==NULL ) )
 {
  $q="SELECT * FROM ".TblModFaqRel." where id='$id'";
  $res = $this->Right->Query( $q, $this->user_id, $this->module );
  if( !$res ) return false;
  $mas = $this->Right->db_FetchAssoc();
 }

 /* Write Form Header */
 $this->Form->WriteHeader( $script );
 ?>
<TABLE BORDER=0 CELLSPACING=0>
<tr><td width=20><td>
 <TABLE BORDER=0 CELLSPACING=4 CELLPADDING=0 CLASS="TableBG_2">
 <TR><TD COLSPAN=2 ALIGN=left>
 <?if( $id!=NULL )echo '<h3>'.$this->Msg->show_text('_TXT_EDIT_DATA').'</h3>';
   else echo '<h3>'.$this->Msg->show_text('_TXT_ADD_DATA').'</h3>';?>
 <TR><TD><TABLE WIDTH=100% BORDER=0 ALIGN=center CELLSPACING=1 CELLPADDING=0 CLASS="COL2">
 <TR><TD CLASS=COL1><b><?=$this->Msg->show_text('_FLD_ID')?></b>
 <TD CLASS="COL1">
<?
   echo ''.$mas['id'].'';
   $this->Form->Hidden( 'id', $mas['id'] );
   $this->Form->Hidden( 'cod', $cod );
?>
 <TR><TD CLASS="COL1"><b><?=$this->Msg->show_text('_FLD_FAQ_SUBJECT')?></b>
 <TD CLASS="COL1">
<?
 if( $cod )
 {
   $q = "SELECT * FROM ".TblModFaq." where 1 ";
   $q = $q." and cod=$cod and lang_id="._LANG_ID;
   $q = $q." group by cod";
   $res = $this->Right->Query( $q, $this->user_id, $this->module );
   $row = $this->Right->db_FetchAssoc();
   echo $row['cod'].'-'.$row['subject'];
 }
 $this->Form->Hidden( 'id_faq', $cod );
?>
 <TR><TD CLASS="COL1"><b><?=$this->Msg->show_text('_FLD_FAQ_RELATED')?></b>
 <TD CLASS="COL1"><?
 $arr = NULL;
 $arr[''] = '';
 $q = "SELECT * FROM ".TblModFaq." where 1 ";
 if( $cod ) $q = $q." and cod!=$cod and lang_id="._LANG_ID;
 $q = $q." group by cod";

 $res = $this->Right->Query( $q, $this->user_id, $this->module );
 if( !$res )return false;
 $rows = $this->Right->db_GetNumRows();

 for( $i = 0; $i < $rows; $i++ )
 {
  $row = $this->Right->db_FetchAssoc();
  $arr[$row['cod']] = $row['cod'].'-'.$row['subject'];
 }
 $this->Form->Select( $arr, 'id_rel', $mas['id_rel'] );
?>
 <TR><TD COLSPAN=2 ALIGN=left CLASS="COL1">
<?
   $this->Form->WriteSavePanel( $script );
?>
</TABLE>
<?
 $this->Form->WriteFooter();
 return true;
}


// ================================================================================================
// Function : rel_save()
// Version : 1.0.0
// Date : 11.02.2005
// Parms :        $id, $id_faq, $id_rel
// Returns :      true,false / Void
// Description :  rel_save for FAQ
// ================================================================================================
// Programmer :  Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function rel_save( $id, $id_faq, $id_rel )
{
 if( empty( $id_rel ) )
 {
     $this->Msg->show_msg('FAQ_REL_EMPTY');
     $this->rel_edit( $id, $id_faq, $_REQUEST );
     return false;
 }

 $q = "SELECT * FROM ".TblModFaqRel." WHERE id_faq='$id_faq' AND id_rel='$id_rel' ";
 $res = $this->Right->Query( $q, $this->user_id, $this->module );
 if( !$res ) return false;
 $rows = $this->Right->db_GetNumRows();
 if( $rows>0 )return true;


 $q="SELECT * FROM ".TblModFaqRel." WHERE id='$id'";

 $res = $this->Right->Query( $q, $this->user_id, $this->module );
 if( !$res ) return false;

 $rows = $this->Right->db_GetNumRows();
 if( $rows>0 )
 {
  $q="update `".TblModFaqRel."` set `id_faq`='$id_faq',
   `id_rel`='$id_rel'
   where `id`='$id'";
  $res = $this->Right->Query( $q, $this->user_id, $this->module );
  if( !$res ) return false;
  else return true;
 }
 else
 {
  $q = "insert into `".TblModFaqRel."` values(NULL,'$id_faq','$id_rel')";
  $res = $this->Right->Query( $q, $this->user_id, $this->module );

  if( !$res ) return false;
  else return true;
 }

}


// ================================================================================================
// Function : rel_del()
// Version : 1.0.0
// Date : 11.02.2005
// Parms :        $id_del
// Returns :      true,false / Void
// Description :  relart_del FAQ
// ================================================================================================
// Programmer :  Andriy Lykhodid
// Date : 11.02.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function rel_del( $id_del )
{
    $kol=count( $id_del );
    $del=0;
    for( $i=0; $i<$kol; $i++ )
    {
     $u = $id_del[$i];
     $q = "DELETE FROM `".TblModFaqRel."` WHERE id='$u'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     if( $res ) $del = $del + 1;
     else return false;
    }
  return $del;
}



// ================================================================================================
// Function : show_faq()
// Version : 1.0.0
// Date : 29.09.2005
// Parms :        $id_del
// Returns :      true,false / Void
// Description :   show all active FAQ on the frontend
// ================================================================================================
// Programmer :  Ihor Trokhymchuk
// Date : 29.09.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

function show_faq()
{
  $q="SELECT * FROM `".TblModFaq."` WHERE status='a' AND lang_id="._LANG_ID."";
  $res = $this->Right->db_Query( $q );
  //echo '<br> q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result; 
  if (!$this->Right->result) return false;
  if (!$res) return false;
  $rows = $this->Right->db_GetNumRows();
  //echo '<br> rows='.$rows;
  if ($rows==0) {
     echo '<table border="0" cellpadding=5 cellspacing=0 width="100%">
            <tr><td>� ��������� �� ������ ������ ���� ������� ����������...</td></tr>
           </table>';
           return false;
  }
  ?>
  <h1 style="padding-left:20px;"><img src="images/design/arrow_1.gif"/>&nbsp;&nbsp;&nbsp;FAQ - ����� ���������� ������� � ����������� �������.</H1>
  <table border="0" cellpadding=3 cellspacing=5>
   <?
   for($i_num=0; $i_num<$rows; $i_num++){
      $row = $this->Right->db_FetchAssoc();
      echo '<tr><td align="left" class="news"><h2>'.$this->Msg->show_text('_FLD_FAQ_QUESTION').': '.stripslashes($row['question']).'</h2></td></tr>';
      echo '<tr><td align="left"><h2>'.$this->Msg->show_text('_FLD_FAQ_ANSWER').':</h2><div  class="sprshort"> '.stripslashes($row['answer']).'</div></td></tr>';
      ?><tr><td width="100%"><hr style="border:none; background-image:url(images/design/hr.gif); background-repeat: repeat-x; width:98%;"/></td></tr><?
   }
   ?>
  </table>
  <?
  return true;
} //end of function show_faq()

} //--- end of class


/*

_FLD_FAQ_SUBJECT
_FLD_FAQ_QUESTION
_FLD_FAQ_ANSWER
_FLD_FAQ_CATEGORY
_FLD_FAQ_RELATED
_FLD_FAQ_STATUS
_FLD_FAQ_DISPLAY


FAQ_CATEGORY_EMPTY
FAQ_SUBJECT_EMPTY
FAQ_QUESTION_EMPTY
FAQ_ANSWER_EMPTY
FAQ_REL_EMPTY
*/

?>
