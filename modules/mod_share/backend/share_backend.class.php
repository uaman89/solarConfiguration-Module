<?php 
// ================================================================================================
//    System     : SEOCMS
//    Module     : Pages
//    Date       : 04.02.2005
//    Licensed To:
//                 Ihor Trokhymchuk     ihoru@mail.ru
//    Purpose    : Class definition for dynamic pages
// ================================================================================================

 include_once( SITE_PATH.'/modules/mod_share/share.defines.php' );

// ================================================================================================
//    Class                      : ShareBackend
//    Date                       : 02.12.2008
//    Constructor                : Yes
//    Returns                    : None
//    Description                : Dynamic Pages Module
//    Programmer                 :  Ihor Trokhymchuk
// ================================================================================================

class ShareBackend extends Share
{
    var $Right;
    var $Form;
    var $Msg;
    var $Spr;

    var $id = NULL;
    var $title;
    var $description;
    var $body;
    var $keywords;
    var $name;
    var $descr;

    var $display;
    var $sort;
    var $start;
    var $move;
    var $id_categ;

    var $user_id;
    var $module;

    var $fltr;
    var $fln;

    var $width;

    var $Err;
    var $lang_id;

    var $visible;
    var $sel = NULL;
    var $main_page = 0;
    
    // ================================================================================================
    //    Function          : ShareBackend (Constructor)
    //    Date              : 04.02.2005
    //    Description       : Pages
    // ================================================================================================
    function __construct($user_id = NULL, $module = NULL)
    {
        if(defined("_LANG_ID")) $this->lang_id = _LANG_ID;
        $this->width = '750';

        $this->user_id = $user_id;
        $this->module = $module;
        
        $this->db =  DBs::getInstance();
        $this->Right =  &check_init('RightsShare', 'Rights', "'$this->user_id','$this->module'");                   /* create Rights obect as a property of this class */
        $this->Form = &check_init('FormPages', 'Form', "'mod_pages'");        /* create Form object as a property of this class */
        $this->ln_sys = &check_init('SysLang', 'SysLang'); 
        $this->ln_arr = $this->ln_sys->LangArray( $this->lang_id ); 
        $this->Spr = &check_init('SysSpr', 'SysSpr'); /* create SysSpr object as a property of this class */
          
        $this->UploadImages = &check_init('UploadImage', 'UploadImage', "'90', 'null', 'uploads/images/share', 'mod_share_file_img'");
        $this->UploadFile = &check_init('UploadClass', 'UploadClass', '90, null, "uploads/files/share","mod_share_file"');
        //$this->UploadVideo = &check_init('UploadVideo', 'UploadVideo', "'90', 'null', 'uploads/video/pages','mod_page_file_video'");
        //$this->UploadFileImages->CreateTables();
        //$this->UploadFileVideo->CreateTables();
        //$this->UploadFile->CreateTables();
        $this->settings = $this->GetSettings(1); 
        $this->multi = &check_init_txt('TblBackMulti',TblBackMulti);

        ( defined("USE_TAGS")                  ? $this->is_tags = USE_TAGS                     : $this->is_tags=0 ); // использовать тэги
        ( defined("PAGES_USE_SHORT_DESCR")     ? $this->is_short_descr = PAGES_USE_SHORT_DESCR : $this->is_short_descr=0 ); // Краткое оисание страницы
        ( defined("PAGES_USE_SPECIAL_POS")     ? $this->is_special_pos = PAGES_USE_SPECIAL_POS : $this->is_special_pos=0 ); // специальное размещение страницы
        ( defined("PAGES_USE_IMAGE")           ? $this->is_image = PAGES_USE_IMAGE             : $this->is_image=0 ); // изображение к странице
        ( defined("PAGES_USE_IS_MAIN")         ? $this->is_main_page = PAGES_USE_IS_MAIN       : $this->is_main_page=0 ); // главная страница сайта
    } //end of constructor ShareBackend


    // ================================================================================================
    // Function : show_levels_tree()
    // Date : 15.10.2009
    // Returns : true,false / Void
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function show_levels_tree()
    {
        $db = DBs::getInstance();
        $q = "SELECT `".TblModShare."`.id, `".TblModShare."`.level, `".TblModShare."`.move, `".TblModShareTxt."`.pname 
              FROM `".TblModShare."`, `".TblModShareTxt."`
              WHERE  `".TblModShare."`.id=`".TblModShareTxt."`.cod
              AND `".TblModShareTxt."`.lang_id='".$this->lang_id."'
              ORDER BY `move` ";
              
        $res = $db->db_Query($q);
        $tree = array();
        $rows = $db->db_GetNUmRows($res);
        for($i=0; $i<$rows; $i++)
        {
            $row = $db->db_FetchAssoc($res);
            $tree[$row['level']][] = $row;
        }
         ?>
        <script src="/admin/include/js/treeView/jquery.treeview.js" type="text/javascript"></script>
        <script src="/admin/include/js/treeView/jquery.cooki.js" type="text/javascript"></script>
            <link rel="stylesheet" href="/admin/include/js/treeView/jquery.treeview.css" />
            <script type="text/javascript">
		$(document).ready(function() {
			$("#tree").treeview({
				collapsed: true,
				animated: "medium",
				control:"#sidetreecontrol",
				persist: "cookie",
                                cookieId: "shareTreeView"
			});
		})
		
            </script>
            <div id="sidetreecontrol"><a href="?#"><?=$this->multi['TXT_COLLAPSE_ALL']?></a> | <a href="?#"><?=$this->multi['TXT_EXPAND_ALL']?></a></div>
        <?
        //echo '<br>$q='.$q.' $res='.$res;
        $this->show_levels_tree_inner($tree);
    } //end of function show_levels_tree()

   // ================================================================================================
    // Function : show_levels_tree_inner()
    // Date : 15.10.2009
    // Returns : true,false / Void
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function show_levels_tree_inner($tree, $level=0)
    {
        if(!isset($tree[$level])) return;
        $curTree = $tree[$level];
        $count = count($curTree);
        ?><ul id="tree" class="filetree treeview"><?
        for($i=0; $i<$count; $i++)
        {
         if(isset($tree[$curTree[$i]['id']])) $sublevels = count($tree[$curTree[$i]['id']]);
         else $sublevels = 0;
         if($sublevels>0) $link = $this->script.'&amp;level='.$curTree[$i]['id'];
         else $link = $this->script.'&amp;level='.$curTree[$i]['level'].'&amp;task=edit&amp;id='.$curTree[$i]['id'];
        
         if($sublevels>0){ $li="lev0";
             $a='folder';
             }else{
                 $a='file';
             }
         ?>
         <li >                                
            <a href='<?=$link;?>' class="<?=$a;?>"><?=stripslashes($curTree[$i]['pname']);?></a>
            <?if($sublevels>0) {?><span class="not_href">[<?=$sublevels;?>]</span><?}?>
         <?
         if($sublevels>0)
            $this->show_levels_tree_inner($tree,$curTree[$i]['id']);
         ?></li><?
        } //end for
        ?></ul><?
    } //end of function show_levels_tree_inner()

    // ================================================================================================
    /*function show_levels_tree_old($level=0, $j=1)
    {
        $db = new DB();
        //$q = "select * from `".TblModShare."` where 1 and `level`='".$level."' order by `move` ";
        $q = "SELECT `".TblModShare."`.id, `".TblModShare."`.level, `".TblModShare."`.move, `".TblModShareTxt."`.pname 
              FROM `".TblModShare."`, `".TblModShareTxt."`
              WHERE `level`='".$level."'
              AND `".TblModShare."`.id=`".TblModShareTxt."`.cod
              AND `".TblModShareTxt."`.lang_id='".$this->lang_id."'
              ORDER BY `move` ";
              
        $res = $db->db_Query($q);
        //echo '<br>$q='.$q.' $res='.$res;
        $rows = $db->db_GetNUmRows($res);
        //echo $this->page;
        ?><ul class="sub_menu"><?
        for($i=0; $i<$rows; $i++)
        {
         $row = $db->db_FetchAssoc($res);
         $sublevels = $this->IsSubLevels($row['id'], 'back');
         if($sublevels>0) $link = $this->script.'&amp;level='.$row['id'];
         else $link = $this->script.'&amp;level='.$row['level'].'&amp;task=edit&amp;id='.$row['id'];
         //$count_content = $this->IsContent($row['id']);
         //echo '<br>$count_content='.$count_content;
         //echo '<br>$this->level='.$this->level.' $row[id]='.$row['id'];
         if( $this->level==$row['id'] ) { $li="lev_act"; $s="mactive";}
         else { 
             if($sublevels>0) $li="lev0";
             else $li="lev_page";
             $s="mpass";
         }
         ?>
         <li class="<?=$li;?>">                                
          <a href=<?=$link;?> class="<?=$s;?>"><?=stripslashes($row['pname']);?></a>
          <?//$this->Spr->GetNameByCod( TblModPagesSprName, $row['id'], $this->lang_id, 0)
          if($sublevels>0) {?><span class="not_href">[<?=$sublevels;?>]</span><?}?>
         </li>
         <?
         $j++;
         if($sublevels>0)
            $this->show_levels_tree($row['id'], $j);

        } //end for
        ?></ul><?
    } */
    
