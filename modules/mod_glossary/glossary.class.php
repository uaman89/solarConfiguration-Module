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


// ================================================================================================
//    Class             : Glossary
//    Version           : 1.0.0
//    Date              : 18.11.2005
//    Constructor       : Yes
//    Parms             :
//    Returns           : None
//    Description       : Glossary
// ================================================================================================
//    Programmer        :  Andriy Lykhodid
//    Date              :  18.11.2005
//    Reason for change :  Creation
//    Change Request Nbr:  N/A
// ================================================================================================

class Glossary {

 var $Right;
 var $Form;
 var $Msg;
 var $Spr;

 var $display;
 var $sort;
 var $start;

 var $user_id;
 var $module;
 var $lang_id = NULL;

 var $fltr;    // filter of group news

 var $width;
 var $sel = NULL;

// ================================================================================================
//    Function          : Glossary (Constructor)
//    Version           : 1.0.0
//    Date              : 18.11.2005
//    Parms             :
//    Returns           :
//    Description       : Glossary
// ================================================================================================

function Glossary()
{
 $this->Right =  new Rights;                       /* create Rights obect as a property of this class */
 $this->Form = new Form( 'form_glossary' );        /* create Form object as a property of this class */
 if (empty($this->Msg)) $this->Msg = new ShowMsg();/* create ShowMsg object as a property of this class */
 $this->Msg->SetShowTable(TblModGlossarySprTxt);    /* set table to get Multilanguagess */                      
 $this->Spr = new SysSpr( NULL, NULL, NULL, NULL, NULL, NULL, NULL ); /* create SysSpr object as a property of this class */
 $this->width = '750';
 
 $this-> lang_id = _LANG_ID;
}


// ================================================================================================
// Function : show()
// Version : 1.0.0
// Date : 18.11.2005
//
// Parms :
// Returns :     true,false / Void
// Description : Show Glossary
// ================================================================================================
// Programmer : Andriy Lykhodid
// Date : 18.11.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================

