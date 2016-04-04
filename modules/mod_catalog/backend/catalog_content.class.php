<?php
// ================================================================================================
// System : SEOCMS
// Module : catalog_content.class.php
// Version : 1.0.0
// Date : 21.03.2006
// Licensed To:
// Igor Trokhymchuk ihoru@mail.ru
//
// Purpose : Class definition for all actions with managment of content of the catalog
//
// ================================================================================================

include_once( SITE_PATH.'/modules/mod_catalog/catalog.defines.php' );

// ================================================================================================
//    Class             : Catalog_content
//    Version           : 1.0.0
//    Date              : 21.03.2006
//
//    Constructor       : Yes
//    Parms             : session_id / session id
//                        usre_id    / UserID
//                        user_      /
//                        user_type  / id of group of user
//    Returns           : None
//    Description       : Class definition for all actions with managment of content of the catalog
// ================================================================================================
//    Programmer        :  Igor Trokhymchuk
//    Date              :  21.03.2006
//    Reason for change :  Creation
//    Change Request Nbr:  N/A
// ================================================================================================
/**
* Class Catalog_content
* Class definition for all actions with managment of content of the catalog
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 04.04.2012
* @property Rights $Right
*/
class Catalog_content extends Catalog {

     public $translit_old = NULL;
     public $files = NULL;
     public $Right = NULL;
     public $shareProc = NULL;

   // ================================================================================================
   //    Function          : Catalog_content (Constructor)
   //    Version           : 1.0.0
   //    Date              : 21.03.2006
   //    Parms             : usre_id   / User ID
   //                        module    / module ID
   //                        sort      / field by whith data will be sorted
   //                        display   / count of records for show
   //                        start     / first records for show
   //                        width     / width of the table in with all data show
   //    Returns           : Error Indicator
   //
   //    Description       : Opens and selects a dabase
   // ================================================================================================
   function __construct($user_id=NULL, $module=NULL, $display=NULL, $sort=NULL, $start=NULL, $width = NULL) {
        //Check if Constants are overrulled
        ( $user_id  !="" ? $this->user_id = $user_id  : $this->user_id = NULL );
        ( $module   !="" ? $this->module  = $module   : $this->module  = NULL );
        ( $display  !="" ? $this->display = $display  : $this->display = 20   );
        ( $sort     !="" ? $this->sort    = $sort     : $this->sort    = NULL );
        ( $start    !="" ? $this->start   = $start    : $this->start   = 0    );
        ( $width    !="" ? $this->width   = $width    : $this->width   = 750  );

        $this-> lang_id = _LANG_ID;

        if (empty($this->db)) $this->db = DBs::getInstance();
        if (empty($this->Right)) $this->Right = &check_init('Rights111', 'Rights', "'$this->user_id', '$this->module'");
        if (empty($this->Msg)) $this->Msg = &check_init('ShowMsg', 'ShowMsg');
        if (empty($this->Form)) $this->Form = &check_init('FormCatalog', 'Form', "'form_mod_catalog'");
        if (empty($this->Spr)) $this->Spr = &check_init('SysSpr', 'SysSpr', "'$this->user_id', '$this->module'");
        if (empty($this->settings)) $this->settings = $this->GetSettings();
        $this->settings['price_quick_edit']=1;

        if (( isset($this->settings['price_currency']) AND $this->settings['price_currency']=='1' ) OR ( isset($this->settings['opt_price_currency']) AND $this->settings['opt_price_currency']=='1' ) ){
            $this->Currencies = &check_init('SystemCurrencies', 'SystemCurrencies');
            $this->Currencies->defCurrencyData = $this->Currencies->GetDefaultData();
            $this->Currencies->GetShortNamesInArray('back');
        }

        //if (empty($this->multi)) $this->multi = $this->Spr->GetMulti(TblModCatalogSprTxt);
        if (empty($this->multi)) $this->multi = &check_init_txt('TblBackMulti',TblBackMulti);

        //load all data of categories to arrays $this->treeCat, $this->treeCatLevels, $this->treeCatData
        $this->loadTree();
        //echo '<br />treeCatLevels=';print_r($this->treeCatLevels);
        //echo '<br />treeCatData=';print_r($this->treeCatData);

   } // End of Catalog_content Constructor

    function microtime_diff($a, $b) {
       list($a_dec, $a_sec) = explode(" ", $a);
       list($b_dec, $b_sec) = explode(" ", $b);
       return (($b_sec - $a_sec) + ($b_dec - $a_dec));
    }

   // ================================================================================================
   // Function : GetContent
   // Version : 1.0.0
   // Date : 19.03.2008
   //
   // Parms :
   // Returns : true,false / Void
   // Description : execute SQL query
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 19.03.2008
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function GetContent($limit='limit')
   {
        //$tmp_db = DBs::getInstance();
        /*
        if( $this->srch ) {
            $q = "SELECT `cod` FROM ".TblModCatalogPropSprName." WHERE `name` LIKE '%".addslashes(htmlspecialchars_decode($this->srch))."%'";
            $tmp_res = $tmp_db->db_Query($q);
            $tmp_rows = $tmp_db->db_GetNumRows();
            $srch_str = NULL;
            for( $i = 0; $i < $tmp_rows; $i++ )
            {
                $row = $tmp_db->db_FetchAssoc();
                if ( empty($srch_str) ) $srch_str = "'".$row['cod']."'";
                else $srch_str = $srch_str.",'".$row['cod']."'";
            }
        }

        if( !$this->sort ) $this->sort='move';
        $q = "SELECT * FROM `".TblModCatalogProp."` where 1";
        if( $this->srch ){
            $s_srch = addslashes(htmlspecialchars_decode($this->srch));
            $str2 = '';
            if ( isset($this->settings['cod_pli']) AND $this->settings['cod_pli']=='1' ) {
                $str2 .= " OR `".TblModCatalogProp."`.`cod_pli`='".$s_srch."'";
            }
            if ( isset($this->settings['art_num']) AND $this->settings['art_num']=='1' ) {
                $str2 .= " OR `".TblModCatalogProp."`.`art_num`='".$s_srch."'";
            }
            if ( isset($this->settings['barcode']) AND $this->settings['barcode']=='1' ) {
                $str2 .= " OR `".TblModCatalogProp."`.`barcode`='".$s_srch."'";
            }
            if( !empty($srch_str) ) $q .= " AND (`".TblModCatalogProp."`.`id`='".$s_srch."' OR `".TblModCatalogProp."`.`number_name` LIKE '%".$s_srch."%' OR `id` IN (".$srch_str.") ".$str2.")";
            else $q .= " AND (`".TblModCatalogProp."`.`id`='".$s_srch."' OR `".TblModCatalogProp."`.`number_name` LIKE '%".$s_srch."%' ".$str2.")";
        }
        if( $this->id_cat ) $q = $q." AND `id_cat`='".$this->id_cat."'";
        if( $this->fltr ) $q = $q." AND `id_manufac`='".$this->fltr."'";
        if( $this->fltr2 ) $q = $q." AND `id_cat`='".$this->fltr2."'";
        if( $this->fltr3 ) $q = $q." AND `id_group`='".$this->fltr3."'";
        $q = $q." ORDER BY `".$this->sort."` ".$this->asc_desc;
        if($limit=='limit') $q = $q." LIMIT ".$this->start.", ".$this->display;
        */
        if( !$this->sort ) $this->sort='move';
        //$search_keywords = stripslashes($this->srch);
        $search_keywords =  addslashes(htmlspecialchars_decode($this->srch));
        $sel_table = NULL;
        $str_like = NULL;
        $filter_cr = ' OR ';
        //--quick search only by name
        $str_like = $this->build_str_like(TblModCatalogPropSprName . '.name', $search_keywords);
        $search_keywords_no_space = str_replace(" ", "", $search_keywords);
        //$str_like .= $filter_cr.$this->build_str_like(TblModCatalogPropSprName.'.name', $str_like_no_space);
        //$str_like .= $filter_cr.$this->build_str_like(TblModCatalogPropSprShort.'.name', $search_keywords);
        //$str_like .= $filter_cr.$this->build_str_like(TblModCatalogPropSprFull.'.name', $search_keywords);
        //$sel_table = "`".TblModCatalogProp."`, `".TblModCatalogPropSprName."`";
        //print_r($this->treeCatList);
        //$categs = implode(',', $this->treeCatList);
        //echo '<br />$categs='.$categs;

        $q = "SELECT
                `" . TblModCatalogProp . "`.*,
                `" . TblModCatalogPropSprName . "`.name,";
        if( $this->srch ){
            $q .= " MATCH `" . TblModCatalogPropSprName . "`.name AGAINST ('" . $search_keywords . "') as relev,
                    MATCH `" . TblModCatalogPropSprName . "`.name AGAINST ('" . $search_keywords_no_space . "') as relev2,";
        }
        $q .= " `" . TblModCatalogSprName . "`.name as cat_name,
                `" . TblModCatalogTranslit . "`.`translit`,
                `" . TblModCatalogPropImg . "`.`path` AS `first_img`,
                `" . TblModCatalogPropImgTxt . "`.`name` AS `first_img_alt`,
                `" . TblModCatalogPropImgTxt . "`.`text` AS `first_img_title`,
                `" . TblModCatalogPropSprShort . "`.`name` AS `short_descr`,
                `" . TblModCatalogPropSprFull . "`.`name` AS `full_descr`
                FROM `" . TblModCatalogProp . "`
                LEFT JOIN `" . TblModCatalogPropImg . "` ON (`" . TblModCatalogProp . "`.`id`=`" . TblModCatalogPropImg . "`.`id_prop` AND `" . TblModCatalogPropImg . "`.`move`='1')
                LEFT JOIN `" . TblModCatalogPropImgTxt . "` ON (`" . TblModCatalogPropImg . "`.`id`=`" . TblModCatalogPropImgTxt . "`.`cod` AND `" . TblModCatalogPropImgTxt . "`.lang_id='" . $this->lang_id . "')
                LEFT JOIN `" . TblModCatalogPropSprShort . "` ON (`" . TblModCatalogProp . "`.`id`=`" . TblModCatalogPropSprShort . "`.`cod` AND `" . TblModCatalogPropSprShort . "`.lang_id='" . $this->lang_id . "')
                LEFT JOIN `" . TblModCatalogPropSprFull . "` ON (`" . TblModCatalogProp . "`.`id`=`" . TblModCatalogPropSprFull . "`.`cod` AND `" . TblModCatalogPropSprFull . "`.lang_id='" . $this->lang_id . "'),
                `" . TblModCatalogPropSprName . "`,`" . TblModCatalogSprName . "`, `" . TblModCatalog . "`, `" . TblModCatalogTranslit . "`
                WHERE `" . TblModCatalogProp . "`.`id_cat`=`" . TblModCatalog . "`.`id`
                AND `" . TblModCatalogProp . "`.id=`" . TblModCatalogPropSprName . "`.cod
                AND `" . TblModCatalogProp . "`.id_cat=`" . TblModCatalogSprName . "`.cod
                AND `" . TblModCatalogPropSprName . "`.lang_id='" . $this->lang_id . "'
                AND `" . TblModCatalogSprName . "`.lang_id='" . $this->lang_id . "'
                AND `" . TblModCatalogProp . "`.id=`" . TblModCatalogTranslit . "`.`id_prop`
                AND `" . TblModCatalogTranslit . "`.`lang_id`='" . $this->lang_id . "'";
        if( $this->id_cat ) $q = $q." AND `" . TblModCatalogProp . "`.`id_cat`='".$this->id_cat."'";
        if( $this->fltr ) $q = $q." AND `" . TblModCatalogProp . "`.`id_manufac`='".$this->fltr."'";
        if( $this->fltr2 ) $q = $q." AND `" . TblModCatalogProp . "`.`id_cat`='".$this->fltr2."'";
        if( $this->fltr3 ) $q = $q." AND `" . TblModCatalogProp . "`.`id_group`='".$this->fltr3."'";
        if( $this->fltr_visible) $q .= " AND `" . TblModCatalogProp . "`.`visible`='2'";
        if( $this->fltr_exist) $q .= " AND `" . TblModCatalogProp . "`.`exist`='1'";
        if( $this->fltr_new) $q .= " AND `" . TblModCatalogProp . "`.`new`='1'";
        if( $this->fltr_best) $q .= " AND `" . TblModCatalogProp . "`.`best`='1'";
        if( $this->srch ){
            $q .= " AND (" . $str_like . "
                        OR LOWER(REPLACE(REPLACE(`" . TblModCatalogPropSprName . "`.name, ' ', ''), '-', '')) LIKE '%" . $search_keywords_no_space . "%'
                        OR `" . TblModCatalogProp . "`.`id` = '" . $search_keywords . "'";
                        if ( isset($this->settings['number_name']) AND $this->settings['number_name']=='1' ){
                            $q .= " OR `" . TblModCatalogProp . "`.`number_name` LIKE '%" . $search_keywords . "%'";
                        }
                        if ( isset($this->settings['cod_pli']) AND $this->settings['cod_pli']=='1' ){
                            $q .= " OR `" . TblModCatalogProp . "`.`cod_pli` = '" . $search_keywords . "'";
                        }
                        if ( isset($this->settings['art_num']) AND $this->settings['art_num']=='1' ){
                            $q .= " OR `" . TblModCatalogProp . "`.`art_num` = '" . $search_keywords . "'";
                        }
                        if ( isset($this->settings['barcode']) AND $this->settings['barcode']=='1' ){
                            $q .= " OR `" . TblModCatalogProp . "`.`barcode` = '" . $search_keywords . "'";
                        }
            $q .=")";
        }
        if( $this->srch ){
            $q .= " ORDER BY relev2 DESC, relev DESC";
        }
        else{
            $q .= " ORDER BY `".$this->sort."` ".$this->asc_desc;
        }
        if($limit=='limit') $q = $q." LIMIT ".$this->start.", ".$this->display;


        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result.' $this->user_id='.$this->user_id;
        if( !$res OR !$this->Right->result )return false;
        $rows = $this->Right->db_GetNumRows();
        $arr = array();
        for( $i = 0; $i < $rows; $i++ ){
                $arr[$i] = $this->Right->db_FetchAssoc();
        }
        return $arr;
   }//end of function GetContent()

   // ================================================================================================
   // Function : ShowContent
   // Date : 27.03.2006
   // Returns : true,false / Void
   // Description : Show content of the catalogue
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function ShowContent()
   {

    /* Write Form Header */
    $this->Form->WriteHeader( $this->script );
    $this->Form->Hidden('module', $this->module);
    if( empty($this->parent_module)) {
        $ModulesPlug = &check_init('ModulesPlug', 'ModulesPlug');
        $this->parent_module = $ModulesPlug->GetModuleIdByPath ( 'mod_catalog/catalog.backend.php' );
    }
    if( empty($this->parent_display) ) $this->parent_display=20;
    if( empty($this->parent_start) ) $this->parent_start = 0;
    $parent_script = $_SERVER['PHP_SELF'].'?module='.$this->parent_module.'&display='.$this->parent_display.'&start='.$this->parent_start.'&sort='.$this->parent_sort.'&fltr='.$this->parent_fltr.'&fln='.$this->parent_fln;
    ?>
     <table border="0" cellpadding="5" cellspacing="0" width="100%">
      <tr>
       <td class="levels_tree">
        <table border="0">
            <tr>
                <td><a href="<?=$parent_script;?>" title="<?=$this->multi['TXT_ROOT_CATEGORY'];?>"><img src="images/icons/categ.png" border="0" alt="<?=$this->multi['TXT_CATALOG_STRUCTURE'];?>" title="<?=$this->multi['TXT_CATALOG_STRUCTURE'];?>" /></a></td>
                <td><a href="<?=$parent_script;?>" title="<?=$this->multi['TXT_ROOT_CATEGORY'];?>"><h4 style="margin:0px; padding:0px;"><?=$this->multi['TXT_CATALOG_STRUCTURE'];?></h4></a></td>
            </tr>
        </table>

        <?//$starttime = microtime();?>
        <?=$this->show_levels_tree_back_end(0, $parent_script, NULL);?>
        <?//echo $this->microtime_diff($starttime, microtime());?>

        <img src="images/spacer.gif" width="200" height="1" />
       </td>
       <td valign="top">
        <div class="path_hleb">
            <?
            if ( $this->id_cat>0 ) {
                $parent_script = $_SERVER['PHP_SELF'].'?module='.$this->parent_module.'&display='.$this->parent_display.'&start='.$this->parent_start.'&sort='.$this->parent_sort;
                $this->ShowPathToLevel($this->id_cat, NULL, $parent_script );
                echo ' <span style="color:#000000; font-size:8pt; font-weight:bold;">('.$this->Msg->show_text('FLD_CONTENT').')</span>';
            }
            ?>
        </div>
        <?
        //$starttime = microtime();
        $this->ShowContentFilters();
        //echo $this->microtime_diff($starttime, microtime());
        ?>
        <div id='content'><?=$this->ShowContentAll();?></div>
       </td>
      </tr>
     </table>
     <?
    $this->Form->WriteFooter();
   } //end of fuinction ShowContent()


   // ================================================================================================
   // Function : ShowContentAll
   // Date : 19.03.2008
   // Returns : true,false / Void
   // Description : Show content of the catalogue
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function ShowContentAll($rows=NULL)
   {
       $arr_rows = $this->GetContent('nolimit');
       if( !is_array($arr_rows)) return false;
       $rows = count($arr_rows);
       $this->ShowJS();

        /* Write Table Part */
        AdminHTML::TablePartH();

        /* Write Links on Pages */
        ?>
        <tr>
         <td colspan="23">
          <?
          $script1 = 'module='.$this->module.'&fltr='.$this->fltr;
          $script1 = $_SERVER['PHP_SELF']."?$script1";

          $this->Form->WriteLinkPages( $this->script, $rows, $this->display, $this->start, $this->sort );
          ?>
         </td>
        </tr>
        <tr>
         <td colspan="7">
          <?
          if($this->Right->IsWrite()) $this->Form->WriteTopPanel( $this->script, 1 );
          if($this->Right->IsDelete()) $this->Form->WriteTopPanel( $this->script, 2 );
          if($this->Right->IsUpdate()){
            ?><a CLASS="toolbar" href="javascript:$('#task').val('show_move_to_category');$('#<?php echo $this->Form->name;?>').submit();" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('move','','images/icons/move_f2.png',1);"><img src="images/icons/move.png" alt="<?=$this->Msg->show_text('_BUTTON_MOVE_TO_CATEGORY');?>" title="<?=$this->Msg->show_text('_BUTTON_MOVE_TO_CATEGORY');?>" align="center" name="move" border="0" /><?=$this->Msg->show_text('_BUTTON_MOVE_TO_CATEGORY');?></a><?
          }
          ?>
         </td>
        </tr>
        <tr>
         <td>
          <div name="load" id="load"></div>
          <div id="result"></div>
          <div id="debug">
           <?
           //$starttime = microtime();
           $this->ShowContentHTML();
           //echo $this->microtime_diff($starttime, microtime());
           ?>
           </div>
         </td>
        </tr>
        <?
        AdminHTML::TablePartF();
   }//end of function ShowContentAll();

   function GetShareInArray($level = NULL, $default_val = NULL, $mas = NULL, $spacer = NULL, $show_content = 1, $front_back = 'back', $show_sublevels = 1)
    {
        $db = new DB();
        $tmp_db = new DB();
        $q = "SELECT `".TblModShare."`.*, `".TblModShareTxt."`.`pname`
              FROM `".TblModShare."`, `".TblModShareTxt."`
              WHERE `".TblModShare."`.`level`='".$level."'
              AND `".TblModShare."`.`id`=`".TblModShareTxt."`.`cod`
              AND `".TblModShareTxt."`.`lang_id`='".$this->lang_id."'";
        //echo " tar=".$front_back;
        if ( $front_back=='front' ) $q = $q." AND `visible`='2'";
        //if ( $front_back=='back' ) $q = $q." AND `visible`='2'";
        $q = $q." order by `move` ";
        $res = $db->db_Query( $q );
        //echo '<br>q='.$q.' res='.$res.' $db->result='.$db->result;
        if( !$res )return false;
        $rows = $db->db_GetNumRows();
        //echo '<br> $rows='.$rows;
        //echo '<br> $show_content='.$show_content;
        $mas[''] = $default_val;
        for( $i = 0; $i < $rows; $i++ )
        {
            $row=$db->db_FetchAssoc();
            $mas[''.$row['id']] = $spacer.'- '.stripslashes($row['pname']);

            $tmp_q = "SELECT `id` FROM ".TblModPages." WHERE `level`=".$row['id'];
            $res = $tmp_db->db_Query( $tmp_q );
            $tmp_rows = NULL;
            if( $res ) $tmp_rows = $tmp_db->db_GetNumRows();
            //echo '<br> $tmp_rows='.$tmp_rows;

            //----------------- show subcategory ----------------------------
            if( $show_sublevels==1 ){
                if ($tmp_rows>0) $mas = $mas + $this->GetShareInArray($row['id'], $default_val, $mas, $spacer.'&nbsp;&nbsp;&nbsp;', $show_content, $front_back, $show_sublevels);
            }
            //------------------------------------------------------------------
        }
        return $mas;
    }

   // ================================================================================================
   // Function : ShowContentHTML
   // Version : 1.0.0
   // Date : 19.03.2008
   //
   // Parms :
   // Returns : true,false / Void
   // Description : Show content of the catalogue
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 19.03.2008
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowContentHTML()
   {
        $arr_rows = $this->GetContent('limit');
        $rows = count($arr_rows);

    if( $this->asc_desc=='asc' ) $asc_desc_new='desc';
    else $asc_desc_new='asc';
    //echo '<br>$this->asc_desc='.$this->asc_desc;
    $script2 = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&task=show&fltr='.$this->fltr.'&fltr2='.$this->fltr2.'&fltr3='.$this->fltr3.'&id_cat='.$this->id_cat.'&asc_desc='.$asc_desc_new;
    //$script2 = $_SERVER['PHP_SELF']."?$script2";
    $script2 = "/admin/index.php?$script2";

    if($rows>$this->display) $ch = $this->display;
    else $ch = $rows;

   $txtSortData = $this->multi['_TXT_SORT_DATA'];
   $editData = $this->multi['TXT_EDIT'];
   ?>
   <table border="0" cellpadding="0" cellspacing="1">
    <tr>
    <Th class="THead"><input value="0" id="cAll" onclick="if (this.value == '1') {unCheckAll(<?=$ch;?>); this.value = '0';} else {checkAll(<?=$ch;?>); this.value = '1';}" type="checkbox"></Th>
    <Th class="THead"><?=$this->Form->LinkSort( $this->sort, 'id', $script2, $this->asc_desc, $this->multi['FLD_ID'], $txtSortData);?></Th>
    <?if ( isset($this->settings['cod_pli']) AND $this->settings['cod_pli']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_COD_PLI'];?></Th>
    <?}
    if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_NAME'];?></Th>
    <?}
    if ( isset($this->settings['img']) AND $this->settings['img']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_IMG'];?></Th>
    <?}
    if ( isset($this->settings['files']) AND $this->settings['files']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_FILES']?></Th>
    <?}
    //if ( empty($this->id_cat) ) { ?>
    <Th class="THead"><?=$this->multi['FLD_CATEGORY'];?></Th>
    <?//}
    if ( isset($this->settings['manufac']) AND $this->settings['manufac']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_MANUFAC'];?></Th>
    <?}

    ?>
    <Th class="THead"><?=$this->Form->LinkSort( $this->sort, 'visible', $script2, $this->asc_desc, $this->multi['FLD_VISIBLE'], $txtSortData);?></Th>
    <?if ( isset($this->settings['exist']) AND $this->settings['exist']=='1' ) {?>
    <Th class="THead"><?=$this->Form->LinkSort( $this->sort, 'exist', $script2, $this->asc_desc, $this->multi['FLD_EXIST'], $txtSortData);?></Th>
    <?}?>
    <?if ( isset($this->settings['new']) AND $this->settings['new']=='1' ) {?>
    <Th class="THead"><?=$this->Form->LinkSort( $this->sort, 'new', $script2, $this->asc_desc, $this->multi['FLD_NEW'], $txtSortData);?></Th>
    <?}?>
    <?if ( isset($this->settings['best']) AND $this->settings['best']=='1' ) {?>
    <Th class="THead"><?=$this->Form->LinkSort( $this->sort, 'best', $script2, $this->asc_desc, $this->multi['FLD_BEST'], $txtSortData);?></Th>
    <?}

    if ( isset($this->settings['id_group']) AND $this->settings['id_group']=='1' ) { ?>
    <Th class="THead"><?=$this->multi['FLD_GROUP'];?></Th>
    <?}
    if ( isset($this->settings['short_descr']) AND $this->settings['short_descr']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_SHORT_DESCR'];?></Th>
    <?}?>
    <?if ( isset($this->settings['full_descr']) AND $this->settings['full_descr']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_FULL_DESCR'];?></Th>
    <?}?>
    <?if ( isset($this->settings['specif']) AND $this->settings['specif']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_SPECIF'];?></Th>
    <?}?>
    <?if ( isset($this->settings['reviews']) AND $this->settings['reviews']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_REVIEWS'];?></Th>
    <?}?>
    <?if ( isset($this->settings['support']) AND $this->settings['support']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_SUPPORT'];?></Th>
    <?}?>
    <?if ( isset($this->settings['number_name']) AND $this->settings['number_name']=='1' ) {?>
    <Th class="THead"><?=$this->Form->LinkSort( $this->sort, 'number_name', $script2, $this->asc_desc, $this->multi['FLD_NUMBER_NAME'], $txtSortData);?></Th>
    <?}?>
    <?if ( isset($this->settings['price']) AND $this->settings['price']=='1' ) {?>
    <Th class="THead"><?=$this->Form->LinkSort( $this->sort, 'price', $script2, $this->asc_desc, $this->multi['FLD_PRICE'], $txtSortData);?></Th>
    <?}?>
    <?if ( isset($this->settings['opt_price']) AND $this->settings['opt_price']=='1' ) {?>
    <Th class="THead"><?=$this->Form->LinkSort( $this->sort, 'opt_price', $script2, $this->asc_desc, $this->multi['FLD_OPT_PRICE'], $txtSortData);?></Th>
    <?}?>
    <?if ( isset($this->settings['price_levels']) AND $this->settings['price_levels']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_PRICE_LEVELS'];?></Th>
    <?}?>
    <?if ( isset($this->settings['grnt']) AND $this->settings['grnt']=='1' ) {?>
    <Th class="THead"><?=$this->Form->LinkSort( $this->sort, 'grnt', $script2, $this->asc_desc, $this->multi['FLD_GUARANTEE'], $txtSortData);?></Th>
    <?}?>
    <?if ( isset($this->settings['dt']) AND $this->settings['dt']=='1' ) {?>
    <Th class="THead"><?=$this->Form->LinkSort($this->sort, 'dt', $script2, $this->asc_desc, $this->multi['FLD_DATE'], $txtSortData);?></Th>
    <?}?>
    <?if ( isset($this->settings['relat_prop']) AND $this->settings['relat_prop']=='1' ) {?>
    <Th class="THead"><?=$this->multi['FLD_RELAT_PROP'];?></Th>
    <?}?>
    <Th class="THead"><?=$this->Form->LinkSort( $this->sort, 'move', $script2, $this->asc_desc, $this->multi['FLD_DISPLAY'], $txtSortData);?></Th>
    <Th class="THead"><?=$this->multi['FLD_INFO'];?></Th>
   <?