   // ================================================================================================
   // Function : GetContent
   // Date : 19.03.2008
   // Returns : true,false / Void
   // Description : execute SQL query
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function GetContent($limit='limit')
   {
     $q = "SELECT `".TblModShare."`.*, `".TblModShareTxt."`.* FROM `".TblModShare."`, `".TblModShareTxt."`
           WHERE `".TblModShare."`.`level`='".$this->level."'
           AND `".TblModShare."`.`id`=`".TblModShareTxt."`.`cod`
           AND `".TblModShareTxt."`.`lang_id`='".$this->lang_id."'
           ORDER BY `".TblModShare."`.`move` asc";
     if($limit=='limit') $q = $q." LIMIT ".$this->start.", ".$this->display;
     $res = $this->Right->Query( $q, $this->user_id, $this->module ); 
     //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result.' $this->user_id='.$this->user_id;        
     if( !$res )return false;
     $rows = $this->Right->db_GetNumRows();
     $arr = array();
     for( $i = 0; $i < $rows; $i++ ){
        $arr[$i] = $this->Right->db_FetchAssoc();
     }
     return $arr;
   }//end of function GetContent()    
    
    // ================================================================================================
    // Function : show()
    // Date : 16.09.2009
    // Returns :     true,false / Void
    // Description : Show News
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function show()
    {
        $db =  &check_init('Rights', 'Rights');
        $arr_data = $this->GetContent('no_limit');
        $rows = count($arr_data);

         /* Write Form Header */
         $this->Form->WriteHeader( $this->script );
         ?>
         <table border="0" cellpadding="5" cellspacing="0" width="100%">
          <tr>
           <td width="200" style="vertical-align:top; border: solid 1px #ACACAC; background:#F7F7F7;">
            <table border="0">
                <tr>
                <td><a href="<?=$_SERVER['PHP_SELF'].'?module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort.'&fltr='.$this->fltr;?>" title="<?=$this->multi['TXT_ROOT_CATEGORY'];?>"><img src="images/icons/categ.png" border="0" alt="<?=$this->multi['TXT_SHARE_STRUCTURE'];?>" title="<?=$this->multi['TXT_PAGES_STRUCTURE'];?>" /></a></td>
                <td><a href="<?=$_SERVER['PHP_SELF'].'?module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort.'&fltr='.$this->fltr;?>" title="<?=$this->multi['TXT_ROOT_CATEGORY'];?>"><h4 style="margin:0px; padding:0px;"><?=$this->multi['TXT_SHARE_STRUCTURE'];?></h4></a></td>
                </tr>
            </table>

            <?$this->show_levels_tree();?>

            <img src="images/spacer.gif" width="200" height="1" />
           </td>
           <td valign="top">
            <?
            if ( empty($this->level) OR $this->level==0) 
                $txt_msg = $this->multi['TXT_ROOT_CATEGORY'];
            else {
                $page_txt = $this->GetShare( $this->level );
                $txt_msg = $this->multi['FLD_SUBLEVEL'].' :: '.stripslashes($page_txt['pname']);
                echo $this->ShowPath($this->level);
            }
            ?>
            <span style="font-size:14px; margin:5px; font-weight:bold;"><?=$txt_msg;?></span>
            <?
            $this->ShowErrBackEnd(); 
            /* Write Table Part */
            AdminHTML::TablePartH();
            /* Write Links on Pages */
            ?><tr><td colspan="12"><?
            $script1 = 'module='.$this->module.'&fltr='.$this->fltr."&level=".$this->level;
            $script1 = $_SERVER['PHP_SELF']."?$script1";
            //echo '<br>$this->display='.$this->display;
            $this->Form->WriteLinkPages( $script1, $rows, $this->display, $this->start, $this->sort );
            ?>
            <tr><td colspan="4"><?$this->Form->WriteTopPanel( $this->script );?></td></tr>
            <tr><td>
              <div name="load" id="load"></div>
              <div id="result"></div>
              <div id="debug"><?=$this->showHTML();?></div>
            </td></tr>
            <?
            AdminHTML::TablePartF();
            ?>
           </td>
          </tr>
         </table>
         <?
         $this->Form->WriteFooter();
         return true;
    }// end of function show


    // ================================================================================================
    // Function : showHTML()
    // Date : 16.10.2009
    // Parms :   $rows
    // Returns :      true,false / Void
    // Description : return path of names to the page
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function showHTML($rows=NULL)
    {
        $arr_data = $this->GetContent();
        $rows = count($arr_data);
        $this->Form->Hidden( 'level', $this->level);
        $arr = NULL;
        
        $script2 = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&task=show&fltr='.$this->fltr."&level=".$this->level;
        $script2 = $_SERVER['PHP_SELF']."?$script2";
        if ($this->sel==1)
            $scr = 2;
        else 
            $scr = 1;
        ?>
        <table border="0" cellpadding="0" cellspacing="1">
         <tr>
          <td class="THead" width="10"><a href="<?=$script2?>&amp;sel=<?=$scr?>" class="aTHead">*</a></td>
          <td class="THead" width="40"><A href="<?=$script2?>&sort=id" class="aTHead"><?=$this->multi['FLD_ID']?></a></td>
          <td class="THead"><?=$this->multi['_FLD_NAME']?></td>
          <td class="THead"><?=$this->multi['FLD_SUBLEVEL']?></Td>
          <?/*<td class="THead"><a href="<?=$script2?>&sort=pages" class="aTHead"><?=$this->Msg->show_text('_FLD_LINK_NAME')?></a></td>*/?>
          <?if( $this->is_image==1 ){?><td class="THead"><?=$this->multi['FLD_IMG']?></td><?}?>
          <td class="THead"><?=$this->multi['_TXT_META_DATA']?></td>
          <td class="THead"><?=$this->multi['_TXT_PAGES_BODY']?></td>
          <?if($this->is_special_pos==1){?>
          <td class="THead"><a href="<?=$script2?>&sort=special_pos" class="aTHead"><?=$this->multi['FLD_SPECIAL_POS']?></a></Td> 
          <?}?>
          <td class="THead"><a href="<?=$script2?>&sort=visible" class="aTHead"><?=$this->multi['_TXT_SHOW_PAGE']?></a></Td>
          <td class="THead"><a href="<?=$script2?>" class="aTHead"><?=$this->multi['BTN_MOVE']?></a></Td>
          <?

         $id = 0;
         $j = 0;
         $a = $rows;
         $up = 0;
         $down = 0;

         for( $i = 0; $i < $rows; $i++ )
         {
           $row = $arr_data[$i];
           //$row = $row_arr[$i];
           if( (float)$i/2 == round( $i/2 ) ) 
                $class = 'TR1';
           else 
                $class = 'TR2';

           $pname = stripslashes($row['pname']);
           $content = stripslashes($row['content']); 
           $title = stripslashes($row['mtitle']);
           $descr = stripslashes($row['mdescr']);
           $keywords = stripslashes($row['mkeywords']);
           $img = stripslashes($row['img_filename']);
           ?>
           <tr class="<?=$class;?>">
           <td><?=$this->Form->CheckBox( "id_del[]", $row['id'], $this->sel);?>
           <td><?=$this->Form->Link( $this->script."&task=edit&id=".$row['id'], stripslashes( $row['id'] ), $this->multi['TXT_EDIT'] );?>
           <td align="left" nowrap="nowrap" style="padding:5px;">
             <div><?=$pname;?></div>
            <p style="padding:5px 0px 0px 0px; margin:0px; font-weight:normal; text-align:left;">URL: <?=$row['name'];?></p>
           </td>
           <td>
            <?
            $num_sub_levels = $this->IsSubLevels($row['id'], 'back');
            $this->Form->Link( $this->script."&task=show&level=".$row['id'], $this->multi['FLD_SUBLEVEL'], $this->multi['_TXT_EDIT_SUBLEVEL'] ); if($num_sub_levels>0){?><span class="simple_text"><?='['.$num_sub_levels.']';?></span><?}
            ?>
           </td>
           <?/*<td align="left" nowrap="nowrap" style="padding:5px; font-weight:normal;"><?=$row['name'];?></td>*/?>
           <?if( $this->is_image==1 ){?>
           <td align="center" style="padding:2px;">
            <?
                if( !empty($img) ) {
                   $img_with_path_small = $this->GetImgWithPath($img, $this->lang_id); //Pages_Img_Path_Small.$lang_id.'/'.$img;  
                   $this->Spr->ShowImageByPath($img_with_path_small, 'size_auto=75', 85, NULL, NULL);
                }
            ?>
           </td>
           <?}?>
           <td align="left" style="padding:5px; font-weight:normal;" nowrap="nowrap" >
            <?
            ?>
            <?if( !empty($title)){ ?><div onmouseover="return overlib('<?=$title;?>',WRAP);" onmouseout="nd();" onclick="nd();"><?=$this->Form->ButtonCheck(); echo ' '.$this->multi['FLD_TITLE']; ?></div><?}?>
            <?if( !empty($descr)){ ?><div onmouseover="return overlib('<?=$descr;?>',WRAP);" onmouseout="nd();" onclick="nd();"><?=$this->Form->ButtonCheck(); echo ' '.$this->multi['FLD_DECRIPTION'];?></div><?}?>
            <?if( !empty($keywords)){ ?><div onmouseover="return overlib('<?=$keywords;?>',WRAP);" onmouseout="nd();" onclick="nd();"><?=$this->Form->ButtonCheck(); echo ' '.$this->multi['FLD_KEYWORDS'];?></div><?}?>
           </td>
           <td align="center"><?
            if( !empty($content) ) { $this->Form->ButtonCheck(); }?>
           </td>
           <?if($this->is_special_pos==1){?>
           <td>
            <?
            //echo '$row[special_pos]='.$row['special_pos'];
            //echo '$row[special_pos]='.$row['special_pos'];
            if( $row['special_pos']==1) {echo '<img src="images/icons/tick.png">';}?>
           <?}?>
           </td>
           <td>
            <?
            switch($row['visible']){
                   case '1':
                    echo '<img src="images/icons/tick.png">';
                    break;
                case '0':
                    echo '<img src="images/icons/publish_x.png">';
                    break;
            }
            ?>
           </td>
           
           <td align="center" nowrap><?
            $url = '/modules/mod_pages/share.backend.php?'.$this->script_ajax; 
            if( $up!=0 ){
                $this->Form->ButtonUpAjax($this->script, $row['id'], $url, 'debug', 'move', $row['move']);?>&nbsp;<?
                /*?><a href=<?=$this->script?>&task=up&move=<?=$row['move']?> title="UP"><?=$this->Form->ButtonUp( $row['id'] );?></a><?*/
            }
            else{?><img src="images/spacer.gif" width="12"/><?}
            //for replace
            $this->Form->TextBoxReplace($url, 'debug', 'move', $row['move'], $row['id']);
            if( $i!=($rows-1) ){
                ?>&nbsp;<?$this->Form->ButtonDownAjax($this->script, $row['id'], $url, 'debug', 'move', $row['move']);
                /*?><a href=<?=$this->script?>&task=down&move=<?=$row['move']?> title="DOWN"><?=$this->Form->ButtonDown( $row['id'] );?></a><?*/
            }
            else{?><img src="images/spacer.gif" width="12"/><?}

            $up=$row['id'];
            $a=$a-1;
            ?>
           </td>
           <?
         } //-- end for
         ?></table><?
    }//end of function showHTML()