       function show( $user_id=NULL, $module=NULL, $display=NULL, $sort=NULL, $start=NULL, $spr=NULL )
       {
        if( $user_id ) $this->user_id = $user_id;
        if( $module ) $this->module = $module;
        if( $display ) $this->display = $display;
        if( $sort ) $this->sort = $sort;
        if( $start ) $this->start = $start;
        if( $spr ) $this->spr = $spr;

        /* Init Table  */
        $tbl = new html_table( 0, 'center', 650, 1, 5 );

        $scriptact = 'module='.$this->module.'&fln='.$this->fln.'&srch='.$this->srch;       /* set action page-adress with parameters */
        $scriplink = $_SERVER['PHP_SELF']."?$scriptact";

        if( empty($this->sort) ) $this->sort='cod';

        // select (R)
        $q="select * FROM `".TblModGlossary."`";
        if ( ($this->fln!=NULL) || ($this->srch!=NULL)  ) $q = $q.' WHERE';
        if ( $this->srch!=NULL ) $q = $q." (cod LIKE '%$this->srch%' OR name LIKE '%$this->srch%')";
        if( $this->fln!=NULL ) {
             if ( $this->srch ) $q = $q." AND lang_id=$this->fln";
             else $q = $q." lang_id=$this->fln";
        }
        if ($this->fln!=NULL) $q=$q." group by `cod` order by `$this->sort`";
        else $q=$q." order by `$this->sort`";
        $res = $this->Right->db_Query($q);
        //echo '<br> $q='.$q.' $this->user_id='.$this->user_id.' $this->module='.$this->module.' $this->Rights->result='.$this->Rights->result. ' $this->spr='.$this->spr.' $res='.$res;
        if ( !$this->Right->result ) return false;
        $rows = $this->Right->db_GetNumRows();
        //echo '<br> rows='.$rows;

        /* Write Form Header */
        $this->Form->WriteHeader( $scriplink.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort );

        /* Write Table Part */
        AdminHTML::TablePartH();

        echo "<TR><TD COLSPAN=5>";
        /* Write Links on Pages */
        $this->Form->WriteLinkPages( $scriplink, $rows, $this->display, $this->start, $this->sort );
        $scriplink = $scriplink.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort;

        echo "<TR><TD COLSPAN=4>";
        /* Write Top Panel (NEW,DELETE - Buttons) */
        $this->Form->WriteTopPanel( $scriplink );

        echo '&nbsp&nbsp';
        echo $this->Form->TextBox('srch', $this->srch, 20);
        echo '<input type=submit value='.$this->Msg->show_text('_BUTTON_SEARCH', TblSysTxt).'>';

        echo "<TD align=center>";
        //echo "<br>fln=".$this->fln;
        $this->Form->WriteSelectLangChange( $scriplink, $this->fln );
		 if ($this->sel==1){
 $scr = 2;
 }
 else $scr = 1;
        ?>
        <TR>
        <Th class="THead"><a href="<?=$scriplink?>&amp;sel=<?=$scr?>">*</a></Th>
        <Th class="THead"><? $this->Form->Link($scriplink."&sort=id", $this->Msg->show_text('_FLD_ID'));?></Th>
        <Th class="THead"><? $this->Form->Link($scriplink."&sort=cod", $this->Msg->show_text('_FLD_CODE'));?></Th>
        <Th class="THead"><? $this->Form->Link($scriplink."&sort=name", $this->Msg->show_text('_FLD_GLOSSARY_NAME'));?></Th>
        <Th class="THead"><? $this->Form->Link($scriplink."&sort=description", $this->Msg->show_text('_FLD_GLOSSARY_DESCR'));?></Th>
        <Th class="THead"><? $this->Form->Link($scriplink."&sort=lang_id", $this->Msg->show_text('_FLD_LANGUAGE'));?></Th>
        <?
        $a=$rows;
        for( $i = 0; $i < $rows; $i++ )
        {
           $row = $this->Right->db_FetchAssoc();
           if( $i >=$this->start && $i < ( $this->start+$this->display ) )
           {
              if ( (float)$i/2 == round( $i/2 ) )
                 echo '<TR CLASS="TR1">';
              else
                 echo '<TR CLASS="TR2">';

              echo '<TD align="center">';$this->Form->CheckBox( "id_del[]", $row['id'], $this->sel);
              echo '<TD align="center">'; $this->Form->Link( "$scriplink&task=edit&id=".$row['id'], stripslashes($row['id']), $this->Msg->show_text('_TXT_EDIT_DATA', TblSysTxt) );
              echo '<TD>'.$row['cod'].'</TD>';

              echo '<TD>'; $this->Form->Link( "$scriplink&task=edit&id=".$row['id'], stripslashes($row['name']), $this->Msg->show_text('_TXT_EDIT_DATA', TblSysTxt) ); echo '</TD>';
              echo '<td>'.substr( $row['description'], 0, 50 );
              $q1 = "select * from `".TblSysLang."` where cod='".$row['lang_id']."' and lang_id='"._LANG_ID."'";
              $res1 = mysql_query($q1);
              if( !$res1 ) return false;
              $mas_f = mysql_fetch_array( $res1 );
              echo '<TD align=center>'.$mas_f['name'].'</TD>';

              //echo '<TD>'; $this->Form->Link("$scriplink&task=add_lang&id=".$row['cod'], '&nbsp;&nbsp;'.$this->msg->show_text('_LNK_OTHER_LANGUAGE').'&nbsp;&nbsp');
              echo '</TR>';
              $a=$a-1;
           }
        }
        AdminHTML::TablePartF();
        /* Write Form Footer */
        $this->Form->WriteFooter();
        return true;
       }




// ================================================================================================
// Function : edit()
// Version : 1.0.0
// Date : 04.02.2005
//
// Parms :
//                 $id   / id of editing record / Void
//                 $mas  / array of form values
// Returns : true,false / Void
// Description : edit/add records in News module
// ================================================================================================
// Programmer : Andriy Lykhodid
// Date : 04.02.2005
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