    $a = $rows;
    $j = 0;
    $up = 0;
    $down = 0;
    $style1 = 'TR1';
    $style2 = 'TR2';
    /*
    if ( isset($this->settings['img']) AND $this->settings['img']=='1' )
        $addImages = $this->multi['TXT_ADD_IMAGES'];
    if ( isset($this->settings['files']) AND $this->settings['files']=='1' )
        $addFiles = $this->multi['TXT_ADD_FILES'];
    if ( isset($this->settings['responses']) AND $this->settings['responses']=='1' )
        $responses = $this->Msg->show_text('TXT_READ_RESPONSES');
    if ( isset($this->settings['rating']) AND $this->settings['rating']=='1' )
        $ratings = $this->Msg->show_text('FLD_AVERAGE_RATING');
    */
    for( $i = 0; $i < $rows; $i++ )
    {
      $row = $arr_rows[$i];
      //$row = $row_arr[$i];

      if ( (float)$i/2 == round( $i/2 ) ) $class=$style1;
      else $class = $style2;
      ?>
      <tr class="<?=$class;?>">
       <td><?=$this->Form->CheckBox( "id_del[]", $row['id'], null, "check".$i );?></td>
       <td>
           <?
            $this->Form->Link( $this->script."&task=edit&id=".$row['id'], stripslashes( $row['id'] ), $editData );
           ?>
      </td>
      <?
      if ( isset($this->settings['cod_pli']) AND $this->settings['cod_pli']=='1' ) {
        ?><td><?=stripslashes($row['cod_pli']);?></td><?
      }
      if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {
        ?><td align="left"><?
        $name = stripslashes( $row['name'] );
        if($this->settings['name_quick_edit']){
            $this->Form->TextArea('propname'.$row['id'], $name, 3, 25, 'style="width:200px;" id="propname'.$row['id'].'"');
            ?>
            <div style="height: 25px;">
                <div style="float:left;"><?$this->Form->Button('save_name',$this->multi['TXT_SAVE'], NULL, "onclick='SaveName(\"".$row['id']."\"); return false;'");?></div>
                <div style="height: 25px;" id="propnameres<?=$row['id'];?>"></div>
            </div>
            <?
        }else echo $name;
        $this->Form->Hidden("id_prop_copy","");
        ?><br /><input type="button" value="Создать копиию" onclick="<?=$this->Form->name?>.task.value='new_by_copy';<?=$this->Form->name?>.id.value='<?=$row['id'];?>';<?=$this->Form->name?>.submit();" /><?
      }

      if ( isset($this->settings['img']) AND $this->settings['img']=='1' ) {
       ?><td align="center"><?
       //$img_arr = $this->GetPicture($row['id'], 'back');
       $img_cnt = $this->GetPictureCount($row['id']);
       if($img_cnt>0){
          echo '<a href="'.$this->script.'&task=showpicture&id='.$row['id'].'">';
          if(!empty($row['first_img'])){
              echo $this->ShowCurrentImage($row['first_img'], 'size_auto=100', 85, NULL, 'border="0" alt="'.htmlspecialchars(stripslashes($row['first_img_alt'])).'" title="'.htmlspecialchars(stripslashes($row['first_img_title'])).'"', $row['id'], false); echo '<br>';
          }
          echo $this->multi['TXT_ADD_EDIT'].'</a> ['.$img_cnt.']';
       }
       else echo '<a href="'.$this->script.'&task=showpicture&id='.$row['id'].'">'.$this->multi['TXT_ADD_IMAGES'].'</a>';
      }

      if ( isset($this->settings['files']) AND $this->settings['files']=='1' ) {
       ?><td align="center"><?
       $files_arr = $this->GetFiles($row['id'], 'back');
       if(count($files_arr)>0) echo '<a href="'.$this->script.'&task=showfiles&id='.$row['id'].'">'.$this->multi['TXT_ADD_EDIT'].'</a>'.'['.count($files_arr).']';
       else echo '<a href="'.$this->script.'&task=showfiles&id='.$row['id'].'">'.$this->multi['TXT_ADD_FILES'].'</a>';
      }

      //if ( empty($this->id_cat) ) {
       ?><td align="center"><?
       echo stripslashes( $row['cat_name'] );
      //}

      if ( isset($this->settings['manufac']) AND $this->settings['manufac']=='1' ) {
        echo '<TD>';
        echo $this->Spr->GetNameByCod( TblModCatalogSprManufac, $row['id_manufac'] );
        echo '</TD>';
      }
      ?>
        <td align="center" id="visible<?=$row['id'];?>">
        <?
        $this->ShowVisibility($row['id'], $row['visible']);
        if ( isset($this->settings['exist']) AND $this->settings['exist']=='1' ) {
            ?><td align="center" id="Exist<?=$row['id']?>"><?
            $this->ShowStatusPerekluchatel($row['id'],"Exist",$row['exist'], 'changeExist');
            ?></td><?
        }
        if ( isset($this->settings['new']) AND $this->settings['new']=='1' ) {
            ?><td align="center" id="New<?=$row['id']?>"><?
            $this->ShowStatusPerekluchatel($row['id'],"New",$row['new'], 'changeNew');
            ?></td><?
        }
        if ( isset($this->settings['best']) AND $this->settings['best']=='1' ) {
            ?><td align="center" id="Best<?=$row['id']?>"><?
            $this->ShowStatusPerekluchatel($row['id'],"Best",$row['best'], 'changeBest');
            ?></td><?
        }

      if ( isset($this->settings['id_group']) AND $this->settings['id_group']=='1' ) {
       ?><td align="center"><?
       $groups_arr=$this->GetGroupsByIdProp($row['id']);
       $grp_cnt = count($groups_arr);
       for($grp=0;$grp<$grp_cnt;$grp++){
           ( (float)$grp/2 == round( $grp/2 ) ? $grp_c=$class : ( $class==$style1 ? $grp_c=$style2 : $grp_c=$style1) );
           ?><div class="<?=$grp_c;?>"><?=$this->Spr->GetNameByCod( TblModCatalogSprGroup, $groups_arr[$grp], $this->lang_id, 1);?></div><?
       }
       ?></td><?
      }
      if ( isset($this->settings['short_descr']) AND $this->settings['short_descr']=='1' ) {
       ?><td align="center"><?  if( strlen(trim( $row['short_descr'] ))>0 ) $this->Form->ButtonCheck();
      }
      if ( isset($this->settings['full_descr']) AND $this->settings['full_descr']=='1' ) {
       ?><td align="center"><?  if( strlen(trim( $row['full_descr'] ))>0 ) $this->Form->ButtonCheck();
      }
      if ( isset($this->settings['specif']) AND $this->settings['specif']=='1' ) {
       ?><td align="center"><?  if( strlen(trim( $this->Spr->GetNameByCod( TblModCatalogPropSprSpecif, $row['id'] ) ))>0 ) $this->Form->ButtonCheck();
      }
      if ( isset($this->settings['reviews']) AND $this->settings['reviews']=='1' ) {
       ?><td align="center"><?  if( strlen(trim( $this->Spr->GetNameByCod( TblModCatalogPropSprReviews, $row['id'] ) ))>0 ) $this->Form->ButtonCheck();
      }
      if ( isset($this->settings['support']) AND $this->settings['support']=='1' ) {
       ?><td align="center"><?  if( strlen(trim( $this->Spr->GetNameByCod( TblModCatalogPropSprSupport, $row['id'] ) ))>0 ) $this->Form->ButtonCheck();
      }
      if ( isset($this->settings['number_name']) AND $this->settings['number_name']=='1' ) {
       ?><td align="center"><?  echo $row['number_name'];
      }

      if ( isset($this->settings['price']) AND $this->settings['price']=='1' ) {
        ?><td align="center" nowrap="nowrap"><?
        $this->ShowPrice($row['id'], $row['price'], $row['price_currency']);
        ?>
        <div style="height: 35px;">
                <div><?$this->Form->Button('saveprice',$this->multi['TXT_SAVE'], NULL, "onclick='SavePrice(\"proppriceres".$row['id']."\", \"".$row['id']."\"); return false;'");?></div>
                <div style="height: 25px;" id="proppriceres<?=$row['id'];?>"></div>
        </div>
        </td><?
      }
      if ( isset($this->settings['opt_price']) AND $this->settings['opt_price']=='1' ) {
        ?><td align="center" nowrap="nowrap"><?
        $this->ShowPriceOpt($row['id'], $row['opt_price'], $row['opt_price_currency']);
        ?>
        <div style="height: 35px;">
                <div><?$this->Form->Button('savepriceopt',$this->multi['TXT_SAVE'], NULL, "onclick='SavePriceOpt(\"proppriceoptres".$row['id']."\", \"".$row['id']."\"); return false;'");?></div>
                <div style="height: 25px;" id="proppriceoptres<?=$row['id'];?>"></div>
        </div>
        </td><?
      }
      if ( isset($this->settings['price_levels']) AND $this->settings['price_levels']=='1' ) {
        ?><td align="center" nowrap><?
        $arr_pice = $this->GetPriceLevels($row['id']);
        //echo '$arr_pice='.$arr_pice;print_r($arr_pice); echo '<br>';
        if( is_array($arr_pice) AND count($arr_pice)>0){
            ?><table border="0" cellpadding="0" cellspacing="1" class="<?=$class;?>"><?
            for($itmp=0;$itmp<count($arr_pice);$itmp++){
                if( isset($this->settings['price_levels_currency']) AND $this->settings['price_levels_currency']=='1' ){
                    $currency_data = $this->Currencies->GetCurrencyData($arr_pice[$itmp]['currency']);
                    $prefix = stripslashes($currency_data['pref']);
                    $sufix = stripslashes($currency_data['suf']);
                }
                else {
                    $prefix = NULL;
                    $sufix = NULL;
                }
                ?>
                 <tr>
                  <td align="left"><?=stripslashes($arr_pice[$itmp]['qnt_from']).'~'.stripslashes($arr_pice[$itmp]['qnt_to']);?></td>
                  <td align="left">=</td>
                  <td align="left"><?=$prefix.stripslashes($arr_pice[$itmp]['price_level']).$sufix;?></td>
                 </tr>
                <?
            }
            ?></table><?
        }
        ?></td><?
      }

      if ( isset($this->settings['grnt']) AND $this->settings['grnt']=='1' ) {
       ?><td align="center"><?  echo $row['grnt'];
      }
      if ( isset($this->settings['dt']) AND $this->settings['dt']=='1' ) {
       ?><td align="center"><?  echo $row['dt'];
      }

      if ( isset($this->settings['relat_prop']) AND $this->settings['relat_prop']=='1' ) {
        ?><td style="font-weight:normal;"><?
        $arr_relat_prop=null;
        $arr_relat_prop = $this->GetRelatProp( $row['id'],'mod_catalog_prop_relat' );
        //$script2 = $this->script."&task=control_relat_prop_form&amp;id_prop1=".$row['id'];
        $script2 = "/admin/index.php?module=96&id_prop=".$row['id'];
        if ( !is_array($arr_relat_prop) OR count($arr_relat_prop)==0) $this->Form->Link( $script2, $this->multi['TXT_ADD']);
        else {
            $this->ShowRelatProp($arr_relat_prop, $row['id'] );
            $this->Form->Link( $script2, $this->multi['TXT_EDIT']);
        }
        ?></td><?
      }

     ?>
     <td align="center" nowrap><?
        $url = '/modules/mod_catalog/catalogcontent.backend.php?'.$this->script_ajax;
        if( $up!=0 ){
          $this->Form->ButtonUpAjax($this->script, $row['id'], $url, 'debug', 'move', $row['move']);
          /*?><a href=<?=$this->script?>&task=up&move=<?=$row['move'];?> title="UP" onClick="up_down('<?=$url;?>', 'debug', 'up', 'move', '<?=$row['move'];?>'); return false;"><?=$this->Form->ButtonUp( $row['id'] );?></a><?*/
        }
        else{?><img src="images/spacer.gif" width="12"/><?}
        //for replace
        ?>&nbsp;<?$this->Form->TextBoxReplace($url, 'debug', 'move', $row['move'], $row['id']);
        if( $i!=($rows-1) ){
          $this->Form->ButtonDownAjax($this->script, $row['id'], $url, 'debug', 'move', $row['move']);
          /*?><a href=<?=$this->script?>&task=down&move=<?=$row['move'];?> title="DOWN" onClick="up_down('<?=$url;?>', 'debug', 'down', 'move', '<?=$row['move'];?>'); return false;"><?=$this->Form->ButtonDown( $row['id'] );?></a><?*/
        }
        else{?><img src="images/spacer.gif" width="12"/><?}

        $up=$row['id'];
        $a=$a-1;
        ?>
       </td>
       <td>
        <img src="images/icons/info2.gif" onmouseover="return overlib('<?=$this->multi['FLD_TRANSLIT'].': '.$this->GetTranslitById( $row['id_cat'], $row['id'], $this->lang_id );?>',WRAP);" onmouseout="nd();">
        <br/><?
        if ( isset($this->settings['responses']) AND $this->settings['responses']=='1' ) {
            if( $this->GetCountResponsesByIdProp($row['id'])>0){
                $ModulesPlug = new ModulesPlug();
                $id_module = $ModulesPlug->GetModuleIdByPath ( '/modules/mod_catalog/catalog_response.backend.php' );
                ?><br/><a href="<?=$_SERVER['PHP_SELF'];?>?module=<?=$id_module;?>&amp;fltr2=<?=$row['id'];?>" ><?=$this->multi['TXT_READ_RESPONSES'];?></a><?
            }
        }
        if ( isset($this->settings['rating']) AND $this->settings['rating']=='1' ) {
            $rating = $this->GetAverageRatingByIdProp($row['id'], 'back');
            if( $rating>0){
                echo '<br/>'.$this->multi['FLD_AVERAGE_RATING'].': '.$rating;
            }
        }
        ?>
       </td>
       <?
    } //-- end for
    ?>
    </tr>
   </table>
   <?
   }//end of function ShowContentHTML()

    function ShowStatusPerekluchatel($id,$div_id, $visible, $task="change_visible")
    {
        if( $visible == 0 ) { ?><a href="#" onclick="QuickChangeData('<?=$div_id.$id;?>', 'module=<?=$this->module;?>&task=<?=$task?>&new_visible=1&id=<?=$id;?>');return false;"><?=$this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/publish_x.png', $this->multi['TXT_UNVISIBLE'], 'border=0' );?></a><?}
        //if( $visible == 1 ) $this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/publish_r.png', $this->multi['TXT_VISIBLE_ONLY_ON_BACKEND'], 'border=0' );
        if( $visible == 1 ) { ?><a href="#" onclick="QuickChangeData('<?=$div_id.$id;?>', 'module=<?=$this->module;?>&task=<?=$task?>&new_visible=0&id=<?=$id;?>');return false;"><?=$this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/tick.png', $this->multi['TXT_VISIBLE'], 'border=0' );?></a><?}

        return;
    }//end of function ShowStatusPerekluchatel

    function ChangeNewProp($id, $new_visible=0,$field)
    {
        $q = "UPDATE `".TblModCatalogProp."` SET `$field`='".$new_visible."' WHERE `id`='".$id."';";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
//        echo '<br />$q='.$q.' $this->Right->result='.$this->Right->result;
        if( !$res OR !$this->Right->result) return false;
        return true;
    }//end of function ChangeVisibleProp


    /**
    * Class method ShowVisibility
    * show visibility of product
    * @param
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 31.01.2011
    * @return
    */
    function ShowVisibility($id, $visible)
    {
        if( $visible == 0 ) { ?><a href="#" onclick="QuickChangeData('visible<?=$id;?>', 'module=<?=$this->module;?>&task=change_visible&new_visible=2&id=<?=$id;?>');return false;"><?=$this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/publish_x.png', $this->multi['TXT_UNVISIBLE'], 'border=0' );?></a><?}
        //if( $visible == 1 ) $this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/publish_r.png', $this->multi['TXT_VISIBLE_ONLY_ON_BACKEND'], 'border=0' );
        if( $visible == 2 ) { ?><a href="#" onclick="QuickChangeData('visible<?=$id;?>', 'module=<?=$this->module;?>&task=change_visible&new_visible=0&id=<?=$id;?>');return false;"><?=$this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/tick.png', $this->multi['TXT_VISIBLE'], 'border=0' );?></a><?}

        return;
    }//end of function ShowVisibility

    /**
    * Class method ChangeVisibleProp
    * change visibility of product
    * @param integer $id - id of the product
    * @param $new_visible - new value for field visible
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 31.01.2011
    * @return true or false
    */
    function ChangeVisibleProp($id, $new_visible=0)
    {
        $q = "UPDATE `".TblModCatalogProp."` SET `visible`='".$new_visible."' WHERE `id`='".$id."';";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br />$q='.$q.' $this->Right->result='.$this->Right->result;
        if( !$res OR !$this->Right->result) return false;
        return true;
    }//end of function ChangeVisibleProp

    /**
    * Class method ShowPrice
    * show price of product
    * @param
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 12.05.2011
    * @return
    */
    function ShowPrice($id, $price, $price_currency)
    {
        if($this->settings['price_quick_edit']){
            $this->Form->Textbox('propprice['.$id.']', $price, 5, 'id="price'.$id.'"');
            if( isset($this->settings['price_currency']) AND $this->settings['price_currency']=='1' ){
                if( empty($price_currency) ) $price_currency = $this->Currencies->defCurrencyData['id'];
                $this->Form->Select($this->Currencies->listShortNames, 'propprice_currency['.$id.']', $price_currency, 5, 'id="price_currency'.$id.'"');
            }
        }
        else{
            if( isset($this->settings['price_currency']) AND $this->settings['price_currency']=='1' ){
                $currency_data = $this->Currencies->GetCurrencyData($row['price_currency']);
                $prefix = stripslashes($currency_data['pref']);
                $sufix = stripslashes($currency_data['suf']);
            }
            else {
                $prefix = NULL;
                $sufix = NULL;
            }
            if( !empty($row['price']) ) echo $prefix.stripslashes($row['price']).$sufix;
        }
        return;
    }//end of function ShowPrice

    /**
    * Class method SavePrice
    * save price of product
    * @param integer $id - id of the product
    * @param $price - new value of price
    * @param $price_currency - new value of price currency
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 12.05.2011
    * @return true or false
    */
    function SavePrice($id, $price=0, $price_currency=NULL)
    {
        $q = "UPDATE `".TblModCatalogProp."` SET `price`='".$price."'";
        if( !empty($price_currency)) $q .=", `price_currency`='".$price_currency."'";
        $q .= " WHERE `id`='".$id."';";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br />$q='.$q.' $this->Right->result='.$this->Right->result;
        if( !$res OR !$this->Right->result) return false;
        return true;
    }//end of function SavePrice

    /**
    * Class method ShowPriceOpt
    * show price of product
    * @param
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 12.05.2011
    * @return
    */
    function ShowPriceOpt($id, $price, $price_currency)
    {
        if($this->settings['price_quick_edit']){
            $this->Form->Textbox('proppriceopt['.$id.']', $price, 5, 'id="price'.$id.'"');
            if( isset($this->settings['price_currency']) AND $this->settings['price_currency']=='1' ){
                if( empty($price_currency) ) $price_currency = $this->Currencies->defCurrencyData['id'];
                $this->Form->Select($this->Currencies->listShortNames, 'proppriceopt_currency['.$id.']', $price_currency, 5, 'id="price_currency'.$id.'"');
            }
        }
        else{
            if( isset($this->settings['price_currency']) AND $this->settings['price_currency']=='1' ){
                $currency_data = $this->Currencies->GetCurrencyData($row['price_currency']);
                $prefix = stripslashes($currency_data['pref']);
                $sufix = stripslashes($currency_data['suf']);
            }
            else {
                $prefix = NULL;
                $sufix = NULL;
            }
            if( !empty($row['price']) ) echo $prefix.stripslashes($row['price']).$sufix;
        }
        return;
    }//end of function ShowPriceOpt

    /**
    * Class method SavePriceOpt
    * save price of product
    * @param integer $id - id of the product
    * @param $price - new value of opt_price
    * @param $price_currency - new value of opt_price_currency
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 12.05.2011
    * @return true or false
    */
    function SavePriceOpt($id, $price=0, $price_currency=NULL)
    {
        $q = "UPDATE `".TblModCatalogProp."` SET `opt_price`='".$price."'";
        if( !empty($price_currency)) $q .=", `opt_price_currency`='".$price_currency."'";
        $q .= " WHERE `id`='".$id."';";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br />$q='.$q.' $this->Right->result='.$this->Right->result;
        if( !$res OR !$this->Right->result) return false;
        return true;
    }//end of function SavePriceOpt

   // ================================================================================================
   // Function : ShowContentFilters
   // Date : 27.03.2006
   // Returns : true,false / Void
   // Description : Show content of the catalogue
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function ShowContentFilters()
   {
     /* Write Table Part */
     AdminHTML::PanelSimpleH();
     ?>
      <tr valign="top">
       <td>
         <table border="0" cellpadding="2" cellspacing="1" width="400">
          <tr class="tr2">
           <td align="center" colspan="2" nowrap="nowrap">
               <h4 style="padding:0px; margin:0px;"><?=$this->multi['TXT_SEARCH'];?>: <?$this->Form->TextBox('srch', $this->srch, 40, 'style="width:330px;"');?></h4>
               <div style="color: #ACACAC;font-size:10px; margin: 0px 0px 10px 0px;">
               <?
               echo $this->multi['FLD_ID'];
               if( isset($this->settings['cod_pli']) AND $this->settings['cod_pli']==1){
                   echo ', '.$this->multi['FLD_COD_PLI'];
               }
               if( isset($this->settings['name']) AND $this->settings['name']==1){
                   echo ', '.$this->multi['FLD_NAME'];
               }
               if ( isset($this->settings['number_name']) AND $this->settings['number_name']=='1' ){
                   echo ', '.$this->multi['FLD_NUMBER_NAME'];
               }
               if( isset($this->settings['art_num']) AND $this->settings['art_num']==1){
                   echo ', '.$this->multi['FLD_ART_NUM'];
               }
               if( isset($this->settings['barcode']) AND $this->settings['barcode']==1){
                   echo ', '.$this->multi['FLD_BARCODE'];
               }
               ?>
               </div>
           </td>
          </tr>
          <?
          //if ( !$this->id_cat ) {
          ?>
          <tr class="tr2">
           <td align="left" width="100"><?=$this->multi['FLD_CATEGORY'];?></td>
           <td align="left">
           <?
            $mas = $this->GetCatalogInArray(NULL, $this->multi['TXT_SELECT_CATEGORY'], NULL, NULL, 0, 'back');
            $name_fld = 'fltr2';
            $scriplink = $this->script;
            ?><div align="left"><?$this->Form->Select( $mas, $name_fld, 'categ='.$this->fltr2, NULL, 'style="width:250px;"' );?></div><?

            //$this->Spr->ShowActSprInCombo(TblModCatalogSprName, 'fltr2', $this->fltr2, $_SERVER["QUERY_STRING"]);
            ?>
           </td>
          </tr>
          <?
          //}
          if ( isset($this->settings['manufac']) AND $this->settings['manufac']=='1' ) {?>
          <tr class="tr2">
           <td align="left"><?=$this->multi['FLD_MANUFAC']?></td>
           <td align="left"><?$this->Spr->ShowInComboBox(TblModCatalogSprManufac, 'fltr', $this->fltr, 0, NULL, 'move', 'asc', 'style="width:250px;"');?></td>
          </tr>
          <?}
          if ( isset($this->settings['id_group']) AND $this->settings['id_group']=='1' ) {
            //$Dealers = new Dealer();
            //$mas_d = $Dealers->GetDealersToArray('back');

            $mas_d[''] = '';
            ksort($mas_d);
          ?>
          <tr class="tr2">
           <td align="left"><?=$this->multi['FLD_GROUP'];?></td>
           <td align="left"><?$this->Spr->ShowInComboBox(TblModCatalogSprGroup, 'fltr3', $this->fltr3, 0, NULL, 'move', 'asc', 'style="width:250px;"');?></td>
          </tr>
          <?}?>
          <tr class="tr2">
              <td colspan="2" align="right">
                  <?
                  ($this->fltr_visible==2) ? $checked = 'checked="checked"': $checked = NULL;
                  ?>
                  <input type="checkbox" class="uicheckbox" name="fltr_visible" id="fltr_exist" <?=$checked;?> /><label for="fltr_visible" ><?=$this->multi['FLD_VISIBLE'];?></label>
                  <?
                  if ( isset($this->settings['exist']) AND $this->settings['exist']=='1' ){
                      ($this->fltr_exist==1) ? $checked = 'checked="checked"': $checked = NULL;
                      ?>&nbsp;<input type="checkbox" class="uicheckbox" name="fltr_exist" id="fltr_exist" <?=$checked;?> /><label for="fltr_exist" ><?=$this->multi['FLD_EXIST'];?></label><?
                  }
                  if ( isset($this->settings['new']) AND $this->settings['new']=='1' ){
                      ($this->fltr_new==1) ? $checked = 'checked="checked"': $checked = NULL;
                      ?>&nbsp;<input type="checkbox" class="uicheckbox" name="fltr_new" id="fltr_new" <?=$checked;?> /><label for="fltr_new" ><?=$this->multi['FLD_NEW'];?></label><?
                  }
                  if ( isset($this->settings['best']) AND $this->settings['best']=='1' ){
                      ($this->fltr_best==1) ? $checked = 'checked="checked"': $checked = NULL;
                      ?>&nbsp;<input type="checkbox" class="uicheckbox" name="fltr_best" id="fltr_best" <?=$checked;?> /><label for="fltr_best" ><?=$this->multi['FLD_BEST'];?></label><?
                  }
                  ?>
              </td>
          </tr>
          <tr class="tr2">
           <?
           $url = '/modules/mod_catalog/catalogcontent.backend.php?'.$this->script_ajax;
           ?>
           <td></td>
           <td align="left"><?$this->Form->Button( 'make_search', $this->multi['TXT_BUTTON_SEARCH'], 50, 'onClick="reload_srch('."'".$url."'".', '."'".'content'."'".'); return false;"' );?></td>
          <tr>
          </tr>
         </table>
       </td>
       <td width="30"></td>
       <td>
         <table border="0" cellpadding="2" cellspacing="1">
          <tr><td><h4 style="padding:0px; margin:0px;"><?=$this->multi['TXT_CATALOG_SERVICES'];?></h4></td></tr>
          <tr class="tr1">
           <td align="left" style="padding:5px;">
            <li><a href="<?=$this->script;?>&amp;task=show_move_to_category_all"><?=$this->multi['TXT_MOVE_FROM_CATEGORY_TO_CATEGORY']?></a></li>
            <li><a href="<?=$this->script;?>&amp;task=del_thumbs" onClick="if( !window.confirm('<?=$this->Msg->get_msg('_SYS_QUESTION_IS_DELETE');?>')) return false;" ><?=$this->multi['TXT_DEL_THUMBS_SELECTED_CATEGORY'];?></a></li>
           </td>
          </tr>
         </table>
       </td>
      </tr>

        <script language="JavaScript">
         function reload_srch(url, div_id){
              document.<?=$this->Form->name?>.task.value='show_srch_res';
              did = "#"+div_id;
              $.ajax({
                    type: "POST",
                    data: $("#<?=$this->Form->name?>").serialize(),
                    url: "/modules/mod_catalog/catalogcontent.backend.php",
                    success: function(msg){
                        //alert(msg);
                        $(did).html( msg );
                    },
                    beforeSend : function(){
                        //$("#sss").html("");
                        $(did).html('<div style="text-align:left;"><img src="/admin/images/icons/loading_animation_liferay.gif" alt="" title="" /></div>');
                    }
              });
         }
        </script>
     <?
     AdminHTML::PanelSimpleF();
   } //end of fuinction ShowContentFilters()


   // ================================================================================================
   // Function : ShowControlRelatPropForm()
   // Version : 1.0.0
   // Date : 08.05.2007
   // Parms :
   // Returns : true,false / Void
   // Description : show relations (similar) positions of catalogue
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.05.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowControlRelatPropForm()
   {
    $number_name = $this->GetNumberName($this->id_prop1);
    if( !empty($number_name) ) $number_name = '['.$number_name.']';

    $txt = $this->Msg->show_text('TXT_CONTROL_RELAT_PROP').' <u><strong>'.$this->Spr->GetNameByCod(TblModCatalogPropSprName, $this->id_prop1, $this->lang_id, 1).' '.$number_name.'</strong></u>';

    AdminHTML::PanelSubH( $txt );

    //-------- Show Error text for validation fields --------------
    $this->ShowErrBackEnd();
    //-------------------------------------------------------------

    AdminHTML::PanelSimpleH();
   ?>
    <table border="0" class="EditTable" width="100%">
      <?
      if( $this->GetRelatProp($this->id_prop1)>0 ){ ?>
      <tr>
       <td width="50%" valign="top"><?$this->ShowRelatPropForm();?></td>
      </tr>
      <?}?>
     <tr>
      <td width="50%" valign="top"><?$this->ShowAddRelatPropForm();?></td>
     </tr>
    </table>

     <a CLASS="toolbar" href=<?=$this->script."&task=show";?> onmouseout="MM_swapImgRestore();" onmouseover="MM_swapImage('restore','','../admin/images/icons/restore_f2.png',1);">
     <IMG src='../admin/images/icons/restore.png' alt="Go to:" align="middle" border="0" name="restore">&nbsp;<?=$this->Msg->show_text('TXT_RETUTN_TO').' '.$this->Spr->GetNameByCod(TblModCatalogSprName, $this->id_cat, $this->lang_id, 1).' '.$number_name;?></a>
    <?
    AdminHTML::PanelSimpleF();
    AdminHTML::PanelSubF();

    $this->Form->WriteFooter();
    return true;
   } //end of function ShowControlRelatPropForm()

   // ================================================================================================
   // Function : ShowAddRelatPropForm()
   // Version : 1.0.0
   // Date : 01.05.2007
   // Parms :   $level      -  id of the category
   // Returns : true,false / Void
   // Description : show relations (similar) categories
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 01.05.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowAddRelatPropForm()
   {
   /* Write Form Header */
    $this->Form->WriteHeader( $this->script );

    $this->Form->Hidden( 'id', $this->id );
    $this->Form->Hidden( 'move', $this->move );
    $this->Form->Hidden( 'group', $this->group );
    $this->Form->Hidden( 'id_cat', $this->id_cat );
    $this->Form->Hidden( 'display', $this->display );
    $this->Form->Hidden( 'start', $this->start );
    $this->Form->Hidden( 'sort', $this->sort );
    $this->Form->Hidden( 'fln', $this->fln );
    $this->Form->Hidden( 'srch', $this->srch );
    $this->Form->Hidden( 'fltr', $this->fltr );
    $this->Form->Hidden( 'fltr2', $this->fltr2 );
    $this->Form->Hidden( 'id_prop1', $this->id_prop1 );
    $this->Form->Hidden( 'task', 'add_relat_prop' );

    $arr_categs = $this->PrepareCatalogForSelect(0, NULL, NULL, 'back', true, true, false, false, NULL, NULL);
    //echo '<br />$arr_categs='.$arr_categs; print_r($arr_categs);
    //echo '<hr/>$arr_categs= ';
    //print_r($arr_categs);
    $disable_idprops = $this->GetRelatPropAsIndex($this->id_prop1);
    $arr_props = $this->PreparePositionsTreeForSelect('all', 'back', 'move', 'asc', $disable_idprops);
    //echo '<br />$arr_props='.$arr_props; print_r($arr_props);
    //echo '<hr/>$arr_props= ';
    //print_r($arr_props);
    $scriplink = $this->script;
    ?>
    <?=AdminHTML::PanelSimpleH();?>
    <table border="0" cellspacing="1" cellpading="0" class="EditTable" width="100%">
     <tr>
      <td valign="top"><b><?=$this->multi['FLD_ADD_RELAT_PROP']?>:</b></td>
     </tr>
     <tr>
      <td valign="top">
      <?
      for($i=0; $i<COUNT_ADD_RELAT_PROP; $i++){
          ?><div><?$this->ShowCatalogInSelect($arr_categs, $arr_props, '--- '.$this->multi['TXT_SELECT_POSITIONS'].' ---', 'arr_relat_prop[]', '','');?></div><?
      }
      ?>
      </td>
     </tr>
     <tr>
      <td><?=$this->Form->Button('submit', $this->multi['TXT_ADD'], 50);?></td>
    </table>
    <?
    AdminHTML::PanelSimpleF();
    $this->Form->WriteFooter();
   } //end of function ShowAddRelatPropForm()


   // ================================================================================================
   // Function : ShowRelatPropForm()
   // Version : 1.0.0
   // Date : 08.05.2007
   // Parms :
   // Returns : true,false / Void
   // Description : show relations (similar) positions of catalogue
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.05.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowRelatPropForm()
   {

    $q = "SELECT * FROM `".TblModCatalogPropRelat."` WHERE (`id_prop1`='".$this->id_prop1."' OR `id_prop2`='".$this->id_prop1."') ORDER BY `move` asc";
    $res = $this->Right->db_Query( $q );
    //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
    if ( !$res OR !$this->Right->result ) return false;
    $rows = $this->Right->db_GetNumRows();
    for ($i=0;$i<$rows;$i++) {
         $row = $this->Right->db_FetchAssoc();
         $arr_relat_prop[$i] = $row;
    }

    /* Write Form Header */
    $this->Form->WriteHeader( $this->script );
    $this->Form->Hidden( 'id', $this->id );
    $this->Form->Hidden( 'move', $this->move );
    $this->Form->Hidden( 'group', $this->group );
    $this->Form->Hidden( 'id_cat', $this->id_cat );
    $this->Form->Hidden( 'display', $this->display );
    $this->Form->Hidden( 'start', $this->start );
    $this->Form->Hidden( 'sort', $this->sort );
    $this->Form->Hidden( 'fln', $this->fln );
    $this->Form->Hidden( 'srch', $this->srch );
    $this->Form->Hidden( 'fltr', $this->fltr );
    $this->Form->Hidden( 'fltr2', $this->fltr2 );
    $this->Form->Hidden( 'id_prop1', $this->id_prop1 );
    $this->Form->Hidden( 'task', 'del_relat_prop' );

    ?>
    <?=AdminHTML::PanelSimpleH();?>
    <table border="0" cellspacing="1" cellpading="0" class="EditTable" width="100%">
     <?
     $a = $rows;
     $up = 0;
     $down = 0;
     for ($i=0;$i<$rows;$i++) {
         $row = $arr_relat_prop[$i];
         if ( (float)$i/2 == round( $i/2 ) ) $class = "TR1";
         else $class = "TR2";
     ?>
     <tr class="<?=$class;?>">
      <td width="20"><?=$this->Form->CheckBox( "id_del[]", $row['id'] );?></td>
      <td align="left">
       <?
       //echo '<br>$row[id_cat1]='.$row['id_cat1'].' $this->id_prop1='.$this->id_prop1;
       if ($row['id_prop1']==$this->id_prop1) $id_relat = $row['id_prop2'];
       else $id_relat = $row['id_prop1'];
       if ( isset($this->settings['name']) AND $this->settings['name']=='1' ){
           echo $this->GetPathToLevel($this->GetCategory($id_relat), ' -> ').' -> '.$this->Spr->GetNameByCod(TblModCatalogPropSprName, $id_relat);
       }
       else {
           echo $this->GetPathToLevel($this->GetCategory($id_relat), ' -> ').' -> '.$this->GetNumberName($id_relat);
       }
       ?>
      </td>
      <td>
       <?
       if( $up!=0 )
       {
       ?>
        <a href="<?=$this->script?>&task=up_relat_prop&amp;id_prop1=<?=$this->id_prop1;?>&amp;move=<?=$row['move']?>">
        <?=$this->Form->ButtonUp( $row['id'] );?>
        </a>
       <?
       }

       if( $i!=($rows-1) )
       {
       ?>
         <a href="<?=$this->script?>&task=down_relat_prop&amp;id_prop1=<?=$this->id_prop1;?>&amp;move=<?=$row['move']?>">
         <?=$this->Form->ButtonDown( $row['id'] );?>
         </a>
       <?
       }

       $up=$row['id'];
       $a=$a-1;
      ?>
      </td>
      <td><?=$this->Form->Link( $this->script."&amp;task=del_relat_prop&amp;id_prop1=$this->id_prop1&amp;id_del[$i]=".$row['id'], $this->multi['TXT_DELETE'] );?></td>
     </tr>
      <?
     }
      ?>
     <tr>
      <td colspan="2"><?=$this->Form->Button('submit', $this->multi['TXT_DELETE'], 50);?></td>
     </tr>
    </table>
    <?
    AdminHTML::PanelSimpleF();
    $this->Form->WriteFooter();
   } //end of function ShowRelatPropForm()

   // ================================================================================================
   // Function : ShowRelatProp()
   // Version : 1.0.0
   // Date : 08.05.2007
   // Parms :   $arr - array with relations positions iocf catalogue
   //           $id  - id of the position in catalogue
   // Returns : true,false / Void
   // Description : show relations (similar) categories
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.05.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowRelatProp( $arr, $id )
   {
    foreach($arr as $key=>$value) {
        if( $value['id_prop1']==$id ) {
            if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) $val = $this->Spr->GetNameByCod(TblModCatalogPropSprName, $value['id_prop2'], $this->lang_id, 1);
            else $val = $this->GetNumberName($value['id_prop2']);
        }
        else {
            if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) $val = $this->Spr->GetNameByCod(TblModCatalogPropSprName, $value['id_prop1'], $this->lang_id, 1);
            else $val = $this->GetNumberName($value['id_prop1']);
        }
        ?><div style="border-bottom: solid 1px #ACACAC; margin: 5px;"><?=$val;?></div><?
    }
   } //end of function ShowRelatProp()

   // ================================================================================================
   // Function : AddRelatProp()
   // Version : 1.0.0
   // Date : 08.05.2007
   // Parms :   $level      -  id of the category
   // Returns : true,false / Void
   // Description : add relations (similar)positions of catalogue
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.05.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function AddRelatProp()
   {
       //print_r($this->arr_relat_categs);
       //for ($i=0;$i<count($this->arr_relat_prop);$i++) {
       foreach($this->arr_relat_prop as $key=>$value){
           //ho '<br>$value='.$value;
           if( empty($value) ) continue;

           $q = "SELECT * FROM `".TblModCatalogPropRelat."` WHERE (`id_prop1`='$this->id_prop1' AND `id_prop2`='".$value."') OR (`id_prop1`='".$value."' AND `id_prop2`='$this->id_prop1')";
           $res = $this->Right->Query( $q, $this->user_id, $this->module );
           //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
           if ( !$res OR !$this->Right->result ) return false;
           $rows = $this->Right->db_GetNumRows();
           if ($rows>0) continue;
           $move = ($this->GetMaxValueOfFieldMove( TblModCatalogPropRelat ) + 1);
           $q = "INSERT INTO `".TblModCatalogPropRelat."` VALUES( NULL, '$this->id_prop1', '".$value."', '$move')";
           $res = $this->Right->Query( $q, $this->user_id, $this->module );
           //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
           if ( !$res OR !$this->Right->result ) return false;
       }
       return true;
   } //end of function AddRelatProp()


   // ================================================================================================
   // Function : DelRelatProp()
   // Version : 1.0.0
   // Date : 08.05.2007
   // Parms :   $user_id, $module_id, $id_del
   // Returns : true,false / Void
   // Description :  Remove data from the table
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.05.2007
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function DelRelatProp( $id_del )
   {
    $del = 0;
    $kol = count( $id_del );
    //print_r($id_del);
    //for( $i=0; $i<$kol; $i++ ){
    foreach( $id_del as $key=>$value ){
        //secho '<br>$key='.$key.' $value='.$value;
        $u=$value;
        $q = "delete from ".TblModCatalogPropRelat." where `id`='$u'";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
        if ( $res ) $del=$del+1;
        else return false;
    }
    return $del;
   } //end of function DelRelatProp()

   // ================================================================================================
   // Function : DelRelatPropByIdProp()
   // Version : 1.0.0
   // Date : 09.05.2007
   // Parms :   $id_prop - id of the current position in catalogue
   // Returns : true,false / Void
   // Description :  Remove all relattions with posirion $id_prop
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 09.05.2007
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function DelRelatPropByIdProp( $id_prop )
   {
        $tmp_db = new DB();
        $q = "DELETE FROM `".TblModCatalogPropRelat."` WHERE (`id_prop1`='$id_prop' OR `id_prop2`='$id_prop')";
        $res =$tmp_db->db_Query( $q );
        //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
        if ( !$res OR !$tmp_db->result ) return false;
        return true;
   } //end of function DelRelatPropByIdProp()


   // ================================================================================================
   // Function : EditContent()
   // Date : 04.01.2011
   // Parms : id/id of the record
   // Returns : true,false / Void
   // Description : Show content of catalogue for editing
   // Programmer : Yaroslav Gyryn
   // ================================================================================================
   function EditContent( $id=NULL )
   {
    $Panel =&check_init('Panel', 'Panel'); //new Panel();
    $ln_sys = &check_init('SysLang', 'SysLang');//new SysLang();
    $mas = NULL;
    $fl = NULL;
     ?>
        <script type="text/javascript">
            $(document).ready(function(){
                $(".uicheckbox").button();
            });
        </script>
     <?
    /* set action page-adress with parameters */
    //$script = $_SERVER['PHP_SELF'].'?module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort.'&fltr='.$this->fltr.'&fltr2='.$this->fltr2.'&fln='.$this->fln.'&id_cat='.$this->id_cat;

    if( $this->id!=NULL  ){
        $this->ShowJS();
        $q="select * from `".TblModCatalogProp."` where id='".$this->id."'";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        if( !$this->Right->result ) return false;
        $mas = $this->Right->db_FetchAssoc();
    }


    /* Write Form Header */
    //$this->Form->WriteHeader( $this->script );
    $this->Form->WriteHeaderFormImg( $this->script );
    $settings=SysSettings::GetGlobalSettings();
    $this->Form->textarea_editor = $settings['editer']; //'tinyMCE';
    $this->Form->IncludeSpecialTextArea( $this->Form->textarea_editor );

    if( $this->id!=NULL )
        $txt = $this->multi['TXT_EDIT'];
    else
        $txt = $this->multi['TXT_ADD'];

    AdminHTML::PanelSubH( $txt );

    //-------- Show Error text for validation fields --------------
    $this->ShowErrBackEnd();
    //-------------------------------------------------------------

    if( (!empty($this->id) AND $this->Right->IsUpdate()) OR (empty($this->id) AND $this->Right->IsWrite()) ){
        $this->Form->WriteSaveAndReturnPanel( $this->script );?>&nbsp;<?
        $this->Form->WriteSavePanel( $this->script );?>&nbsp;<?
    }
    $this->Form->WriteCancelPanel( $this->script );?>&nbsp;<?
    if( !empty($this->id) ){
       $CatalogLayout = &check_init('CatalogLayout', 'CatalogLayout');
       $CatalogLayout->mod_rewrite=1;
       //echo '<br>$publish='.$publish;
       $this->Form->WritePreviewPanelNewWindow( "http://".NAME_SERVER.$CatalogLayout->Link($mas['id_cat'], $this->id) );
    }


    AdminHTML::PanelSimpleH();
  ?>
    <tr>
     <td width="170">
      <b><?=$this->multi['FLD_ID']?>:</b>
      <?
      if($this->task=='new_by_copy'){
          $this->Form->Hidden( 'id_prop_copy', $this->id );
          $this->Form->Hidden( 'id', '' );
      }
      elseif( $this->id!=NULL){
          echo $mas['id'];
          $this->Form->Hidden( 'id', $this->id );
          if ( isset($this->settings['cod_pli']) AND $this->settings['cod_pli']=='1' ) {
              ?><br/><b><?=$this->multi['FLD_COD_PLI']?>: </b><?
              echo $mas['cod_pli'];
          }
      }
      else{
          $this->Form->Hidden( 'id', '' );
      }

      $this->Form->Hidden( 'group', $this->group );
      $this->Form->Hidden( 'move', $mas['move'] );

      $img_arr = $this->GetPicture($this->id, 'back');
      if(isset($img_arr['0'])) echo '<br/>'.$this->ShowCurrentImage($img_arr['0']['id'], 'size_auto=100', 85, NULL, "border=0");
      ?>
     </td>
     <td valign="top">
        <div class="EditTable">
             <?
             if( $this->id!=NULL ) $this->Err!=NULL ? $visible=$this->visible : $visible=$mas['visible'];
             else $this->Err!=NULL ? $visible=$this->visible : $visible=2;
             ($visible==2)? $checked = 'checked="checked"': $checked = NULL;
             ?>
             <input id="visibleCheck" class="uicheckbox" type="checkbox" name="visible" <?=$checked?> value="visible" onclick=""/>
             <label for="visibleCheck"><?=$this->multi['FLD_VISIBLE']?></label>
             <?
             //$arr_v[0]=$this->Msg->show_text('TXT_UNVISIBLE');
             //$arr_v[1]=$this->Msg->show_text('TXT_VISIBLE_ONLY_ON_BACKEND');
             //$arr_v[2]=$this->Msg->show_text('TXT_VISIBLE');
             //$this->Form->Select( $arr_v, 'visible', $visible );

             if ( isset($this->settings['exist']) AND $this->settings['exist']=='1' ) {
                 if( $this->id!=NULL ) $this->Err!=NULL ? $exist=$this->exist : $exist=$mas['exist'];
                 else $exist=$this->exist;
                 ($exist==1)? $checked = 'checked="checked"': $checked = NULL;
                 ?><input type="checkbox" id="existCheck" class="uicheckbox" name="exist" <?=$checked?> value="exist" onclick=""/>
                 <label for="existCheck"><?=$this->multi['FLD_EXIST']?></label>
                 <?
                 //$this->Spr->ShowInComboBox( TblSysLogic, 'exist', $exist, 20 );
             }

             if ( isset($this->settings['new']) AND $this->settings['new']=='1' ) {
                 if( $this->id!=NULL ) $this->Err!=NULL ? $new=$this->new : $new=$mas['new'];
                 else $new=$this->new;
                 ($new==1)? $checked = 'checked="checked"': $checked = NULL;
                 ?><input type="checkbox" id="newCheck" class="uicheckbox"  name="new" <?=$checked?> onclick=""/>
                 <label for="newCheck"><?=$this->multi['FLD_NEW']?></label>
                 <?
             }

             if ( isset($this->settings['best']) AND $this->settings['best']=='1' ) {
                 if( $this->id!=NULL ) $this->Err!=NULL ? $best=$this->best : $best=$mas['best'];
                 else $best=$this->best;
                 ($best==1)? $checked = 'checked="checked"': $checked = NULL;
                 ?><input type="checkbox" id="bestCheck" class="uicheckbox" name="best" <?=$checked?> onclick=""/>
                 <label for="bestCheck"><?=$this->multi['FLD_BEST']?></label>
                 <?
             }
             ?>
        </div>

        <div class="EditTable" style="margin:5px 0px;">
           <?
            if ( isset($this->settings['responses']) AND $this->settings['responses']=='1' ) {
                if( $this->GetCountResponsesByIdProp($mas['id'])>0){
                    $ModulesPlug = new ModulesPlug();
                    $id_module = $ModulesPlug->GetModuleIdByPath ( '/modules/mod_catalog/catalog_response.backend.php' );
                    ?><a href="<?=$_SERVER['PHP_SELF'];?>?module=<?=$id_module;?>&amp;fltr2=<?=$mas['id'];?>" ><?=$this->multi['TXT_READ_RESPONSES']?></a><br/><?
                }
            }
            if ( isset($this->settings['rating']) AND $this->settings['rating']=='1' ) {
                $rating = $this->GetAverageRatingByIdProp($mas['id'], 'back');
                if( $rating>0)
                    echo '<b>'.$this->multi['FLD_AVERAGE_RATING'].': '.$rating.'</b>';
            }
           ?>
        </div>
     </td>
    </tr>
    <tr>
     <td><b><?=$this->multi['FLD_ADD_TO_CATEGORY']?>:</b></td>
     <td>
        <?
        if( $this->id!=NULL ) $this->Err!=NULL ? $id_cat=$this->new_id_cat : $id_cat=$mas['id_cat'];
        else $this->Err!=NULL ? $id_cat=$this->new_id_cat : $id_cat=$this->id_cat;
        //echo '<br>$id_cat='.$id_cat;
        $arr_categs = $this->GetCatalogInArray(NULL, $this->multi['TXT_SELECT_CATEGORY'], NULL, NULL, 0, 'back');
        //print_r($arr_categs);
        $this->Form->Select( $arr_categs, 'new_id_cat', 'categ='.$id_cat, NULL, 'style="width:320px;"' );
        $this->Form->Hidden( 'old_id_cat', $id_cat );
        ?>
     </td>
    </tr>
    <?
    if ( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']=='1' ) { ?>
        <tr><td valign="top" colspan="2">
         <div class="EditTable">
             <div style="width:90px; background-color:#E0E0E0; padding: 4px; float:left;">
                <b><?=$this->multi['FLD_ADDITIONAL_CATEGORIES']?>:</b>
             </div>
             <?
                if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->multi_categs : $val=$this->GetMultiCategsByIdProp($this->id);
                else $val=$this->multi_categs;
             ?>
             <div id="tag-categories-all" class="ui-tabs-panel">
              <?
              $arr_categs = $this->PrepareCatalogForSelect(0, NULL, $spacer = NULL, 'back', true, false, false, false, NULL, NULL, 0);
              $this->ShowCatalogInCheckbox($arr_categs, NULL, 'multi_categs', $val,'');
              ?>
              <div>
         </div>
        </td></tr>
        <?
    }

    if ( isset($this->settings['manufac']) AND $this->settings['manufac']=='1' ) { ?>
    <tr><td><b><?=$this->multi['FLD_MANUFAC']?>:</b>
        <td>
        <?
        if( $this->id!=NULL ) $this->Err!=NULL ? $id_manufac=$this->id_manufac : $id_manufac=$mas['id_manufac'];
        else $id_manufac=$this->id_manufac;
        //$this->Spr->ShowInComboBox( TblModCatalogSprManufac,'id_manufac', $id_manufac, 50, NULL, 'name', 'asc' );

        $arr_categs = $this->Spr->GetStructureInArray(TblModCatalogSprManufac, NULL, $this->lang_id,  NULL, NULL, 0, 1, 1, 'back');
        //print_r($arr_categs);
        $this->Form->Select( $arr_categs, 'id_manufac', $id_manufac);
    }

    if ( isset($this->settings['tags']) AND $this->settings['tags']=='1' ) {
        $Tags = new SystemTags($this->user_id, $this->module);
        if( $this->id!=NULL ) $this->Err!=NULL ? $id_tag=$this->id_tag : $id_tag=$Tags->GetTagsByModuleAndItem($this->module, $this->id);
        else $id_tag=$this->id_tag;
        //echo '<br>$id_tag='.$id_tag; print_r($id_tag);
        ?><tr><td valign="top" colspan="2"><?$Tags->ShowEditTags($id_tag);?></td></tr><?
    }
   if ( isset($this->settings['share']) AND $this->settings['share']=='1' ) { ?>
     </td></tr><?
      if ($this->id != NULL)
                $this->Err != NULL ? $shareFld = $this->share : $shareFld = $mas['share'];
            else
                $shareFld=$this->share;
            ($shareFld == 1) ? $checked = 'checked="checked"' : $checked = NULL;
                        ?><tr><td><b><?= $this->multi['FLD_SHARE'] ?></b></td><td>
            <input type="checkbox" name="share" <?= $checked ?> onclick="
                            if(this.checked)
                            document.getElementById('share_id').style.display='block'
                            else document.getElementById('share_id').style.display='none'"

                            >
                        <?
                $arr=$this->GetShareInArray(0);
                if(isset($mas['share_id'])) $tmp_lev = $mas['share_id'];
               else $tmp_lev = $this->share_id;
               $params="";
               ?><div id="share_id" style="<?if(($shareFld != 1)) echo "display: none"?>"><?
                $this->Form->Select( $arr, 'share_id', $tmp_lev, NULL, 'id="idlevelp" '.$params );
                        ?>
               </div></td></tr><?
   }

    if ( isset($this->settings['id_group']) AND $this->settings['id_group']=='1' ) { ?>
        <tr><td valign="top" colspan="2">
         <div class="EditTable">
             <div style="width:90px; background-color:#E0E0E0; padding: 4px; float:left;">
                <b><?=$this->multi['FLD_GROUP']?>:</b>
             </div>
             <?
                if( $this->id!=NULL ) $this->Err!=NULL ? $id_group=$this->id_group : $id_group=$this->GetGroupsByIdProp($this->id);
                else $id_group=$this->id_group;
             ?>
             <div id="tag-categories-all" class="ui-tabs-panel"><?$this->Spr->ShowInCheckBox( TblModCatalogSprGroup, 'id_group', 1, $id_group, "left", NULL, 'move', 'asc', 1, 0 );?><div>
         </div>
        </td></tr>
        <?
    }

    if ( isset($this->settings['number_name']) AND $this->settings['number_name']=='1' ) { ?>
        <TR><TD><b><?=$this->multi['FLD_NUMBER_NAME']?>:</b>
        <TD>
        <?
        if( $this->id!=NULL ) $this->Err!=NULL ? $number_name=$this->number_name : $number_name=$mas['number_name'];
        else $number_name=$this->number_name;
        $this->Form->TextBox( 'number_name', $number_name, 50 );
    }

    if ( isset($this->settings['art_num']) AND $this->settings['art_num']=='1' ) { ?>
        <TR><TD><b><?=$this->multi['FLD_ART_NUM']?>:</b>
        <TD>
        <?
        if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->art_num : $val=$mas['art_num'];
        else $val=$this->art_num;
        $this->Form->TextBox( 'art_num', stripslashes($val), 50 );
    }

    if ( isset($this->settings['barcode']) AND $this->settings['barcode']=='1' ) { ?>
        <TR><TD><b><?=$this->multi['FLD_BARCODE']?>:</b>
        <TD>
        <?
        if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->barcode : $val=$mas['barcode'];
        else $val=$this->barcode;
        $this->Form->TextBox( 'barcode', stripslashes($val), 50 );
    }
    ?>
    <tr>
     <td colspan="2">
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
             echo "\n <td><b>".$this->multi['FLD_PAGE_URL'].":</b>";
             echo "\n <br>";
             ?><span style="font-size:10px;">../<?=$this->GetTranslitById( $id_cat, NULL, $lang_id );?>/<?
             if( $this->id!=NULL ) {
                 if($this->Err!=NULL) $val=$this->translit[$lang_id];
                 else{
                      if($this->task=='new_by_copy') $val='';
                      else $val=$this->GetTranslitById( $mas['id_cat'], $mas['id'], $lang_id );
                 }
             }
             else $this->Err!=NULL ? $val=$this->translit[$lang_id] : $val='';
             if( $this->id ){
                 $params = 'disabled';
                 $this->Form->Hidden( 'translit['.$lang_id.']', stripslashes($val) );
             }
             else{
                 $params="onkeyup=\"CheckTranslitField('translit".$lang_id."','tbltranslit".$lang_id."');\"";
             }
             $this->Form->TextBox( 'translit['.$lang_id.']', stripslashes($val), 50, 'id="translit'.$lang_id.'"; style="font-size:10px; "'.$params );?>.html</span><?
             $this->Form->Hidden( 'translit_old['.$lang_id.']', stripslashes($val) );
             if( $this->id ){?>&nbsp;<?$this->Form->ButtonSimple("btn", $this->Msg->show_text('TXT_EDIT'), NULL, "id='button".$lang_id."' onClick=\"EditTranslit('translit".$lang_id."','button".$lang_id."');\"");}
             ?>
             <br><table><tr><td><img src='images/icons/info.png' alt='' title='' border='0' /></td><td class='info'><?=stripslashes($this->multi['HELP_FLD_PAGE_URL']);?></td></tr></table>
             <br/>
             <?

           if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {
             echo "\n <tr>";
             echo "\n <td><b>".$this->multi['FLD_NAME'].":</b>";
             echo "\n <tr><td>";
             $row = $this->Spr->GetByCod( TblModCatalogPropSprName, $mas['id'], $lang_id );
             if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->name[$lang_id] : $val=$row[$lang_id];
             else $val=$this->name[$lang_id];
             $this->Form->TextBox( 'name['.$lang_id.']', stripslashes($val), 80 );
             echo "\n <br><br>";
             echo "\n </td>";
             echo "\n </tr>";
           }

           echo "\n <tr>";
           echo "\n <td><b>".$this->multi['_FLD_H1'].":</b>";
           echo "\n <br>";
           echo '<div class="help">'.$this->multi['_HELP_MSG_H1'].'</div>';
           echo "\n <tr><td>";
           $row = $this->Spr->GetByCod( TblModCatalogPropSprH1, $mas['id'], $lang_id );
           if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->h1[$lang_id] : $val=$row[$lang_id];
           else $val=$this->h1[$lang_id];
           $this->Form->TextBox( 'h1['.$lang_id.']', stripslashes($val), 80 );
           echo "\n <br><br>";
           echo "\n </td>";
           echo "\n </tr>";


           if ( isset($this->settings['short_descr']) AND $this->settings['short_descr']=='1' ) {
             echo "\n <tr>";
             echo "\n <td><b>".$this->multi['FLD_SHORT_DESCR'].":</b>";
             echo "\n <tr><td>";
             $row = $this->Spr->GetByCod( TblModCatalogPropSprShort, $mas['id'], $lang_id );
             if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->short_descr[$lang_id] : $val=$row[$lang_id];
             else $val=$this->short_descr[$lang_id];
             $this->Form->SpecialTextArea(NULL, 'short_descr['.$lang_id.']', stripslashes($val), 15, 70, 'class="contentInput"', $lang_id, 'short_descr' );
             echo "\n <br><br>";
             echo "\n </td>";
             echo "\n </tr>";
           }
           if ( isset($this->settings['full_descr']) AND $this->settings['full_descr']=='1' ) {
             echo "\n <tr>";
             echo "\n <td><b>".$this->multi['FLD_FULL_DESCR'].":</b>";
             echo "\n <tr><td>";
             $row = $this->Spr->GetByCod( TblModCatalogPropSprFull, $mas['id'], $lang_id );
             if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->full_descr[$lang_id] : $val=$row[$lang_id];
             else $val=$this->full_descr[$lang_id];
             $this->Form->SpecialTextArea(NULL, 'full_descr['.$lang_id.']', stripslashes($val), 20, 70, 'class="contentInput"', $lang_id, 'full_descr'  );
             echo "\n <br><br>";
             echo "\n </td>";
             echo "\n </tr>";
           }
           if ( isset($this->settings['specif']) AND $this->settings['specif']=='1' ) {
             echo "\n <tr>";
             echo "\n <td><b>".$this->multi['FLD_SPECIF'].":</b>";
             echo "\n <tr><td>";
             $row = $this->Spr->GetByCod( TblModCatalogPropSprSpecif, $mas['id'], $lang_id );
             if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->specif[$lang_id] : $val=$row[$lang_id];
             else $val=$this->specif[$lang_id];
             $this->Form->SpecialTextArea(NULL, 'specif['.$lang_id.']', stripslashes($val), 20, 70, 'class="contentInput"', $lang_id, 'specif' );
             echo "\n <br><br>";
             echo "\n </td>";
             echo "\n </tr>";
           }
           if ( isset($this->settings['reviews']) AND $this->settings['reviews']=='1' ) {
             echo "\n <tr>";
             echo "\n <td><b>".$this->multi['FLD_REVIEWS'].":</b>";
             echo "\n <tr><td>";
             $row = $this->Spr->GetByCod( TblModCatalogPropSprReviews, $mas['id'], $lang_id );
             if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->reviews[$lang_id] : $val=$row[$lang_id];
             else $val=$this->reviews[$lang_id];
             $this->Form->SpecialTextArea(NULL, 'reviews['.$lang_id.']', stripslashes($val), 15, 70, 'class="contentInput"', $lang_id, 'reviews' );
             echo "\n <br><br>";
             echo "\n </td>";
             echo "\n </tr>";
           }
           if ( isset($this->settings['support']) AND $this->settings['support']=='1' ) {
             echo "\n <tr>";
             echo "\n <td><b>".$this->multi['FLD_SUPPORT'].":</b>";
             echo "\n <tr><td>";
             $row = $this->Spr->GetByCod( TblModCatalogPropSprSupport, $mas['id'], $lang_id );
             if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->support[$lang_id] : $val=$row[$lang_id];
             else $val=$this->support[$lang_id];
             $this->Form->SpecialTextArea(NULL, 'support['.$lang_id.']', stripslashes($val), 15, 70, 'class="contentInput"', $lang_id, 'support');
           }
             echo   "\n </table><br>";

             echo "\n<fieldset title='".$this->multi['TXT_META_DATA']."'> <legend><img src='images/icons/meta.png' alt='".$this->multi['TXT_META_DATA']."' title='".$this->multi['TXT_META_DATA']."' border='0' /> ".$this->multi['TXT_META_DATA']." </legend>";
             ?><div class='EditTable'>
                    <b><?=$this->multi['FLD_PAGES_TITLE']?>:</b><br/>
                    <span class="help"><?=$this->multi['HELP_MSG_PAGE_TITLE']?></span><br/>
                    <?
                     $row = $this->Spr->GetByCod( TblModCatalogPropSprMTitle, $mas['id'], $lang_id );
                     if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->mtitle[$lang_id] : $val=$row[$lang_id];
                     else $val=$this->mtitle[$lang_id];
                     $this->Form->TextBox( 'mtitle['.$lang_id.']', stripslashes($val), 70 );
                    ?>
                    <br/>
                    <b><?=$this->multi['FLD_PAGES_DESCR']?>:</b><br/>
                    <span class="help"><?=$this->multi['HELP_MSG_PAGE_DESCRIPTION']?></span><br/>
                    <?
                     $row = $this->Spr->GetByCod( TblModCatalogPropSprMDescr, $mas['id'], $lang_id );
                     if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->mdescr[$lang_id] : $val=$row[$lang_id];
                     else $val=$this->mdescr[$lang_id];
                     $this->Form->TextArea( 'mdescr['.$lang_id.']', stripslashes($val), 3, 70 );
                    ?>
                    <br/>
                    <b><?=$this->multi['FLD_KEYWORDS']?>:</b><br/>
                    <span class="help"><?=$this->multi['HELP_MSG_PAGE_KEYWORDS']?></span>
                    <br />
                    <?
                    $row = $this->Spr->GetByCod( TblModCatalogPropSprMKeywords, $mas['id'], $lang_id );
                    if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->mkeywords[$lang_id] : $val=$row[$lang_id];
                    else $val=$this->mkeywords[$lang_id];
                    $this->Form->TextArea( 'mkeywords['.$lang_id.']', stripslashes($val),3, 70 );
                    ?>
                    <br/>
                    <table><tr><td><img src='images/icons/info.png' alt='' title='' border='0' /></td><td class='info'>"<?=$this->multi['HELP_MSG_META_TAGS_POSITION']?>"</td></tr></table>
             </div><?
             echo "</fieldset>";
             $Panel->WriteItemFooter();
        }
        $Panel->WritePanelFooter();
      ?>
     </td>
    </tr>
    <?

    if (( isset($this->settings['price_currency']) AND $this->settings['price_currency']=='1' ) OR ( isset($this->settings['opt_price_currency']) AND $this->settings['opt_price_currency']=='1' ) ){
        $show_currencies = true;
        //$def_currency = $Currencies->GetDefaultCurrency();
        $def_currency = $this->Currencies->defCurrencyData['id'];
    }
    else
        $show_currencies = false;

    if ( isset($this->settings['price']) AND $this->settings['price']=='1' ) { ?>
        <TR><TD><b><?=$this->multi['FLD_PRICE'];?>:</b>
        <TD>
        <?
        if ( isset($this->settings['price_currency']) AND $this->settings['price_currency']=='1' ){
            if( $this->id!=NULL ) $this->Err!=NULL ? $price_currency=$this->price_currency : $price_currency=$mas['price_currency'];
            else $price_currency=$this->price_currency;
            if( empty($price_currency) AND $price_currency!='0' ) $price_currency = $def_currency;
        }

        if( $this->id!=NULL ) $this->Err!=NULL ? $price=$this->price : $price=$mas['price'];
        else $price=$this->price;
        $this->Form->TextBox( 'price', stripslashes($price), 10 );
        if($show_currencies) $this->Form->Select($this->Currencies->listShortNames, 'price_currency', $price_currency);
    }

    if ( isset($this->settings['opt_price']) AND $this->settings['opt_price']=='1' ) { ?>
        <TR><TD><b><?=$this->multi['FLD_OPT_PRICE']?>:</b>
        <TD>
        <?
        if ( isset($this->settings['opt_price_currency']) AND $this->settings['opt_price_currency']=='1' ){
            if( $this->id!=NULL ) $this->Err!=NULL ? $opt_price_currency=$this->opt_price_currency : $opt_price_currency=$mas['opt_price_currency'];
            else $opt_price_currency=$this->opt_price_currency;
            if( empty($opt_price_currency) AND $opt_price_currency!='0' ) $opt_price_currency = $def_currency;
        }

        if( $this->id!=NULL ) $this->Err!=NULL ? $opt_price=$this->opt_price : $opt_price=$mas['opt_price'];
        else $opt_price=$this->opt_price;
        $this->Form->TextBox( 'opt_price', stripslashes($opt_price), 10 );
        if($show_currencies) $this->Form->Select($this->Currencies->listShortNames, 'opt_price_currency', $opt_price_currency);
    }

    if ( isset($this->settings['price_levels']) AND $this->settings['price_levels']=='1' ) { ?>
        <TR><TD valign="top"><b><?=$this->multi['FLD_PRICE_LEVELS'];?>:</b>
        <TD nowrap="nowrap">
         <div name="price_level_data<?=$this->cnt_div;?>" id="price_level_data<?=$this->cnt_div;?>"><?$this->AddHTMLPriceLevel();?></div>

        <script language="JavaScript">
          var nameform='';
          function add_price_level(mydata, div_id){
              did = "#"+div_id;
              $.ajax({
                    type: "POST",
                    data: mydata+'&task=add_html_price_level',
                    url: '/modules/mod_catalog/catalogcontent.backend.php',
                    success: function(msg){
                        //alert(msg);
                        $(did).html( msg );
                    },
                    beforeSend : function(){
                        //$("#sss").html("");
                        $(did).html('<div style="text-align:left;"><img src="/admin/images/icons/loading_animation_liferay.gif" alt="" title="" /></div>');
                    }
              });
          }
          function del_price_level(mydata, div_id){
              did = "#"+div_id;
              $.ajax({
                    type: "POST",
                    data: mydata+'&task=del_html_price_level',
                    url: '/modules/mod_catalog/catalogcontent.backend.php',
                    success: function(msg){
                        //alert(msg);
                        $(did).html( msg );
                    },
                    beforeSend : function(){
                        //$("#sss").html("");
                        $(did).html('<div style="text-align:left;"><img src="/admin/images/icons/loading_animation_liferay.gif" alt="" title="" /></div>');
                    }
              });
          }
         </script>
         <?
    }

    if ( isset($this->settings['grnt']) AND $this->settings['grnt']=='1' ) { ?>
        <TR><TD><b><?=$this->multi['FLD_GUARANTEE']?>:</b>
        <TD>
        <?
        if( $this->id!=NULL ) $this->Err!=NULL ? $grnt=$this->grnt : $grnt=$mas['grnt'];
        else $grnt=$this->grnt;
        $this->Form->TextBox( 'grnt', stripslashes($grnt), 10 );
    }

    if ( isset($this->settings['dt']) AND $this->settings['dt']=='1' ) { ?>
        <TR><TD><b><?=$this->multi['FLD_DATE']?>:</b>
        <TD>
        <?
        if( $this->id!=NULL ) {
            if($this->Err!=NULL OR $this->task=='new_by_copy') $dt=$this->dt;
            else $dt=$mas['dt'];
        }
        else $dt=$this->dt;

        ?><script type="text/javascript">
             $(document).ready(function(){
              $("#dateField").datepicker({ dateFormat: 'yy-mm-dd' });
            });
        </script>
        <?
        $this->Form->TextBox( 'dt', $dt, 10 , "id='dateField'");
    }

    //--------------------------------------------------------------------------------------------------
    //------------------------------------ SHOW PARAMETERS ---------------------------------------------
    //--------------------------------------------------------------------------------------------------
    if( !empty($this->id_cat) ) $id_cat_for_params = $this->id_cat;
    else  $id_cat_for_params = $mas['id_cat'];
    $params = $this->IsParams( $id_cat_for_params );
    if ( $params>0 ) {
      ?>
      <tr>
       <td colspan="2">
       <fieldset title="<?=$this->multi['FLD_COMPARE_PARAMS']?>"><legend><img src='images/icons/params.png' alt="<?=$this->multi['FLD_COMPARE_PARAMS'];?>" title="<?=$this->multi['FLD_COMPARE_PARAMS'];?>" border="0" /> <?=$this->multi['FLD_COMPARE_PARAMS'];?></legend>
        <table border="0" cellspacing="1" cellpadding="0" class="EditTable" width="100%">
         <tr>
          <Th class="THead"><?=$this->multi['FLD_PARAM_NAME']?></Th>
          <Th class="THead"><?=$this->multi['FLD_PREFIX']?></Th>
          <Th class="THead"><?=$this->multi['FLD_VALUES']?></Th>
          <Th class="THead"><?=$this->multi['FLD_SUFIX']?></Th>
         </tr>
      <?

    }
    $style1 = 'TR1';
    $style2 = 'TR2';
    $params_row = $this->GetParams( $id_cat_for_params );
    $value=$this->GetParamsValuesOfProp( $this->id );
    for ($i=0;$i<count($params_row);$i++){

      if ( (float)$i/2 == round( $i/2 ) )
      {
       echo '<TR CLASS="'.$style1.'">';
      }
      else echo '<TR CLASS="'.$style2.'">';

      ?><td align=left><b><?=$this->Spr->GetNameByCod(TblModCatalogParamsSprName, $params_row[$i]['id'], $this->lang_id, 1);?>:</b><?
      ?><td align=center><?=$this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($params_row[$i]['id']), $this->lang_id, 1);
      ?><td align=left width="50%"><?
      //$tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);
      //echo '<br> $tblname='.$tblname;

      isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;

      if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->arr_params[$params_row[$i]['id']] : $val=$val_from_table;
      else $val=$this->arr_params[$params_row[$i]['id']];
      //$val = stripslashes($val);
      //echo '<br> $params_row['.$i.'][id]='.$params_row[$i]['id'];
      //echo '<br> $val='.$val.' $value='.$value;
      switch ($params_row[$i]['type'] ) {
        case '1':
                $this->Form->TextBox( 'arr_params['.$params_row[$i]['id'].']', stripslashes($val), 15 );
                break;
        case '2':
                //$this->Spr->ShowInComboBox( TblSysLogic, 'arr_params['.$params_row[$i]['id'].']', $val, 50 );
                if(!$val) $val=0;
                else $val = 1;
                $text = $this->multi['TXT_PARAM_LOGIC_YES'];
                //$this->Form->Hidden('arr_params['.$params_row[$i]['id'].']', $val);
                $this->Form->CheckBox( 'arr_params['.$params_row[$i]['id'].']', $val, $val, 'arr_params'.$params_row[$i]['id'], NULL, $text );
                break;
                break;
        case '3':
                //$this->Spr->ShowInComboBoxWithShortName( $tblname, 'arr_params['.$params_row[$i]['id'].']', $val, '100%', NULL, 'move', NULL, 'left', ' - ' );
                $this->ShowParamsValInComboBoxWithShortName($params_row[$i]['id_categ'], $params_row[$i]['id'], 'arr_params['.$params_row[$i]['id'].']', $val, '100%', NULL, 'move', NULL, 'left', ' - ' );
                break;
        case '4':
                //$this->Spr->ShowInCheckBox( $tblname, 'arr_params['.$params_row[$i]['id'].']', 4, $val );
                $this->ShowParamValInCheckBox($params_row[$i]['id_categ'], $params_row[$i]['id'], 'arr_params['.$params_row[$i]['id'].']', 4, $val);
                break;
        case '5':
                //$this->Form->TextBox( 'arr_params['.$params_row[$i]['id'].']', $val, 40 );
                $this->Form->TextArea( 'arr_params['.$params_row[$i]['id'].']', stripslashes($val), 5, 50, 'width="100%"' );
                break;
         }
         ?><td align="center"><?=$this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($params_row[$i]['id']), $this->lang_id, 1);
    }
    if ( $params>0 ) {
        ?></table>
        </fieldset>
        <?
    }
    //--------------------------------------------------------------------------------------------------
    //---------------------------------- END SHOW PARAMETERS -------------------------------------------
    //--------------------------------------------------------------------------------------------------

    //-------------------- Images Start ---------------------
    if ( isset($this->settings['img']) AND $this->settings['img']=='1' ){
        if(isset($this->settings['imgColors']) AND $this->settings['imgColors']=='1'){
           //$this->CatalogColors=&check_init('CatalogColors', 'CatalogColors');
           $this->CatalogColors->showImageDialog();
        }else{
        ?>

        <tr>
         <td colspan="2">
             <script type="text/javascript">
             $(document).ready(function(){
                 $("#sortableImageOrder").sortable({
			placeholder: "ui-state-highlight",
                        update:function(event,ui){
                                $sortedArr=$("#sortableImageOrder").sortable('toArray');
                                $sortedStr=$sortedArr.join(',');
                                $("#imagesOrder").val($sortedStr);
                        }
		}).disableSelection();
             });

           </script>
          <fieldset title="<?=$this->multi['FLD_IMAGES']?>"><legend><img src='images/icons/pictures.png' alt="<?=$this->multi['FLD_IMAGES'];?>" title="<?=$this->multi['FLD_IMAGES'];?>" border="0" /> <?=$this->multi['FLD_IMAGES'];?></legend>
              <?
              if ( count($img_arr)>0  ){
                  ?>
              <input type="hidden" id="imagesOrder" name="imagesOrder" value=""/>
              <ul id="sortableImageOrder" class="sortableUl"><?
                  $jtmp=0;
                  for($itmp=0;$itmp<count($img_arr);$itmp++){
                    if (isset($img_arr[$itmp])) {
                        echo '<li id="'.$img_arr[$itmp]['id'].'"><div>'.$this->ShowCurrentImage($img_arr[$itmp]['id'], 'size_auto=100', 85, NULL, "border=0"); echo '&nbsp </div></li>';
                        $jtmp++;
                    }
                  }
                  ?> </ul><?
                echo '<br style="clear:both;"/><a href="'.$this->script.'&task=showpicture&id='.$this->id.'">'.$this->multi['TXT_ADD_EDIT'].'</a>'.' ['.count($img_arr).']<br/><br/>';
              }
              ?>
              <input type="hidden" name="MAX_FILE_SIZE" value="<?=MAX_IMAGE_SIZE;?>">
              <?
              for($i=0;$i<UPLOAD_IMAGES_COUNT; $i++){
                ?><INPUT TYPE="file" NAME="image[]" size="80" VALUE="<?=$this->img['name'][$i]?>"><br/><?
              }
              ?>
          </fieldset>
         </td>
        </tr>
        <?
        }
    }// end if
    //-------------------- Images End ---------------------

    //-------------------- Files Start ---------------------
    if ( isset($this->settings['files']) AND $this->settings['files']=='1' ){
        ?>
        <tr>
         <td colspan="2">
          <fieldset title="<?=$this->multi['FLD_FILES']?>"><legend><img src='images/icons/files.png' alt="<?=$this->multi['FLD_FILES'];?>" title="<?=$this->multi['FLD_FILES'];?>" border="0" /> <?=$this->multi['FLD_FILES'];?></legend><?
             $files_arr = $this->GetFiles($this->id, 'back');
             if ( count($files_arr)>0  ){
                 for($jjj=0;$jjj<count($files_arr);$jjj++){
                     echo SITE_PATH.$this->settings['files_path'].'/'.$this->id.'/'.$files_arr[$jjj]['path'].'<br/>';
                 }
                 ?>
                 <a href="<?=$this->script.'&task=showfiles&id='.$this->id;?>">
                 <?=$this->multi['TXT_ADD_EDIT'].'</a>'.' ['.count($files_arr).']<br/><br/>';
             }
             ?>
             <input type="hidden" name="MAX_FILE_SIZE" value="<?=MAX_FILE_SIZE;?>">
             <?
             for($i=0;$i<UPLOAD_FILES_COUNT; $i++){
                ?><INPUT TYPE="file" NAME="files[]" size="80" VALUE="<?=$this->files['name'][$i]?>"><br/><?
             }
             ?>
          </fieldset>
         </td>
        </tr>
        <?
    }// end if
    //-------------------- Files End ---------------------

    AdminHTML::PanelSimpleF();
    if( (!empty($this->id) AND $this->Right->IsUpdate()) OR (empty($this->id) AND $this->Right->IsWrite()) ){
        $this->Form->WriteSaveAndReturnPanel( $this->script );?>&nbsp;<?
        $this->Form->WriteSavePanel( $this->script );?>&nbsp;<?
    }
    $this->Form->WriteCancelPanel( $this->script );?>&nbsp;<?
    if( !empty($this->id) ){
       $CatalogLayout = &check_init('CatalogLayout', 'CatalogLayout');
       $CatalogLayout->mod_rewrite=1;
       //echo '<br>$publish='.$publish;
       $this->Form->WritePreviewPanelNewWindow( "http://".NAME_SERVER.$CatalogLayout->Link($mas['id_cat'], $this->id) );
    }
    AdminHTML::PanelSubF();

    $this->Form->WriteFooter();

    return true;
   } // end of function EditContent()

   // ================================================================================================
   // Function : ShowJS()
   // Version : 1.0.0
   // Date : 08.08.2007
   // Parms :
   // Returns : true,false / Void
   // Description : show form with rating from users about goods
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 08.08.2007
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowJS()
   {
       ?>
        <script type="text/javascript">
        function EditTranslit(div_id, idbtn){
            Did = "#"+div_id;
            idbtn = "#"+idbtn;
            if( !window.confirm('<?=$this->multi['MSG_DO_YOU_WANT_TO_EDIT_TRANSLIT'];?>')) return false;
            else{
              $(Did).removeAttr("disabled")
                     .focus();
              $(idbtn).css("display", "none");
            }
        } // end of function EditTranslit
        function CheckTranslitField(div_id, idtbl){
            Did = "#"+div_id;
            idtbl = "#"+idtbl;
            //alert('val='+(Did).val());
            if( $(Did).val()!='') $(idtbl).css("display", "none");
            else $(idtbl).css("display", "block");
        } // end of function CheckTranslitField

        function ChangeVisibility(div_id, mydata){
            did = "#visible"+div_id;
            $.ajax({
                    type: "POST",
                    data: mydata,
                    url: "/modules/mod_catalog/catalogcontent.backend.php",
                    success: function(msg){
                        //alert(msg);
                        $(did).html( msg );
                    },
                    beforeSend : function(){
                        //$("#sss").html("");
                        $(did).html('<div style="text-align:center;"><img src="/admin/images/ajax-loader.gif" alt="" title="" /></div>');
                    }
            });
        } // end of function ChangeVisibility

        function QuickChangeData(div_id, mydata){
            did = "#"+div_id;
            $.ajax({
                    type: "POST",
                    data: mydata,
                    url: "/modules/mod_catalog/catalogcontent.backend.php",
                    success: function(msg){
                        //alert(msg);
                        $(did).html( msg );
                    },
                    beforeSend : function(){
                        //$("#sss").html("");
                        $(did).html('<div style="text-align:center;"><img src="/admin/images/ajax-loader.gif" alt="" title="" /></div>');
                    }
            });
        } // end of function QuickChangeData

        function SaveName(id_prop){
            did = "#propnameres"+id_prop;
            val = $("#propname"+id_prop).val();
            $("#<?=$this->Form->name;?>.task").val("savename");
            mydata = "module=<?=$this->module;?>&task=savename&id="+id_prop+"&lang_id=<?=$this->lang_id;?>&new_name="+val;
            //alert('val='+val+' mydata='+mydata);
            $.ajax({
                    type: "POST",
                    data: mydata,
                    url: "/modules/mod_catalog/catalogcontent.backend.php",
                    success: function(msg){
                        $(did).html( msg );
                    },
                    beforeSend : function(){
                        $(did).html('<div style="text-align:center;"><img src="/admin/images/icons/loading_animation_liferay.gif" alt="" title="" /></div>');
                    }
            });
        } // end of function SaveName

        function SavePrice(div_id, id_prop){
            did = "#"+div_id;
            //price = $("#price"+id_prop).val();
            //price_currency = $("#price_currency"+id_prop).val();
            //$("#<?=$this->Form->name;?>.task").val("saveprice");
            ddd = $("#<?=$this->Form->name?>").serialize();
            //mydata = "module=<?=$this->module;?>&task=saveprice&id="+id_prop+"&price="+price+"&price_currency="+price_currency;
            mydata = ddd+"&task=saveprice&id="+id_prop;
            //alert('mydata='+mydata);
            $.ajax({
                    type: "POST",
                    data: mydata,
                    url: "/modules/mod_catalog/catalogcontent.backend.php",
                    success: function(msg){
                        $(did).html( msg );
                    },
                    beforeSend : function(){
                        $(did).html('<div style="text-align:center;"><img src="/admin/images/icons/loading_animation_liferay.gif" alt="" title="" /></div>');
                    }
            });

        } // end of function SavePrice

        function SavePriceOpt(div_id, id_prop){
            did = "#"+div_id;
            //price = $("#price"+id_prop).val();
            //price_currency = $("#price_currency"+id_prop).val();
            //$("#<?=$this->Form->name;?>.task").val("saveprice");
            ddd = $("#<?=$this->Form->name?>").serialize();
            //mydata = "module=<?=$this->module;?>&task=saveprice&id="+id_prop+"&price="+price+"&price_currency="+price_currency;
            mydata = ddd+"&task=savepriceopt&id="+id_prop;
            //alert('mydata='+mydata);
            $.ajax({
                    type: "POST",
                    data: mydata,
                    url: "/modules/mod_catalog/catalogcontent.backend.php",
                    success: function(msg){
                        $(did).html( msg );
                    },
                    beforeSend : function(){
                        $(did).html('<div style="text-align:center;"><img src="/admin/images/icons/loading_animation_liferay.gif" alt="" title="" /></div>');
                    }
            });

        } // end of function SavePriceOpt
        </script>
        <?
   }//end of function ShowJS()

   // ================================================================================================
   // Function : AddHTMLPriceLevel()
   // Version : 1.0.0
   // Date : 21.03.2006
   //
   // Parms :        $id - id of the record in the table
   // Returns :      true,false / Void
   // Description :  Checking all fields for filling and validation
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 10.01.2006
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function AddHTMLPriceLevel()
   {
        $arr_pice = $this->GetPriceLevels($this->id);
        //phpinfo();
        //echo '$this->id='.$this->id.' $arr_pice='.$arr_pice;print_r($arr_pice); echo '<br>';
        if ( isset($this->settings['price_levels_currency']) AND $this->settings['price_levels_currency']=='1' ){
            $Currencies = &check_init('SystemCurrencies', 'SystemCurrencies');
            $def_currency = $Currencies->GetDefaultCurrency();
        }
        else{
            $Currencies = NULL;
            $def_currency = NULL;
        }
        //echo '<br>count($arr_pice)='.count($arr_pice).' $this->qnt_from='.$this->qnt_from;
        //print_r($this->qnt_from);
        if( $this->Err!=NULL ){
            for($i=0;$i<count($this->price_level);$i++){
                $qnt_from=$this->qnt_from[$i];
                $qnt_to=$this->qnt_to[$i];
                $price_level=$this->price_level[$i];
                $price_levels_currency=$this->price_levels_currency[$i];
                if( empty($price_levels_currency) AND $price_levels_currency!='0' ) $price_levels_currency = $def_currency;
                echo $this->Msg->show_text('FLD_QUANTITY').' '.$this->Msg->show_text('FLD_QUANTITY_FROM');$this->Form->TextBox( 'qnt_from[]', stripslashes($qnt_from), 5 ); echo ' '.$this->Msg->show_text('FLD_QUANTITY_TO'); $this->Form->TextBox( 'qnt_to[]', stripslashes($qnt_to), 5 ); echo ' '.$this->Msg->show_text('FLD_PRICE'); $this->Form->TextBox( 'price_level[]', stripslashes($price_level), 10 );
                if($Currencies) $this->Form->Select($Currencies->GetShortNamesInArray('back'), 'price_levels_currency[]', $price_levels_currency);
                if( isset($arr_pice[$i]['id'])) {?><input type="button" name="button" value="   -   " onclick="javascript:del_price_level('module=<?=$this->module;?>&id_price_level=<?=$arr_pice[$i]['id']?>&id=<?=$this->id;?>', 'price_level_data<?=$this->cnt_div;?>');"><? }
                ?><br/><?
            }
        }
        else{
            if( $this->task!='add_html_price_level' AND is_array($arr_pice) AND count($arr_pice)>0){
                for($i=0;$i<count($arr_pice);$i++){
                    if( $this->id!=NULL ) $this->Err!=NULL ? $qnt_from=$this->qnt_from[$i] : $qnt_from=$arr_pice[$i]['qnt_from'];
                    else $qnt_from=$this->qnt_from[$i];
                    if( $this->id!=NULL ) $this->Err!=NULL ? $qnt_to=$this->qnt_to[$i] : $qnt_to=$arr_pice[$i]['qnt_to'];
                    else $qnt_to=$this->qnt_to[$i];
                    if( $this->id!=NULL ) $this->Err!=NULL ? $price_level=$this->price_level[$i] : $price_level=$arr_pice[$i]['price_level'];
                    else $price_level=$this->price_level[$i];
                    if( $this->id!=NULL ) $this->Err!=NULL ? $price_levels_currency=$this->price_levels_currency[$i] : $price_levels_currency=$arr_pice[$i]['currency'];
                    else $price_levels_currency=$this->price_levels_currency[$i];
                    if( empty($price_levels_currency) AND $price_levels_currency!='0' ) $price_levels_currency = $def_currency;
                    echo $this->Msg->show_text('FLD_QUANTITY').' '.$this->Msg->show_text('FLD_QUANTITY_FROM');$this->Form->TextBox( 'qnt_from[]', stripslashes($qnt_from), 5 ); echo $this->Msg->show_text('FLD_QUANTITY_TO'); $this->Form->TextBox( 'qnt_to[]', stripslashes($qnt_to), 5 ); echo $this->Msg->show_text('FLD_PRICE'); $this->Form->TextBox( 'price_level[]', stripslashes($price_level), 10 );
                    if($Currencies) $this->Form->Select($Currencies->GetShortNamesInArray('back'), 'price_levels_currency[]', $price_levels_currency);
                    ?><input type="button" name="button" value="   -   " onclick="javascript:del_price_level('module=<?=$this->module;?>&id_price_level=<?=$arr_pice[$i]['id']?>&id=<?=$this->id;?>', 'price_level_data<?=$this->cnt_div;?>');"><?
                    ?><br/><?
                }
            }
            else{
                //show empty fields for filling
                if( $this->id!=NULL ) $this->Err!=NULL ? $qnt_from=$this->qnt_from : $qnt_from='';
                else $qnt_from='';
                if( $this->id!=NULL ) $this->Err!=NULL ? $qnt_to=$this->qnt_to : $qnt_to='';
                else $qnt_to='';
                if( $this->id!=NULL ) $this->Err!=NULL ? $price_level=$this->price_level : $price_level='';
                else $price_level='';
                if( $this->id!=NULL ) $this->Err!=NULL ? $price_levels_currency=$this->price_levels_currency : $price_levels_currency='';
                else $price_levels_currency='';
                if( empty($price_levels_currency) AND $price_levels_currency!='0' ) $price_levels_currency = $def_currency;
                echo $this->Msg->show_text('FLD_QUANTITY').' '.$this->Msg->show_text('FLD_QUANTITY_FROM');$this->Form->TextBox( 'qnt_from[]', stripslashes($qnt_from), 5 ); echo $this->Msg->show_text('FLD_QUANTITY_TO'); $this->Form->TextBox( 'qnt_to[]', stripslashes($qnt_to), 5 ); echo $this->Msg->show_text('FLD_PRICE'); $this->Form->TextBox( 'price_level[]', stripslashes($price_level), 10 );
                if($Currencies) $this->Form->Select($Currencies->GetShortNamesInArray('back'), 'price_levels_currency[]', $price_levels_currency);
            }
        }
        //echo '<br>$this->cnt_div='.$this->cnt_div;
        $this->cnt_div=$this->cnt_div+1;
        //echo '<br>$this->cnt_div='.$this->cnt_div;
        ?>
        <?/*<div name="price_level_data<?=$this->cnt_div;?>" id="price_level_data<?=$this->cnt_div;?>">*/?>
        </div>
        <div name="price_level_data<?=$this->cnt_div;?>" id="price_level_data<?=$this->cnt_div;?>">
        <?
        if( $this->task!='del_html_price_level'){ ?><input type="button" name="button" value="   +   " onclick="javascript:add_price_level('module=<?=$this->module;?>&cnt_div=<?=$this->cnt_div;?>', 'price_level_data<?=$this->cnt_div;?>');"><?}
        ?>
        </div>
        <?
   }//end of function AddHTMLPriceLevel()


   // ================================================================================================
   // Function : CheckContentFields()
   // Version : 1.0.0
   // Date : 21.03.2006
   //
   // Parms :        $id - id of the record in the table
   // Returns :      true,false / Void
   // Description :  Checking all fields for filling and validation
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 10.01.2006
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function CheckContentFields($id = NULL)
   {
    $this->Err=NULL;

    $ln_sys = new SysLang();
    $ln_arr = $ln_sys->LangArray( _LANG_ID );
    while( $el = each( $ln_arr ) ){
        $lang_id = $el['key'];
        if( !empty( $this->translit[$lang_id] )) {
            $val = $this->IsExistTranslit($this->translit[$lang_id], $this->new_id_cat, $this->GetTreeCatData($this->new_id_cat, 'level'), $this->id );
            if ( !empty($val) ) $this->Err=$this->Err.$this->Msg->show_text('MSG_FLD_TRANSLIT_ALREADY_EXIST').' '.$this->Spr->GetNameByCod(TblModCatalogSprName, $this->new_id_cat, $this->lang_id, 1).'<br>';
        }
    }

    if (empty( $this->new_id_cat )) {
        $this->Err=$this->Err.$this->Msg->show_text('MSG_FLD_CATEGORY_EMPTY').'<br>';
    }
    /*
    if (empty( $this->id_manufac )) {
        $this->Err=$this->Err.$this->Msg->show_text('MSG_FLD_MANUFAC_EMPTY').'<br>';
    }
     */
    if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {
        if (empty( $this->name[_LANG_ID] )) {
            $this->Err=$this->Err.$this->Msg->show_text('MSG_FLD_NAME_EMPTY').'<br>';
        }
    }
    //echo '<br>$this->Err='.$this->Err.' $this->Msg->table='.$this->Msg->table;
    return $this->Err;
   } //end of fuinction CheckContentFields()

   // ================================================================================================
   // Function : SaveContent()
   // Version : 1.0.0
   // Date : 22.03.2006
   //
   // Parms :   $user_id, $module, $id, $group_menu, $level, $description, $function, $move
   // Returns : true,false / Void
   // Description : Store data to the table
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 22.03.2006
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function SaveContent()
   {
       $q="select * from `".TblModCatalogProp."` where id='".$this->id."'";
       $res = $this->Right->Query( $q, $this->user_id, $this->module );
       if( !$this->Right->result ) return false;
       $rows = $this->Right->db_GetNumRows();
       //echo '<br>$q='.$q;

       $id_cat_parent = $this->GetTreeCatData($this->new_id_cat, 'level');
       //echo '<br>$id_cat_parent='.$id_cat_parent;
       if($rows==0) $this->old_price=$this->price;
       if( isset($this->settings['share']) AND $this->settings['share']=='1' ){
           $this->shareProc=0;
           if($this->setPriceManually==0){
               if($this->share==1 && !empty($this->share_id) && $this->share_id>0){
                   $q="SELECT * FROM `".TblModShare."` WHERE `id`='".$this->share_id."'";
                   $res = $this->Right->Query( $q, $this->user_id, $this->module );
                   $share=$this->Right->db_FetchAssoc();
                   if($share['Active']==1){
                    $this->price=$this->old_price-($share['skidka']*$this->old_price/100);
                     $this->shareProc=$share['skidka'];
                   }
               }else{
                   $q="SELECT * FROM `".TblModShare."` WHERE `UseCateg`='1' AND `CategId`='".$this->new_id_cat."' AND `Active`='1'";
                   $res = $this->Right->Query( $q, $this->user_id, $this->module );
                   $categShareRows=$this->Right->db_GetNumRows();
                   $share=$this->Right->db_FetchAssoc();
                   if($categShareRows>0){
                    $this->price=$this->old_price-($share['skidka']*$this->old_price/100);
                    $this->shareProc=$share['skidka'];
                   }
                   if($categShareRows==0){
                       $q="SELECT * FROM `".TblModShare."` WHERE `UseManufac`='1' AND `manufacId`='".$this->id_manufac."' AND `Active`='1'";
                      // echo $q;die;
                       $res = $this->Right->Query( $q, $this->user_id, $this->module );
                       $ManufShareRows=$this->Right->db_GetNumRows();
                        $share=$this->Right->db_FetchAssoc();
                        if($ManufShareRows>0){
                           $this->price=$this->old_price-($share['skidka']*$this->old_price/100);
                           $this->shareProc=$share['skidka'];
                        }
                   }
               }
           }
       }

       if($rows>0){
          $q="UPDATE `".TblModCatalogProp."` SET
              `id_cat`='".$this->new_id_cat."',
              `id_manufac`='".$this->id_manufac."',
              `id_group`='".$this->id_group."',
              `img`='',
              `exist`='".$this->exist."',
              `number_name`='".$this->number_name."',
              `price`='".$this->price."',
              `old_price`='".$this->old_price."',
              `opt_price`='".$this->opt_price."',
              `grnt`='".$this->grnt."',
              `dt`='".$this->dt."',
              `move`='".$this->move."',
              `share`='".$this->share."',
              `share_id`='".$this->share_id."',
              `visible`='".$this->visible."',
              `new`='".$this->new."',
              `best`='".$this->best."',
              `price_currency`='".$this->price_currency."',
              `opt_price_currency`='".$this->opt_price_currency."',
              `art_num`='".$this->art_num."',
              `setPriceManually`='".$this->setPriceManually."',
              `barcode`='".$this->barcode."',
              `dontUseSizes`='".$this->dontUseSizes."'";
          if(isset($this->colorsStr) && !empty($this->colorsStr)) $q.=", `colors`='".$this->colorsStr."'";
          $q=$q." WHERE `id`='".$this->id."'";
          $res = $this->Right->Query( $q, $this->user_id, $this->module );
//          echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
          if( !$res OR !$this->Right->result ) return false;

          //if change categor for current position then change and parent category for this position in translit table
          if($this->old_id_cat!=$this->new_id_cat){
              $q = "UPDATE `".TblModCatalogTranslit."` SET
                    `id_cat`='".$this->new_id_cat."',
                    `id_cat_parent`='".$id_cat_parent."'
                    WHERE `id_prop`='".$this->id."'";
              $res = $this->Right->Query( $q, $this->user_id, $this->module );
              //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
              if( !$res OR ! $this->Right->result ) return false;
          }
       }
       else{
          $q="SELECT MAX(`move`) AS `maxx` FROM `".TblModCatalogProp."` WHERE `id_cat`='".$this->new_id_cat."'";
          $res = $this->Right->Query( $q, $this->user_id, $this->module );
          $rows = $this->Right->db_GetNumRows();
          $my = $this->Right->db_FetchAssoc();
          $maxx=$my['maxx']+1;

          //$q="insert into `".TblModCatalogProp."` values(NULL,'$this->new_id_cat','$this->id_manufac','$this->id_group','','$this->exist','$this->number_name','$this->price','$this->opt_price','$this->grnt','$this->dt','$maxx', '$this->visible', '$this->price_currency', '$this->opt_price_currency')";
          $q="INSERT INTO `".TblModCatalogProp."` SET
              `id_cat`='".$this->new_id_cat."',
              `id_manufac`='".$this->id_manufac."',
              `id_group`='".$this->id_group."',
              `img`='',
              `exist`='".$this->exist."',
              `number_name`='".$this->number_name."',
              `price`='".$this->price."',
              `old_price`='".$this->price."',
              `opt_price`='".$this->opt_price."',
              `share`='".$this->share."',
              `share_id`='".$this->share_id."',
              `grnt`='".$this->grnt."',
              `dt`='".$this->dt."',
              `move`='".$maxx."',
              `visible`='".$this->visible."',
              `new`='".$this->new."',
              `best`='".$this->best."',
              `price_currency`='".$this->price_currency."',
              `opt_price_currency`='".$this->opt_price_currency."',
              `art_num`='".$this->art_num."',
              `setPriceManually`='".$this->setPriceManually."',
              `barcode`='".$this->barcode."',
              `dontUseSizes`='".$this->dontUseSizes."'
              ";
          if(isset($this->colorsStr) && !empty($this->colorsStr)) $q.=", `colors`='".$this->colorsStr."'";
          $res = $this->Right->Query( $q, $this->user_id, $this->module );
          //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
          if( !$this->Right->result) return false;
       }

       if ( empty($this->id) ){
          $this->id = $this->Right->db_GetInsertID();
          if ( isset($this->settings['cod_pli']) AND $this->settings['cod_pli']=='1' ) {
              $q="UPDATE `".TblModCatalogProp."` SET
                  `cod_pli`='".$this->id."'
                   WHERE `id`='".$this->id."'
                  ";
              $res = $this->Right->Query( $q, $this->user_id, $this->module );
              //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
              if( !$this->Right->result) return false;
          }
       }

       if(isset($this->colorsStr) && !empty($this->colorsStr)){
           $q="DELETE FROM `".TblModCatalogPropColors."` WHERE `id_prop`='".$this->id."'";
           $res = $this->Right->Query($q, $this->user_id, $this->module);
           $arrColors=explode(',', $this->colorsStr);
           for ($i = 0; $i < count($arrColors); $i++) {
               if($arrColors[$i]==-1) continue;
                $q="INSERT INTO `".TblModCatalogPropColors."` SET
                            `id_prop`='".$this->id."',
                            `id_color`='".$arrColors[$i]."'
                           ";
                   $res = $this->Right->Query($q, $this->user_id, $this->module);
           }
       }
        if($this->settings['sizes']==1){
           $q="DELETE FROM `".TblModCatalogPropSizes."` WHERE `id_prop`='".$this->id."'";
           $res = $this->Right->Query($q, $this->user_id, $this->module);
	   if(!$this->dontUseSizes){
	    if(isset($this->sizes) && count($this->sizes)){
	    $keys=array_keys($this->sizes);
	    for ($i = 0; $i < count($keys); $i++) {
		    $keysSizes=array_keys($this->sizes[$keys[$i]]);
//			$sizes=$this->sizes[$keys[$i]];
		    for ($j = 0; $j < count($keysSizes); $j++) {
			    $q="INSERT INTO `".TblModCatalogPropSizes."` SET
				`id_prop`='".$this->id."',
				`id_size`='".$keysSizes[$j]."',
				`id_color`='".$keys[$i]."'
			    ";
                            if($this->settings['sizesCount']==1){
                                $q.=", `cnt`='".$this->sizes[$keys[$i]][$keysSizes[$j]]."'";
                            }

                            if($this->settings['priceFromSizeColor']==1){
                                $this->priceSize[$keys[$i]][$keysSizes[$j]]=$this->old_priceSize[$keys[$i]][$keysSizes[$j]]-($this->shareProc*$this->old_priceSize[$keys[$i]][$keysSizes[$j]]/100);
                                $q.=", `price`='".($this->priceSize[$keys[$i]][$keysSizes[$j]])."'";
                                $q.=", `old_price`='".$this->old_priceSize[$keys[$i]][$keysSizes[$j]]."'";
                                $q.=", `price_currency`='".$this->price_currencySize[$keys[$i]][$keysSizes[$j]]."'";
                            }
			    $res = $this->Right->Query($q, $this->user_id, $this->module);
			    if( !$res ) return false;
		    }

	    }
	    }
           }else{
	       $keys=array_keys($this->dontUseSizesArr);
	       for ($i = 0; $i < count($keys); $i++) {
		     $q="INSERT INTO `".TblModCatalogPropSizes."` SET
                            `id_prop`='".$this->id."',
                            `id_size`='-1',
                            `id_color`='".$keys[$i]."'
                           ";
                   if($this->settings['sizesCount']==1){
                        $q.=", `cnt`='".$this->sizes[$keys[$i]][-1]."'";
                    }
                    if($this->settings['priceFromSizeColor']==1){
                        $this->priceSize[$keys[$i]][-1]=$this->old_priceSize[$keys[$i]][-1]-($this->shareProc*$this->old_priceSize[$keys[$i]][-1]/100);
                        $q.=", `price`='".$this->priceSize[$keys[$i]][-1]."'";
                        $q.=", `old_price`='".$this->old_priceSize[$keys[$i]][-1]."'";
                        $q.=", `price_currency`='".$this->price_currencySize[$keys[$i]][-1]."'";
                    }
                   $res = $this->Right->Query($q, $this->user_id, $this->module);
                       if( !$res ) return false;
	       }
	   }
       }
       if ( isset($this->settings['tags']) AND $this->settings['tags']=='1' ) {
          $Tags = new SystemTags();
          $res=$Tags->SaveTagsById( $this->module, $this->id, $this->id_tag );
          if( !$res ) return false;
       }

       if ( isset($this->settings['id_group']) AND $this->settings['id_group']=='1' ) {
          $res=$this->SaveGroupsByIdrop( $this->id, $this->id_group );
          if( !$res ) return false;
       }

       if ( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']=='1' ) {
          $res=$this->SaveMultiCategsByIdProp( $this->id, $this->multi_categs );
          if( !$res ) return false;
       }
       //---- Save fields on different languages ----
       $res=$this->Spr->SaveNameArr( $this->id, $this->name, TblModCatalogPropSprName );
       if( !$res ) return false;
       $res=$this->Spr->SaveNameArr( $this->id, $this->h1, TblModCatalogPropSprH1 );
       if( !$res ) return false;
       if ( isset($this->settings['short_descr']) AND $this->settings['short_descr']=='1' ) {
           $res=$this->Spr->SaveNameArr( $this->id, $this->short_descr, TblModCatalogPropSprShort );
           if( !$res ) return false;
       }

       if ( isset($this->settings['full_descr']) AND $this->settings['full_descr']=='1' ) {
           $res=$this->Spr->SaveNameArr( $this->id, $this->full_descr, TblModCatalogPropSprFull );
           if( !$res ) return false;
       }

       if ( isset($this->settings['specif']) AND $this->settings['specif']=='1' ) {
           $res=$this->Spr->SaveNameArr( $this->id, $this->specif, TblModCatalogPropSprSpecif );
           if( !$res ) return false;
       }

       if ( isset($this->settings['reviews']) AND $this->settings['reviews']=='1' ) {
           $res=$this->Spr->SaveNameArr( $this->id, $this->reviews, TblModCatalogPropSprReviews );
           if( !$res ) return false;
       }

       if ( isset($this->settings['support']) AND $this->settings['support']=='1' ) {
           $res=$this->Spr->SaveNameArr( $this->id, $this->support, TblModCatalogPropSprSupport );
           if( !$res ) return false;
       }

       //save price levels
       if ( isset($this->settings['price_levels']) AND $this->settings['price_levels']=='1' ) {
           $res=$this->SavePriceLevels();
           if( !$res ) return false;
       }

       //-------- SAVE PARAMETERS ----------
       $res = $this->SaveParamsValuesOfProp($this->id, $this->new_id_cat);
       if (!$res) return false;
       // join short name, prefix and sufix of parameters to the string and save it as number_name,
       // if field "name" is not set in settings of catalog
       /*
       if ( isset($this->settings['name']) AND $this->settings['name']!='1'){
           //phpinfo();
           $CatalogLayout = &check_init('CatalogLayout', 'CatalogLayout');
           $CatalogLayout->id = $this->id;
           $CatalogLayout->id_cat = $this->new_id_cat;
           $CatalogLayout->arr_params = $this->arr_params;
           $tmp_number_name = $CatalogLayout->SaveParamsValuesToNumberName();
           $this->number_name = $tmp_number_name;
       }//end if
       */
       //----- END SAVE PARAMETERS ---------

       //---------------- save META DATA START -------------------
       $res=$this->Spr->SaveNameArr( $this->id, $this->mtitle, TblModCatalogPropSprMTitle );
       if( !$res ) return false;
       $res=$this->Spr->SaveNameArr( $this->id, $this->mdescr, TblModCatalogPropSprMDescr );
       if( !$res ) return false;
       $res=$this->Spr->SaveNameArr( $this->id, $this->mkeywords, TblModCatalogPropSprMKeywords );
       if( !$res ) return false;
       //---------------- save META DATA END ---------------------


       //---------------- save translit of curent name START -----------------------
       // if field name is set in catalog settings then use it as translit, else use field number_name as translit.
       if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) $field_for_translit = $this->name;
       else {
           //prepare language array for translit
           $lang = &check_init('SysLang', 'SysLang');
           $ln_arr = $lang->LangArray( _LANG_ID );
           while( $el = each( $ln_arr ) ){
                $lang_id = $el['key'];
                $field_for_translit[$lang_id] = $this->number_name;
           }
       }

       //echo '<br>$field_for_translit='.print_r($field_for_translit);
       $res = $this->SaveTranslitProp($this->new_id_cat, $id_cat_parent, $this->id, $this->translit, $field_for_translit, $this->translit_old);
       if( !$res ) return false;
       //---------------- save translit of current name END  ------------------------

       //if ( empty($this->settings['img_path'])) $uploaddir = Img_Path;
       //else $uploaddir = $this->settings['img_path'].'/';
       //$Uploads = new Uploads( $this->user_id , $this->module , $uploaddir, 200, $this->module );
       //$Uploads->saveCurentImages($this->id, $this->module);

       return true;
   } // end of function SaveContent()

   // ================================================================================================
   // Function : CopyImagesToNewId()
   // Version : 1.0.0
   // Date : 30.08.2010
   // Parms :   $id_prop_from
   //           $id_prop_to
   // Returns : true,false / Void
   // Description : copy images from onw position to another
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 27.03.2006
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function CopyImagesToNewId($id_prop_from, $id_prop_to)
   {

       $q = "SELECT `".TblModCatalogPropImg."`.*, `".TblModCatalogPropImgTxt."`.`lang_id`, `".TblModCatalogPropImgTxt."`.`name`, `".TblModCatalogPropImgTxt."`.`text`
             FROM `".TblModCatalogPropImg."` LEFT JOIN `".TblModCatalogPropImgTxt."` ON (`".TblModCatalogPropImg."`.`id`=`".TblModCatalogPropImgTxt."`.`cod`)
             WHERE `".TblModCatalogPropImg."`.`id_prop`='".$id_prop_from."'
            ";
       $res = $this->Right->Query($q, $this->user_id, $this->module);
       $rows = $this->Right->db_GetNumRows();
       //echo '<br />$q='.$q.' $res='.$res.' $rows='.$rows;
       for($i=0;$i<$rows;$i++){
           $row_arr[$i] = $this->Right->db_FetchAssoc();
       }
       $path_old='';
       for($i=0;$i<$rows;$i++){
           $row = $row_arr[$i];
           //echo '<br />$row[path]='.$row['path'].' $path_old='.$path_old;
           if( !empty($row['path']) AND $row['path']!=$path_old ){
               $source = SITE_PATH.$this->settings['img_path'].'/'.$id_prop_from.'/'.$row['path'];
               //$ext = substr($row['path'],1 + strrpos($row['path'], "."));
               $uploaddir = SITE_PATH.$this->settings['img_path'].'/'.$id_prop_to;
               $uploaddir_0 = $uploaddir;
               if ( !file_exists ($uploaddir) ) mkdir($uploaddir,0777);
               else @chmod($uploaddir,0777);
               //$uploaddir2 = time().$i.'.'.$ext;
               $uploaddir2 = $row['path'];
               $uploaddir = $uploaddir."/".$uploaddir2;
               //echo '<br />$source='.$source.' <br />$uploaddir='.$uploaddir;
               $res = copy($source, $uploaddir);
               @chmod($uploaddir_0,0755);
               //echo '<br>$res='.$res;
               if(!$res) return false;
               $q = "INSERT INTO `".TblModCatalogPropImg."` SET
                     `id_prop` = '".$id_prop_to."',
                     `path` = '".$uploaddir2."',
                     `show` = '".$row['show']."',
                     `move` = '".$row['move']."',
                     `colid` = '".$row['colid']."'
                    ";
               $res = $this->Right->Query($q, $this->user_id, $this->module);
               //echo '<br />$q='.$q.' $res='.$res;
               if(!$res OR !$this->Right->result) return false;
               $id_new = $this->Right->db_GetInsertID();
               $path_old = $row['path'];
           }
           //echo '<br />$row[lang_id]'.$row['lang_id'].' empty($row[name]='.$row['name'].' $row[text]='.$row['text'];
           if( !empty($row['lang_id']) OR !empty($row['name']) OR !empty($row['text']) ){
               $q = "INSERT INTO `".TblModCatalogPropImgTxt."` SET
                     `cod` = '".$id_new."',
                     `lang_id` = '".$row['lang_id']."',
                     `name` = '".$row['name']."',
                     `text` = '".$row['text']."'
                    ";
               $res = $this->Right->Query($q, $this->user_id, $this->module);
               //echo '<br />$q='.$q.' $res='.$res;
               if(!$res OR !$this->Right->result) return false;
           }
       }
       return true;
   }//end of function CopyImagesToNewId()

   // ================================================================================================
   // Function : CopyFilesToNewId()
   // Version : 1.0.0
   // Date : 18.11.2010
   // Parms :   $id_prop_from
   //           $id_prop_to
   // Returns : true,false / Void
   // Description : copy files from onw position to another
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 18.11.2010
   // ================================================================================================
   function CopyFilesToNewId($id_prop_from, $id_prop_to)
   {

       $q = "SELECT `".TblModCatalogPropFiles."`.*, `".TblModCatalogPropFilesTxt."`.`lang_id`, `".TblModCatalogPropFilesTxt."`.`name`, `".TblModCatalogPropFilesTxt."`.`text`
             FROM `".TblModCatalogPropFiles."` LEFT JOIN `".TblModCatalogPropFilesTxt."` ON (`".TblModCatalogPropFiles."`.`id`=`".TblModCatalogPropFilesTxt."`.`cod`)
             WHERE `".TblModCatalogPropFiles."`.`id_prop`='".$id_prop_from."'
            ";
       $res = $this->Right->Query($q, $this->user_id, $this->module);
       $rows = $this->Right->db_GetNumRows();
       //echo '<br />$q='.$q.' $res='.$res.' $rows='.$rows;
       for($i=0;$i<$rows;$i++){
           $row_arr[$i] = $this->Right->db_FetchAssoc();
       }
       $path_old='';
       for($i=0;$i<$rows;$i++){
           $row = $row_arr[$i];
           //echo '<br />$row[path]='.$row['path'].' $path_old='.$path_old;
           if( !empty($row['path']) AND $row['path']!=$path_old ){
               $source = SITE_PATH.$this->settings['files_path'].'/'.$id_prop_from.'/'.$row['path'];
               //$ext = substr($row['path'],1 + strrpos($row['path'], "."));
               $uploaddir = SITE_PATH.$this->settings['files_path'].'/'.$id_prop_to;
               $uploaddir_0 = $uploaddir;
               if ( !file_exists ($uploaddir) ) mkdir($uploaddir,0777);
               else @chmod($uploaddir,0777);
               $uploaddir2 = $row['path'];
               $uploaddir = $uploaddir."/".$uploaddir2;
               //echo '<br />$source='.$source.' <br />$uploaddir='.$uploaddir;
               $res = copy($source, $uploaddir);
               @chmod($uploaddir_0,0755);
               if(!$res) return false;
               $q = "INSERT INTO `".TblModCatalogPropFiles."` values (NULL, '".$id_prop_to."', '".$uploaddir2."', '".$row['show']."', '".$row['move']."')";
               $res = $this->Right->Query($q, $this->user_id, $this->module);
               //echo '<br />$q='.$q.' $res='.$res;
               if(!$res OR !$this->Right->result) return false;
               $id_new = $this->Right->db_GetInsertID();
               $path_old = $row['path'];
           }
           if( !empty($row['lang_id']) OR !empty($row['name']) OR !empty($row['text']) ){
               $q = "INSERT INTO `".TblModCatalogPropFilesTxt."` values (NULL, '".$id_new."', '".$row['lang_id']."', '".$row['name']."', '".$row['text']."')";
               $res = $this->Right->Query($q, $this->user_id, $this->module);
               //echo '<br />$q='.$q.' $res='.$res;
               if(!$res OR !$this->Right->result) return false;
           }
       }
       return true;
   }//end of function CopyFilesToNewId()

   /**
    * Class method CopyColorsToNewId
    * copy colors from one item to new one.
    * @param integer $id_prop_from - id of the item form
    * @param integer $id_prop_to - id of the item to
    * @return true/false
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 16.08.2012
    */
   function CopyColorsToNewId($id_prop_from, $id_prop_to)
   {

       $q = "SELECT `".TblModCatalogPropColors."`.*
             FROM `".TblModCatalogPropColors."`
             WHERE `".TblModCatalogPropColors."`.`id_prop`='".$id_prop_from."'
            ";
       $res = $this->Right->Query($q, $this->user_id, $this->module);
       $rows = $this->Right->db_GetNumRows();
       //echo '<br />$q='.$q.' $res='.$res.' $rows='.$rows;
       for($i=0;$i<$rows;$i++){
           $row_arr[$i] = $this->Right->db_FetchAssoc();
       }
       $path_old='';
       for($i=0;$i<$rows;$i++){
           $row = $row_arr[$i];
           $q = "INSERT INTO `".TblModCatalogPropColors."` SET
                 `id_prop` = '".$id_prop_to."',
                 `id_color` = '".$row['id_color']."'
                ";
           $res = $this->Right->Query($q, $this->user_id, $this->module);
           //echo '<br />$q='.$q.' $res='.$res;
       }
   } //end of function CopyColorsToNewId

   /**
    * Class method CopySizesToNewId
    * copy sizes from one item to new one.
    * @param integer $id_prop_from - id of the item form
    * @param integer $id_prop_to - id of the item to
    * @return true/false
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 16.08.2012
    */
   function CopySizesToNewId($id_prop_from, $id_prop_to)
   {

       $q = "SELECT `".TblModCatalogPropSizes."`.*
             FROM `".TblModCatalogPropSizes."`
             WHERE `".TblModCatalogPropSizes."`.`id_prop`='".$id_prop_from."'
            ";
       $res = $this->Right->Query($q, $this->user_id, $this->module);
       $rows = $this->Right->db_GetNumRows();
       //echo '<br />$q='.$q.' $res='.$res.' $rows='.$rows;
       for($i=0;$i<$rows;$i++){
           $row_arr[$i] = $this->Right->db_FetchAssoc();
       }
       $path_old='';
       for($i=0;$i<$rows;$i++){
           $row = $row_arr[$i];
           $q = "INSERT INTO `".TblModCatalogPropSizes."` SET
                 `id_prop` = '".$id_prop_to."',
                 `id_size` = '".$row['id_size']."',
                 `cnt` = '".$row['cnt']."',
                 `id_color` = '".$row['id_color']."',
                 `price` = '".$row['price']."',
                 `old_price` = '".$row['old_price']."',
                 `price_currency` = '".$row['price_currency']."'
                ";
           $res = $this->Right->Query($q, $this->user_id, $this->module);
           //echo '<br />$q='.$q.' $res='.$res;
       }
   } //end of function CopySizesToNewId

   /**
    * Class method CopyRelatPropToNewId
    * copy sizes from one item to new one.
    * @param integer $id_prop_from - id of the item form
    * @param integer $id_prop_to - id of the item to
    * @return true/false
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 16.08.2012
    */
   function CopyRelatPropToNewId($id_prop_from, $id_prop_to)
   {

       $q = "SELECT `".TblModCatalogPropRelat."`.*
             FROM `".TblModCatalogPropRelat."`
             WHERE `".TblModCatalogPropRelat."`.`id_prop1`='".$id_prop_from."' OR `".TblModCatalogPropRelat."`.`id_prop2`='".$id_prop_from."'
            ";
       $res = $this->Right->Query($q, $this->user_id, $this->module);
       $rows = $this->Right->db_GetNumRows();
       //echo '<br />$q='.$q.' $res='.$res.' $rows='.$rows;
       for($i=0;$i<$rows;$i++){
           $row_arr[$i] = $this->Right->db_FetchAssoc();
       }
       for($i=0;$i<$rows;$i++){
           $row = $row_arr[$i];
           if($row['id_prop1']==$id_prop_from){
               $q = "INSERT INTO `".TblModCatalogPropRelat."` SET
                     `id_prop1` = '".$id_prop_to."',
                     `id_prop2` = '".$row['id_prop2']."',
                     `move` = '".$row['move']."'
                    ";
           }
           else{
               $q = "INSERT INTO `".TblModCatalogPropRelat."` SET
                     `id_prop1` = '".$row['id_prop1']."',
                     `id_prop2` = '".$id_prop_to."',
                     `move` = '".$row['move']."'
                    ";
           }
           $res = $this->Right->Query($q, $this->user_id, $this->module);
           //echo '<br />$q='.$q.' $res='.$res;
       }
   } //end of function CopyRelatPropToNewId

   // ================================================================================================
   // Function : DelContent()
   // Version : 1.0.0
   // Date : 27.03.2006
   // Parms :   $user_id, $module_id, $id_del
   // Returns : true,false / Void
   // Description :  Remove data from the table
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 27.03.2006
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function DelContent( $id_del )
   {
    $del = 0;
    $kol = count( $id_del );


    for( $i=0; $i<$kol; $i++ )
    {
     $u=$id_del[$i];

      $q = "delete from ".TblModCatalogProp." where `id`='".$u."'";
      $res = $this->Right->Query( $q, $this->user_id, $this->module );
      //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
      if( !$res ) return false;

      if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {
        $res = $this->Spr->DelFromSpr( TblModCatalogPropSprName, $u );
        if( !$res ) return false;
      }
      if ( isset($this->settings['short_descr']) AND $this->settings['short_descr']=='1' ) {
        $res = $this->Spr->DelFromSpr( TblModCatalogPropSprShort, $u );
        if( !$res ) return false;
      }
      if ( isset($this->settings['full_descr']) AND $this->settings['full_descr']=='1' ) {
        $res = $this->Spr->DelFromSpr( TblModCatalogPropSprFull, $u );
        //echo '<br>TblModCatalogPropSprFull res='.$res;
        if( !$res ) return false;
      }
      if ( isset($this->settings['specif']) AND $this->settings['specif']=='1' ) {
        $res = $this->Spr->DelFromSpr( TblModCatalogPropSprSpecif, $u );
        //echo '<br>TblModCatalogPropSprSpecif res='.$res;
        if( !$res ) return false;
      }
      if ( isset($this->settings['reviews']) AND $this->settings['reviews']=='1' ) {
        $res = $this->Spr->DelFromSpr( TblModCatalogPropSprReviews, $u );
        //echo '<br>TblModCatalogPropSprReviews res='.$res;
        if( !$res ) return false;
      }
      if ( isset($this->settings['support']) AND $this->settings['support']=='1' ) {
        $res = $this->Spr->DelFromSpr( TblModCatalogPropSprSupport, $u );
        //echo '<br>TblModCatalogPropSprSupport res='.$res;
        if( !$res ) return false;
      }

      //---------------- delete META DATA START -------------------
      $res = $this->Spr->DelFromSpr( TblModCatalogPropSprMTitle, $u );
      if( !$res ) return false;
      $res = $this->Spr->DelFromSpr( TblModCatalogPropSprMDescr, $u );
      if( !$res ) return false;
      $res = $this->Spr->DelFromSpr( TblModCatalogPropSprMKeywords, $u );
      if( !$res ) return false;
      //---------------- delete META DATA END ---------------------

      //--- delete translit ---
      $res = $this->DelTranslit( NULL, $u);

      if ( isset($this->settings['sizes']) AND $this->settings['sizes']=='1' ) {
        $q="DELETE FROM `".TblModCatalogPropSizes."` WHERE `id_prop`='".$u."'";
        $res = $this->Right->Query($q, $this->user_id, $this->module);
        if( !$res ) return false;
      }

      if ( isset($this->settings['imgColors']) AND $this->settings['imgColors']=='1' ) {
        $q="DELETE FROM `".TblModCatalogPropColors."` WHERE `id_prop`='".$u."'";
        $res = $this->Right->Query($q, $this->user_id, $this->module);
        if( !$res ) return false;
      }

      //--- delete relation between gropus for current position ---
      if ( isset($this->settings['id_group']) AND $this->settings['id_group']=='1' ) {
        $res=$this->DelGroupsByIdrop( $u );
        if( !$res ) return false;
      }

      //--- delete values of parameters for current position ---
      if ( isset($this->settings['cat_params']) AND $this->settings['cat_params']=='1' ) {
        $res = $this->DelParamsValuesOfProp( $u );
        if( !$res ) return false;
      }

      //--- delete relations with other positions for current position ---
      if ( isset($this->settings['relat_prop']) AND $this->settings['relat_prop']=='1' ) {
        $res = $this->DelRelatPropByIdProp($u);
        if( !$res ) return false;
      }

      //--- delete response and rating for current position ---
      if ( isset($this->settings['responses']) AND $this->settings['responses']=='1' ) {
        $q = "DELETE FROM ".TblModCatalogResponse." WHERE `id_prop`='".$u."'";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
        if( !$res ) return false;
      }

      //--- delete additional categories for current position ---
      if ( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']=='1' ) {
        $q = "DELETE FROM `".TblModCatalogPropMultiCategs."` WHERE `id_prop`='".$u."'";
        $res = $this->Right->Query($q, $this->user_id, $this->module );
        //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
        if(!$res OR !$this->Right->result) return false;
      }

      //--- delete price levels ---
      if ( isset($this->settings['price_levels']) AND $this->settings['price_levels']=='1' ) {
        $q = "DELETE FROM ".TblModCatalogPriceLevels." WHERE `id_prop`='".$u."'";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
        if(!$res OR !$this->Right->result) return false;
      }

       //--- delete relation between tags for current position ---
      if ( isset($this->settings['tags']) AND $this->settings['tags']=='1' ) {
        $Tags = &chek_init('SystemTags', 'SystemTags');
        $res=$Tags->DelTagsByModuleItem( $this->module, $u);
        if( !$res ) return false;
      }

      //--- delete pitures for current position ---
      if ( isset($this->settings['img']) AND $this->settings['img']=='1' ) {
        $res = $this->DelPicturesByIdProp($u);
        if( !$res ) return false;
      }

      //--- delete files for current position ---
      if ( isset($this->settings['files']) AND $this->settings['files']=='1' ) {
        $res = $this->DelFilesByIdProp($u);
        if( !$res ) return false;
      }


      if ( $res )
       $del=$del+1;
      else
       return false;
    }
     return $del;
   } //end of function DelContent()

   // ================================================================================================
   // Function : DelContentTesting()
   // Version : 1.0.0
   // Date : 27.03.2006
   // Parms :   $user_id, $module_id, $id_del
   // Returns : true,false / Void
   // Description :  Удаление данніх по товару. Пробовал делать с мин. кол-вом запросов - но результат пока нерадует. Похоже, что скорость удаления не увеличилась.
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 27.03.2006
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function DelContentTesting( $id_del )
   {
    $del = 0;
    $kol = count( $id_del );


    for( $i=0; $i<$kol; $i++ )
    {
     $u=$id_del[$i];
/*
      $q = "DELETE
                `".TblModCatalogProp."`,
                `".TblModCatalogPropSprName."`,
                `".TblModCatalogPropSprShort."`,
                `".TblModCatalogPropSprFull."`,
                `".TblModCatalogPropSprSpecif."`,
                `".TblModCatalogPropSprReviews."`,
                `".TblModCatalogPropSprSupport."`,
                `".TblModCatalogPropSprMTitle."`,
                `".TblModCatalogPropSprMDescr."`,
                `".TblModCatalogPropSprMKeywords."`,
                `".TblModCatalogPropSizes."`,
                `".TblModCatalogPropColors."`,
                `".TblModCatalogPropGroups."`,
                `".TblModCatalogPropMultiCategs."`,
                `".TblModCatalogPriceLevels."`,
                `".TblModCatalogParamsProp."`,
                `".TblModCatalogPropRelat."`,
                `".TblModCatalogTranslit."`,
                `".TblModCatalogResponse."`
            FROM
                `".TblModCatalogProp."`
                LEFT JOIN `".TblModCatalogPropSprName."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprName."`.`cod`)
                LEFT JOIN `".TblModCatalogPropSprShort."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprShort."`.`cod`)
                LEFT JOIN `".TblModCatalogPropSprFull."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprFull."`.`cod`)
                LEFT JOIN `".TblModCatalogPropSprSpecif."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprSpecif."`.`cod`)
                LEFT JOIN `".TblModCatalogPropSprReviews."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprReviews."`.`cod`)
                LEFT JOIN `".TblModCatalogPropSprSupport."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprSupport."`.`cod`)
                LEFT JOIN `".TblModCatalogPropSprMTitle."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprMTitle."`.`cod`)
                LEFT JOIN `".TblModCatalogPropSprMDescr."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprMDescr."`.`cod`)
                LEFT JOIN `".TblModCatalogPropSprMKeywords."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprMKeywords."`.`cod`)
                LEFT JOIN `".TblModCatalogPropSizes."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSizes."`.`id_prop`)
                LEFT JOIN `".TblModCatalogPropColors."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropColors."`.`id_prop`)
                LEFT JOIN `".TblModCatalogPropGroups."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropGroups."`.`id_prop`)
                LEFT JOIN `".TblModCatalogPropMultiCategs."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropMultiCategs."`.`id_prop`)
                LEFT JOIN `".TblModCatalogPriceLevels."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPriceLevels."`.`id_prop`)
                LEFT JOIN `".TblModCatalogParamsProp."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogParamsProp."`.`id_prop`)
                LEFT JOIN `".TblModCatalogPropRelat."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropRelat."`.`id_prop1` OR `".TblModCatalogProp."`.`id` = `".TblModCatalogPropRelat."`.`id_prop2`)
                LEFT JOIN `".TblModCatalogTranslit."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogTranslit."`.`id_prop`)
                LEFT JOIN `".TblModCatalogResponse."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogResponse."`.`id_prop`)
            WHERE
                `".TblModCatalogProp."`.`id`='".$u."'";
*/
      //удаляю сам товар и все мультиязычность для него
      $q = "DELETE
                `".TblModCatalogProp."`
           ";
      if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {
          $q .= ", `".TblModCatalogPropSprName."`";
      }
      if ( isset($this->settings['short_descr']) AND $this->settings['short_descr']=='1' ) {
          $q .= ", `".TblModCatalogPropSprShort."`";
      }
      if ( isset($this->settings['full_descr']) AND $this->settings['full_descr']=='1' ) {
          $q .= ", `".TblModCatalogPropSprFull."`";
      }
      if ( isset($this->settings['specif']) AND $this->settings['specif']=='1' ) {
          $q .= ", `".TblModCatalogPropSprSpecif."`";
      }
      if ( isset($this->settings['reviews']) AND $this->settings['reviews']=='1' ) {
          $q .= ", `".TblModCatalogPropSprReviews."`";
      }
      if ( isset($this->settings['support']) AND $this->settings['support']=='1' ) {
          $q .= ", `".TblModCatalogPropSprSupport."`";
      }
      $q .= ", `".TblModCatalogPropSprMTitle."`
             ,`".TblModCatalogPropSprMDescr."`
             ,`".TblModCatalogPropSprMKeywords."`
            ";
      $q .= "FROM
                `".TblModCatalogProp."`
            ";
      if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropSprName."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprName."`.`cod`)";
      }
      if ( isset($this->settings['short_descr']) AND $this->settings['short_descr']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropSprShort."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprShort."`.`cod`)";
      }
      if ( isset($this->settings['full_descr']) AND $this->settings['full_descr']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropSprFull."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprFull."`.`cod`)";
      }
      if ( isset($this->settings['specif']) AND $this->settings['specif']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropSprSpecif."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprSpecif."`.`cod`)";
      }
      if ( isset($this->settings['reviews']) AND $this->settings['reviews']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropSprReviews."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprReviews."`.`cod`)";
      }
      if ( isset($this->settings['support']) AND $this->settings['support']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropSprSupport."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprSupport."`.`cod`)";
      }
      $q .= " LEFT JOIN `".TblModCatalogPropSprMTitle."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprMTitle."`.`cod`)
              LEFT JOIN `".TblModCatalogPropSprMDescr."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprMDescr."`.`cod`)
              LEFT JOIN `".TblModCatalogPropSprMKeywords."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropSprMKeywords."`.`cod`)
            ";
      $q .= "WHERE
                `".TblModCatalogProp."`.`id`='".$u."'
            ";
      $res = $this->Right->Query( $q, $this->user_id, $this->module );
      echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
      if( !$res ) return false;



      //удаляю другие данные по товару
      $q = "DELETE
                `".TblModCatalogTranslit."`
           ";
      if ( isset($this->settings['sizes']) AND $this->settings['sizes']=='1' ) {
          $q .= ", `".TblModCatalogPropSizes."`";
      }
      if ( isset($this->settings['imgColors']) AND $this->settings['imgColors']=='1' ) {
          $q .= ", `".TblModCatalogPropColors."`";
      }
      if ( isset($this->settings['id_group']) AND $this->settings['id_group']=='1' ) {
          $q .= ", `".TblModCatalogPropGroups."`";
      }
      /*
      if ( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']=='1' ) {
          $q .= ", `".TblModCatalogPropMultiCategs."`";
      }
      */
      /*
      if ( isset($this->settings['price_levels']) AND $this->settings['price_levels']=='1' ) {
          $q .= ", `".TblModCatalogPriceLevels."`";
      }
       *
       */
      if ( isset($this->settings['cat_params']) AND $this->settings['cat_params']=='1' ) {
          $q .= ", `".TblModCatalogParamsProp."`";
      }
      if ( isset($this->settings['relat_prop']) AND $this->settings['relat_prop']=='1' ) {
          $q .= ", `".TblModCatalogPropRelat."`";
      }
      if ( isset($this->settings['responses']) AND $this->settings['responses']=='1' ) {
          $q .= ", `".TblModCatalogResponse."`";
      }
      $q .= " FROM
                `".TblModCatalogTranslit."`
            ";
      if ( isset($this->settings['sizes']) AND $this->settings['sizes']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropSizes."` ON (`".TblModCatalogTranslit."`.`id_prop` = `".TblModCatalogPropSizes."`.`id_prop`)";
      }
      if ( isset($this->settings['imgColors']) AND $this->settings['imgColors']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropColors."` ON (`".TblModCatalogTranslit."`.`id_prop` = `".TblModCatalogPropColors."`.`id_prop`)";
      }
      if ( isset($this->settings['id_group']) AND $this->settings['id_group']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropGroups."` ON (`".TblModCatalogTranslit."`.`id_prop` = `".TblModCatalogPropGroups."`.`id_prop`)";
      }
      /*
      if ( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropMultiCategs."` ON (`".TblModCatalogTranslit."`.`id_prop` = `".TblModCatalogPropMultiCategs."`.`id_prop`)";
      }
      */
      /*
      if ( isset($this->settings['price_levels']) AND $this->settings['price_levels']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPriceLevels."` ON (`".TblModCatalogTranslit."`.`id_prop` = `".TblModCatalogPriceLevels."`.`id_prop`)";
      }
       *
       */
      if ( isset($this->settings['cat_params']) AND $this->settings['cat_params']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogParamsProp."` ON (`".TblModCatalogTranslit."`.`id_prop` = `".TblModCatalogParamsProp."`.`id_prop`)";
      }
      if ( isset($this->settings['relat_prop']) AND $this->settings['relat_prop']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogPropRelat."` ON (`".TblModCatalogTranslit."`.`id_prop` = `".TblModCatalogPropRelat."`.`id_prop1` OR `".TblModCatalogTranslit."`.`id_prop` = `".TblModCatalogPropRelat."`.`id_prop2`)";
      }
      if ( isset($this->settings['responses']) AND $this->settings['responses']=='1' ) {
          $q .= " LEFT JOIN `".TblModCatalogResponse."` ON (`".TblModCatalogTranslit."`.`id_prop` = `".TblModCatalogResponse."`.`id_prop`)";
      }
      $q .= " WHERE
                `".TblModCatalogTranslit."`.`id_prop`='".$u."'
            ";
      $res = $this->Right->Query($q, $this->user_id, $this->module);
      echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
      if(!$res OR !$this->Right->result) return false;


      //--- delete additional categories for current position ---
      if ( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']=='1' ) {
        $q = "DELETE FROM `".TblModCatalogPropMultiCategs."` WHERE `id_prop`='".$u."'";
        $res = $this->Right->Query($q, $this->user_id, $this->module );
        echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
        if(!$res OR !$this->Right->result) return false;
      }

      //--- delete price levels ---
      if ( isset($this->settings['price_levels']) AND $this->settings['price_levels']=='1' ) {
        $q = "DELETE FROM ".TblModCatalogPriceLevels." WHERE `id_prop`='".$u."'";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
        if(!$res OR !$this->Right->result) return false;
      }

       //--- delete relation between tags for current position ---
      if ( isset($this->settings['tags']) AND $this->settings['tags']=='1' ) {
        $Tags = &chek_init('SystemTags', 'SystemTags');
        $res=$Tags->DelTagsByModuleItem( $this->module, $u);
        if( !$res ) return false;
      }

      //--- delete pitures for current position ---
      if ( isset($this->settings['img']) AND $this->settings['img']=='1' ) {
        $res = $this->DelPicturesByIdProp($u);
        if( !$res ) return false;
      }

      //--- delete files for current position ---
      if ( isset($this->settings['files']) AND $this->settings['files']=='1' ) {
        $res = $this->DelFilesByIdProp($u);
        if( !$res ) return false;
      }

      if ( $res )
       $del=$del+1;
      else
       return false;
    }
     return $del;
   } //end of function DelContentTesting()


   /**
    * Class method DelPicturesByIdProp
    * Remove images from table for this goods
    * @params integer $id_prop - id of the goods where the picture belongs
    * @return true/false
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 12.10.2012
    */
   function DelPicturesByIdProp($id_prop) {
        $q = "DELETE
                `" . TblModCatalogPropImg . "`,
                `" . TblModCatalogPropImgTxt . "`
              FROM
                `" . TblModCatalogPropImg . "`
                LEFT JOIN `" . TblModCatalogPropImgTxt . "` ON (`" . TblModCatalogPropImg . "`.`id` = `" . TblModCatalogPropImgTxt . "`.`cod`)
              WHERE `" . TblModCatalogPropImg . "`.`id_prop`='" . $id_prop . "'
             ";
        $res = $this->Right->Query($q);
        //echo '<br>2q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
        if (!$res OR !$this->Right->result)
            return false;

        $pathToPropImgDir = SITE_PATH . $this->settings['img_path'] . '/' . $id_prop;
        if(is_dir($pathToPropImgDir)) {
            $this->full_rmdir($pathToPropImgDir);
        }
        return true;
    }