    // ================================================================================================
    // Function : ShowPath()
    // Date : 19.02.2008
    // Parms :   $id - id of the page
    //           $path - string with path for recursive execute
    // Returns :      true,false / Void
    // Description : return path of names to the page
    // Programmer : Igor Trokhymchuk
    // ================================================================================================
    function ShowPath($id, $path=NULL)
    {
        $db = DBs::getInstance();
        $level = $this->GetLevel($id);
        $q = "SELECT `".TblModShare."`.`id`, `".TblModShare."`.`level`, `".TblModShareTxt."`.`pname` 
              FROM `".TblModShare."`, `".TblModShareTxt."` 
              WHERE `".TblModShare."`.`id`='".$level."'
              AND `".TblModShare."`.`id`=`".TblModShareTxt."`.`cod`
              AND `".TblModShareTxt."`.`lang_id`='".$this->lang_id."'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $row = $db->db_FetchAssoc();

        $name = stripslashes($row['pname']);
        //echo '<br>$name='.$name;
        if( !empty($name) ) $path = '<a href="'.$this->script.'&level='.$row['id'].'">'.$name.'</a> / '.$path;
        //echo '<br>$path='.$path.' $row[level]='.$row['level'];
        if( $row['level']>0 ) $res = $this->ShowPath($row['id'], $path);
        else{
            if( strstr($path, '/')) $res = '<a href="'.$this->script.'&level=0">'.$this->multi['TXT_ROOT_CATEGORY'].'</a> / '.$path;
            else $res = $path;
        }
        return $res;
    }//end of function ShowPath()

    // ================================================================================================
    // Function : up()
    // Date : 11.02.2005
    // Returns :      true,false / Void
    // Description :  Up position
    // Programmer :  Andriy Lykhodid
    // ================================================================================================
    function up($table, $level_name = NULL, $level_val = NULL)
    {
     $q="select * from `$table` where `move`='$this->move'";
     if ( !empty($level_name) ) $q = $q." AND `$level_name`='$level_val'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res )return false;
     $rows = $this->Right->db_GetNumRows();
     $row = $this->Right->db_FetchAssoc();
     $move_down = $row['move'];
     $id_down = $row['id'];


     $q="select * from `$table` where `move`<'$this->move'";
     if ( !empty($level_name) ) $q = $q." AND `$level_name`='$level_val'";
     $q = $q." order by `move` desc";
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
    } // end of function up()


    // ================================================================================================
    // Function : down()
    // Date : 11.02.2005
    // Returns :      true,false / Void
    // Description :  Down position
    // Programmer :  Andriy Lykhodid
    // ================================================================================================
    function down($table, $level_name = NULL, $level_val = NULL)
    {
     $q="select * from `$table` where `move`='$this->move'";
     if ( !empty($level_name) ) $q = $q." AND `$level_name`='$level_val'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res )return false;
     $rows = $this->Right->db_GetNumRows();
     $row = $this->Right->db_FetchAssoc();
     $move_up = $row['move'];
     $id_up = $row['id'];


     $q="select * from `$table` where `move`>'$this->move'";
     if ( !empty($level_name) ) $q = $q." AND `$level_name`='$level_val'";
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
    } // end of function down()