        $scriptact = $_SERVER['PHP_SELF'].'?module='.$this->module.'&display='.$_REQUEST['display'].'&start='.$_REQUEST['start'].'&sort='.$_REQUEST['sort'];

        if( $id AND ( !isset($mas['id'])) )
        {
         $q = "select * FROM `".TblModGlossary."` where id='$id'";

         // edit (U)
         $res = $this->Right->Query( $q, $this->user_id, $this->module );

         if( !$res ) return false;
         $mas = $this->Right->db_FetchAssoc();
        }

        /* Write Form Header */
        $this->Form->WriteHeader( $scriptact );

        $this->Form->Hidden( 'sort', $this->sort );
        $this->Form->Hidden( 'display', $this->display );
        $this->Form->Hidden( 'start', $this->start );

        $this->Form->Hidden( 'fln', $this->fln );
        $this->Form->Hidden( 'srch', $this->srch );

        if( $id!=NULL ) $txt = $this->Msg->show_text('_TXT_EDIT_DATA', TblSysTxt);
        else $txt = $this->Msg->show_text('_TXT_ADD_DATA', TblSysTxt);

        AdminHTML::PanelSubH( $txt );
       /* Write Simple Panel*/
        AdminHTML::PanelSimpleH();
        echo '<table border=0 width=100% align=center class="EditTable"><tr><td>';
        ?>
         <b><?echo $this->Msg->show_text('_FLD_ID')?></b>
         <TD width=95%> <?
              if ( $id )
              {
                 echo $mas['id'];
                 $this->Form->Hidden( 'id', $mas['id'] );
              }
             ?>
         <TR><TD><b><?echo $this->Msg->show_text('_FLD_CODE')?></b>
             <TD><?
              if ( $id )
              {
                  echo $mas["cod"];
                  $this->Form->Hidden( 'cod', $mas['cod'] );
              }
              else
              {
                   $q="select * from `".TblModGlossary."` order by `cod` desc";
                   $res = $this->Right->Query($q, $this->user_id, $this->module);

                   $tmp = $this->Right->db_FetchAssoc();
                   echo  ($tmp['cod']+1);
                   $this->Form->Hidden( 'cod', ($tmp['cod']+1) );
              }
        ?>
        <tr><td colspan=2>

        <?
    $Panel->WritePanelHead( "SubPanel_" );

    $ln_arr = $ln_sys->LangArray( _LANG_ID );
    while( $el = each( $ln_arr ) )
    {
     $lang_id = $el['key'];
     $lang = $el['value'];
     $mas_s[$lang_id] = $lang;

     $Panel->WriteItemHeader( $lang );
        echo "\n <table border=0 class='EditTable'>";
        echo "\n <tr>";
        echo "\n <td><b>".$this->Msg->show_text('_FLD_GLOSSARY_NAME').":</b>";
        echo "\n <br>";

        $row = NULL;
        if( $fl ) $this->Form->TextBox( 'name['.$lang_id.']', $mas['name'][$lang_id], 60 );
        else $this->Form->TextBox( 'name['.$lang_id.']', $mas['name'], 60 );
        echo "\n <br>";

        echo "\n <tr>";
        echo "\n <td><b>".$this->Msg->show_text('_FLD_GLOSSARY_DESCR').":</b>";
        echo "\n <br>";

        if( $fl ) $this->Form->HTMLTextArea( 'description['.$lang_id.']', $mas['description'][$lang_id], 8, 70 );
        else $this->Form->HTMLTextArea( 'description['.$lang_id.']', $mas['description'], 8, 70 );
        echo "\n <br>";

        echo "\n <br>";
        echo   "\n </table>";
        $Panel->WriteItemFooter();
      }
    $Panel->WritePanelFooter();
    $this->Form->WriteSavePanel( $scriptact );
    $this->Form->WriteCancelPanel( $scriptact ); 
    $this->Form->WriteFooter();
    return true;
}