// end of function DelPicturesByIdProp()


    /**
    * Class method DelFilesByIdProp
    * Remove files from table for this goods
    * @params integer $id_prop - id of the goods where the picture belongs
    * @return true/false
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 14.10.2012
    */
    function DelFilesByIdProp($id_prop) {
        $q = "DELETE
                `" . TblModCatalogPropFiles . "`,
                `" . TblModCatalogPropFilesTxt . "`
              FROM
                `" . TblModCatalogPropFiles . "`
                LEFT JOIN `" . TblModCatalogPropFilesTxt . "` ON (`" . TblModCatalogPropFiles . "`.`id` = `" . TblModCatalogPropFilesTxt . "`.`cod`)
              WHERE `" . TblModCatalogPropFiles . "`.`id_prop`='" . $id_prop . "'
             ";
        $res = $this->Right->Query($q);
        //echo '<br>3q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
        if (!$res OR !$this->Right->result)
            return false;

        $pathToPropImgDir = SITE_PATH . $this->settings['files_path'] . '/' . $id_prop;
        if(is_dir($pathToPropImgDir)) {
            $this->full_rmdir($pathToPropImgDir);
        }
        return true;
    }

   /**
   * Class method SaveMultiCategsByIdProp
   * store id of additional categories from $arr for position $id_item
   * @param integer $id_item - id of the position
   * @param array `$arr - array with id of additional categories
   * @return true/false
   * @author Igor Trokhymchuk  <ihor@seotm.com>
   * @version 1.0, 31.03.2011
   */
   function SaveMultiCategsByIdProp( $id_item, $arr )
   {
       $db = new DB();
       $q = "DELETE FROM `".TblModCatalogPropMultiCategs."` WHERE `id_prop`='".$id_item."'";
       $res = $db->db_Query( $q );
       //echo '<br> $q='.$q.' $res='.$res.' $db->result='.$db->result;
       if( !$res OR !$db->result ) {return false;}
       if( !is_array($arr) ) return true;
       $keys = array_keys($arr);
       $cnt = count($keys);
       for($i=0;$i<$cnt;$i++){
           $q = "INSERT INTO `".TblModCatalogPropMultiCategs."` SET
                 `id_prop`='".$id_item."',
                 `id_cat`='".$keys[$i]."'";
           $res = $db->db_Query( $q );
           //echo '<br> $q='.$q.' $res='.$res.' $db->result='.$db->result;
           if( !$res OR !$db->result ) {return false;}
       }
       return true;
   } //end of function SaveMultiCategsByIdProp()

   /**
   * Class method GetMultiCategsByIdProp
   * get array of id of additional categories to $arr for position $id_item
   * @param integer $id_item - id of the position
   * @return array $arr with list of id of additional categories
   * @author Igor Trokhymchuk  <ihor@seotm.com>
   * @version 1.0, 31.03.2011
   */
   function GetMultiCategsByIdProp( $id_item=NULL )
   {
       $db = new DB();
       $q = "SELECT `id_cat` FROM `".TblModCatalogPropMultiCategs."` WHERE `id_prop`='".$id_item."'";
       $res = $db->db_Query( $q );
       //echo '<br> $q='.$q.' $res='.$res.' $db->result='.$db->result;
       if( !$res OR !$db->result ) {return false;}
       $rows = $db->db_GetNumRows();
       //echo '<br>$rows='.$rows;
       $arr = array();
       for($i=0;$i<$rows;$i++){
            $row = $db->db_FetchAssoc();
            $arr[$row['id_cat']] =  $row['id_cat'];
       }
       return $arr;
   } //end of function GetMultiCategsByIdProp()

   /**
   * Class method DelMultiCategsByIdProp
   * delete list of additional categories for position $id_item
   * @param integer $id_item - id of the position
   * @return true/false
   * @author Igor Trokhymchuk  <ihor@seotm.com>
   * @version 1.0, 31.03.2011
   */
   function DelMultiCategsByIdProp($id_item)
   {
       $db = new DB();
       $q = "DELETE FROM `".TblModCatalogPropMultiCategs."` WHERE `id_prop`='".$id_item."'";
       $res = $db->db_Query( $q );
       //echo '<br> $q='.$q.' $res='.$res.' $db->result='.$db->result;
       if( !$res OR !$db->result ) {return false;}
       return true;
   } //end of function DelMultiCategsByIdProp()


   // ================================================================================================
   // Function : SaveGroupsByIdrop()
   // Version : 1.0.0
   // Date : 16.01.2009
   // Parms :   $id_item - id of item position in module $id_module
   //           $arr - array with values
   // Returns : true,false / Void
   // Description : save gropus by id of position
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 16.01.2009
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function SaveGroupsByIdrop( $id_item, $arr )
   {
       $db = new DB();
       $q = "DELETE FROM `".TblModCatalogPropGroups."` WHERE `id_prop`='".$id_item."'";
       $res = $db->db_Query( $q );
       //echo '<br> $q='.$q.' $res='.$res.' $db->result='.$db->result;
       if( !$res OR !$db->result ) {return false;}
       for($i=0;$i<count($arr);$i++){
           $q = "INSERT INTO `".TblModCatalogPropGroups."` SET
                 `id_prop`='".$id_item."',
                 `id_group`='".$arr[$i]."'";
           $res = $db->db_Query( $q );
           //echo '<br> $q='.$q.' $res='.$res.' $db->result='.$db->result;
           if( !$res OR !$db->result ) {return false;}
       }
       return true;
   } //end of function SaveGroupsByIdrop()

   // ================================================================================================
   // Function : GetGroupsByIdProp()
   // Version : 1.0.0
   // Date : 16.01.2009
   // Parms :  $id_item - id of item position
   // Returns : true,false / Void
   // Description : get groups by id of position
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 16.01.2009
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function GetGroupsByIdProp( $id_item=NULL )
   {
       $db = new DB();
       $q = "SELECT `id_group` FROM `".TblModCatalogPropGroups."` WHERE `id_prop`='".$id_item."'";
       $res = $db->db_Query( $q );
       //echo '<br> $q='.$q.' $res='.$res.' $db->result='.$db->result;
       if( !$res OR !$db->result ) {return false;}
       $rows = $db->db_GetNumRows();
       //echo '<br>$rows='.$rows;
       $arr = array();
       for($i=0;$i<$rows;$i++){
            $row = $db->db_FetchAssoc();
            $arr[$i] =  $row['id_group'];
       }
       return $arr;
   } //end of function GetGroupsByIdProp()

   // ================================================================================================
   // Function : DelGroupsByIdrop()
   // Version : 1.0.0
   // Date : 16.01.2009
   // Parms :  id_item    / id of the item position
   // Returns : true,false / Void
   // Description : delete relative between item position and groups
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 16.01.2009
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function DelGroupsByIdrop($id_item)
   {
       $db = new DB();
       $q = "DELETE FROM `".TblModCatalogPropGroups."` WHERE `id_prop`='".$id_item."'";
       $res = $db->db_Query( $q );
       //echo '<br> $q='.$q.' $res='.$res.' $db->result='.$db->result;
       if( !$res OR !$db->result ) {return false;}
       return true;
   } //end of function DelGroupsByIdrop()


   // ================================================================================================
   // Function : ShowMoveToCategoryForm()
   // Version : 1.0.0
   // Date : 19.03.2008
   // Parms :
   // Returns : true,false / Void
   // Description : show form for move position from one category to another
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 19.03.2008
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowMoveToCategoryForm( $id_del=NULL )
   {
       ?>
       <script type="text/javascript">
        var form = "";
        var submitted = false;
        var error = false;
        var error_message = "";

        function check_select(field_name, field_default, message) {
          if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
            var field_value = form.elements[field_name].value;
            if (field_value == field_default) {
              error_message = error_message + "* " + message + "\n";
              error = true;
            }
          }
        }

        function check_form_move(form_name) {
          error_message = '';
          if (submitted == true) {
            alert("<?=$this->Msg->show_text('MSG_FRONT_ERR_FORM_ALREADY_SUBMITED');?>");
            return false;
          }

          error = false;
          form = form_name;

          check_select("id_cat_move_from", '', "<?=$this->Msg->show_text('ERR_EMPTY_CATEGORY_MOVE_FROM');?>");
          check_select("id_cat_move_to", '', "<?=$this->Msg->show_text('ERR_EMPTY_CATEGORY_MOVE_TO');?>");

          if (error == true) {
            alert(error_message);
            return false;
          } else {
            submitted = true;
            return true;
          }
        }
        </script>
        <?

        //phpinfo();
        /* Write Form Header */
        $this->Form->WriteHeader( $this->script, 'onsubmit="return check_form_move(this);"' );
        $this->Form->Hidden( 'id', $this->id );
        $this->Form->Hidden( 'move', $this->move );
        $this->Form->Hidden( 'group', $this->group );
        $this->Form->Hidden( 'id_cat', $this->id_cat );
        $this->Form->Hidden( 'display', $this->display );
        $this->Form->Hidden( 'start', $this->start );
        $this->Form->Hidden( 'sort', $this->sort );
        $this->Form->Hidden( 'fln', $this->fln );
        $this->Form->Hidden( 'srch', $this->srch );
        $this->Form->Hidden( 'fltr', $this->fltr );
        $this->Form->Hidden( 'fltr2', $this->fltr2 );
        $this->Form->Hidden( 'task', 'move_to_category' );

        $arr_categs = $this->GetCatalogInArray(NULL, $this->Msg->show_text('TXT_SELECT_CATEGORY'), NULL, NULL, 0, 'back', 1, 1);
        //print_r($arr_categs);

        AdminHTML::PanelSubH( $this->Msg->show_text('TXT_MOVE_FROM_CATEGORY_TO_CATEGORY' ) );
        AdminHTML::PanelSimpleH();
        ?>
        <table border="0" cellspacing="1" cellpading="0" class="EditTable">
         <tr>
          <td><b><?=$this->Msg->show_text('TXT_SELECT_CATEGORY_FROM_MOVE');?>:</b></td>
          <td>
           <?
           if( !isset($this->id_cat_move_from) OR empty($this->id_cat_move_from) ) $this->id_cat_move_from = $this->id_cat;
           $this->Form->Select( $arr_categs, 'id_cat_move_from', 'categ='.$this->id_cat_move_from );
           ?>
          </td>
         </tr>
         <tr>
          <td>
           <div name="debug" id="debug">
           <?
           for($i=0;$i<count($id_del);$i++){
               $this->Form->Hidden( 'id_del[]', $id_del[$i] );
               if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {
                    $name = $this->Spr->GetNameByCod(TblModCatalogPropSprName, $id_del[$i]);
               }
               else{
                    $name = $this->GetNumberName($id_del[$i]);
               }
               echo ($i+1).'. '.$name;?><br/><?
           }//end for
           ?>
           </div>
          </td>
         </tr>
         <tr>
          <td><b><?=$this->Msg->show_text('_BUTTON_MOVE_TO_CATEGORY');?>:</b></td>
          <td>
           <?
           $this->Form->Select( $arr_categs, 'id_cat_move_to', 'categ='.$this->id_cat_move_to );
           ?>
          </td>
         </tr>
         </tr>
         <?
         if ( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']=='1' ) { ?>
             <tr>
              <td><b><?=$this->Msg->show_text('FLD_ADDITIONAL_CATEGORIES');?>:</b></td>
              <td>
                 <?
                 if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->multi_categs : $val=$this->GetMultiCategsByIdProp($this->id);
                 else $val=$this->multi_categs;
                 ?>
                 <div id="tag-categories-all" class="ui-tabs-panel">
                  <?
                  $arr_categs = $this->PrepareCatalogForSelect(0, NULL, $spacer = NULL, 'back', true, false, false, false, NULL, NULL, 0);
                  $this->ShowCatalogInCheckbox($arr_categs, NULL, 'multi_categs', $val,'');
                  ?>
                 <div>
              </td>
             </tr>
         <?}?>
         <tr>
          <td></td>
          <td>
           <?=$this->Form->Button('submit', $this->Msg->show_text('BTN_MOVE'), 50);?>
          </td>
         </tr>
        </table>
        <?
        AdminHTML::PanelSimpleF();
        if( empty($this->id_cat) ) $txtback = $this->Msg->show_text('TXT_ROOT_CATEGORY');
        else $txtback = $this->Spr->GetNameByCod(TblModCatalogSprName, $this->id_cat);
        $txtback = $this->Msg->show_text('TXT_BACK_TO').' "'.$txtback.'"';
        ?><a CLASS="toolbar" href="<?=$this->script;?>&task=show" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('move','','images/icons/restore_f2.png',1);"><img src="images/icons/restore.png" alt="<?=$txtback;?>" title="<?=$txtback;?>" align="center" name="move" border="0" /><?=$txtback;?></a><?
        AdminHTML::PanelSubF();
        $this->Form->WriteFooter();
   } //end of function ShowMoveToCategoryForm()

    // ================================================================================================
    // Function : MoveToCategory
    // Version : 1.0.0
    // Date : 19.03.2008
    //
    // Parms :
    // Returns : $res / Void
    // Description : move position to selected category
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 19.03.2008
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function MoveToCategory( $id_del )
    {
        $tmp_db = DBs::getInstance();
        $this->del = 0;
        $kol = count( $id_del );
        ?>
        <textarea readonly="readonly" style="width:100%; height: 200px;"><?=$this->Msg->show_text('TXT_MOVED_POSITIONS');?>:
        <?
        if($kol>0){
            for( $i=0; $i<$kol; $i++ ){
                $u=$id_del[$i];
                if ( isset($this->settings['name']) AND $this->settings['name']=='1' ) {
                    $name = $this->Spr->GetNameByCod(TblModCatalogPropSprName, $u, $this->lang_id, 1);
                }
                else{
                    $name = $this->GetNumberName($u);
                }
                echo "\n".($i+1).'. ['.$u.'] '.$name;

                $q = "UPDATE ".TblModCatalogProp." SET `id_cat`='".$this->id_cat_move_to."' WHERE `id`='".$u."' AND `id_cat`='".$this->id_cat_move_from."'";
                $res = $this->Right->Query( $q, $this->user_id, $this->module );
                //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
                if( !$res OR !$this->Right->result ) return false;

                //--- update category in Translit table ---
                $q = "UPDATE ".TblModCatalogTranslit." SET `id_cat`='".$this->id_cat_move_to."', `id_cat_parent`='".$this->GetTreeCatData($this->id_cat_move_to, 'level')."' WHERE `id_prop`='".$u."'";
                $res = $this->Right->Query( $q, $this->user_id, $this->module );
                //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
                if( !$res OR !$this->Right->result ) return false;

                if ( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']=='1' ) {
                    $res=$this->SaveMultiCategsByIdProp( $u, $this->multi_categs );
                    if( !$res ) return false;
                }

                echo ' - OK';
                $this->del=$this->del+1;
            }//end for
        }
        else{
            $q = "SELECT `id` FROM ".TblModCatalogProp." WHERE `id_cat`='".$this->id_cat_move_from."'";
            $res = $tmp_db->db_Query( $q );
            $rows = $tmp_db->db_GetNumRows();
            for( $i=0; $i<$rows; $i++ ){
                $row = $tmp_db->db_FetchAssoc();
                $q = "UPDATE ".TblModCatalogProp." SET `id_cat`='".$this->id_cat_move_to."' WHERE `id`='".$row['id']."' AND `id_cat`='".$this->id_cat_move_from."'";
                $res = $this->Right->Query( $q, $this->user_id, $this->module );
                if( !$res OR !$this->Right->result ) return false;
                echo "\n".($i+1).'. ['.$row['id'].'] '.$this->Spr->GetNameByCod(TblModCatalogPropSprName, $row['id'], $this->lang_id, 1);

                //--- update category in Translit table ---
                $q = "UPDATE ".TblModCatalogTranslit." SET `id_cat`='".$this->id_cat_move_to."', `id_cat_parent`='".$this->GetTreeCatData($this->id_cat_move_to, 'level')."' WHERE `id_prop`='".$row['id']."'";
                $res = $this->Right->Query( $q, $this->user_id, $this->module );
                //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
                if( !$res OR !$this->Right->result ) return false;

                if ( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']=='1' ) {
                    $res=$this->SaveMultiCategsByIdProp( $row['id'], $this->multi_categs );
                    if( !$res ) return false;
                }

                echo ' - OK';
                $this->del=$this->del+1;
            }
            /*
            $q = "UPDATE ".TblModCatalogProp." SET `id_cat`='".$this->id_cat_move_to."' WHERE `id_cat`='".$this->id_cat_move_from."'";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br>$rows='.$rows.' $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
            if( !$res OR !$this->Right->result ) return false;
            */
            //$this->del = $rows;
        }
        ?></textarea><?
        return true;
    }//end of function MoveToCategory()

    // ================================================================================================
    // Function : ShowPicture
    // Version : 1.0.0
    // Date : 03.04.2006
    //
    // Parms :
    // Returns : $res / Void
    // Description : Show the immages of item product
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 03.04.2006
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowPicture()
    {
     $Panel = &check_init('Panel', 'Panel');
     $ln_sys = &check_init('SysLang', 'SysLang');
     echo '<a href="'.$this->script.'"> '.$this->multi['TXT_BACK_TO'].' ';
     if ( !empty($this->id_cat) ) echo $this->Spr->GetNameByCod(TblModCatalogSprName,$this->id_cat, $this->lang_id, 1);
     else echo $this->multi['FLD_CONTENT'];
     echo '</a>';

     $txt = $this->multi['TXT_ADDITING_IMAGES_FOR'];
     $number_name = $this->GetNumberName($this->id);
     if( !empty($number_name) ) $number_name = '['.$number_name.']';
     $txt.=' <strong>'.$this->Spr->GetNameByCod( TblModCatalogPropSprName, $this->id, $this->lang_id, 1 ).' '.$number_name.'</strong>';
     AdminHTML::PanelSubH( $txt );

    //-------- Show Error text for validation fields --------------
    $this->ShowErrBackEnd();
    //-------------------------------------------------------------

    $params = $this->GetCountOfParamsInfluenceOnImage( $this->GetCategory($this->id) );
    //echo '<br> $params='.$params;
    if ($params>0) {
        $params_row = $this->GetParamsInfluenceOnImage( $this->GetCategory($this->id) );
    }
    else $params_row = 0;
    //echo '<br><br>$params_row='; print_r($params_row);


     ?>
     <FORM ACTION="<?=$_SERVER['PHP_SELF']?>" name="AddImg" enctype="multipart/form-data" method="post">

     <?
     //phpinfo();

     $up = 0;
     $down = 0;
     if($this->settings['imgColors']==1){
         $colorsClass=&check_init('CatalogColors', 'CatalogColors', "'$this->user_id', '$this->module'");
         $colorsClass->getColorsOfProp($this->id);
         //$colorsClass->colors[]='-1';
         $colorsClass->treeColorsData[-1]['name']=$this->multi['TXT_IMAGES_WITHIUT_COLORS'];
         ?>
         <script type="text/javascript">
            $(document).ready(function(){
                $( "#tabsColors,#tabsAddImages" ).tabs({
                    cookie: {
                        // store cookie for a day, without, it would be a session cookie
                        expires: 1
                    }
                });
            });
         </script>

         <div id="tabsColors" >
            <ul>
                <?//print_r($this->treeColorsData)?>
                <?
                for ($c = 0; $c < count($colorsClass->colors); $c++) {
                    if(empty($colorsClass->colors[$c])) continue;
                    $img=NULL;
                    if($colorsClass->colors[$c]!=-1 && !empty($colorsClass->treeColorsData[$colorsClass->colors[$c]]['img']))
                    $img = "/images/spr/mod_catalog_spr_colors/" . $this->lang_id . "/" . $colorsClass->treeColorsData[$colorsClass->colors[$c]]['img'];
                    ?>
                    <li>
                        <a <?if($colorsClass->colors[$c]==-1) echo 'style="line-height: 20px;"';?> href="#tabs-<?=$c?>">
                        <?
                        if(isset($img)){
                            echo $this->ShowCurrentImage($img, 'size_auto=20', 85, NULL, "border=0 alt='" . $colorsClass->treeColorsData[$colorsClass->colors[$c]]['name'] . "' title='" . $colorsClass->treeColorsData[$colorsClass->colors[$c]]['name'] . "'");
                        }
                        elseif($colorsClass->colors[$c]!=-1){
                            echo '<div style="float:left;width:20px;height:20px;background-color:#'.$colorsClass->treeColorsData[$colorsClass->colors[$c]]['colorsBit'].'"></div>'?>&nbsp;<?
                        }
                        echo $colorsClass->treeColorsData[$colorsClass->colors[$c]]['name'];?></a><?
                        ?>
                    </li>
                    <?
                }
                ?>
            </ul>

         <?
        $cntRowByAllColors = 0;
        for ($c = 0; $c < count($colorsClass->colors); $c++) {
            ?><div id="tabs-<?=$c?>" class="colorTabs"><?
            $q="SELECT * FROM `".TblModCatalogPropImg."` WHERE `id_prop`='".$this->id."'";
            if($colorsClass->colors[$c]!=-1 && $colorsClass->colors[$c]!=0) $q.=" AND colid='".$colorsClass->colors[$c]."'";
            else $q.=" AND (colid='0' OR colid='-1')";
            $q.=" order by `move`";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br>q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
            if( !$res OR !$this->Right->result ) return false;
            $rows = $this->Right->db_GetNumRows();
            $a = $rows;
            for ($i=0; $i<$rows; $i++) {
                $row = $this->Right->db_FetchAssoc();
                $arr_data[$i] = $row;
            }
            ?>
            <Table border="0" class="EditTable"><?
            for ($i=0; $i<$rows; $i++) {
                if ( (float)$i/2 == round( $i/2 ) ){
                    $style="TR1";
                }
                else $style="TR2";
                $row = $arr_data[$i];
                ?>
                <TR <?="class='$style'";?>>
                <TD colspan=2>
                <?//=AdminHTML::PanelSimpleH();?>
                    <TABLE border=0 cellpadding=2 cellspacing=0 class="EditTable">
                    <TR>
                    <TD align=right><INPUT class='checkbox' TYPE=checkbox NAME='id_img_del[]' VALUE="<?=$row['id'];?>"></TD>
                    <TD align=left valign="middle">
                    <a href="http://<?=NAME_SERVER?>/thumb.php?img=<?=$this->settings['img_path'].'/'.$row['id_prop'].'/'.$row['path']?>&amp;wtm=img" target=_blank><?=$this->ShowCurrentImage($row['id'], 'size_auto=200', 85, NULL, "border=0");?></a>
                    </TD>
                    <TD valign="top">
                    <table border=0 cellpadding=0 cellspacing=2 class="EditTable">
                        <tr>
                        <TD><b><?=$this->multi['FLD_ID'];?>:</b><?=$row['id']; $this->Form->Hidden( 'id_img[]', $row['id'] );?></TD>
                        </tr>
                        <tr>
                        <TD><b><?=$this->multi['FLD_IMG'];?>:</b> <?=SITE_PATH.$this->settings['img_path'].'/'.$row['id_prop'].'/'.$row['path']?></TD>
                        </tr>
                        <tr>
                        <td>
                    <?
                        //-- get multilanguages text for this image into array --
                        $q2="SELECT * FROM `".TblModCatalogPropImgTxt."` WHERE `cod`='".$row['id']."'";
                        $res2 = $this->db->db_Query( $q2 );
                        //echo '<br>q2='.$q.' $res2='.$res2.' $this->db->result='.$this->db->result;
                        if( !$res OR !$this->db->result ) return false;
                        $rows2 = $this->db->db_GetNumRows();
                        for($j=0;$j<$rows2;$j++){
                            $row2 = $this->db->db_FetchAssoc();
                            $img_txt[$row2['lang_id']]=$row2;
                        }
                        $Panel->WritePanelHead( "SubPanel_" );
                        $ln_arr = $ln_sys->LangArray( _LANG_ID );
                        while( $el = each( $ln_arr ) )
                        {
                        $lang_id = $el['key'];
                        $lang = $el['value'];
                        $mas_s[$lang_id] = $lang;

                        $Panel->WriteItemHeader( $lang );
                        echo "\n <table border=0 class='EditTable'>";
                        echo "\n<tr><td><b>".$this->multi['FLD_IMG_ALT'].":</b></td>";
                        echo "\n<tr><td>";
                        isset($img_txt[$lang_id]) ? $name = $img_txt[$lang_id]['name'] : $name = '';
                        if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->img_title[$row['id']][$lang_id] : $val=$name;
                        else $val=$this->img_title[$lang_id];
                        $this->Form->TextBox( 'img_title['.$row['id'].']['.$lang_id.']', stripslashes($val), 60 );

                        echo "\n<tr><td><b>".$this->multi['FLD_IMG_TITLE'].":</b></td>";
                        echo "\n<tr><td>";
                        isset($img_txt[$lang_id]) ? $name = $img_txt[$lang_id]['text'] : $name = '';
                        if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->img_descr[$row['id']][$lang_id] : $val=$name;
                        else $val=$this->img_descr[$lang_id];
                        //$this->Form->HTMLTextArea( 'img_descr['.$row['id'].']['.$lang_id.']', stripslashes($val), 5, 50  );
                        $this->Form->TextArea( 'img_descr['.$row['id'].']['.$lang_id.']', stripslashes($val), 4, 50  );
                        echo "\n</table>";
                        $Panel->WriteItemFooter();
                        }
                        $Panel->WritePanelFooter();
                        ?>
                        </td>
                        </tr>

                        <tr>
                        <td align=left><b><?=$this->multi['FLD_VISIBLE']?>:</b><INPUT class='checkbox' TYPE=checkbox NAME='id_img_show[<?=$row['id']?>]' VALUE="<?=$row['id']?>" <?if ($row['show']=='1') echo 'CHECKED';?>></TD>
                        </tr>

                    <?

                        //--------------------------------------------------------------------------------------------------
                        //-------------------- SHOW PARAMETERS INFLUENCE ON THE IMAGE --------------------------------------
                        //--------------------------------------------------------------------------------------------------
                        if ( $params>0 ) {
                        /* ?><TR><TD colspan=2><?*///=AdminHTML::PanelSimpleH(); ?>
                        <TR><TD colspan=2>
                        <table border=0 cellspacing=1 cellpadding=0 class="EditTable">
                        <tr><td colspan=4><b><?=$this->multi['FLD_PARAMS_INFLUENCE_ON_IMAGE'];?></b></td></tr>
                        <tr>
                            <Th class="THead"><?=$this->multi['FLD_PARAM_NAME'];?></Th>
                            <Th class="THead"><?=$this->multi['FLD_PREFIX'];?></Th>
                            <Th class="THead"><?=$this->multi['FLD_VALUES'];?></Th>
                            <Th class="THead"><?=$this->multi['FLD_SUFIX'];?></Th>
                        </tr>
                        <?

                            $style1 = 'TR1';
                            $style2 = 'TR2';
                            $value=$this->GetParamsValuesOfPropForImg( $row['id'] );
                            // if ( count($value)==0 ) $value = $this->GetParamsValuesOfProp( $this->id );
                            //echo '<br><br>$value='; print_r($value);

                            for ($i_param=0;$i_param<count($params_row);$i_param++){

                            if ( (float)$i_param/2 == round( $i_param/2 ) )
                            {
                            echo '<TR CLASS="'.$style1.'">';
                            }
                            else echo '<TR CLASS="'.$style2.'">';

                            ?><td align=left><b><?=$this->Spr->GetNameByCod(TblModCatalogParamsSprName, $params_row[$i_param]['id'], $this->lang_id, 1);?>:</b><?
                            ?><td align=center><?=$this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($params_row[$i_param]['id']), $this->lang_id, 1);
                            ?><td align=left width="50%"><?
                            $tblname = TblModCatalogParamsVal;//$this->BuildNameOfValuesTable($params_row[$i_param]['id_categ'], $params_row[$i_param]['id']);
                            // echo '<br> $tblname='.$tblname;

                            isset($value[$params_row[$i_param]['id']]) ? $val_from_table = $value[$params_row[$i_param]['id']] : $val_from_table = NULL;

                            if( $row['id']!=NULL ) $this->Err!=NULL ? $val=$this->arr_params[$params_row[$i_param]['id']] : $val=$val_from_table;
                            else $val=$this->arr_params[$params_row[$i_param]['id']];

                            //echo '<br> $params_row['.$i.'][id]='.$params_row[$i]['id'];
                            //echo '<br> $val='.$val.' $value='.$value;
                            switch ($params_row[$i_param]['type'] ) {
                                case '1':
                                        $this->Form->TextBox( 'arr_params['.$row['id'].']['.$params_row[$i_param]['id'].']', $val, 15 );
                                        break;
                                case '2':
                                        $this->Spr->ShowInComboBox( TblSysLogic, 'arr_params['.$row['id'].']['.$params_row[$i_param]['id'].']', $val, 50 );
                                        break;
                                case '3':
                                        $this->Spr->ShowInComboBox( $tblname, 'arr_params['.$row['id'].']['.$params_row[$i_param]['id'].']', $val, 50 );
                                        break;
                                case '4':
                                        $this->Spr->ShowInCheckBox( $tblname, 'arr_params['.$row['id'].']['.$params_row[$i_param]['id'].']', 6, $val );
                                        break;
                                case '5':
                                        $this->Form->TextBox( 'arr_params['.$row['id'].']['.$params_row[$i_param]['id'].']', $val, 40 );
                                        break;
                                }
                                ?><td align=center><?=$this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($params_row[$i_param]['id']), $this->lang_id, 1);

                            }
                            ?></table></TD></TR><?
                            //AdminHTML::PanelSimpleF();
                        }
                        //--------------------------------------------------------------------------------------------------
                        //------------------ END SHOW PARAMETERS INFLUENCE ON THE IMAGE ------------------------------------
                        //--------------------------------------------------------------------------------------------------

                //echo '<br>$row[move]='.$row['move'];
                if( empty($row['move']) OR $row['move']==0){
                    $new_move = ($this->GetMaxValueOfFieldMove( TblModCatalogPropImg ) + 1);
                    $tmp_res = $this->SetValueOfFieldMove( TblModCatalogPropImg, $row['id'],$new_move);
                }
                else $new_move = $row['move'];
                //echo '<br>$new_move='.$new_move;
                        ?>
                        <tr>
                        <td><b><?=$this->multi['FLD_DISPLAY'];?>:</b>
                        <?
                        if( $up!=0 )
                        {
                        ?>
                            <a href=<?=$this->script?>&task=up_img&move=<?=$new_move;?>>
                            <?=$this->Form->ButtonUp( $row['id'] );?>
                            </a>
                        <?
                        }
                        if( $i!=($rows-1) )
                        {
                        ?>
                            <a href=<?=$this->script?>&task=down_img&move=<?=$new_move;?>>
                            <?=$this->Form->ButtonDown( $row['id'] );?>
                            </a>
                        <?
                        }
                        $up=$row['id'];
                        $a=$a-1;
                        ?>
                        </td>
                        </tr>
                    </table>
                    </TD>
                    </table>
                    </TD>
                    </TR>

            <?//=AdminHTML::PanelSimpleF();?>

        <?
        }
        ?></TABLE></div><?
        $cntRowByAllColors += $rows;
        }//END COLORS FOR
        ?></div><?
        //делаю это что бы не испортить логику. потому что далее везде используетсья
        //для провреки пременная $rows - в которй храниться кол-во изображений.
        //В даном случае - здесь будет храниться кол-во изображений по всем цветам.
        $rows = $cntRowByAllColors;
     }//endif use colors
     else{
     $q="SELECT * FROM `".TblModCatalogPropImg."` WHERE `id_prop`='".$this->id."' order by `move`";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res OR !$this->Right->result ) return false;
     $rows = $this->Right->db_GetNumRows();
     for ($i=0; $i<$rows; $i++) {
       $row = $this->Right->db_FetchAssoc();
       $arr_data[$i] = $row;
     }
     $a = $rows;
     ?><Table border="0" class="EditTable"><?
     for ($i=0; $i<$rows; $i++) {
       $row = $arr_data[$i];

       ?>
      <TR>
       <TD colspan=2>
       <?//=AdminHTML::PanelSimpleH();?>
        <TABLE border=0 cellpadding=2 cellspacing=0 class="EditTable">
         <TR>
          <TD align=right><INPUT class='checkbox' TYPE=checkbox NAME='id_img_del[]' VALUE="<?=$row['id'];?>"></TD>
          <TD align=left valign="middle">
           <a href="http://<?=NAME_SERVER?>/thumb.php?img=<?=$this->settings['img_path'].'/'.$row['id_prop'].'/'.$row['path']?>&amp;wtm=img" target=_blank><?=$this->ShowCurrentImage($row['id'], 'size_auto=200', 85, NULL, "border=0");?></a>
          </TD>
          <TD valign="top">
           <table border=0 cellpadding=0 cellspacing=2 class="EditTable">
            <tr>
             <TD><b><?=$this->multi['FLD_ID'];?>:</b><?=$row['id']; $this->Form->Hidden( 'id_img[]', $row['id'] );?></TD>
            </tr>
            <tr>
             <TD><b><?=$this->multi['FLD_IMG'];?>:</b> <?=SITE_PATH.$this->settings['img_path'].'/'.$row['id_prop'].'/'.$row['path']?></TD>
            </tr>
            <tr>
             <td>
          <?
            //-- get multilanguages text for this image into array --
            $q2="SELECT * FROM `".TblModCatalogPropImgTxt."` WHERE `cod`='".$row['id']."'";
            $res2 = $this->db->db_Query( $q2 );
            //echo '<br>q2='.$q.' $res2='.$res2.' $this->db->result='.$this->db->result;
            if( !$res OR !$this->db->result ) return false;
            $rows2 = $this->db->db_GetNumRows();
            for($j=0;$j<$rows2;$j++){
                $row2 = $this->db->db_FetchAssoc();
                $img_txt[$row2['lang_id']]=$row2;
            }
            $Panel->WritePanelHead( "SubPanel_" );
            $ln_arr = $ln_sys->LangArray( _LANG_ID );
            while( $el = each( $ln_arr ) )
            {
              $lang_id = $el['key'];
              $lang = $el['value'];
              $mas_s[$lang_id] = $lang;

              $Panel->WriteItemHeader( $lang );
              echo "\n <table border=0 class='EditTable'>";
              echo "\n<tr><td><b>".$this->multi['FLD_TITLE'].":</b></td>";
              echo "\n<tr><td>";
              isset($img_txt[$lang_id]) ? $name = $img_txt[$lang_id]['name'] : $name = '';
              if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->img_title[$row['id']][$lang_id] : $val=$name;
              else $val=$this->img_title[$lang_id];
              $this->Form->TextBox( 'img_title['.$row['id'].']['.$lang_id.']', stripslashes($val), 60 );

              echo "\n<tr><td><b>".$this->multi['FLD_DESCRIP'].":</b></td>";
              echo "\n<tr><td>";
              isset($img_txt[$lang_id]) ? $name = $img_txt[$lang_id]['text'] : $name = '';
              if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->img_descr[$row['id']][$lang_id] : $val=$name;
              else $val=$this->img_descr[$lang_id];
              //$this->Form->HTMLTextArea( 'img_descr['.$row['id'].']['.$lang_id.']', stripslashes($val), 5, 50  );
              $this->Form->TextArea( 'img_descr['.$row['id'].']['.$lang_id.']', stripslashes($val), 4, 50  );
              echo "\n</table>";
              $Panel->WriteItemFooter();
            }
            $Panel->WritePanelFooter();
            ?>
             </td>
            </tr>
            <tr>
             <td align=left><b><?=$this->multi['FLD_VISIBLE']?>:</b><INPUT class='checkbox' TYPE=checkbox NAME='id_img_show[<?=$row['id']?>]' VALUE="<?=$row['id']?>" <?if ($row['show']=='1') echo 'CHECKED';?>></TD>
            </tr>
           <?

            //--------------------------------------------------------------------------------------------------
            //-------------------- SHOW PARAMETERS INFLUENCE ON THE IMAGE --------------------------------------
            //--------------------------------------------------------------------------------------------------
            if ( $params>0 ) {
              ?><TR><TD colspan=2><?//=AdminHTML::PanelSimpleH(); ?>
              <table border=0 cellspacing=1 cellpadding=0 class="EditTable">
               <tr><td colspan=4><b><?=$this->multi['FLD_PARAMS_INFLUENCE_ON_IMAGE'];?></b></td></tr>
               <tr>
                <Th class="THead"><?=$this->multi['FLD_PARAM_NAME'];?></Th>
                <Th class="THead"><?=$this->multi['FLD_PREFIX'];?></Th>
                <Th class="THead"><?=$this->multi['FLD_VALUES'];?></Th>
                <Th class="THead"><?=$this->multi['FLD_SUFIX'];?></Th>
               </tr>
               <?

                $style1 = 'TR1';
                $style2 = 'TR2';
                $value=$this->GetParamsValuesOfPropForImg( $row['id'] );
                // if ( count($value)==0 ) $value = $this->GetParamsValuesOfProp( $this->id );
                //echo '<br><br>$value='; print_r($value);

                for ($i_param=0;$i_param<count($params_row);$i_param++){

                  if ( (float)$i_param/2 == round( $i_param/2 ) )
                  {
                   echo '<TR CLASS="'.$style1.'">';
                  }
                  else echo '<TR CLASS="'.$style2.'">';

                  ?><td align=left><b><?=$this->Spr->GetNameByCod(TblModCatalogParamsSprName, $params_row[$i_param]['id'], $this->lang_id, 1);?>:</b><?
                  ?><td align=center><?=$this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($params_row[$i_param]['id']), $this->lang_id, 1);
                  ?><td align=left width="50%"><?
                  $tblname = TblModCatalogParamsVal; //$this->BuildNameOfValuesTable($params_row[$i_param]['id_categ'], $params_row[$i_param]['id']);
                  // echo '<br> $tblname='.$tblname;

                  isset($value[$params_row[$i_param]['id']]) ? $val_from_table = $value[$params_row[$i_param]['id']] : $val_from_table = NULL;

                  if( $row['id']!=NULL ) $this->Err!=NULL ? $val=$this->arr_params[$params_row[$i_param]['id']] : $val=$val_from_table;
                  else $val=$this->arr_params[$params_row[$i_param]['id']];

                  //echo '<br> $params_row['.$i.'][id]='.$params_row[$i]['id'];
                  //echo '<br> $val='.$val.' $value='.$value;
                  switch ($params_row[$i_param]['type'] ) {
                    case '1':
                            $this->Form->TextBox( 'arr_params['.$row['id'].']['.$params_row[$i_param]['id'].']', $val, 15 );
                            break;
                    case '2':
                            $this->Spr->ShowInComboBox( TblSysLogic, 'arr_params['.$row['id'].']['.$params_row[$i_param]['id'].']', $val, 50 );
                            break;
                    case '3':
                            $this->Spr->ShowInComboBox( $tblname, 'arr_params['.$row['id'].']['.$params_row[$i_param]['id'].']', $val, 50 );
                            break;
                    case '4':
                            $this->Spr->ShowInCheckBox( $tblname, 'arr_params['.$row['id'].']['.$params_row[$i_param]['id'].']', 6, $val );
                            break;
                    case '5':
                            $this->Form->TextBox( 'arr_params['.$row['id'].']['.$params_row[$i_param]['id'].']', $val, 40 );
                            break;
                     }
                     ?><td align=center><?=$this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($params_row[$i_param]['id']), $this->lang_id, 1);

                }
                ?></table><?
                //AdminHTML::PanelSimpleF();
            }
            //--------------------------------------------------------------------------------------------------
            //------------------ END SHOW PARAMETERS INFLUENCE ON THE IMAGE ------------------------------------
            //--------------------------------------------------------------------------------------------------

       //echo '<br>$row[move]='.$row['move'];
       if( empty($row['move']) OR $row['move']==0){
           $new_move = ($this->GetMaxValueOfFieldMove( TblModCatalogPropImg ) + 1);
           $tmp_res = $this->SetValueOfFieldMove( TblModCatalogPropImg, $row['id'],$new_move);
       }
       else $new_move = $row['move'];
       //echo '<br>$new_move='.$new_move;
            ?>
            <tr>
             <td><b><?=$this->multi['FLD_DISPLAY'];?>:</b>
              <?
              if( $up!=0 )
              {
               ?>
                <a href=<?=$this->script?>&task=up_img&move=<?=$new_move;?>>
                <?=$this->Form->ButtonUp( $row['id'] );?>
                </a>
               <?
              }
              if( $i!=($rows-1) )
              {
               ?>
                 <a href=<?=$this->script?>&task=down_img&move=<?=$new_move;?>>
                 <?=$this->Form->ButtonDown( $row['id'] );?>
                 </a>
               <?
              }
              $up=$row['id'];
              $a=$a-1;
              ?>
             </td>
            </tr>
           </table>
          </TD>
         </TR>
        </TABLE>
        <?//=AdminHTML::PanelSimpleF();?>
       </TD>
      </TR>
       <?
     }
     ?></table><?
     }
     ?>
     <table style="margin-top: 20px;margin-bottom: 20px;">
      <TR>
       <TD colspan="2">
        <?
        if ( $rows>0 ){
            if($this->Right->IsUpdate()){ $this->Form->Button('updimg',$this->multi['TXT_SAVE']);}
            if($this->Right->IsDelete()){ $this->Form->Button('delimg',$this->multi['TXT_DELETE_SELECTED']);}
          }?>
          <?=$this->Form->Button('cancel',$this->multi['_BUTTON_CANCEL']);?>
       </TD>
      </TR>
     </table>

      <?
      if($this->Right->IsWrite() OR $this->Right->IsUpdate()){
        if($this->settings['imgColors']==1 ){
            ?>
            <div id="tabsAddImages">
            <ul>
                    <?//print_r($this->treeColorsData)?>
                    <?

                    for ($c = 0; $c < count($colorsClass->colors); $c++) {
                        if(empty($colorsClass->colors[$c])) continue;
                        $img=NULL;
                        if($colorsClass->colors[$c]!=-1 && !empty($colorsClass->treeColorsData[$colorsClass->colors[$c]]['img']))
                        $img = "/images/spr/mod_catalog_spr_colors/" . $this->lang_id . "/" . $colorsClass->treeColorsData[$colorsClass->colors[$c]]['img'];

                        ?>
                    <li><a <?if($colorsClass->colors[$c]==-1) echo 'style="line-height: 20px;"';?> href="#tabs-<?=$c?>">
                    <? if(isset($img))
                        echo $this->ShowCurrentImage($img, 'size_auto=20', 85, NULL, "border=0 alt='" . $colorsClass->treeColorsData[$colorsClass->colors[$c]]['name'] . "' title='" . $colorsClass->treeColorsData[$colorsClass->colors[$c]]['name'] . "'");
                    elseif($colorsClass->colors[$c]!=-1)
                        echo '<div style="float:left;width:20px;height:20px;background-color:#'.$colorsClass->treeColorsData[$colorsClass->colors[$c]]['colorsBit'].'"></div>'?>&nbsp;
                            <?=$colorsClass->treeColorsData[$colorsClass->colors[$c]]['name']?></a></li>
                    <?}

                    ?>
                </ul>

            <?
        for ($c = 0; $c < count($colorsClass->colors); $c++) {
        ?><div id="tabs-<?=$c?>" class="colorTabs"><?
            if ((MAX_UPLOAD_IMAGES_COUNT-$rows)>0){?>
            <br /><br />
            <?AdminHTML::PanelSimpleH();?>
        <TR>
        <TD colspan="2">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?=MAX_IMAGE_SIZE;?>">
            <?
            for($i=0;$i<UPLOAD_IMAGES_COUNT; $i++){

                ?>
                <INPUT TYPE="file" NAME="image[<?=$colorsClass->colors[$c]?>][]" size="120" VALUE="<?=$this->img['name'][$i]?>"/>
                <br/>
                <?

            }
            ?>
            <br>
            <table>
            <tr>
            <td><img src='images/icons/info.png' alt='' title='' border='0' /></td>
            <td class='info'>
            <?=$this->multi['HELP_MSG_ADD_IMAGES1'].' '.MAX_IMAGE_WIDTH.'x'.MAX_IMAGE_HEIGHT.'px, '.$this->multi['HELP_MSG_ADD_IMAGES2'].' '.round(floatval((MAX_IMAGE_SIZE/1024)/1024),1).' Mb.';?>
            <br/><?=$this->multi['HELP_MSG_ADD_IMAGES3'].' '.STORE_IMAGE_WIDTH.'x'.STORE_IMAGE_HEIGHT.'px'.$this->multi['HELP_MSG_ADD_IMAGES4'];?>
            </td>
            </tr>
            </table>
        </TD>
        </TR>
        <?AdminHTML::PanelSimpleF();?>
        </div>
        <?
        }//end if

        }//for colors
        ?></div><br/><?=$this->Form->Button('saveimg',$this->multi['TXT_ADD_IMAGES']);
        }else{
            if ((MAX_UPLOAD_IMAGES_COUNT-$rows)>0){?>
                <br /><br />
                <?AdminHTML::PanelSimpleH();?>
                <TR>
                <TD colspan="2">
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?=MAX_IMAGE_SIZE;?>">
                    <?
                    for($i=0;$i<UPLOAD_IMAGES_COUNT; $i++){
                        ?>
                        <INPUT TYPE="file" NAME="image[]" size="120" VALUE="<?=$this->img['name'][$i]?>">
                        <br/>
                        <?
                    }
                    ?>
                    <br>
                    <table>
                    <tr>
                    <td><img src='images/icons/info.png' alt='' title='' border='0' /></td>
                    <td class='info'>
                    <?=$this->multi['HELP_MSG_ADD_IMAGES1'].' '.MAX_IMAGE_WIDTH.'x'.MAX_IMAGE_HEIGHT.'px, '.$this->multi['HELP_MSG_ADD_IMAGES2'].' '.round(floatval((MAX_IMAGE_SIZE/1024)/1024),1).' Mb.';?>
                    <br/><?=$this->multi['HELP_MSG_ADD_IMAGES3'].' '.STORE_IMAGE_WIDTH.'x'.STORE_IMAGE_HEIGHT.'px'.$this->multi['HELP_MSG_ADD_IMAGES4'];?>
                    </td>
                    </tr>
                    </table>
                    <br/><?=$this->Form->Button('saveimg',$this->multi['TXT_ADD_IMAGES']);
                    ?>

                </TD>
                </TR>
                <?AdminHTML::PanelSimpleF();?>
                <?
            }//end if
        }//end if colors
      }//end if rights

       //echo "<input type=hidden name='task' value='saveimg'>";
       echo "<input type=hidden name='id' value='".$this->id."'>";
       echo "<input type=hidden name='module' value='".$this->module."'>";
       echo "<input type=hidden name='display' value='".$this->display."'>";
       echo "<input type=hidden name='start' value='".$this->start."'>";
       echo "<input type=hidden name='sort' value='".$this->sort."'>";
       echo "<input type=hidden name='fltr' value='".$this->fltr."'>";
       echo "<input type=hidden name='fltr2' value='".$this->fltr2."'>";
       echo "<input type=hidden name='srch' value='".$this->srch."'>";
       echo "<input type=hidden name='id_cat' value='".$this->id_cat."'>";

       echo "<input type=hidden name='parent' value='".$this->parent."'>";
       echo "<input type=hidden name='parent_module' value='".$this->parent_module."'>";
       echo "<input type=hidden name='parent_id' value='".$this->parent_id."'>";
       echo "<input type=hidden name='parent_display' value='".$this->parent_display."'>";
       echo "<input type=hidden name='parent_start' value='".$this->parent_start."'>";
       echo "<input type=hidden name='parent_sort' value='".$this->parent_sort."'>";
       echo "<input type=hidden name='parent_task' value='".$this->parent_task."'>";
       echo "<input type=hidden name='parent_level' value='".$this->parent_level."'>";
      ?>
      </FORM>

     <?
     AdminHTML::PanelSubF();
     return true;
    } // end of function ShowPicture()

    // ================================================================================================
    // Function : UpdatePicture
    // Version : 1.0.0
    // Date : 03.04.2006
    //
    // Parms :
    // Returns : $res / Void
    // Description : Save comments of the image to the table
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 03.04.2006
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function UpdatePicture()
    {
     $this->Err = NULL;
     for($i=0; $i<count($this->id_img); $i++){
        if( isset($this->img_show[$this->id_img[$i]]) ) $is_to_show=1;
        else $is_to_show=0;

        $q="UPDATE `".TblModCatalogPropImg."` SET `show`='".$is_to_show."' WHERE `id`='".$this->id_img[$i]."'";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
        if( !$res OR !$this->Right->result ) $this->Err = $this->Err.$this->Msg->show_text('MSG_ERR_SAVE_FILE_TO_DB').'<br>';
        //echo '<br />$this->Err='.$this->Err;

        $ln_sys = new SysLang();
        $ln_arr = $ln_sys->LangArray( _LANG_ID );
        while( $el = each( $ln_arr ) ){
            $lang_id = $el['key'];
            $name = $this->Form->GetRequestTxtData($this->img_title[$this->id_img[$i]][$lang_id], 1);
            $short = $this->Form->GetRequestTxtData($this->img_descr[$this->id_img[$i]][$lang_id], 0);

            $q = "SELECT COUNT(`cod`) AS `cnt` FROM `".TblModCatalogPropImgTxt."` WHERE `cod`='".$this->id_img[$i]."' AND `lang_id`='".$lang_id."'";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br />$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
            if( !$res OR !$this->Right->result ) return false;
            $row = $this->Right->db_FetchAssoc();

            if($row['cnt']>0){
                $q = "UPDATE `".TblModCatalogPropImgTxt."` SET
                      `name`='".$name."',
                      `text`='".$short."'
                      WHERE `cod`='".$this->id_img[$i]."'
                      AND `lang_id`='".$lang_id."'
                     ";
            }
            elseif(!empty($name) OR !empty($short)){
                $q = "INSERT INTO `".TblModCatalogPropImgTxt."` SET
                      `cod`='".$this->id_img[$i]."',
                      `lang_id`='".$lang_id."',
                      `name`='".$name."',
                      `text`='".$short."'
                     ";
            }
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br />$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
            if( !$res OR !$this->Right->result ) return false;
        }

        //$res=$this->Spr->SaveNameArr( $this->id_img[$i], $this->img_title[$this->id_img[$i]], TblModCatalogPropImgSprName );
        //if( !$res ) return false;
        //$res=$this->Spr->SaveNameArr( $this->id_img[$i], $this->img_descr[$this->id_img[$i]], TblModCatalogPropImgSprDescr );
        //if( !$res ) return false;

        //------ save parameterss of prop for images -----------
        $res = $this->SaveParamsValuesOfPropForImg($this->id_img[$i]);
        if( !$res ) return false;
     }
     return $this->Err;
    }  // end of function UpdatePicture()




    // ================================================================================================
    // Function : ShowFiles
    // Version : 1.0.0
    // Date : 02.08.2007
    //
    // Parms :
    // Returns : $res / Void
    // Description : Show the files of item product
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 02.08.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowFiles()
    {
     $Panel = &check_init('Panel', 'Panel');
     $ln_sys = &check_init('SysLang', 'SysLang');

     $q="SELECT * FROM `".TblModCatalogPropFiles."` WHERE `id_prop`='".$this->id."' order by `move`";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res OR !$this->Right->result ) return false;
     $rows = $this->Right->db_GetNumRows();
     for ($i=0; $i<$rows; $i++) {
       $row = $this->Right->db_FetchAssoc();
       $arr_data[$i] = $row;
      }

     echo '<a href="'.$this->script.'"> '.$this->multi['TXT_BACK_TO'].' ';
     if ( !empty($this->id_cat) ) echo $this->Spr->GetNameByCod(TblModCatalogSprName,$this->id_cat, $this->lang_id, 1);
     else echo $this->multi['FLD_CONTENT'];
     echo '</a>';

     $txt = $this->multi['TXT_ADDITING_FILES_FOR'];
     $number_name = $this->GetNumberName($this->id);
     if( !empty($number_name) ) $number_name = '['.$number_name.']';
     $txt.=' <strong>'.$this->Spr->GetNameByCod( TblModCatalogPropSprName, $this->id, $this->lang_id, 1 ).' '.$number_name.'</strong>';
     AdminHTML::PanelSubH( $txt );

     //-------- Show Error text for validation fields --------------
     $this->ShowErrBackEnd();
     //-------------------------------------------------------------

     $number_name = $this->GetNumberName($this->id);
     if( !empty($number_name) ) $number_name = '['.$number_name.']';
     ?>
     <FORM ACTION="<?=$_SERVER['PHP_SELF']?>" name="AddFiles" enctype="multipart/form-data" method="post">
     <input type="hidden" name="task" value="savefiles">
     <Table border=0 class="EditTable">
     <?

     $a = $rows;
     $up = 0;
     $down = 0;
     for ($i=0; $i<$rows; $i++) {
       $row = $arr_data[$i];

       ?>
      <TR>
       <TD colspan="2">
        <TABLE border="0" cellpadding="2" cellspacing="0" class="EditTable">
         <TR>
          <TD align=right><INPUT class='checkbox' TYPE=checkbox NAME='id_img_del[]' VALUE="<?=$row['id'];?>"></TD>
          <TD align=left valign="middle">
          </TD>
          <TD valign="top">
           <table border=0 cellpadding=0 cellspacing=2 class="EditTable">
            <tr>
             <TD><b><?=$this->multi['FLD_ID']?>:</b><?=$row['id']; $this->Form->Hidden( 'id_img[]', $row['id'] );?></TD>
            </tr>
            <tr>
             <TD><b><?=$this->multi['FLD_FILE']?>:</b> <a href="<?=$this->settings['files_path'].'/'.$row['id_prop'].'/'.$row['path']?>" target="_blank"><?=SITE_PATH.$this->settings['files_path'].'/'.$row['id_prop'].'/'.$row['path']?></a></TD>
            </tr>
            <tr>
             <td>
          <?
            //-- get multilanguages text for this file into array --
            $q2="SELECT * FROM `".TblModCatalogPropFilesTxt."` WHERE `cod`='".$row['id']."'";
            $res2 = $this->db->db_Query( $q2 );
            //echo '<br>q2='.$q.' $res2='.$res2.' $this->db->result='.$this->db->result;
            if( !$res OR !$this->db->result ) return false;
            $rows2 = $this->db->db_GetNumRows();
            for($j=0;$j<$rows2;$j++){
                $row2 = $this->db->db_FetchAssoc();
                $files_txt[$row2['lang_id']]=$row2;
            }

            $Panel->WritePanelHead( "SubPanel_" );
            $ln_arr = $ln_sys->LangArray( _LANG_ID );
            while( $el = each( $ln_arr ) )
            {
              $lang_id = $el['key'];
              $lang = $el['value'];
              $mas_s[$lang_id] = $lang;

              $Panel->WriteItemHeader( $lang );
              echo "\n <table border=0 class='EditTable'>";
              echo "\n<tr><td><b>".$this->multi['FLD_TITLE'].":</b></td>";
              echo "\n<tr><td>";
              isset($files_txt[$lang_id]) ? $name = $files_txt[$lang_id]['name'] : $name = '';
              if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->files_title[$lang_id] : $val=$name;
              else $val=$this->files_title[$lang_id];
              $this->Form->TextBox( 'files_title['.$row['id'].']['.$lang_id.']', stripslashes($val), 60 );

              echo "\n<tr><td><b>".$this->multi['FLD_DESCRIP'].":</b></td>";
              echo "\n<tr><td>";
              isset($files_txt[$lang_id]) ? $name = $files_txt[$lang_id]['text'] : $name = '';
              if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->files_descr[$lang_id] : $val=$name;
              else $val=$this->files_descr[$lang_id];
              //$this->Form->HTMLTextArea( 'img_descr['.$row['id'].']['.$lang_id.']', stripslashes($val), 5, 50  );
              $this->Form->TextArea( 'files_descr['.$row['id'].']['.$lang_id.']', stripslashes($val), 4, 50  );
              echo "\n</table>";
              $Panel->WriteItemFooter();
            }
            $Panel->WritePanelFooter();
            ?>
             </td>
            </tr>
            <tr>
             <td align=left><b><?=$this->multi['FLD_VISIBLE']?>:</b><input class="checkbox" type="checkbox" name="id_img_show[<?=$row['id']?>]" value="<?=$row['id']?>" <?if ($row['show']=='1') echo 'checked';?>></TD>
            </tr>
           <?

       //echo '<br>$row[move]='.$row['move'];
       if( empty($row['move']) OR $row['move']==0){
           $new_move = ($this->GetMaxValueOfFieldMove( TblModCatalogPropFiles ) + 1);
           $tmp_res = $this->SetValueOfFieldMove( TblModCatalogPropFiles, $row['id'],$new_move);
       }
       else $new_move = $row['move'];
       //echo '<br>$new_move='.$new_move;
            ?>
            <tr>
             <td><b><?=$this->multi['FLD_DISPLAY'];?>:</b>
              <?
              if( $up!=0 )
              {
               ?>
                <a href=<?=$this->script?>&task=up_files&move=<?=$new_move;?>>
                <?=$this->Form->ButtonUp( $row['id'] );?>
                </a>
               <?
              }
              if( $i!=($rows-1) )
              {
               ?>
                 <a href=<?=$this->script?>&task=down_files&move=<?=$new_move;?>>
                 <?=$this->Form->ButtonDown( $row['id'] );?>
                 </a>
               <?
              }
              $up=$row['id'];
              $a=$a-1;
              ?>
             </td>
            </tr>
           </table>
          </TD>
         </TR>
        </TABLE>
        <?=AdminHTML::PanelSimpleF();?>
       </TD>
       <?
     }
     ?>
      </TR>
      <TR>
       <TD colspan="2">
        <?if ( $rows>0 ){
          if($this->Right->IsUpdate()) $this->Form->Button('updfiles',$this->multi['TXT_SAVE']);
          if($this->Right->IsDelete()) $this->Form->Button('delfiles',$this->multi['TXT_DELETE_SELECTED']);
          }?>
          <?=$this->Form->Button('cancel',$this->multi['_BUTTON_CANCEL']);?>
       </TD>
      </TR>
     </table>
      <?
      if ((MAX_UPLOAD_FILES_COUNT-$rows)>0 AND ($this->Right->IsWrite() OR $this->Right->IsUpdate()) ){?>
      <br /><br />
      <?=AdminHTML::PanelSimpleH();?>
      <TR>
       <TD colspan="2">

        <input type="hidden" name="MAX_FILE_SIZE" value="<?=MAX_FILE_SIZE;?>">
        <?
        for($i=0;$i<UPLOAD_FILES_COUNT; $i++){
            ?>
            <INPUT TYPE="file" NAME="files[]" size="60" VALUE="<?=$this->files['name'][$i]?>">
            <br>
            <?
        }
        ?>
        <br><?=$this->Form->Button('savefiles',$this->multi['TXT_ADD_FILES']);
        ?>
       </TD>
      </TR>
      <?=AdminHTML::PanelSimpleF();?>
      <?} //end if

       //echo "<input type=hidden name='task' value='saveimg'>";
       echo "<input type=hidden name='id' value='".$this->id."'>";
       echo "<input type=hidden name='module' value='".$this->module."'>";
       echo "<input type=hidden name='display' value='".$this->display."'>";
       echo "<input type=hidden name='start' value='".$this->start."'>";
       echo "<input type=hidden name='sort' value='".$this->sort."'>";
       echo "<input type=hidden name='fltr' value='".$this->fltr."'>";
       echo "<input type=hidden name='fltr2' value='".$this->fltr2."'>";
       echo "<input type=hidden name='srch' value='".$this->srch."'>";
       echo "<input type=hidden name='id_cat' value='".$this->id_cat."'>";

       echo "<input type=hidden name='parent' value='".$this->parent."'>";
       echo "<input type=hidden name='parent_module' value='".$this->parent_module."'>";
       echo "<input type=hidden name='parent_id' value='".$this->parent_id."'>";
       echo "<input type=hidden name='parent_display' value='".$this->parent_display."'>";
       echo "<input type=hidden name='parent_start' value='".$this->parent_start."'>";
       echo "<input type=hidden name='parent_sort' value='".$this->parent_sort."'>";
       echo "<input type=hidden name='parent_task' value='".$this->parent_task."'>";
       echo "<input type=hidden name='parent_level' value='".$this->parent_level."'>";
      ?>
      </FORM>
     <?
     AdminHTML::PanelSubF();
     return true;
    } // end of function ShowFiles()

    // ================================================================================================
    // Function : SaveFiles
    // Version : 1.0.0
    // Date : 03.04.2006
    //
    // Parms :
    // Returns : $res / Void
    // Description : Save the files to the folder  and save path in the database
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 03.04.2006
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function SaveFiles()
    {
     $this->Err = NULL;
     $max_image_size= MAX_FILE_SIZE;
     //$valid_types =  array("gif", "GIF", "jpg", "JPG", "png", "PNG", "jpeg", "JPEG");
     //print_r($_FILES["files"]);
     $cols = count($_FILES["files"]["name"]);
     //echo '<br><br>$cols='.$cols;
     for ($i=0; $i<$cols; $i++) {
         //echo '<br>$_FILES["image"]='.$_FILES["image"].' $_FILES["image"]["tmp_name"]["'.$i.'"]='.$_FILES["image"]["tmp_name"]["$i"].' $_FILES["image"]["size"]["'.$i.'"]='.$_FILES["image"]["size"]["$i"];
         //echo '<br>$_FILES["image"]["name"][$i]='.$_FILES["image"]["name"][$i];
         if ( !empty($_FILES["files"]["name"][$i]) ) {
           if ( isset($_FILES["files"]) && is_uploaded_file($_FILES["files"]["tmp_name"][$i]) && $_FILES["files"]["size"][$i] ){
            $filename = $_FILES['files']['tmp_name'][$i];
            $ext = substr($_FILES['files']['name'][$i],1 + strrpos($_FILES['files']['name'][$i], "."));
            //echo '<br>filesize($filename)='.filesize($filename).' $max_image_size='.$max_image_size;
            if (filesize($filename) > $max_image_size) {
                $this->Err = $this->Err.$this->Msg->show_text('MSG_ERR_FILE_SIZE').' ('.$_FILES['files']['name']["$i"].')<br>';
                continue;
            }
            /*
            if (!in_array($ext, $valid_types)) {
                $this->Err = $this->Err.$this->Msg->show_text('MSG_ERR_FILE_TYPE').' ('.$_FILES['files']['name']["$i"].')<br>';
            }
            */
            else {
             $uploaddir0 = SITE_PATH.$this->settings['files_path'];
             if ( !file_exists ($uploaddir0) ) mkdir($uploaddir0,0777);
             else @chmod($uploaddir0,0777);
             $alias = $this->id;
             $uploaddir1 = $uploaddir0.'/'.$alias;
             if ( !file_exists ($uploaddir1) ) mkdir($uploaddir1,0777);
             else @chmod($uploaddir1,0777);
             $uploaddir2 = $_FILES['files']['name']["$i"];
             //$uploaddir2 = time().$i.'.'.$ext;
             $uploaddir = $uploaddir1."/".$uploaddir2;

             //echo '<br>$filename='.$filename.'<br> $uploaddir='.$uploaddir.'<br> $uploaddir2='.$uploaddir2;
             //if (@move_uploaded_file($filename, $uploaddir)) {
             if ( copy($filename,$uploaddir) ) {
                 //====== set next max value for move START ============
                 $maxx = NULL;
                 $q = "SELECT MAX(`move`) AS `maxx` FROM `".TblModCatalogPropFiles."` WHERE `id_prop`='".$this->id."'";
                 $res = $this->Right->Query( $q, $this->user_id, $this->module );
                 $row = $this->Right->db_FetchAssoc();
                 $maxx = $row['maxx']+1;
                 //====== set next max value for move END ============

                 $q="INSERT into `".TblModCatalogPropFiles."` values(NULL,'".$this->id."','".$uploaddir2."','1', '".$maxx."')";
                 $res = $this->Right->Query( $q, $this->user_id, $this->module );
                 if( !$this->Right->result ) $this->Err = $this->Err.$this->Msg->show_text('MSG_ERR_SAVE_FILE_TO_DB').' ('.$_FILES['files']['name']["$i"].')<br>';
                 //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
             }
             else {
                 $this->Err = $this->Err.$this->Msg->show_text('MSG_ERR_FILE_MOVE').' ('.$_FILES['files']['name']["$i"].')<br>';
             }
             @chmod($uploaddir1,0755);
             @chmod($uploaddir0,0755);
            }
           }
           else $this->Err = $this->Err.$this->Msg->show_text('MSG_ERR_FILE').' ('.$_FILES['files']['name']["$i"].')<br>';
         }
         //echo '<br>$i='.$i;
     } // end for
     return $this->Err;
    }  // end of function SaveFiles()


    // ================================================================================================
    // Function : UpdateFiles
    // Version : 1.0.0
    // Date : 03.08.2007
    //
    // Parms :
    // Returns : $res / Void
    // Description : Save comments of the files to the table
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 03.08.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function UpdateFiles()
    {
     $this->Err = NULL;
     for($i=0; $i<count($this->id_img); $i++){
        if( isset($this->img_show[$this->id_img[$i]]) ) $is_to_show=1;
        else $is_to_show=0;

        $q="UPDATE `".TblModCatalogPropFiles."` SET `show`='".$is_to_show."' WHERE `id`='".$this->id_img[$i]."'";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
        if( !$this->Right->result ) $this->Err = $this->Err.$this->Msg->show_text('MSG_ERR_SAVE_FILE_TO_DB').'<br>';

        $ln_sys = new SysLang();
        $ln_arr = $ln_sys->LangArray( _LANG_ID );
        while( $el = each( $ln_arr ) ){
            $lang_id = $el['key'];
            $name = $this->Form->GetRequestTxtData($this->files_title[$this->id_img[$i]][$lang_id], 1);
            $short = $this->Form->GetRequestTxtData($this->files_descr[$this->id_img[$i]][$lang_id], 0);

            $q = "SELECT COUNT(`cod`) AS `cnt` FROM `".TblModCatalogPropFilesTxt."` WHERE `cod`='".$this->id_img[$i]."' AND `lang_id`='".$lang_id."'";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br />$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
            if( !$res OR !$this->Right->result ) return false;
            $row = $this->Right->db_FetchAssoc();

            if($row['cnt']>0){
                $q = "UPDATE `".TblModCatalogPropFilesTxt."` SET
                      `name`='".$name."',
                      `text`='".$short."'
                      WHERE `cod`='".$this->id_img[$i]."'
                      AND `lang_id`='".$lang_id."'
                     ";
            }
            elseif(!empty($name) OR !empty($short)){
                $q = "INSERT INTO `".TblModCatalogPropFilesTxt."` SET
                      `cod`='".$this->id_img[$i]."',
                      `lang_id`='".$lang_id."',
                      `name`='".$name."',
                      `text`='".$short."'
                     ";
            }
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br />$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
            if( !$res OR !$this->Right->result ) return false;
        }
     }
     return $this->Err;
    }  // end of function UpdateFiles()





    // ================================================================================================
    // Function : up_relat_prop()
    // Version : 1.0.0
    // Date : 02.05.2007
    // Parms :
    // Returns :      true,false / Void
    // Description :  Up position
    // ================================================================================================
    // Programmer :  Ihor Trokhymchuk
    // Date : 02.05.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function up_relat_prop($table)
    {
     $q="SELECT * FROM `$table` WHERE `move`='$this->move'";
     $q = $q." AND (`id_prop1`='$this->id_prop1' OR `id_prop2`='$this->id_prop1')";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res )return false;
     $rows = $this->Right->db_GetNumRows();
     $row = $this->Right->db_FetchAssoc();
     $move_down = $row['move'];
     $id_down = $row['id'];


     $q="SELECT * FROM `$table` WHERE `move`<'$this->move'";
     $q = $q." AND (`id_prop1`='$this->id_prop1' OR `id_prop2`='$this->id_prop1')";
     $q = $q." ORDER BY `move` desc";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res )return false;
     $rows = $this->Right->db_GetNumRows();
     $row = $this->Right->db_FetchAssoc();
     $move_up = $row['move'];
     $id_up = $row['id'];

     //echo '<br> $move_down='.$move_down.' $id_down ='.$id_down.' $move_up ='.$move_up.' $id_up ='.$id_up;
     if( $move_down!=0 AND $move_up!=0 )
     {
     $q="update `$table` set
         `move`='$move_down' where id='$id_up'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;

     $q="update `$table` set
         `move`='$move_up' where id='$id_down'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;

     }
    } // end of function up_relat_prop()


    // ================================================================================================
    // Function : down_relat_prop()
    // Version : 1.0.0
    // Date : 02.05.2007
    // Parms :
    // Returns :      true,false / Void
    // Description :  Down position
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 02.05.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================

    function down_relat_prop($table)
    {
     $q="select * from `$table` where `move`='$this->move'";
     $q = $q." AND (`id_prop1`='$this->id_prop1' OR `id_prop2`='$this->id_prop1')";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res )return false;
     $rows = $this->Right->db_GetNumRows();
     $row = $this->Right->db_FetchAssoc();
     $move_up = $row['move'];
     $id_up = $row['id'];


     $q="select * from `$table` where `move`>'$this->move'";
     $q = $q." AND (`id_prop1`='$this->id_prop1' OR `id_prop2`='$this->id_prop1')";
     $q = $q." order by `move` asc";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res )return false;
     $rows = $this->Right->db_GetNumRows();
     $row = $this->Right->db_FetchAssoc();
     $move_down = $row['move'];
     $id_down = $row['id'];

     if( $move_down!=0 AND $move_up!=0 )
     {
     $q="update `$table` set
         `move`='$move_down' where id='$id_up'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );

     $q="update `$table` set
         `move`='$move_up' where id='$id_down'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     }
    } // end of function down_relat_prop()

    // ================================================================================================
    // Function : SavePriceLevels()
    // Version : 1.0.0
    // Date : 28.09.2007
    // Parms : $id_prop - id of the position
    // Returns :      true,false / Void
    // Description : save Price levels to table
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 28.09.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function SavePriceLevels()
    {
        $tmp_db = new DB();
        $q = "DELETE FROM `".TblModCatalogPriceLevels."` WHERE `id_prop`='$this->id'";
        $res = $tmp_db->db_Query( $q );
        //echo '<br>q='.$q.' res='.$res.' $tmp_db->result='.$tmp_db->result;
        if( !$res OR !$tmp_db->result )return false;
        for($i=0;$i<count($this->price_level);$i++){
            if( !empty($this->price_level[$i])){
                $q = "INSERT INTO `".TblModCatalogPriceLevels."` SET
                      `id_prop`='$this->id',
                      `qnt_from`='".addslashes(strip_tags(trim($this->qnt_from[$i])))."',
                      `qnt_to`='".addslashes(strip_tags(trim($this->qnt_to[$i])))."',
                      `price_level`='".str_replace(',', '.',addslashes(strip_tags(trim($this->price_level[$i]))))."',
                      `currency`='".$this->price_levels_currency[$i]."'";
                $res = $tmp_db->db_Query( $q );
                //echo '<br>q='.$q.' res='.$res.' $tmp_db->result='.$tmp_db->result;
                if( !$res OR !$tmp_db->result )return false;
            }
        }
        return true;
    }//end of function SavePriceLevels()

    // ================================================================================================
    // Function : DelCurrentPriceLevel()
    // Version : 1.0.0
    // Date : 29.09.2007
    // Parms : $id_prop - id of the position
    // Returns :      true,false / Void
    // Description : del current line of Price levels
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 29.09.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function DelCurrentPriceLevel()
    {
        $tmp_db = new DB();
        $q = "DELETE FROM `".TblModCatalogPriceLevels."` WHERE `id`='$this->id_price_level'";
        $res = $tmp_db->db_Query( $q );
        //echo '<br>q='.$q.' res='.$res.' $tmp_db->result='.$tmp_db->result;
        if( !$res OR !$tmp_db->result )return false;
    }//end of function DelCurrentPriceLevel()

 } // end of class Catalog_content