    // ================================================================================================
    // Function : edit()
    // Date : 14.01.2011
    // Returns : true,false / Void
    // Description : edit/add records in News module
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function edit()
    {
        $ShareLayout = &check_init('ShareLayout', 'ShareLayout'); 
        $Panel = &check_init('Panel', 'Panel');
        $ln_sys = &check_init('SysLang', 'SysLang');
        $mas = NULL;
        $script = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort.'&fltr='.$this->fltr."&level=".$this->level;
        $script = $_SERVER['PHP_SELF']."?$script";
        $fl=NULL;
        if( $this->id!=NULL ){
           $q = "SELECT `".TblModShare."`.*, `".TblModShareTxt."`.`pname` 
                 FROM `".TblModShare."`, `".TblModShareTxt."` 
                 WHERE `".TblModShare."`.`id`='".$this->id."'
                 AND `".TblModShare."`.`id`=`".TblModShareTxt."`.`cod`
                 AND `".TblModShareTxt."`.`lang_id`='".$this->lang_id."'";
           $res = $this->Right->Query( $q, $this->user_id, $this->module );
           if( !$res ) return false;
           $mas = $this->Right->db_FetchAssoc();
           //echo '<br>$q='.$q.' $res='.$res;
        }
        
        /* Write Form Header */
        $this->Form->WriteHeaderFormImg( $script );
        $this->Form->Hidden( 'item_img', "" );  
        $this->Form->Hidden( 'lang_id', "" );
        $this->Form->Hidden( 'textarea_editor', "" );
        $settings=SysSettings::GetGlobalSettings();
        $this->Form->textarea_editor = $settings['editer']; //'tinyMCE'; 
        $this->Form->IncludeSpecialTextArea( $this->Form->textarea_editor );  
         
         if( $this->id!=NULL ) $txt = $this->multi['TXT_EDIT'];
         else $txt = $this->multi['_TXT_ADD_DATA'];
         AdminHTML::PanelSubH( $txt );

         if( !empty($this->Err) ) $this->ShowErrBackEnd();

         AdminHTML::PanelSimpleH();
         
         ?>
             <tr>
              <td width="50%"><b><?=$this->multi['FLD_ID'];?>:</b>
               <?
               if( $this->id!=NULL ){
                  echo $mas['id'];
                  $this->Form->Hidden( 'id', $mas['id'], 'pageId' );
                   }
               else $this->Form->Hidden( 'id', '' );
               $this->Form->Hidden( 'level', $this->level);
               //echo "this->level = ".$this->level;
               ?>
              </td>
             </tr>
             <tr>
              <td colspan="3">
               <?
               $arr = $this->GetShareInArray(0, $this->multi['TXT_ROOT_CATEGORY']);
               //print_r($arr);
               $scriplink = $this->script;
               if(isset($mas['level'])) $tmp_lev = $mas['level'];
               else $tmp_lev = $this->level;
               if( !$this->id ){
                   ?><b><?=$this->multi['_FLD_ADD_TO_LEVEL'];?>:</b><?
                   $params = '';
               }
               else{
                   ?><b><?=$this->multi['_FLD_SHARE_IN_LEVEL'];?>:</b><?
                   $params = 'disabled';
                   $this->Form->Hidden( 'level', $tmp_lev );
                   if( array_key_exists($this->id, $arr) ) unset($arr[$this->id]);
               }
               $this->Form->Select( $arr, 'level', $tmp_lev, NULL, 'id="idlevelp" '.$params );
               if( $this->id ){?>&nbsp;<?$this->Form->ButtonSimple("btn", $this->multi['TXT_EDIT'], NULL, "id='button01' onClick=\"EditField('idlevelp','button01');\"");}
               ?>
              </td>
             </tr>
             
             <tr>
                <td>
                   <? 
                   if( $this->id!=NULL ) $this->Err!=NULL ? $visible=$this->visible : $visible=$mas['visible'];
                   else $this->Err!=NULL ? $visible=$this->visible : $visible=1; 
                   ($visible==1)? $checked = 'checked="checked"': $checked = NULL;
                   $this->Form->CheckBox('visible', 'visible', $visible, 'visibleId');
                   echo '<label for="visibleId"><b>'.$this->multi['_TXT_SHOW_SHARE'].'</b></label>';
                   ?>
                   <span class="help">&nbsp;-&nbsp;<?=$this->multi['_HELP_MSG_SHOW_SHARE'];?></span>
                   <br/>
                   
                   <?
                  
                   if($this->is_special_pos==1){
                       if( $this->id!=NULL ) $this->Err!=NULL ? $special_pos=$this->special_pos : $special_pos=$mas['special_pos'];
                       else $this->Err!=NULL ? $special_pos=$this->special_pos : $special_pos=0;
                       echo '<br/>';$this->Form->CheckBox('special_pos', 'special_pos', $special_pos, 'special_posId');
                       echo '<label for="special_posId"><b>'.$this->multi['FLD_SPECIAL_POS'].'</b></label>';

                   } 
                   ?>
                </td>
             </tr>
             <tr valign="top">
                <td colspan="3">
                   <b><?=$this->multi['_FLD_LINK_SHARE_NAME'];?>:</b>
                   <br/>
                   <?
                   if( $this->id!=NULL ) $this->Err!=NULL ? $ctrlscript=$this->ctrlscript : $ctrlscript=$mas['ctrlscript'];
                   else $this->Err!=NULL ? $ctrlscript=$this->ctrlscript : $ctrlscript=1;
                   
                   $pname = stripslashes($mas['pname']);
                   //echo '<br />$pname='.$pname;
                   ?>
                  <div id="urlname"><?$this->ShowURLName($ShareLayout,$ctrlscript, $pname);?></div>
                </td>
             </tr>             
             <tr valign="top">
                <td>
                   <?
                   $url = '/modules/mod_share/share.backend.php?'.$this->script_ajax;
                   $formname=$this->Form->name;
                   $params = " onClick=\"RefrefhURL('".$url."&task=ajax_refresh_urlname&descr[".$this->lang_id."]=".$pname."', 'urlname')\" ";
                   $this->Form->Hidden('old_ctrlscript', $ctrlscript);
                   $this->Form->CheckBox('ctrlscript', 'ctrlscript', $ctrlscript, "ctrlscriptId", $params);
                   echo '<label for="ctrlscriptId">'.$this->multi['FLD_CTRL_SCRIPT'].'<label>';
                   ?>
                   <div class="help leftPadding"><?=$this->multi['HELP_FLD_CTRL_SCRIPT'];?></div>
                   <?

                   ?>
                </td>
             </tr>
             <?
             if ( $this->is_tags==1 ) {  
                $Tags = new SystemTags($this->user_id, $this->module);
                if( $this->id!=NULL ) $this->Err!=NULL ? $id_tag=$this->id_tag : $id_tag=$Tags->GetTagsByModuleAndItem($this->module, $this->id);
                else $id_tag=$this->id_tag;
                //echo '<br>$id_tag='.$id_tag; print_r($id_tag);
                ?><tr><td valign="top" colspan="2"><?$Tags->ShowEditTags($id_tag);?></td></tr><?        
             } 
             ?> 
             <tr>
              <td colspan="3">
              <?
              $Panel->WritePanelHead( "SubPanel_" );
              reset($this->ln_arr);
              while( $el = each( $this->ln_arr ) ){
                 $lang_id = $el['key'];
                 $lang = $el['value'];
                 $mas_s[$lang_id] = $lang;

                 $q2 = "SELECT * FROM `".TblModShareTxt."` WHERE `cod`='".$this->id."' AND `lang_id`='".$lang_id."'";
                 $res2 = $this->db->db_Query($q2);
                 //echo '<br>$q2='.$q2.' $res2='.$res2;
                 $mas2 = $this->db->db_FetchAssoc();
                 
                 $Panel->WriteItemHeader( $lang );
                 echo "\n <table border=0 class='EditTable'>";

                 echo "\n <tr>";
                 echo "\n <td><b>".$this->multi['_FLD_NAME'].":</b>";
                 echo "\n <br>";
                 echo '<div class="help">'.$this->multi['_HELP_MSG_PAGE_NAME'].'</div>';
                 echo "\n <br>";
                 if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->descr[$lang_id] : $val=$mas2['pname'];
                 else $val=$this->descr[$lang_id];
                 $params = "onBlur=\"RefrefhURL('".$url."', 'urlname', 'ajax_refresh_urlname')\" ";
                 $this->Form->TextBox( 'descr['.$lang_id.']', stripslashes($val), 110 );

                 if ( $this->is_short_descr==1 ){
                     echo "\n <tr>";
                     echo "\n <td><b>".$this->multi['FLD_SHORT_DESCR'].":</b>";
                     echo "\n <br>";
                     echo '<div class="help">'.$this->multi['_HELP_MSG_SHORT_DESCR'].'</div>';
                     echo "\n";
                     if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->short[$lang_id] : $val=$mas2['short'];
                     else $val=$this->short[$lang_id];
                     $this->Form->Textarea( 'short['.$lang_id.']', stripslashes($val), 3, 110 );
                 }
                 echo "\n</table><br>";

                 echo "\n<fieldset title='".$this->multi['_TXT_META_DATA']."'> <legend><span style='vetical-align:middle; font-size:15px;'><img src='images/icons/meta.png' alt='".$this->multi['_TXT_META_DATA']."' title='".$this->multi['_TXT_META_DATA']."' border='0' /> ".$this->multi['_TXT_META_DATA']."</span></legend>";
                 echo "\n <table border=0 class='EditTable'>";
                 echo "\n <tr>";
                 echo "\n <td><b>".$this->multi['FLD_TITLE'].":</b>";
                 echo "\n <br>";
                 echo '<span class="help">'.$this->multi['HELP_MSG_PAGE_TITLE'].'</span>';
                 echo "\n <br>";
                 if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->title[$lang_id] : $val=$mas2['mtitle'];
                 else $val=$this->title[$lang_id];
                 $this->Form->TextBox( 'title['.$lang_id.']', stripslashes($val), 110 );
                 echo "<hr width='70%' align='left' size='1'>";


                 echo "\n <tr>";
                 echo "\n <td><b>".$this->multi['FLD_DECRIPTION'].":</b>";
                 echo "\n <br>";
                 echo '<span class="help">'.$this->multi['HELP_MSG_PAGE_DESCRIPTION'].'</span>';
                 echo "\n <br>";
                 if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->description[$lang_id] : $val=$mas2['mdescr'];
                 else $val=$this->description[$lang_id];
                 $this->Form->TextArea( 'description['.$lang_id.']', stripslashes($val), 2, 110 );
                 echo "<hr width='70%' align='left' size='1'>";

                 echo "\n <tr>";
                 echo "\n <td><b>".$this->multi['FLD_KEYWORDS'].":</b>";
                 echo "\n <br>";
                 echo '<span class="help">'.$this->multi['_HELP_MSG_PAGE_KEYWORDS'].'</span>';
                 echo "\n <br>";
                 if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->keywords[$lang_id] : $val=$mas2['mkeywords'];
                 else $val=$this->keywords[$lang_id];
                 $this->Form->TextArea( 'keywords['.$lang_id.']', stripslashes($val),2, 110 );
                 echo "\n </table>";
                 echo "</fieldset><br>";

                 echo "\n <table border=0 class='EditTable'>";
                 echo "\n <tr>";
                 $params = " onClick=\"ChangeEditor('".$url."', 'pagecontent$lang_id', 'ajax_refresh_editor', 'tinyMCE', '".$lang_id."'); return false;\"";
                 echo "\n <td><b>".$this->multi['_TXT_PAGES_BODY'].": </b> &nbsp;";
                 //$this->Form->Button("chng_edt","Change editor", NULL, $params);
                 echo "\n <br>";
                 echo '<span class="help">'.$this->multi['_HELP_MSG_PAGE_CONTENT'].'</span></td></tr>';
                 echo "\n <tr><td id=\"pagecontent$lang_id\">";
                 $this->EditShareContentHtml($mas2['content'], $lang_id);
                 echo "\n </td></tr>";
                
                 if($this->is_image==1){
                    echo "\n <tr>";
                    echo "\n <td><br/><b>".$this->multi['FLD_IMG'].":</b>";
                    echo "\n <br/>";
                    //$row = $this->Spr->GetImageByCodOnLang(TblModPagesSprName, $mas['id'], $lang_id, 0);
                    //print_r($row);
                    if ( !isset($this->img[$lang_id]) ) $this->img[$lang_id]=NULL;
                    if( $this->id!=NULL ) $this->Err!=NULL ? $img=$this->img[$lang_id] : $img = $mas2['img_filename'];
                    else $img=$this->img[$lang_id];                 
                    if( !empty($img) ) {
                        ?>
                        <table border="0" cellpadding="0" cellspacing="5">
                         <tr>
                          <td><?
                           $this->Form->Hidden( 'img['.$lang_id.']', $img );
                           //$this->Form->Hidden( 'item_img', NULL );
                           $img_with_path_small = $this->GetImgWithPath($img, $lang_id); //Pages_Img_Path_Small.$lang_id.'/'.$img;  
                           $img_with_path = $this->GetImgWithPathFull($img, $lang_id); Pages_Img_Path.$lang_id.'/'.$img;
                           $this->Spr->ShowImageByPath($img_with_path_small, 'size_width=150', 85, NULL, NULL);
                           ?>
                          </td> 
                          <td class='EditTable'>
                           <br><?=$img_with_path;?><br>
                           <a href="javascript:<?=$this->Form->name;?>.item_img.value='<?=$img;?>';<?=$this->Form->name;?>.submit();"><?=$this->multi['_TXT_DELETE_IMG'];?></a>
                          </td>
                         </tr> 
                        </table>
                        <b><?=$this->multi['_TXT_REPLACE_IMG'];?>:</b><?
                    }
                    ?>
                    <INPUT TYPE="file" NAME="image_icon[<?=$lang_id;?>]" size="40" VALUE="<?=$img?>">                    
                    <br/><span class="help"><?=$this->multi['_HELP_MSG_IMAGE'];?></span>
                    <?
                 }
                 echo "\n </table>";
                 $Panel->WriteItemFooter();
              }//end while
              ?>
                    <fieldset title="<?=$this->multi['TXT_SHARE_DeTAILS']?>" >
                        <legend>
                            <?=$this->multi['TXT_SHARE_DeTAILS']?>
                        </legend>
                  <table>
                        <tr>
                  <td>
                   <? 
                   if( $this->id!=NULL ) $this->Err!=NULL ? $UseEndDate=$this->UseEndDate : $UseEndDate=$mas['UseEndDate'];
                   else $this->Err!=NULL ? $UseEndDate=$this->UseEndDate : $UseEndDate=1; 
                   ($UseEndDate==1)? $checked = 'checked="checked"': $checked = NULL;
                   $this->Form->CheckBox('UseEndDate', 'UseEndDate', $UseEndDate,'UseEndDate', 'onclick=\'
                            if(this.checked)
                            $("#endShareDateBox,#BeginShareDateBox").css("display","block");
                            else $("#endShareDateBox,#BeginShareDateBox").css("display","none");\'');
                   echo '<label for="UseEndDate"><b>'.$this->multi['_TXT_SHOW_SHARE_END_DATE'].'</b></label>';
                   ?>
                   
                </td>
             </tr>
             
             <tr id="BeginShareDateBox" style="<?if($UseEndDate!=1) echo "display: none"?>">
                 <td>
                    <b><?=$this->multi['TXT_SHARE_Begin_DATE']?>: </b>

                     <?
                      $calendar = new DHTML_Calendar(false, 'en', 'calendar-win2k-2', false);
                      $calendar->load_files();
                     if( $this->id!=NULL ) $this->Err!=NULL ? $share_date_begin=$this->ShareBegin : $share_date_begin=$mas['ShareBegin'];
                        else $share_date_begin=strftime('%Y-%m-%d %H:%M', strtotime('now'));   
                            $a1 = array('firstDay'       => 1, // show Monday first
                         'showsTime'      => true,
                         'showOthers'     => true,
                         'ifFormat'       => '%Y-%m-%d %H:%M',
                         'timeFormat'     => '12');
              $a2 = array('style'       => 'width: 15em; color: #363654; background-color: #FBEC88; border: 1px solid #000; text-align: center',
                          'name'        => 'ShareBegin',
                          'value'       => $share_date_begin );
              //echo '<br>$a1='.$a1.' $a2='.$a2.' $start_date_val='.$start_date_val;
              $calendar->make_input_field( $a1, $a2 );?>
                               </td>
             </tr>
           
             <tr id="endShareDateBox" style="<?if($UseEndDate!=1) echo "display: none"?>">
                 <td>
                    <b><?=$this->multi['TXT_SHARE_END_DATE']?>: </b>

                     <?
                      
                     if( $this->id!=NULL ) $this->Err!=NULL ? $share_end_val=$this->ShareEnd : $share_end_val=$mas['ShareEnd'];
                        else $share_end_val=strftime('%Y-%m-%d %H:%M', strtotime('now'));   
                            $a1 = array('firstDay'       => 1, // show Monday first
                         'showsTime'      => true,
                         'showOthers'     => true,
                         'ifFormat'       => '%Y-%m-%d %H:%M',
                         'timeFormat'     => '12');
              $a2 = array('style'       => 'width: 15em; color: #363654; background-color: #FBEC88; border: 1px solid #000; text-align: center',
                          'name'        => 'ShareEnd',
                          'value'       => $share_end_val );
              //echo '<br>$a1='.$a1.' $a2='.$a2.' $start_date_val='.$start_date_val;
              $calendar->make_input_field( $a1, $a2 );?>
                               </td>
             </tr>
             <tr>
                  <td>
                   <? 
                   if( $this->id!=NULL ) $this->Err!=NULL ? $UseManufac=$this->UseManufac : $UseManufac=$mas['UseManufac'];
                   else $this->Err!=NULL ? $UseManufac=$this->UseEndDate : $UseManufac=1; 
                   ($UseManufac==1)? $checked = 'checked="checked"': $checked = NULL;
                   $this->Form->CheckBox('UseManufac', 'UseManufac', $UseManufac,'UseManufac', 'onclick=\'
                            if(this.checked)
                            document.getElementById("ChooseManufacBox").style.display="block"
                            else document.getElementById("ChooseManufacBox").style.display="none"\'');
                   echo '<label for="UseManufac"><b>'.$this->multi['_TXT_USE_SHARE_IN_MANUFAC'].'</b></label>';
                   ?>
                   
                </td>
             </tr>
             <tr id="ChooseManufacBox" style="<?if($UseManufac!=1) echo "display: none"?>">
                <td> <?
                 if( $this->id!=NULL ) $this->Err!=NULL ? $manufacId=$this->manufacId : $manufacId=$mas['manufacId'];
                    else $manufacId=$this->manufacId;         
                    //$this->Spr->ShowInComboBox( TblModCatalogSprManufac,'id_manufac', $id_manufac, 50, NULL, 'name', 'asc' );

                    $arr_manufac = $this->Spr->GetStructureInArray(TblModCatalogSprManufac, NULL, $this->lang_id,  NULL, NULL, 0, 1, 1, 'back');
                    //print_r($arr_categs);
                    $this->Form->Select( $arr_manufac, 'manufacId', $manufacId);  
                 ?>
                </td>
             </tr >
             <tr>
                  <td>
                   <? 
                   if( $this->id!=NULL ) $this->Err!=NULL ? $UseCateg=$this->UseCateg : $UseCateg=$mas['UseCateg'];
                   else $this->Err!=NULL ? $UseCateg=$this->UseEndDate : $UseCateg=1; 
                   ($UseCateg==1)? $checked = 'checked="checked"': $checked = NULL;
                   $this->Form->CheckBox('UseCateg', 'UseCateg', $UseCateg,'UseCateg', 'onclick=\'
                            if(this.checked)
                            document.getElementById("ChooseCategBox").style.display="block"
                            else document.getElementById("ChooseCategBox").style.display="none"\'');
                   echo '<label for="UseCateg"><b>'.$this->multi['_TXT_USE_SHARE_IN_CATEG'].'</b></label>';
                   ?>
                   
                </td>
             </tr>
             <tr id="ChooseCategBox" style="<?if($UseCateg!=1) echo "display: none"?>">
                <td> <?
                 if( $this->id!=NULL ) $this->Err!=NULL ? $CategId=$this->CategId : $CategId=$mas['CategId'];
                    else $CategId=$this->CategId;         
                    $this->Catalog= &check_init('Catalog', 'Catalog'); 
                    $arr_Categ = $this->Catalog->GetCatalogInArray(NULL, $this->multi['TXT_SELECT_CATEGORY'], NULL, NULL, 0, 'back');
                    //print_r($arr_categs);
                    $this->Form->Select( $arr_Categ, 'CategId', 'categ='.$CategId);  
                 ?>
                </td>
             </tr >
             <tr>
                 <td>
                     <?
                     if( $this->id!=NULL ) $this->Err!=NULL ? $skidka=$this->share_date_end : $skidka=$mas['skidka'];
                        else $skidka=$this->skidka;   
                     ?>
                     <b><?=$this->multi['TXT_SCIDCKA']?></b>:<?=$this->Form->TextBox( 'skidka', stripslashes($skidka), 40 );?>
                 </td>
             </tr>
                    </table>
                        
                       
                    </fieldset>
              <?
              //-------------------- Upload Files Start --------------------- 
              //$this->UploadFile->ShowFormToUpload(NULL,$this->id);
              $this->UploadImages->ShowFormToUpload(NULL,$this->id);
              //$this->UploadVideo->ShowFormToUpload(NULL,$this->id);
              //-------------------- Upload Files End --------------------- 
              
              $Panel->WritePanelFooter();
              ?>
              </td>
             </tr>
           <?
        $this->Form->WriteFooter();
        AdminHTML::PanelSimpleF();
        ?>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
         <tr>
          <td>
           <?
           $this->Form->WriteSaveAndReturnPanel( $script );?>&nbsp;<?
           $this->Form->WriteSavePanel( $script );?>&nbsp;<?
           
           $this->Form->WriteCancelPanel( $script );?>&nbsp;<?
           //echo '<br>$this->id='.$this->id;
          
           ?>
          </td>
         </tr>
        </table>
        <?
        AdminHTML::PanelSubF();
        ?>
        
        <script language="JavaScript"> 
         function RefrefhURL(url_with_params, div_id){
              var str='';
              name = document.getElementById('name').value;
              if($("#ctrlscript").is(":checked")){ str = '&ctrlscript=1' }
              did = "#"+div_id;
              $.ajax({
                  type: "POST",
                  url: url_with_params,
                  data: "&name="+name+str,
                  success: function(html){
                      $(did).empty();
                      $(did).append(html);
                  },
                  beforeSend: function(html){
                      $(did).html('<img src="images/icons/loading_animation_liferay.gif"/>');
                  }
              });
         }
         
        function EditField(div_id, idbtn){
            Did = "#"+div_id;
            idbtn = "#"+idbtn;
            if( !window.confirm('<?=$this->multi['MSG_DO_YOU_WANT_TO_MOVE_PAGE'];?>')) return false;
            else{
              $(Did).removeAttr("disabled")
                     .focus();
              $(idbtn).css("display", "none");
            }
        } // end of function EditField
                
        </script>
        <?
    }//end of function edit()
    