// ================================================================================================
// Function : save()
// Version : 1.0.0
// Date : 18.11.2005
//
// Parms :
// Returns : true,false / Void
// Description : Store data to the table
// ================================================================================================
// Programmer : Andriy Lykhodid
// Date : 18.11.2005
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================
function save( $id, $cod, $name, $description )
{
 $ln_sys = new SysLang();

 $id_relart = NULL;

 if (empty( $name[_LANG_ID] )) {
     $this->Msg->show_msg('GLOSSARY_NAME_EMPTY');
     $this->edit( $id, $_REQUEST );
     return false;
 }
 if (empty( $description[_LANG_ID] )) {
     $this->Msg->show_msg('GLOSSARY_DESCR_EMPTY');
     $this->edit( $id, $_REQUEST );
     return false;
 }

       $ln_arr = $ln_sys->LangArray( _LANG_ID );
        if ( empty( $ln_arr ) ) $ln_arr[1]='';
        while( $el = each( $ln_arr ) )
        {
           $name1 = addslashes($name[ $el['key'] ]);
           $name2 = addslashes($description[ $el['key'] ]);
           $lang_id = $el['key'];

           $q = "select * FROM `".TblModGlossary."` WHERE `cod`='$cod' and lang_id='$lang_id'";
           $res = $this->Right->Query($q, $this->user_id, $this->module);
           if( !$res ) return false;
           $rows = $this->Right->db_GetNumRows();

           if( $rows>0 )   //--- update
           {
              $q="update `".TblModGlossary."` set `cod`='$cod', `lang_id`='$lang_id', `name`='$name1', description='$name2' where cod='$cod' and lang_id='".$el['key']."'";
              $res = $this->Right->Query( $q, $this->user_id, $this->module );
              if( !$res ) return false;
           }
           else          //--- insert
           {
             $q = "insert into `".TblModGlossary."` values(NULL, '$cod', '$lang_id', '$name1', '$name2')";
             $res = $this->Right->Query( $q, $this->user_id, $this->module );
             if( !$res ) return false;
           }
        } //--- end while

 return true;
}





// ================================================================================================
// Function : del()
// Version : 1.0.0
// Date : 04.02.2005
//
// Parms :
// Returns :      true,false / Void
// Description :  Remove data from the table
// ================================================================================================
// Programmer :  Andriy Lykhodid
// Date : 04.02.2005
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
     $q = "DELETE FROM `".TblModGlossary."` WHERE id='$u'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     if( !$res )return false;
     if ( $res )
      $del = $del + 1;
     else
      return false;
    }
  return $del;
}




// ================================================================================================
// =======================             FRONT END           ========================================
// ================================================================================================


 // ================================================================================================
 // Function : ShowNewsLinks()
 // Version : 1.0.0
 // Date : 07.02.2005
 // Parms :
 // Returns :      true,false / Void
 // Description :  Show News Links
 // ================================================================================================
 // Programmer :  Andriy Lykhodid
 // Date : 07.02.2005
 // Reason for change : Creation
 // Change Request Nbr:
 // ================================================================================================

function ShowLinks()
{
 $db = new DB();
 $script = $_SERVER['PHP_SELF']."?";
 if( $this->module ) $script = $script."module=".$this->module;

 echo '<h1>'.$this->Msg->show_text('_FLD_GLOSSARY').'</h1>';
 echo '<p style="text-align:center;">';
 $q = "select * from ".TblModGlossary." where lang_id='"._LANG_ID."'";
//echo '<br>'.$q;
 $res = $db->db_Query( $q );
 $rows = $db->db_GetNumRows();

 if( $rows )
 {
  $l = NULL;
  for( $i = 0; $i < $rows; $i++ )
  {
   $row = $db->db_FetchAssoc();
   $l[ strtoupper( substr( trim( $row['name'] ), 0, 1 ) ) ] = substr( trim( $row['name'] ), 0, 1 );
  }
  ksort( $l );
  while( $el = each( $l ) )
  {
   if( $el['key'] )
   //echo ' <a href="'.$script.'l='.$el['key'].'">'.$el['key'].'</a> | ';
   echo ' <a href="glossary_'.urlencode( $el['key'] ).'.html">'.$el['key'].'</a> | ';
  }

 } //--- end if
}



 // ================================================================================================
 // Function : ShowGlossary()
 // Version : 1.0.0
 // Date : 20.11.2005
 // Parms :
 // Returns :      true,false / Void
 // Description :  Show Glossary
 // ================================================================================================
 // Programmer :  Andriy Lykhodid
 // Date : 18.11.2005
 // Reason for change : Creation
 // Change Request Nbr:
 // ================================================================================================