    // ================================================================================================
    // Function : ShowURLName()
    // Date : 28.06.2008
    // Returns : true,false / Void
    // Description : show to edit url of the page
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function ShowURLName($ShareLayout,$ctrlscript, $pname)
    {
        $db = new DB();   
        $q = "SELECT * FROM ".TblModShare." WHERE `id`='".$this->id."'";
        $res = $db->db_Query( $q );
        if( !$res OR !$db->result ) return false;
        $mas = $db->db_FetchAssoc();
           
        if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->name : $val=$mas['name'];
        else $val=$this->name;
        //generate translit for empty URL only if this is not main page of the site and only for exist pages, not new one.
        if( empty($val) AND $this->id!=NULL ) $val=$this->GenerateTranslit($this->level, $this->id, $pname );
        
//        if page is one of dynamic pages then generate path to this page
        if( $ctrlscript){
            if ($this->level>0) $path = $ShareLayout->Link($this->level);
            else $path = 'http://www.'.$_SERVER['SERVER_NAME'].'/share/';
        }
       //if page is not dynamic page (this can be script of other module, or other page of the site, or link to the page of other site)
        //then user can manually specify full URL to the page
        else {
            $path = '';
            if( empty($val) ) $val = 'http://www.'.$_SERVER['SERVER_NAME'].'/share/';
        }
        $path = $this->PrepareLink($path);
        echo $path; $this->Form->TextBox( 'name', stripslashes($val), 40, 'id="name"' );
        $this->Form->Hidden('name_old', stripslashes($val) );        
    }//end of function ShowURLName()    

    // ================================================================================================
    // Function : EditPageContentHtml()
    // Date : 28.06.2008
    // Returns : true,false / Void
    // Description : show to edit content of the page  
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function EditShareContentHtml($val_db, $lang_id)
    {
        if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->body[$lang_id] : $val=$val_db;
        else $val=$this->body[$lang_id];
        $this->Form->SpecialTextArea( $this->textarea_editor, 'body['.$lang_id.']', stripslashes($val), 40, 70, 'style="width:100%;"', $lang_id, 'body'  );        
    }//end of function EditPageContentHtml()     
    
    function setOldPriceCateg($oldShare,$rows){
        if($rows>0){
                if(!empty($oldShare['CategId']) && $oldShare['CategId']>0){
                    $q="UPDATE 
                            `".TblModCatalogProp."`,
                            `".TblModCatalogPropSizes."` 
                            SET  `".TblModCatalogProp."`.`price`=`".TblModCatalogProp."`.`old_price`,
                            `".TblModCatalogPropSizes."`.`price`=`".TblModCatalogPropSizes."`.`old_price`
                            WHERE `".TblModCatalogProp."`.`id_cat`='".$oldShare['CategId']."' 
                                AND `".TblModCatalogProp."`.`setPriceManually`='0' 
                                AND `".TblModCatalogProp."`.`share`='0' 
                                AND `".TblModCatalogProp."`.`id`=`".TblModCatalogPropSizes."`.`id_prop`";
                    $res = $this->Right->Query( $q, $this->user_id, $this->module );
                }
            }
    }
    function setOldPrice($oldShare,$rows){
        if($rows>0){
                    $q="UPDATE 
                        `".TblModCatalogProp."`,
                        `".TblModCatalogPropSizes."` 
                        SET  
                        `".TblModCatalogProp."`.`price`=`".TblModCatalogProp."`.`old_price`,
                        `".TblModCatalogPropSizes."`.`price`=`".TblModCatalogPropSizes."`.`old_price` 
                            WHERE `".TblModCatalogProp."`.`setPriceManually`='0' 
                            AND `".TblModCatalogProp."`.`share`='1' 
                            AND `".TblModCatalogProp."`.`share_id`='".$this->id."'
                            AND `".TblModCatalogProp."`.`id`=`".TblModCatalogPropSizes."`.`id_prop`";
                    $res = $this->Right->Query( $q, $this->user_id, $this->module );
            }
    }
    function setOldPriceManuf($oldShare,$rows,$catQ=''){
        if($rows>0){
                if(!empty($oldShare['manufacId']) && $oldShare['manufacId']>0){
                    $q="UPDATE 
                            `".TblModCatalogProp."`,
                            `".TblModCatalogPropSizes."` 
                            SET  
                            `".TblModCatalogProp."`.`price`=`".TblModCatalogProp."`.`old_price`,
                            `".TblModCatalogPropSizes."`.`price`=`".TblModCatalogPropSizes."`.`old_price` 
                            WHERE `".TblModCatalogProp."`.`id_manufac`='".$oldShare['manufacId']."' 
                                AND `".TblModCatalogProp."`.`setPriceManually`='0' 
                                AND `".TblModCatalogProp."`.`share`='0'
                                AND `".TblModCatalogProp."`.`id`=`".TblModCatalogPropSizes."`.`id_prop`";
                    $res = $this->Right->Query( $q, $this->user_id, $this->module );
                }
            }
    }
    
    // ================================================================================================
    // Function : save()
    // Version : 1.0.0
    // Date : 23.12.2009
    // Returns : true,false / Void
    // Description : Store data to the table
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function save()
    {
        $q="SELECT * FROM `".TblModShare."` WHERE `id`='".$this->id."'";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        if( !$res OR !$this->Right->result ) return false;
        $rows = $this->Right->db_GetNumRows();
        $oldShare = $this->Right->db_FetchAssoc();

        if($this->CategId==0 || empty($this->CategId)) $this->UseCateg=0;
        if($this->manufacId==0 || empty($this->manufacId)) $this->UseManufac=0;
        if($this->UseEndDate==1){
            $timestampEnd = $this->getTimeStamp($this->ShareEnd);
            $timestampBegin = $this->getTimeStamp($this->ShareBegin);
            $timestampNow=(mktime(date("H"),date("i"),0,date("m"),date("d"),date('Y')));
            //$difference = floor(($timestampEnd - $timestampBegin)/86400);
            if($timestampEnd>=$timestampNow && $timestampBegin<=$timestampNow) $this->Active=1;
            elseif($timestampBegin>$timestampNow) $this->Active=2;
            elseif($timestampEnd<$timestampNow) $this->Active=0;
        }
        
        $catQ='';
        
        $this->setOldPrice($oldShare,$rows);
        if(($this->Active!=$oldShare['Active'] && $this->Active==0) || ($this->visible==0 && $oldShare['visible']==1)){
            $this->setOldPriceCateg($oldShare,$rows);
            $this->setOldPriceManuf($oldShare,$rows);
        }else{
            if($this->UseCateg==1 && !empty($this->CategId) && $this->CategId!=0){
                $this->setOldPriceCateg($oldShare,$rows);
                $q="UPDATE 
                    `".TblModCatalogProp."`,
                    `".TblModCatalogPropSizes."` 
                    SET  
                    `".TblModCatalogProp."`.`price`=(`".TblModCatalogProp."`.`old_price`-(`".TblModCatalogProp."`.`old_price`*'".$this->skidka."'/100)), 
                    `".TblModCatalogPropSizes."`.`price`=(`".TblModCatalogPropSizes."`.`old_price`-(`".TblModCatalogPropSizes."`.`old_price`*'".$this->skidka."'/100)) 
                    WHERE `".TblModCatalogProp."`.`id_cat`='".$this->CategId."' 
                    AND `".TblModCatalogProp."`.`setPriceManually`='0' 
                    AND `".TblModCatalogProp."`.`share`='0'
                    AND `".TblModCatalogProp."`.`id`=`".TblModCatalogPropSizes."`.`id_prop`";
                $res = $this->Right->Query( $q, $this->user_id, $this->module );
                $catQ=" AND `".TblModCatalogProp."`.`id_cat` NOT LIKE '".$this->CategId."'";
            }elseif($oldShare['UseCateg']==1 && $this->UseCateg==0){
                $this->setOldPriceCateg($oldShare,$rows);
            }
            if($this->UseManufac==1 && !empty($this->manufacId) && $this->manufacId!=0){
                $this->setOldPriceManuf($oldShare,$rows,$catQ);
                $q="UPDATE 
                    `".TblModCatalogProp."`,
                    `".TblModCatalogPropSizes."` 
                    SET  
                    `".TblModCatalogProp."`.`price`=(`".TblModCatalogProp."`.`old_price`-(`".TblModCatalogProp."`.`old_price`*'".$this->skidka."'/100)), 
                    `".TblModCatalogPropSizes."`.`price`=(`".TblModCatalogPropSizes."`.`old_price`-(`".TblModCatalogPropSizes."`.`old_price`*'".$this->skidka."'/100))  
                    WHERE `".TblModCatalogProp."`.`id_manufac`='".$this->manufacId."' 
                    AND `".TblModCatalogProp."`.`setPriceManually`='0' 
                    AND `".TblModCatalogProp."`.`share`='0'
                    AND `".TblModCatalogProp."`.`id`=`".TblModCatalogPropSizes."`.`id_prop`
                    ".$catQ."";
                $res = $this->Right->Query( $q, $this->user_id, $this->module );
            }elseif($oldShare['UseManufac']==1 && $this->UseManufac==0){
                $this->setOldPriceManuf($oldShare,$rows,$catQ);
            }
            $q="UPDATE 
                `".TblModCatalogProp."`,
                `".TblModCatalogPropSizes."` 
                SET  
                `".TblModCatalogProp."`.`price`=(`".TblModCatalogProp."`.`old_price`-(`".TblModCatalogProp."`.`old_price`*'".$this->skidka."'/100)),
                `".TblModCatalogPropSizes."`.`price`=(`".TblModCatalogPropSizes."`.`old_price`-(`".TblModCatalogPropSizes."`.`old_price`*'".$this->skidka."'/100))  
                WHERE `".TblModCatalogProp."`.`setPriceManually`='0' 
                AND `".TblModCatalogProp."`.`share`='1' 
                AND `".TblModCatalogProp."`.`share_id`='".$this->id."'
                AND `".TblModCatalogProp."`.`id`=`".TblModCatalogPropSizes."`.`id_prop`";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
        }
//        echo $q;
        if($rows>0){
            $q="UPDATE `".TblModShare."` SET
                `name`='".$this->name."',
                `visible`='".$this->visible."',
                `level`='".$this->level."',
                `publish`='".$this->publish."',
                `UseEndDate`='".$this->UseEndDate."',
                `ShareEnd`='".$this->ShareEnd."',
                `ShareBegin`='".$this->ShareBegin."',
                `UseManufac`='".$this->UseManufac."',
                `manufacId`='".$this->manufacId."',
                `UseCateg`='".$this->UseCateg."',
                `CategId`='".$this->CategId."',
                `skidka`='".$this->skidka."',
                `Active`='".$this->Active."',
                `ctrlscript`='".$this->ctrlscript."'";
            if($this->is_special_pos==1){
                $q=$q.", `special_pos`='".$this->special_pos."'";
            }
            if($this->is_main_page==1){
                $q=$q.", `main_page`='".$this->main_page."'";
            }
            $q=$q." WHERE `id`='".$this->id."'";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
            if( !$res OR !$this->Right->result ) return false;
        }
        else{
            $q="SELECT MAX(`move`) FROM `".TblModShare."` WHERE 1";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            $my = $this->Right->db_FetchAssoc();
            $maxx=$my['MAX(`move`)']+1;  //add link with position auto_incremental

            $q="INSERT INTO `".TblModShare."` SET
                `name`='".$this->name."',
                `move`='".$maxx."',
                `level`='".$this->level."',
                `visible`='".$this->visible."',
                `publish`='".$this->publish."',
                `UseEndDate`='".$this->UseEndDate."',
                `ShareEnd`='".$this->ShareEnd."',
                `ShareBegin`='".$this->ShareBegin."',
                `UseManufac`='".$this->UseManufac."',
                `manufacId`='".$this->manufacId."',
                `UseCateg`='".$this->UseCateg."',
                `CategId`='".$this->CategId."',
                `skidka`='".$this->skidka."',
                `Active`='".$this->Active."',
                `ctrlscript`='".$this->ctrlscript."'";
            if($this->is_special_pos==1){
                $q=$q.", `special_pos`='".$this->special_pos."'";
            }
            if($this->is_main_page==1){
                $q=$q.", `main_page`='".$this->main_page."'";
            }                
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
            if( !$res OR !$this->Right->result ) return false;
        }

        if ( empty($this->id) ) $this->id = $this->Right->db_GetInsertID();

        
        //--- Del old image Image START ---
        //print_r($this->img);
        if( isset($this->img) AND is_array($this->img)){
            $q = "SELECT `lang_id`, `img_filename` FROM `".TblModShareTxt."` WHERE `cod`='".$this->id."'";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            if( !$res OR !$this->Right->result ) return false;
            $rows = $this->Right->db_GetNumRows();
            for($i=0;$i<$rows;$i++){
                $row = $this->Right->db_FetchAssoc();
                $img_arr[$row['lang_id']] = $row['img_filename'];
            }
            foreach($this->img as $key=>$value){
                //$img = $this->Spr->GetImageByCodOnLang(TblModPagesSprName, $this->id, $key, 0);
                //echo '<br>$value='.$value.' $img='.$img;
                if ( !empty($value) AND isset($img_arr[$key]) AND $value!=$img_arr[$key]) {
                     //echo '<br>$img='.$img; 
                    $this->DelItemImage($img_arr[$key]);
                }         
            }
        }
        //--- Del old image Image END ---
        
        if ( $this->is_tags==1 ) {
            $Tags = new SystemTags();
            $res=$Tags->SaveTagsById( $this->module, $this->id, $this->id_tag );
            if( !$res ) return false;
        }
        
        //--- save content of the page START ---
        $keys = array_keys($this->ln_arr);
        $cnt = count($keys);
        for($i=0;$i<$cnt;$i++){
            $lang_id = $keys[$i];
            $name = $this->Form->GetRequestTxtData($this->descr[ $lang_id ], 1);
            $short = $this->Form->GetRequestTxtData($this->short[ $lang_id ], 0);
            $content = $this->Form->GetRequestTxtData($this->body[ $lang_id ], 0);
            $mtitle = $this->Form->GetRequestTxtData($this->title[ $lang_id ], 1);
            $mdescr = $this->Form->GetRequestTxtData($this->description[ $lang_id ], 1);
            $mkeywords = $this->Form->GetRequestTxtData($this->keywords[ $lang_id ], 1);
            if( isset($this->img[$lang_id]) ) $img_filename = $this->img[$lang_id];
            else $img_filename='';
            
            $q = "SELECT `".TblModShareTxt."`.`pname`
                  FROM `".TblModShareTxt."` 
                  WHERE `".TblModShareTxt."`.`cod`='".$this->id."' AND `lang_id`='".$lang_id."'";
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
            if( !$res OR !$this->Right->result ) return false;
            $rows = $this->Right->db_GetNumRows();
                        
            if($rows>0){
                $q = "UPDATE `".TblModShareTxt."` SET
                      `pname`='".$name."',
                      `short`='".$short."',
                      `content`='".$content."',
                      `mtitle`='".$mtitle."',
                      `mdescr`='".$mdescr."',
                      `mkeywords`='".$mkeywords."'";
                if( !empty($img_filename)) $q .= ", `img_filename`='".$img_filename."'";
                $q .= " WHERE `cod`='".$this->id."' AND `lang_id`='".$lang_id."'";
            }
            else{
                $q = "INSERT INTO `".TblModShareTxt."` SET
                      `cod`='".$this->id."',
                      `lang_id`='".$lang_id."',
                      `pname`='".$name."',
                      `short`='".$short."',
                      `content`='".$content."',
                      `mtitle`='".$mtitle."',
                      `mdescr`='".$mdescr."',
                      `mkeywords`='".$mkeywords."'
                     ";
                if(!empty($img_filename)) $q .= ", `img_filename`='".$img_filename."'";  
            }
            $res = $this->Right->Query( $q, $this->user_id, $this->module );
            //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
            if( !$res OR !$this->Right->result ) return false;
        }
        //--- save content of the page END ---
        
        //$uploaddir = Pages_Img_Path_Small;
        //$Uploads = new Uploads( $this->user_id , $this->module , $uploaddir, 200, $this->module );
        //$Uploads->saveCurentImages($this->id, $this->module);
        
        return true;
    } // end of function save()