function ShowGlossary( $l )
{
 $db = new DB();

 $script = $_SERVER['PHP_SELF']."?";

 $db = new DB();
 $q = "select * from ".TblModGlossary." where lang_id='"._LANG_ID."'";
 $res = $db->db_Query( $q );
 $rows1 = $db->db_GetNumRows();


 if( $l ) $q = "select * from ".TblModGlossary." where lang_id='"._LANG_ID."' and name like '$l%' ";
 else $q = "select * from ".TblModGlossary." where lang_id='"._LANG_ID."'";

 if( $this->fltr ) $q = $q.$this->fltr;
 $q = $q." order by name";

 $res = $db->db_Query( $q );
 $rows = $db->db_GetNumRows();

?>
<h1><?=$l;?></h1>
<p><u>¬сего терминов</u>: <?=$rows1;?></p>
<P><u>“ерминов в категории</u>: <?=$rows;?></p>
<?
 for( $i = 0; $i < $rows; $i++ )
 {
   $row = $db->db_FetchAssoc();
   echo '<p><strong>'.ucfirst( strtolower( $row['name'] ) ).'</strong> - '.stripslashes( $row['description'] );
 }
}


function ShowPage()
{
 $db = new DB();
 $q = "select * from ".TblModGlossary." where lang_id='"._LANG_ID."'";
 $res = $db->db_Query( $q );
 $rows = $db->db_GetNumRows();
?>
<p>ѕутеводитель, словарь по <strong>мебельным и дизайнерским терминам</strong>.
Ќаш глоссарий мебельной-терминологии поможет как новичкам так и более опытным пользовател€м сети.</p>
<p>
 <u>¬сего терминов</u>: <?=$rows;?>
</p>
<?
}

function MAP()
{
 $db = new DB();
 $script = $_SERVER['PHP_SELF']."?";
 if( $this->module ) $script = $script."module=".$this->module;
 $q = "select * from ".TblModGlossary." where lang_id='"._LANG_ID."'";
 $res = $db->db_Query( $q );
 $rows = $db->db_GetNumRows();

 if( $rows )
 {
  $l = NULL;
  for( $i = 0; $i < $rows; $i++ )
  {
   $row = $db->db_FetchAssoc();
   $l[ strtoupper( substr( trim( $row['name'] ), 0, 1 ) ) ] = substr( trim( $row['name'] ), 0, 1 );
  }
  ksort( $l );

echo '<ul>';
  while( $el = each( $l ) )
  {
   if( $el['key'] )
   //echo ' <a href="'.$script.'l='.$el['key'].'">'.$el['key'].'</a> | ';
   echo '<li><a href="glossary_'.urlencode($el['key']).'.html">'.$el['key'].' - √лоссарий</a>';
  }
echo '</ul>';

 } //--- end if

}


function GetGlossaryKeywords( $l )
{
 $KW = NULL;

 $db = new DB();
 $q = "select * from ".TblModGlossary." where lang_id='"._LANG_ID."'";
 $res = $db->db_Query( $q );
 $rows1 = $db->db_GetNumRows();


 if( $l )
 {
  $q = "select * from ".TblModGlossary." where lang_id='"._LANG_ID."' and name like '$l%' ";

  if( $this->fltr ) $q = $q.$this->fltr;
  $q = $q." order by name";

  $res = $db->db_Query( $q );
  $rows = $db->db_GetNumRows();

  for( $i = 0; $i < $rows; $i++ )
  {
   $row = $db->db_FetchAssoc();
   if( $KW ) $KW = $KW.', '.strtolower( $row['name'] );
   else $KW = ' '.strtolower( $row['name'] );
  }
 }
 return $KW;
}

} // - -  end of class Glossary