    // ================================================================================================
    // Function : delPages()
    // Date : 23.12.2009
    // Parms :   $user_id, $module_id, $id_del
    // Returns : true,false / Void
    // Description :  Remove data from the table
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function delShares( $id_del )
    {
	
	
        $ln_sys = new SysLang();
        $Right = new Rights($this->user_id, $this->module);
        $del = 0;
        $kol = count( $id_del );
        for( $i=0; $i<$kol; $i++ ){
            $u=$id_del[$i];

            //--- select sublevels of curent category ---
            $q="SELECT * FROM ".TblModShare." WHERE `level`='".$u."'";
            $res = $Right->Query( $q, $this->user_id, $this->module );
            $rowsaaa = $Right->db_GetNumRows();
            for( $i_ = 0; $i_ < $rowsaaa; $i_++ ){
                $row = $Right->db_FetchAssoc();
                $id_del_l[$i_] = $row['id'];
            }
            //--- delete sublevels ---
	    $q="SELECT * FROM `".TblModShare."` WHERE `id`='".$u."'";
	    $res = $this->Right->Query( $q, $this->user_id, $this->module );
	    if( !$res OR !$this->Right->result ) return false;
	    $rows = $this->Right->db_GetNumRows();
	    $oldShare = $this->Right->db_FetchAssoc();
	    $this->setOldPrice($oldShare,$rows);
	    $this->setOldPriceCateg($oldShare,$rows);
	    $this->setOldPriceManuf($oldShare,$rows);
            if( $rowsaaa>0 )$this->delShares( $id_del_l );

            /*
            //--- delete images for current page ---
            $this->ln_arr = $ln_sys->LangArray( _LANG_ID );
            foreach($ln_arr as $key=>$value){
                $q="SELECT * FROM ".TblModShare." WHERE `level`='".$u."'";
                $res = $Right->Query( $q, $this->user_id, $this->module );
                $img = $this->Spr->GetImageByCodOnLang(TblModPagesSprName, $u, $key, 0);
                $this->DelItemImage($img);
                if( !$res ) return false;                
            }
            */            

            //---delete tags for current page ---
            if( $this->is_tags==1){
                $Tags = new SystemTags();
                $res = $Tags->DelTagsByModuleItem($this->module, $u);
                if( !$res ) return false;
            }            
            
            //--- delete current page ---
            $q = "DELETE 
                  FROM `".TblModShare."`, `".TblModShareTxt."`
                  USING  `".TblModShare."` INNER JOIN `".TblModShareTxt."`
                  WHERE `".TblModShare."`.`id`='".$u."'
                  AND `".TblModShare."`.`id`=`".TblModShareTxt."`.cod";
            $res = $Right->Query( $q, $this->user_id, $this->module );
            if( !$res ) return false;

            $this->UploadFile->DeleteAllFilesForPosition($u);
            $this->UploadImages->DeleteAllImagesForPosition($u);
            //$this->UploadVideo->DeleteAllFilesForPosition($u);
            
            if ( $res ) $del=$del+1;
            else return false;
        }
        return $del;
    } //end of function delPages()

    // ================================================================================================
    // Function : GetNumSublevels()
    // Date : 17.08.2006
    // Parms :   $user_id, $module_id, $id_del
    // Returns : true,false / Void
    // Description :  Remove data from the table
    // Programmer : Dmitriy Kerest
    // ================================================================================================
    function GetNumSublevels($level)
    {
        $db = new DB();
        $q = "select * from `".TblModShare."` where 1 and `level`='".$level."'";
        $res = $db->db_Query($q);
        $rows = $db->db_GetNumRows($res);
        if($rows==0){return NULL;}
        else{return " ( ".$rows." )";}
    } //end of fucntion GetNumSublevels

    // ================================================================================================
    // Function : CheckFields()
    // Date : 19.02.2008
    // Returns :      true,false / Void
    // Description :  Checking all fields for filling and validation
    // Programmer :  Igor Trokhymchuk
    // ================================================================================================
    function CheckFields()
    {
        $this->Err=NULL;

        if (empty( $this->descr[$this->lang_id] )) $this->Err .= $this->multi['MSG_FLD_NAME_EMPTY'].'<br>';

        $db = new DB();
        $q = "SELECT * FROM `".TblModShare."` WHERE `name`='".$this->name."' AND `level`='".$this->level."'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $row = $db->db_FetchAssoc();
        if( !empty($row['id']) AND $this->id!=$row['id']){
            $this->Err .= $this->multi['MSG_FLD_FILENAME_ALREADY_EXIST1'].' "'.$this->name.'" '.$this->multi['MSG_FLD_FILENAME_ALREADY_EXIST2'].'<br>';
        }
        if( !empty($this->Err)) $this->action = 'return';
        
        //echo '<br>$this->id='.$this->id.' $row[id]='.$row['id'];
        if( !empty($row['id']) AND $this->id!=$row['id'] AND $row['visible']==$this->visible AND $row['level']==$this->level AND $row['publish']==$this->publish AND $row['ctrlscript']==$this->ctrlscript AND $row['special_pos']==$this->special_pos AND $row['main_page']==$this->main_page){
            $this->Err = $this->multi['MSG_FLD_PAGE_ALREADY_EXIST1'].'<u>'.$this->descr[$this->lang_id].'</u>'.$this->multi['MSG_FLD_PAGE_ALREADY_EXIST2'];
            $this->action = '';
        }
        //echo '<br>$this->Err='.$this->Err.' $this->action='.$this->action;
        return $this->Err;
    } //end of fuinction CheckFields()

       // ================================================================================================
       // Function : ShowErrBackEnd()
       // Date : 10.01.2006
       // Returns :      true,false / Void
       // Description :  Show errors
       // Programmer :  Igor Trokhymchuk
       // ================================================================================================
       function ShowErrBackEnd()
       {
         if ($this->Err){
           echo '
            <table border=0 cellspacing=0 cellpadding=0 class="err" align="center">
             <tr><td align="left">'.$this->Err.'</td></tr>
            </table>';
         }
       } //end of fuinction ShowErrBackEnd()

        // ================================================================================================
        // Function : SavePicture
        // Date : 03.04.2006
        // Returns : $res / Void
        // Description : Save the file (image) to the folder  and save path in the database (table user_images)
        // Programmer : Igor Trokhymchuk
        // ================================================================================================
        function SavePicture()
        {
         $ln_sys = new SysLang();    
         
         $this->Err = NULL;
         $max_image_width = SPR_MAX_IMAGE_WIDTH;
         $max_image_height = SPR_MAX_IMAGE_HEIGHT;
         $max_image_size = SPR_MAX_IMAGE_SIZE;
         $valid_types =  array("gif", "GIF", "jpg", "JPG", "png", "PNG", "jpeg", "JPEG");
         //print_r($_FILES["image"]);
         if( !isset($_FILES["image_icon"]) ) return true;
         $varFiles = $_FILES["image_icon"];
         if ( empty($this->ln_arr) ) $this->ln_arr[1]='';
         while( $el = each( $this->ln_arr ) )
         {         
             $lang_id = $el['key'];
             
             //echo '<br>$_FILES["image"]='.$_FILES["image"].' $_FILES["image"]["tmp_name"]["'.$lang_id.'"]='.$_FILES["image"]["tmp_name"]["$lang_id"].' $_FILES["image"]["size"]["'.$lang_id.'"]='.$_FILES["image"]["size"]["$lang_id"];
             //echo '<br>$_FILES["image"]["name"][$lang_id]='.$_FILES["image"]["name"][$lang_id];
             //$this->img[$lang_id] = $_FILES["image"]["name"][$lang_id]; 
             if ( !empty($varFiles["name"][$lang_id]) ) {
               if ( isset($varFiles) && is_uploaded_file($varFiles["tmp_name"][$lang_id]) && $varFiles["size"][$lang_id] ){
                $filename = $varFiles['tmp_name'][$lang_id];
                $ext = substr($varFiles['name'][$lang_id],1 + strrpos($varFiles['name'][$lang_id], "."));
                //echo '<br>filesize($filename)='.filesize($filename).' $max_image_size='.$max_image_size;
                if (filesize($filename) > $max_image_size) {
                    $this->Err = $this->Err.$this->multi['MSG_ERR_FILE_SIZE'].' ('.$varFiles['name']["$lang_id"].')<br>';
                    continue;
                }
                if (!in_array($ext, $valid_types)) {
                    $this->Err = $this->Err.$this->multi['MSG_ERR_FILE_TYPE'].' ('.$varFiles['name']["$lang_id"].')<br>';  
                }
                else {
                  $size = GetImageSize($filename);
                  //echo '<br>$size='.$size.'$size[0]='.$size[0].' $max_image_width='.$max_image_width.' $size[1]='.$size[1].' $max_image_height='.$max_image_height;
                  //if (($size) && ($size[0] < $max_image_width) && ($size[1] < $max_image_height)) {
                     //$uploaddir0 = Pages_Img_Path;
                     //if ( !file_exists ($uploaddir0) ) mkdir($uploaddir0,0777);
                     $uploaddir1 = Pages_Img_Path;
                     if ( !file_exists ($uploaddir1) ) mkdir($uploaddir1,0777); 
                     $uploaddir2 = $uploaddir1.$lang_id;
                     if ( !file_exists ($uploaddir2) ) mkdir($uploaddir2,0777);
                     else @chmod($uploaddir2,0777);
                     
                     //$this->img[$lang_id] = $_FILES['image']['name'][$lang_id];
                     // Формирую новое название файла, которе будет храниться на сервере
                     //$this->img[$lang_id] = time().'_'.$lang_id.'.'.$ext; 
                     $this->img[$lang_id] = $varFiles["name"][$lang_id];
                     //echo '<br>$this->img['.$lang_id.']='.$this->img[$lang_id]; 
                     $uploaddir = $uploaddir2."/".$this->img[$lang_id];
                     //echo '<br>$filename='.$filename.'<br> $uploaddir='.$uploaddir.'<br> $uploaddir2='.$uploaddir2;
                     //if (@move_uploaded_file($filename, $uploaddir)) {
                     if ( copy($filename,$uploaddir) ) {
                         if (($size) AND (($size[0] > $max_image_width) OR ($size[1] > $max_image_height)) ){
                             //============= resize original image to size from settings =============
                             $thumb = new Thumbnail($uploaddir);
                             if($max_image_width==$max_image_height) $thumb->size_auto($max_image_width);
                             else{ 
                                $thumb->size_width($max_image_width);
                                $thumb->size_height($max_image_height);
                             }
                             $thumb->quality = 85;
                             $thumb->process();       // generate image
                             $thumb->save($uploaddir); //make new image
                             //=======================================================================
                         }                         
                     }
                     else{    
                         $this->Err = $this->Err.$this->multi['MSG_ERR_FILE_MOVE'].' ('.$varFiles['name']["$lang_id"].')<br>';
                     }
                     @chmod($uploaddir2,0755);
                     @chmod($uploaddir1,0755);
                     //@chmod($uploaddir0,0755);
                  //}
                  //else {
                  //   $this->Err = $this->Err.$this->multi['MSG_ERR_FILE_PROPERTIES'].' ['.$max_image_width.'x'.$max_image_height.'] ('.$_FILES['image']['name']["$lang_id"].')<br>'; 
                  //}
                }
               }
               else $this->Err = $this->Err.$this->multi['MSG_ERR_FILE'].' ('.$varFiles['name']["$lang_id"].')<br>';
             } 
             //echo '<br>$lang_id='.$lang_id;
         } // end while
         return $this->Err;
        }  // end of function SavePicture()
        
       // ================================================================================================
       // Function : DelItemImage
       // Date : 06.11.2006
       // Parms :   $img   / name of the image
       // Returns : true,false / Void
       // Description :  Remove iamge from table and from the disk
       // Programmer : Igor Trokhymchuk
       // ================================================================================================
       function DelItemImage($img)
       {       
           $q = "SELECT * FROM `".TblModShareTxt."` WHERE `img_filename`='".$img."'";
           $res = $this->Right->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
           if( !$this->Right->result ) return false;
           $rows = $this->Right->db_GetNumRows();
           //echo '<br>$rows='.$rows;
           if ($rows == 0) return false;
           $row = $this->Right->db_FetchAssoc(); 
           //echo '<br>$row'; print_r($row);
           
           $path = Pages_Img_Path.$row['lang_id'];
           $path_file = $path.'/'.$row['img_filename'];
           //echo '<br>$path='.$path.'<br>$path_file='.$path_file;
           // delete file which store in the database
           if( is_dir($path) ){
               if (file_exists($path_file)) {
                  $res = unlink ($path_file);
                  //echo '<br>$res='.$res;
                  if( !$res ) return false;
               }
           }
           //echo '<br> $path='.$path;
           $handle = @opendir($path);
           //echo '<br> $handle='.$handle; 
           $cols_files = 0;
           while ( ($file = readdir($handle)) !==false ) {
               //echo '<br> $file='.$file;
               $mas_file=explode(".",$file);
               $mas_img_name=explode(".",$row['img_filename']);
               if ( strstr($mas_file[0], $mas_img_name[0].SPR_ADDITIONAL_FILES_TEXT) and $mas_file[1]==$mas_img_name[1] ) {
                  $res = unlink ($path.'/'.$file);
                  if( !$res ) return false;                    
               }
               if ($file == "." || $file == ".." ) {
                   $cols_files++;
               }
           }
           if ($cols_files==2){
               @chmod($uploaddir2,0777);
               rmdir($path);
           }
           closedir($handle);           

           $q = "UPDATE `".TblModShareTxt."` SET `img_filename`='' WHERE `img_filename`='".$img."'";
           $res = $this->Right->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
           if( !$this->Right->result ) return false;
           
           return true;                        
      } //end of function DelItemImage()         

 }// end of class ShareBackend