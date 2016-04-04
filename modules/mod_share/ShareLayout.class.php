<?php
/**
* pagesLayout.class.php
* class for display interface of Dynamic Front-end Pages
* @package Dynamic Pages Package of SEOCMS
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 05.08.2011
* @copyright (c) 2010+ by SEOTM
*/

include_once( SITE_PATH.'/modules/mod_share/share.defines.php' );

/**
* Class ShareLayout
* class for display interface of Dynamic Front-end Pages.
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 05.08.2011
* @property FrontSpr $Spr
* @property FrontForm $Form 
* @property db $db
* @property CatalogLayout $Catalog
* @property UploadImage $UploadImages
*/ 
class ShareLayout extends Share{
    public $share_id = 0;
    public $module = NULL;
    public $is_tags = NULL;
    public $is_comments = NULL;
    public $main_page = NULL;
    public $mod_rewrite = 1;
    public $Spr = NULL;
    public $Form = NULL;
    public $db = NULL;
    public $UploadImages = NULL;
    public $Catalog = NULL;
    
    public $treeCatList = NULL; //array $this->treeCatList[]=$id_cat
    public $treeShareLevels = NULL; //array $this->treeCatLevels[level][id_cat]=''
    public $treeShareData = NULL; //array treeCatData[id_cat]=array with category data    
    
 
    /**
    * Class Constructor
    * 
    * @param $module - id of the module
    * @return true/false
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 05.04.2011
    */      
    function __construct($module=NULL)
    {
        ( $module   !="" ? $this->module  = $module   : $this->module  = NULL );
        
        if(defined("_LANG_ID")) $this->lang_id = _LANG_ID;
        
        if(empty($this->db)) $this->db = DBs::getInstance();
        if(empty($this->Spr)) $this->Spr = &check_init('FrontSpr', 'FrontSpr');
        if(empty($this->Form)) $this->Form = &check_init('FrontForm', 'FrontForm');
        if(empty($this->Catalog)) $this->Catalog = &check_init('CatalogLayout', 'CatalogLayout');
        
        $this->UploadImages = &check_init('UploadImage', 'UploadImage', "'90', 'null', 'uploads/images/share', 'mod_share_file_img'");
        $this->UploadFile = &check_init('UploadClass', 'UploadClass', '90, null, "uploads/files/share","mod_share_file"');
        //$this->UploadVideo = &check_init('UploadVideo', 'UploadVideo', '90, null, "uploads/video/pages","mod_page_file_video"');

        // for folders links
        if( !isset($this->mod_rewrite) OR empty($this->mod_rewrite) ) $this->mod_rewrite = 1;
        
        ( defined("USE_TAGS")                  ? $this->is_tags = USE_TAGS                     : $this->is_tags=0 ); // использовать тэги
        ( defined("USE_COMMENTS")              ? $this->is_comments = USE_COMMENTS             : $this->is_comments=0 ); // возможность оставлять комментарии
        ( defined("PAGES_USE_SHORT_DESCR")     ? $this->is_short_descr = PAGES_USE_SHORT_DESCR : $this->is_short_descr=0 ); // Краткое оисание страницы
        ( defined("PAGES_USE_SPECIAL_POS")     ? $this->is_special_pos = PAGES_USE_SPECIAL_POS : $this->is_special_pos=0 ); // специальное размещение страницы
        ( defined("PAGES_USE_IMAGE")           ? $this->is_image = PAGES_USE_IMAGE             : $this->is_image=0 ); // изображение к странице
        ( defined("PAGES_USE_IS_MAIN")         ? $this->is_main_page = PAGES_USE_IS_MAIN       : $this->is_main_page=0 ); // главная страница сайта
        
        if(empty ($this->multi)) $this->multi = &check_init_txt('TblFrontMulti', TblFrontMulti);
        
        $this->loadTree();
        //print_r($this->treeCatData);
        //echo '<br />treeCatList=';print_r($this->treeCatList);
        //echo '<br />treeCatLevels=';print_r($this->treeCatLevels);
        //echo '<br />treeCatData=';print_r($this->treeCatData);         
         
     } // end of constructor ShareLayout()  
   
    /**
    * Class method loadTree
    * load all data of catalog categories to arrays
    * @return true/false or arrays:
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.1, 05.04.2011
    */
    function loadTree()
    {
        if( is_array($this->GetTreeCatLevelsAll()) AND is_array($this->GetTreeCatDataAll()) ) return true;
        
//        $q = "SELECT `".TblModShare."`.*, `".TblModShareTxt."`.`pname` FROM `".TblModShare."`, `".TblModShareTxt."` 
//              WHERE `".TblModShare."`.`visible`='1'
//              AND `".TblModShare."`.`level`='0'
//              AND `".TblModShareTxt."`.cod=`".TblModShare."`.id
//              AND `".TblModShareTxt."`.lang_id='".$this->lang_id."'
//              ORDER BY `move` asc";      
        
        $q="SELECT DISTINCT `".TblModShare."`.*,
            `".TblModShare."`.`id` AS `share_id`,
            `".TblModShareTxt."`.*,
            `".TblModShareFileImg."`.`path` AS `imgPath`
           FROM `".TblModShare."`
               LEFT JOIN `".TblModShareTxt."` ON (`".TblModShareTxt."`.cod=`".TblModShare."`.id AND `".TblModShareTxt."`.lang_id='".$this->lang_id."')
               LEFT JOIN `".TblModShareFileImg."` ON (`".TblModShareFileImg."`.`id_position`=`".TblModShare."`.`id`)
           WHERE `".TblModShare."`.`visible`='1'
               ORDER BY `".TblModShare."`.`move` asc";

        $res = $this->db->db_Query($q);
        //echo $q.' <br/>$res = '.$res.' $this->db->result='.$this->db->result;
        if(!$res OR !$this->db->result) return false;
        $rows = $this->db->db_GetNUmRows($res);   
        if($rows==0) 
            return false;
            
        $tree = array();
        $activityArr=array();
        for($i = 0; $i < $rows; $i++){
            $row = $this->db->db_FetchAssoc($res);
            
            $this->SetTreeCatLevel($row['level'], $row['share_id']);
            $this->treeShareData[$row['share_id']]=$row;
            
            if($row['UseEndDate']=='1'){
                $timestampEnd = $this->getTimeStamp($row['ShareEnd']);
                $timestampBegin = $this->getTimeStamp($row['ShareBegin']);
                $timestampNow=(mktime(date("H"),date("i"),0,date("m"),date("d"),date('Y')));
                $difference = floor(($timestampEnd - $timestampBegin)/86400);


                if($timestampEnd>=$timestampNow && $timestampBegin<=$timestampNow) $this->treeShareData[$row['share_id']]['shareActivity']='active';
                elseif($timestampBegin>$timestampNow) $this->treeShareData[$row['share_id']]['shareActivity']='notBegin';
                elseif($timestampEnd<$timestampNow) $this->treeShareData[$row['share_id']]['shareActivity']='notActive';
            }else{
                $this->treeShareData[$row['share_id']]['shareActivity']='active';
            }
//            print_r($this->treeShareData);
            if($row['Active']==1 && $this->treeShareData[$row['share_id']]['shareActivity']=='notActive' && $row['UseEndDate']=='1'){
                $activityArr[]=$row['id'];
            }
            
            //$this->SetTreeCatData($row);
            //$this->treeCatData[$row['id']]=$row;
        }
        if(count($activityArr)>0){
                for ($i = 0; $i < count($activityArr); $i++) {
                    $q="UPDATE `".TblModShare."` SET `Active`='0' WHERE `id`='".$activityArr[$i]."'";
                    $res = $this->db->db_Query($q);
                    $catQ='';
                    if(!empty($this->treeShareData[$activityArr[$i]]['CategId']) && $this->treeShareData[$activityArr[$i]]['CategId']>0 && $this->treeShareData[$activityArr[$i]]['UseCateg']=='1'){
                        $q="UPDATE 
                            `".TblModCatalogProp."`,
                            `".TblModCatalogPropSizes."` 
                            SET  `".TblModCatalogProp."`.`price`=`".TblModCatalogProp."`.`old_price`,
                            `".TblModCatalogPropSizes."`.`price`=`".TblModCatalogPropSizes."`.`old_price`
                            WHERE `".TblModCatalogProp."`.`id_cat`='".$this->treeShareData[$activityArr[$i]]['CategId']."' 
                                AND `".TblModCatalogProp."`.`setPriceManually`='0' 
                                AND `".TblModCatalogProp."`.`share`='0' 
                                AND `".TblModCatalogProp."`.`id`=`".TblModCatalogPropSizes."`.`id_prop`";
                        $catQ=" AND `".TblModCatalogProp."`.`id_cat` NOT LIKE '".$this->treeShareData[$activityArr[$i]]['CategId']."'";
                        $res = $this->db->db_Query($q);
                    }
                    if(!empty($this->treeShareData[$activityArr[$i]]['manufacId']) && $this->treeShareData[$activityArr[$i]]['manufacId']>0 && $this->treeShareData[$activityArr[$i]]['UseManufac']=='1'){
                        $q="UPDATE 
                            `".TblModCatalogProp."`,
                            `".TblModCatalogPropSizes."` 
                            SET  
                            `".TblModCatalogProp."`.`price`=`".TblModCatalogProp."`.`old_price`,
                            `".TblModCatalogPropSizes."`.`price`=`".TblModCatalogPropSizes."`.`old_price` 
                            WHERE `".TblModCatalogProp."`.`id_manufac`='".$this->treeShareData[$activityArr[$i]]['manufacId']."' 
                                AND `".TblModCatalogProp."`.`setPriceManually`='0' 
                                AND `".TblModCatalogProp."`.`share`='0'
                                AND `".TblModCatalogProp."`.`id`=`".TblModCatalogPropSizes."`.`id_prop`
                                    ".$catQ."";
                        $res = $this->db->db_Query($q);
                    }
                    $q="UPDATE 
                        `".TblModCatalogProp."`,
                        `".TblModCatalogPropSizes."` 
                        SET  
                        `".TblModCatalogProp."`.`price`=`".TblModCatalogProp."`.`old_price`,
                        `".TblModCatalogPropSizes."`.`price`=`".TblModCatalogPropSizes."`.`old_price` 
                            WHERE `".TblModCatalogProp."`.`setPriceManually`='0' 
                            AND `".TblModCatalogProp."`.`share`='1' 
                            AND `".TblModCatalogProp."`.`share_id`='".$activityArr[$i]."'
                            AND `".TblModCatalogProp."`.`id`=`".TblModCatalogPropSizes."`.`id_prop`";
                    $res = $this->db->db_Query($q);
                }
        }
        //build category translit path for all categories and subcategories
        //exit();
        //print_r($this->treeShareData);
        $this->makeCatPath();
        return true;
    } //end of function loadTree()
    
    /**
    * Class method SetTreeCatLevel
    * set new vlaue to property $this->treeCatLevels. It build array $this->treeCatLevels[level][id_cat]='' 
    * @param integer $level - id of the parent category
    * @param integer $id - id of the category
    * @return none
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 17.05.2011
    */
    function SetTreeCatLevel($level, $id)
    {
        $this->treeShareLevels[$level][$id]='';
    } //end of function SetTreeCatLevel()
    
    /**
    * Class method GetTreeCatLevelsAll
    * get array $this->treeCatLevels
    * @return array $this->treeCatLevels
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 17.05.2011
    */
    function GetTreeCatLevelsAll()
    {
        return $this->treeShareLevels;
    } //end of function GetTreeCatLevelsAll()
    
    /**
    * Class method GetTreeCatLevel
    * get node of array $this->treeCatLevels where store array with sublevels
    * @param integer $item - id of the category as node in array
    * @return node of array $this->treeCatLevels[$item]
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 17.05.2011
    */
    function GetTreeCatLevel($item=0)
    {
        if(!isset($this->treeShareLevels[$item])) return false;
        return $this->treeShareLevels[$item];
    } //end of function GetTreeCatLevel()  
    
    /**
    * Class method SetTreeCatData
    * set new vlaue to property $this->treeCatData. It build array $this->treeCatData[id_cat]=array with category data 
    * @param array $row - assoc array with data of category
    * @return true
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 17.05.2011
    */
//    function SetTreeCatData($row)
//    {
//        $this->treeShareData[$row['share_id']]=$row;
//        return true;
//    } //end of function SetTreeCatData()

    /**
    * Class method SetTreeCatDataAddNew
    * set new vlaue to property $this->treeCatData. It build array $this->treeCatData[id_cat]=array with category data 
    * @param integer $id_cat - id of the category
    * @param varchar $key - name of new key
    * @param varchar $val - value for key $key 
    * @return true
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 17.05.2011
    */
    function SetTreeCatDataAddNew($id_cat, $key, $val)
    {
        $this->treeShareData[$id_cat][$key]=$val;
        return true;
    } //end of function SetTreeCatDataAddNew()

    /**
    * Class method GetTreeCatDataAll
    * get array $this->treeCatData
    * @return array $this->treeCatData
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 17.05.2011
    */
    function GetTreeCatDataAll()
    {
        return $this->treeShareData;
    } //end of function GetTreeCatDataAll()  
    
    /**
    * Class method GetTreeCatData
    * get node of array $this->treeCatData where store array with data about category
    * @param integer $item - id of the category as node in array
    * @return node of array $this->treeCatData[$item]
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 17.05.2011
    */
    function GetTreeCatData($item)
    {
        if(!isset($this->treeShareData[$item])) return false;
        return $this->treeShareData[$item];
    } //end of function GetTreeCatData()  
    
    /**
    * Class method SetTreeCatList
    * set new vlaue to property $this->treeCatList. It build array $this->treeCatList[counter]=id of the category 
    * @param integer $counter - counter for array
    * @param integer $id_cat - id of the category
    * @return true
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 17.05.2011
    */
    function SetTreeCatList($counter, $id_cat)
    {
        $this->treeCatList[$counter] = $id_cat;
        return true;
    } //end of function SetTreeCatList() 
    
    /**
    * Class method GetTreeListAll
    * get array $this->treeCatList
    * @return array $this->treeCatList
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 17.05.2011
    */
    function GetTreeListAll()
    {
        return $this->treeCatList;
    } //end of function GetTreeListAll()                     
    
    /**
    * Class method makeCatPath
    * build relative url to category using category translit for all categories and subcategories 
    * @param integer $level - id of the category
    * @param string $path - path to category
    * @return true/false
    * @author Igor Trokhymchuk  <ihor@seotm.com>
    * @version 1.0, 14.01.2011
    */
    function makeCatPath($level = 0, $path = NULL)
    {
        if( !$this->GetTreeCatLevel($level) ) return;
        $n = count($this->GetTreeCatLevel($level));
        $keys = array_keys($this->GetTreeCatLevel($level));
        for($i = 0; $i < $n; $i++) {
            //echo '<br />$keys[$i]='.$keys[$i];
            $row = $this->GetTreeCatData($keys[$i]);
            if(!$path) $full_path = '/'.$row['name'];
            else $full_path  = $path.$row['name'];
            //$this->treeCatData[$keys[$i]]['path'] = $full_path;
            $this->SetTreeCatDataAddNew($keys[$i], 'path', $full_path);
            //$this->treeCatList[]=$row['id'];
            $this->SetTreeCatList($i, $row['id']);
            $this->makeCatPath($row['id'], $full_path);
        }
    }//end of function makeCatPath()       
     

    // ================================================================================================
    // Function : ShowHorizontalMenu_old()
    // Date : 31.03.2007
    // Returns : true,false / Void
    // Description :  show main menu from Dynamic pages (where level=0)
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function ShowHorizontalMenu_old($layout='hor')
    {     
        $db = DBs::getInstance();
        $q = "SELECT `".TblModShare."`.*, `".TblModShareTxt."`.`pname` FROM `".TblModShare."`, `".TblModShareTxt."` 
              WHERE `".TblModShare."`.`visible`='1'
              AND `".TblModShareTxt."`.cod=`".TblModShare."`.id
              AND `".TblModShareTxt."`.lang_id='".$this->lang_id."'
              ORDER BY `move` asc";
        $res = $db->db_Query($q);
        //echo '<br>$q='.$q.' $res='.$res; 
        $rows = $db->db_GetNumRows($res);
        ?>
        <div id="mainNavBox">
            <table cellpadding="0" cellspacing="0">
                <tbody>
                    <tr>
                    <?
                    for($i=0; $i<$rows; $i++)
                    {                                                                          
                        $row = $db->db_FetchAssoc($res);
                        if( !empty($row['pname']) ){
                            if ($this->MainPage()==$row['id']) $href=_LINK;
                            else $href = $this->Link($row['id']);
                            ?>  
                            <td>
                            <a href="<?=$href;?>" class="main_menu"><?=stripslashes($row['pname']);?></a>
                            <?
                            if( $this->IsSubLevels($row['id'], 'front')){
                                $this->ShowSubLevels1($row['id']);
                            }
                            ?>
                            </td>
                            <?
                        }
                    }// end for
                    ?>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <?
    }//end of function ShowHorizontalMenu_old()
    
     // ================================================================================================
     // Function : ShowSubLevels1()
     // Date : 09.02.2008
     // Parms :   $user_id, $module_id, $id_del
     // Returns : true,false / Void
     // Description : show sublevels of the share $level
     // Programmer : Ihor Trokhymchuk
     // ================================================================================================
    function ShowSubLevels1($level)
    {
        $db = DBs::getInstance();
        $q  = "SELECT `".TblModShare."`.*, `".TblModShareTxt."`.`pname` FROM `".TblModShare."`, `".TblModShareTxt."`
               WHERE `".TblModShare."`.`level`='".$level."'
               AND `".TblModShare."`.`visible`='1'
               AND `".TblModShareTxt."`.cod=`".TblModShare."`.id
               AND `".TblModShareTxt."`.lang_id='".$this->lang_id."'
               ORDER BY `move`";
        $res = $db->db_Query($q);
        //echo '<br>$q='.$q.' $res='.$res;
        $rows = $db->db_GetNumRows($res);
        if(!$rows)
        //echo $rows;
        ?>
        <div class="subNav">
         <ul>
         <?
         for($i=0; $i<$rows; $i++)
         {
            $row = $db->db_FetchAssoc();
            if( !empty($row['pname']) ){
                $href = $this->Link($row['id']);
                ?><li><a href="<?=$href;?>"><?=stripslashes($row['pname']);?></a></li><?
            }
         }
         ?>            
         </ul>
        </div>                                         
        <?
    }// end of function ShowSubLevels1()         


    /**
     * ShareLayout::LinkForMenu()
     * 
     * @param mixed $id
     * @param mixed $array_links
     * @return
     */
    function LinkForMenu($id,$array_links)
    {
         $str='';
         if($array_links[$id]['ctrlscript']==0){ 
            $str=$array_links[$id]['link'];
            return $str;
         }
         $currend_id=$id;
         while(1)
         {
             $str=$array_links[$currend_id]['link'].$str;
             $currend_id=$array_links[$currend_id]['level'];
             if($currend_id==0) break;
         }
         return _LINK.$str;
    }
    
   /**
     * ShareLayout::ShowHorizontalMenu()
     *
     * @author Yaroslav Gyryn 21.10.2011 
     * @param integer $level
     * @return void
     */
    function ShowHorizontalMenu($level = 0)
    {     
        $db = DBs::getInstance();
        $this->main_page = $this->MainPage();        
        $q2 = "SELECT `".TblModShare."`.name as link, `".TblModShare."`.ctrlscript,`".TblModShare."`.move,`".TblModShare."`.id,`".TblModShare."`.level, `".TblModShareTxt."`.pname 
               FROM `".TblModShare."`,`".TblModShareTxt."`
               WHERE `".TblModShare."`.visible='1'
               AND `".TblModShareTxt."`.lang_id='".$this->lang_id."'
               AND `".TblModShareTxt."`.cod= `".TblModShare."`.id 
               ORDER BY `level`, `move`";
        
        $res = $db->db_Query($q2);
        $rows = $db->db_GetNumRows($res);
        $this->menuArray = array();
        $this->levelCount = array();
        $this->menuLinks= array();
        $fr_level=0;
        $counter=0;
        for( $i = 0; $i < $rows; $i++ )
         {
          $row = $db->db_FetchAssoc( $res );
          if ($row['level']!=$fr_level)
          {
              $this->levelCount[$fr_level] = $counter;
              $fr_level = $row['level'];
              $counter = 0; 
          }
          
          $this->menuArray[$row['level']][$counter]['id']=$row['id'];
          $this->menuArray[$row['level']][$counter]['pname']=$row['pname'];
          $this->menuArray[$row['level']][$counter]['move']=$row['move'];
          $this->menuLinks[$row['id']]['link']=$row['link'];
          $this->menuLinks[$row['id']]['level']=$row['level'];
          $this->menuLinks[$row['id']]['ctrlscript']=$row['ctrlscript'];
          
          if($i==($rows-1))
             $this->levelCount[$row['level']]=$counter+1;
          $counter++;
         }
        ?>
        <div class="menu">
          <table cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr align="center">
                    <?
                    $j = 0;
                    //echo '$this->levelCount[0] ='.$this->levelCount[1];
                    for( $i = 0; $i < $this->levelCount[$level]; $i++ )
                    {
                        if ($this->main_page == $this->menuArray[$level][$i]['id']) 
                            $href=_LINK;
                        else 
                            $href = $this->LinkForMenu($this->menuArray[$level][$i]['id'],$this->menuLinks, $level);
                        ?><td<?
                            /*if($this->GetTopMainLevel($this->share)==$this->menuArray[0][$i]['id']) {
                                echo ' class="current';
                                if($i==($this->levelCount[0]-1)) 
                                    echo ' last"'; 
                                else 
                                    echo '"';
                            } 
                            else {
                                if($i==($this->levelCount[0]-1)) 
                                    echo ' class="last"';
                            }
                            //$this->GetTopMainLevel($this->share);
                            */
                            ?>>
                            <a <?if($this->share_id == $this->menuArray[$level][$i]['id']) 
                                echo ' class="current';?> href="<?=$href;?>"<?
                            if (isset($this->menuArray[$this->menuArray[$level][$i]['id']])){
                                ?> rel="ddsubmenu<?=$this->menuArray[$level][$i]['id']?>"<?
                            }?>><?=$this->menuArray[$level][$i]['pname']?>
                            </a>
                        </td><?
                    }// end for
                    ?>
                </tr>
            </tbody>
          </table>
        </div>
         <?
    }//end of function ShowHorizontalMenu()
    

    
    // ================================================================================================
    // Function : ShowSubLevelsInList()
    // Date : 09.02.2008
    // Parms :   $user_id, $module_id, $id_del
    // Returns : true,false / Void
    // Description : show sublevels of the share $level
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function ShowSubLevelsInList($key,$value,$menu_array,$links,$list=true)
    {
        if($value!=0)
        {if ($list) echo '<ul>';
         for($i=0; $i<$value; $i++)
         {
            //$href = $this->Link($menu_array[$key][$i]['id'] );
            $href = $this->LinkForMenu($menu_array[$key][$i]['id'],$links); 
            ?><li><a href="<?=$href;?>" class="sub_levels"><?=$menu_array[$key][$i]['pname']?></a><?
            if (isset($menu_array[$menu_array[$key][$i]['id']]))
             $this->ShowSubLevelsInList($menu_array[$key][$i]['id'], $menu_array[$key][$i]['pname'], $menu_array, $links);?></li><?
         }
         if ($list) echo '</ul>';
        } 
    }// end of function ShowSubLevelsInList()
 

    // ================================================================================================
    // Function : ShowVerticalMenu()
    // Date : 21.02.2008
    // Returns : true,false / Void
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function ShowVerticalMenu($level=0, $cnt_sublevels=99, $cnt=0)
    {
        
        $q  = "SELECT `".TblModShare."`.*, `".TblModShareTxt."`.`pname` FROM `".TblModShare."`, `".TblModShareTxt."`
               WHERE `".TblModShare."`.`level`='".$level."'
               AND `".TblModShare."`.`visible`='1'
               AND `".TblModShareTxt."`.cod=`".TblModShare."`.id
               AND `".TblModShareTxt."`.lang_id='".$this->lang_id."'
               ORDER BY `".TblModShare."`.`move` asc";
        $res = $this->db->db_Query($q);
        if(!$res OR !$db->result) return false;
        $rows = $this->db->db_GetNUmRows($res);
        for($i=0;$i<$rows;$i++){
            $arr_data[$i] = $this->db->db_FetchAssoc($res);
        }
        //echo '<br>$q='.$q.' $res='.$res.' $rows='.$rows;
        if($rows==0) return false;
        ?><ul><?
        for($i=0;$i<$rows;$i++){
            $row = $arr_data[$i];
            if ($this->GetMainPage()==$row['id']) $href="/";
            else $href = $this->Link($row['id']);
            if($this->share_id==$row['id']){$s="item";}
            else{$s="general";}
            $name = stripslashes($row['pname']);
            //echo '<br>$name='.$name.' $row[id]='.$row['id'];
            ?><li><?
            ?><a href="<?=$href;?>" class="<?=$s;?>"><?=$name;?></a><br/><?
            ?></li><?
            //echo '<br>$cnt_sublevels='.$cnt_sublevels.' $cnt='.$cnt;
            if($this->IsSubLevels($row['id'], 'front')){
                $cnt=$cnt+1;
                 
                if($cnt<$cnt_sublevels){
                    ?>
                    <ul>
                     <?$this->ShowVerticalMenu($row['id'], $cnt_sublevels, $cnt);?>
                    </ul>
                    <?
                    $cnt=0;
                }
            }
        }
        ?></ul><?
    }// end of function ShowVerticalMenu()
    
    
    /**
     * ShareLayout::ShowFooterMenu()
     * @author Yaroslav Gyryn 21.10.2011  
     * @return void
     */
    function ShowFooterMenu($level = 0)
    {     
        ?>
        <div id="footerNavBox">
            <ul>
                <?
                 for( $i = 0; $i < $this->levelCount[$level]; $i++ )
                    {
                        if ($this->main_page == $this->menuArray[$level][$i]['id']) 
                            $href=_LINK;
                        else 
                            $href = $this->LinkForMenu($this->menuArray[$level][$i]['id'],$this->menuLinks, $level);
                        ?>
                            <li><a <?
                            if($this->share_id == $this->menuArray[$level][$i]['id']) 
                            {
                                echo ' class="current"';
                            }
                            ?> href="<?=$href;?>"><?=$this->menuArray[$level][$i]['pname']?></a>
                            </li>
                        <?
                    }// end for
                ?>                            
            </ul>
        </div>
        <?
    }//end of function ShowFooterMenu()    
      
    // ================================================================================================
    // Function : showContent()
    // Date : 18.08.2006
    // Returns : true,false / Void
    // Description :  show content of the dynamic share
    // Programmer : Ihor Trohymchuk 
    // ================================================================================================
    function showContent()
    {
	$q="SELECT * FROM `sys_spr_mounth` WHERE `lang_id`='".$this->lang_id."'";
         $res=$this->db->db_Query($q);
         $rows=$this->db->db_GetNumRows();
         $this->month=array();
         for ($j = 1; $j < $rows+1; $j++) {
             $row=$this->db->db_FetchAssoc();
             $this->month[$j]=$row['name'];
         }
        $imgFull="/uploads/images/share/".$this->share_id."/".$this->share_txt['img'];
        $body = stripslashes($this->share_txt['content']);
        $img=$this->Catalog->ShowCurrentImageExSize($imgFull, 330, 116, true, true, 85, NULL, NULL, NULL,true);
        $path = $this->ShowPath($this->share_id);
        $path.="<span class='spanShareName'>".stripslashes($this->share_txt['pname'])."</span>";
//        $this->Form->WriteContentHeader(false, false,$path);
        $date_timeArr=explode(' ', $this->share_txt['ShareBegin']);
        $timeArr=explode(':', $date_timeArr[1]);
        $dateArr=explode('-', $date_timeArr[0]);
        $day=$dateArr[2];
	$month_=(int)$dateArr[1];
	if($month_!=0)
	    $month_=$this->month[$month_];
	$year=$dateArr[0];
	$data=$day." ".$month_." ".$year;
        ?>
	    <div class="path floatToLeft width100Procentov">
		<?=$path?>
	    </div>
	    <h1 class="floatToLeft width100Procentov RelatPropHeader basketHeader">Акции</h1>
	  <div class="floatToLeft shareBox marginTop15 width100Procentov">
            <div class="floatToLeft ShareInnerDescrBox" style="background: url(<?=$img?>) no-repeat left center">
                <?
             
                    ?>
		<span class="ShareDescrName floatToLeft "><?=$this->share_txt['pname']?>&nbsp;/&nbsp;</span><?=$data?>
                    <span id="counter<?=$this->share_id?>" class="floatToLeft timerSingleShareBox"></span>
                   
                    <div class="userText width100Procentov floatToLeft">
                         <?=$body?>
                    </div>
                    <?
                ?>
                
            </div>
            <div class="floatToLeft propByPagesShareBox marginTop20">
            <?
            $this->Catalog->page=$this->page;
            $this->Catalog->display=$this->display;
            $this->Catalog->start=$this->start;
            $this->Catalog->id_cat=0;
            $this->Catalog->rowsFromShare=count($this->Catalog->GetListPositionsSortByDate($this->Catalog->id_cat,'nolimit',NULL,'asc',true,NULL,false,false,false,$this->share_id));
            $this->Catalog->shareLink=$this->Link($this->share_id);
            $this->Catalog->ShowListOfContentByPages($this->Catalog->GetListPositionsSortByDate($this->Catalog->id_cat,'limit',NULL,'asc',true,NULL,false,false,false,$this->share_id));
            ?>
            </div>
         </div>
        <?         
//        $this->Form->WriteContentFooter();
    }// end of function showContent

     // ================================================================================================
     // Function : ShowSubLevelsInContent()
     // Date : 09.02.2008
     // Parms :   $user_id, $module_id, $id_del
     // Returns : true,false / Void
     // Description : show sublevels of the share $level
     // Programmer : Ihor Trokhymchuk
     // ================================================================================================
    function ShowSubLevelsInContent($level)
    {
	    $db = DBs::getInstance();
	    $q  = "SELECT `".TblModShare."`.`id`, `".TblModShareTxt."`.`pname`
               FROM `".TblModShare."`, `".TblModShareTxt."`
               WHERE `".TblModShare."`.`level`='".$level."' 
               AND `".TblModShare."`.`visible`='1'
               AND `".TblModShare."`.`id`=`".TblModShareTxt."`.`cod`
               AND `".TblModShareTxt."`.`lang_id`='".$this->lang_id."' 
               ORDER BY `".TblModShare."`.`move`";
	    $res = $db->db_Query($q);
	    $rows = $db->db_GetNumRows($res);
	    if(!$rows){
	        for($i=0; $i<$rows; $i++){
		      $arr_data[$i] = $db->db_FetchAssoc();
            }
	    ?>
	    <table border="0">
	     <tr>
	     <?
	     for($i=0; $i<$rows; $i++)
	     {
		    $row = $arr_data[$i];
            $href = $this->Link($row['id']);
		    ?><td>&nbsp;[<a href="<?=$href;?>" class="sub_levels"><?=stripslashes($row['pname']);?></a>]&nbsp;</td><?
	     }
	     ?>
	     </tr>
	    </table>
	    <?
        }
    }// end of function ShowSubLevelsInContent()

    // ================================================================================================
    // Function : ShowPath()
    // Date : 19.02.2008
    // Parms :   $id - id of the share
    //           $path - string with path for recursive execute
    // Returns :      true,false / Void
    // Description : return path of names to the share 
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function ShowPath($id, $path=NULL)
    {
        $res = NULL;
        $devider = '<span class="pathDevider"></span>';
        $level = $this->GetLevel($id);
        if($level>0){
	    if(!isset($this->treeShareData[$level])) return false;
//	    $levelsArr=  array_keys($this->treeShareLevels[$level]);
	    $row=$this->treeShareData[$level];
//            $db = DBs::getInstance();
//            $q  = "SELECT `".TblModShare."`.`id`, `".TblModShare."`.`level`, `".TblModShareTxt."`.`pname`
//                   FROM `".TblModShare."`, `".TblModShareTxt."`
//                   WHERE `".TblModShare."`.`id`='".$level."' 
//                   AND `".TblModShare."`.`visible`='1'
//                   AND `".TblModShare."`.`id`=`".TblModShareTxt."`.`cod`
//                   AND `".TblModShareTxt."`.`lang_id`='".$this->lang_id."'
//                   AND `main_page`='0'
//                   ORDER BY `".TblModShare."`.`move`";
//            $res =$db->db_Query( $q );
//            //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
//            if( !$res OR !$db->result ) return false;
//            $rows = $db->db_GetNumRows();
//            if($rows==0) return false;
//            $row = $db->db_FetchAssoc();
            
            $name = stripslashes($row['pname']);
            if( !empty($name) ) $path = '<a href="'.$this->Link($row['id']).'">'.$name.'</a> '.$devider.' '.$path;
            //echo '<br>$path0='.$path;
            if( $row['level']>0 ){
                $res = $this->ShowPath($row['id'], $path);
            }
            else{
                //echo '<br>$name='.$name.'<br />';
                //$res = '<a href="'._LINK.'">'.$this->Msg->show_text('TXT_FRONT_HOME_PAGE').'</a> / '; 
                if( strstr($path, $devider)) $res = '<a href="'._LINK.'">'.$this->multi['TXT_FRONT_HOME_PAGE'].'</a> '.$devider.'<a href="'._LINK.'share">'.$this->multi['TXT_FRONT_SHARES'].'</a>'.$devider.$path; 
                else {
                    $res = $path;
                }
            }
        }
        else{
            if(!empty($this->share_id))
                $res = '<a href="'._LINK.'">'.$this->multi['TXT_FRONT_HOME_PAGE'].'</a>'.$devider.'<a href="'._LINK.'share">'.$this->multi['TXT_FRONT_SHARES'].'</a> '.$devider;
            else
                $res = '<a href="'._LINK.'">'.$this->multi['TXT_FRONT_HOME_PAGE'].'</a>'.$devider.$this->multi['TXT_FRONT_SHARES'];
        }
        return $res;
    }//end of function ShowPath() 
        

    /**
     * ShareLayout::MAP()
     * Show map of dynamic pages 
     * @author Yaroslav
     * @param integer $level
     * @return
     */
    function MAP($level=0)
    {
        $q = "SELECT `".TblModShare."`.*, `".TblModShareTxt."`.*  FROM `".TblModShare."`
              LEFT JOIN `".TblModShareTxt."` ON (`".TblModShare."`.`id`=`".TblModShareTxt."`.`cod` AND `".TblModShareTxt."`.`lang_id`='".$this->lang_id."')
              WHERE `".TblModShare."`.`level`='".$level."' AND `".TblModShare."`.`visible`='1' ORDER BY `".TblModShare."`.`move` asc";

        $res = $this->db->db_Query($q);
        $rows = $this->db->db_GetNumRows($res);
        //echo '<br>'.$q.'<br/> $res='.$res.' $rows='.$rows;
        if($rows==0)
            return false;
        $arr_data = array();
        for($i=0; $i<$rows; $i++){
            $arr_data[] = $this->db->db_FetchAssoc($res);
        }
        /*?><h1 align="left"><?=$this->Msg->show_text('_TXT_SITE_MAP')?></h1><?*/
        ?><ul><?
        for($i=0; $i<$rows; $i++){
            $row = $arr_data[$i];
            $id = $row['id']; 
            if ($this->MainPage() == $id) $href="/";
            else $href = $this->Link($id);
            ?><li><a href="<?=$href;?>"><?=stripslashes($row['pname']);?></a></li><? 
            $this->MAP($id);
            
            if($id == PAGE_NEWS)   { //News
                $News = &check_init('NewsLayout', 'NewsLayout');
                $News->GetMap();
            }
            
            if($id == PAGE_ARTICLE)   { //Articles
                $Article = &check_init('ArticleLayout', 'ArticleLayout');
                $Article->GetMap();
            }
            
            if($id ==PAGE_CATALOG)   { //Catalog
                if(!isset($this->Catalog)) $this->Catalog = &check_init('CatalogLayout', 'CatalogLayout');
                $this->Catalog->MAP();
            }

        } //end for
        ?></ul><?
    }// end of function MAP()

    // ================================================================================================
    // Function : GetTitle()
    // Date : 18.08.2006
    // Returns : true,false / Void
    // Description :  return titleiption of the share 
    // Programmer : Ihor Trohymchuk 
    // ================================================================================================
    function GetTitle()
    {
        if(empty($this->share_txt['mtitle'])) return stripslashes($this->share_txt['pname']);
        else return stripslashes($this->share_txt['mtitle']);
    } //end of function GetTitle()
    
     /**
    * Class method getSharesCurrentLevel
    * load all data of catalog categories to arrays
    * @return array: rows of shares current level
    * @author Panarin Sergey  <sp@seotm.com>
    * @version 1.0, 26.11.2011
    */
    function getSharesCurrentLevel(){
        if(empty($this->share_id))
           $level=0;
        else
           $level=$this->share_id; 
        $dateNow=strftime('%Y-%m-%d %H:%M', strtotime('now'));
        $q="SELECT DISTINCT `".TblModShare."`.*,
            `".TblModShare."`.`id` AS `share_id`,
            `".TblModShareTxt."`.*,
            `".TblModShareFileImg."`.`path` AS `imgPath`
           FROM `".TblModShare."`, `".TblModShareTxt."`,`".TblModShareFileImg."`
           WHERE `".TblModShare."`.`level`='".$level."'
               AND `".TblModShare."`.`visible`='1'
               AND `".TblModShareTxt."`.cod=`".TblModShare."`.id
               AND `".TblModShareTxt."`.lang_id='".$this->lang_id."'
               AND `".TblModShareFileImg."`.`id_position`=`".TblModShare."`.`id`
               AND `".TblModShareFileImg."`.`visible`='1'
               AND `".TblModShare."`.`ShareEnd`>'".$dateNow."'
               AND `".TblModShare."`.`ShareBegin`<'".$dateNow."'
               ORDER BY `".TblModShare."`.`move` asc";
        //echo $q;
        $res=$this->db->db_Query($q);
        $rows=$this->db->db_GetNumRows($res);
        $arr=array();
        for ($i = 0; $i < $rows; $i++) {
            $row=$this->db->db_FetchAssoc();
            $arr[]=$row;
        }
        return $arr;
    }
    
    /**
    * Class method ShowSharesCurrentLevel
    * load all data of catalog categories to arrays
    * @return true/false or arrays:
    * @author Panarin Sergey  <sp@seotm.com>
    * @version 1.0, 26.11.2011
    */
    function ShowSharesCurrentLevel(){
        if(empty($this->share_id))
           $path=$this->ShowPath(0);
        else
           $path=$this->ShowPath($this->share_id);
        ///echo $path;
//        $this->Form->WriteContentHeader(NULL, NULL, $path);
        ?>
	<div class="path floatToLeft width100Procentov">
	    <?=$path?>
	</div>
	<h1 class="floatToLeft width100Procentov RelatPropHeader basketHeader">Акции</h1>
	<div class="floatToLeft shareBox marginTop15 width100Procentov"><?
	//$this->getSharesCurrentLevel()
            $this->ShowSharesByPages();
      ?></div><?
//        $this->Form->WriteContentFooter();
    }
    
    /**
    * Class method ShowSharesCurrentLevel
    * Output data about shares by pages
    * @return true/false or arrays:
    * @author Panarin Sergey  <sp@seotm.com>
    * @param $arr array of shares
    * @version 1.0, 28.11.2011
    */
    function ShowSharesByPages(){
	$q="SELECT * FROM `sys_spr_mounth` WHERE `lang_id`='".$this->lang_id."'";
         $res=$this->db->db_Query($q);
         $rows=$this->db->db_GetNumRows();
         $this->month=array();
         for ($j = 1; $j < $rows+1; $j++) {
             $row=$this->db->db_FetchAssoc();
             $this->month[$j]=$row['name'];
         }
	$ouput=0;
	if(isset($this->treeShareLevels[$this->share_id])){
	    $sharesArr=  array_keys($this->treeShareLevels[$this->share_id]);
	    
	    for ($i = 0; $i < count($sharesArr); $i++) {
		$row=$this->treeShareData[$sharesArr[$i]];
		if($row['shareActivity']=='active'){
		    $this->layoutSingleShareByPages($row);
		    $ouput++;
		}
	    }
	}
	if($ouput==0) echo $this->multi['TXT_NO_POSITIONS_IN_SHARE'];
       
    }
    
    /**
    * Class method ShowSharesCurrentLevel
    * Output data about single share by pages
    * @return true/false or arrays:
    * @author Panarin Sergey  <sp@seotm.com>
    * @param $row array with data single share
    * @version 1.0, 28.11.2011
    */
    function layoutSingleShareByPages($row,$short=false,$propId=NULL){
        $imgFull="/uploads/images/share/".$row['share_id']."/".$row['imgPath'];
        $img=$this->Catalog->ShowCurrentImageExSize($imgFull, 330, 116, NULL, NULL, 85, NULL, NULL, NULL,true);
        $date_timeArr=explode(' ', $row['ShareBegin']);
        $timeArr=explode(':', $date_timeArr[1]);
        $dateArr=explode('-', $date_timeArr[0]);
        //$link=$this->GetPath($row['share_id']);
        $link=$this->Link($row['share_id']);
	
	$day=$dateArr[2];
	$month_=(int)$dateArr[1];
	if($month_!=0)
	    $month_=$this->month[$month_];
	$year=$dateArr[0];
	$data=$day." ".$month_." ".$year;
        //$this->UploadImages->Get
        ?>
            
            <div class="floatToLeft <? if(!$short) echo "marginBottom25"?> width100Procentov">
                <a href="<?=$link?>">
                <div class="imgShare floatToLeft" style="background: url(<?=$img?>) no-repeat left center">
                    
                </div>
                </a>
                <?if(!$short){?>
                <div class="floatToLeft ShareDescription">
                        <span class="ShareDescrName"><?=$row['pname']?> / </span><?=$data?>
                        <div class="ShareDescrText dotdotdot userText width100Procentov"><?=$row['content']?></div>
                        <a href="<?=$link?>" class="floatToLeft marginTop7 detail"><?=$this->multi['TXT_DETAILS_SHARE']?></a>
                </div>
                
                <?}?>
            </div>
            
        <?
//        if($short){
//            $dateTimeArr[0]=$dateArr;
//            $dateTimeArr[1]=$timeArr;
//            return $dateTimeArr;
//        }
    }

    // ================================================================================================
    // Function : GetDescription()
    // Date : 18.08.2006
    // Returns : true,false / Void
    // Description :  return description of the share 
    // Programmer : Ihor Trohymchuk 
    // ================================================================================================
    function GetDescription()
    {
        return stripslashes($this->share_txt['mdescr']);
    } //end of function GetDescription()

    // ================================================================================================
    // Function : GetKeywords()
    // Date : 18.08.2006
    // Returns : true,false / Void
    // Description :  return kyewords of the share
    // Programmer : Ihor Trohymchuk 
    // ================================================================================================
    function GetKeywords()
    {
        return stripslashes($this->share_txt['mkeywords']);
    } //end of function GetKeywords()


    // ================================================================================================
    // Function : Link()
    // Date : 09.02.2008
    // Parms :  $id - id of the share
    //          $add_domen_name (0/1). If 1 then add domen name before share url (like http://www.seotm.com/news/)
    //          if 0 then don't show domen name before share url (like /news/)
    //          $lang - id of the lang for build link
    // Returns : true,false / Void
    // Description :  return link to the share
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function Link($id, $add_domen_name=1, $lang = NULL)
    {
        $link=NULL;
        if( !empty($lang) ){
            //$Lang = new SysLang(NULL, "front");
            $Lang = &check_init('SysLang', 'SysLang', 'NULL, "front"');
            $tmp_lang = $Lang->GetDefFrontLangID();
            if( ($Lang->GetCountLang('front')>1 OR isset($_GET['lang_st'])) AND $lang!=$tmp_lang) $lang_prefix =  "/".$Lang->GetLangShortName($lang)."/";
            else $lang_prefix = "/"; 
        }
        else{
            if( !defined("_LINK")){
                //define("_LINK", "/");
                //$Lang = new SysLang(NULL, "front");
                $Lang = &check_init('SysLang', 'SysLang', 'NULL, "front"');
                $tmp_lang = $Lang->GetDefFrontLangID();
                if( ($Lang->GetCountLang('front')>1 OR isset($_GET['lang_st'])) AND _LANG_ID!=$tmp_lang) {
                    define("_LINK", "/".$Lang->GetLangShortName(_LANG_ID)."/");
                    $lang_prefix =  "/".$Lang->GetLangShortName(_LANG_ID)."/";
                }
                else {
                    define("_LINK", "/");
                    $lang_prefix = "/";
                }
            }
            else $lang_prefix = _LINK;
        }
        
        //is this share is main share of the site
        
        // echo '<br>$this->mod_rewrite='.$this->mod_rewrite;
        if($this->mod_rewrite==1){
           //$link = $this->GetNameById($id);
           $link = $this->GetPath($id);
           //echo '<br>$link='.$link;
           
           if( !empty($link)){
               //echo '<br>_LINK='._LINK.' strlen(_LINK)='.strlen(_LINK);
               if( strlen($lang_prefix)>1 AND $this->IsSharePage($id)==1 ) {
                   if($add_domen_name==1) $link = 'http://'.$_SERVER['SERVER_NAME'].$lang_prefix.$link;
                   else $link = $lang_prefix.$link;
               }
               else {
                   //if share is not dynamic share and this is not link to the share of other site then show path to this site
                   if( !strstr($link, "http://") ){
                       if($add_domen_name==1) $link = 'http://'.$_SERVER['SERVER_NAME'].$lang_prefix.$link;
                       else $link = $lang_prefix.$link;
                   }
                   else{
                       if( $this->is_main_page){
                           if( $main_page_flag ) $link=$link.$lang_prefix;
                       }
                   }
               }
           }
           else{
               //$link = $this->SetPath($id);
           }
           $link = $this->PrepareLink($link);
        }
        if( empty($link) ){
            if($main_page_flag) $link=$lang_prefix;
            else $link = $lang_prefix."index.php?share=".$id;
        }
        //echo '<br>$link='.$link;
        return $link;
    } //end of function Link()
    
     // ================================================================================================
     // Function : ShowPagesSpecialPos()
     // Date : 10.10.2008
     // Returns : true,false / Void
     // Description : show pages in special position
     // Programmer : Ihor Trokhymchuk
     // ================================================================================================
    function ShowSharesSpecialPos()
    {
	$keys=  array_keys($this->treeShareData);
	$specialPosArr=array();
	for($i=0; $i<count($keys); $i++){
	    if($this->treeShareData[$keys[$i]]['special_pos']=='1')
		$specialPosArr[]=$this->treeShareData[$keys[$i]];
	}
        //echo '<br>$rows='.$rows;
	$count=count($specialPosArr);
	if($count==0) return false;
	$num=rand(1,$count);
	$row = $specialPosArr[$num-1];
	
	$imgFull="/uploads/images/share/".$row['share_id']."/".$row['imgPath'];
        $img=$this->Catalog->ShowCurrentImageExSize($imgFull, 461, 123, NULL, NULL, 85, NULL, NULL, NULL,true);
        $date_timeArr=explode(' ', $row['ShareBegin']);
        $timeArr=explode(':', $date_timeArr[1]);
        $dateArr=explode('-', $date_timeArr[0]);
        //$link=$this->GetPath($row['share_id']);
        $link=$this->Link($row['share_id']);
        //$this->UploadImages->Get
        ?>
            
            <div class="floatToLeft width100Procentov marginTop3">
                <a href="<?=$link?>">
                <img class="imgShareMain floatToLeft" src="<?=$img?>" alt="<?=$row['pname']?>" title="<?=$row['pname']?>"/>
                </a>
            </div>
            
        <?
    }// end of function ShowPagesSpecialPos()
    
    // ================================================================================================
    // Function : ShowSearchRes()
    // Date : 31.03.2008
    // Returns : true,false / Void
    // Description : Show Add form on fontend
    // Programmer : Ihor Trohymchuk 
    // ================================================================================================     
    function ShowSearchRes($rows=0)
    {
        
        if($rows>0){
           ?><ul><?
           for($i=0;$i<$rows;$i++){
               $row = $this->db->db_FetchAssoc();
               ?> 
               <li><a href=<?=$this->Link($row['id']);?> class="map"><?=stripslashes($row['pname']);?></a></li>
               <?        
           }
           ?></ul><?
        }
        else{
            echo $this->Msg->show_text('SEARCH_NO_RES');
        }
    } // end of function ShowSearchRes()


    // ================================================================================================
    // Function : ShowSearchResHead()
    // Date : 31.03.2008
    // Returns : true,false / Void
    // Description : Show Add form on fontend
    // Programmer : Ihor Trohymchuk
    // ================================================================================================       
    function ShowSearchResHead($str)
    {
        ?>
        <div><?=$str;?></div>
        <?
    } // end of function ShowSearchResHead()
    
    
        // ================================================================================================
    // Function : UploadFileList()
    // Date : 30.05.2010
    // Parms : $pageId - id of the share
    // Returns : true,false / Void
    // Description : Show list of files attached to share with $pageId
    // Programmer : Yaroslav Gyryn
    // ================================================================================================         
    function ShowUploadFileList($pageId)
    {
        $array = $this->UploadFile->GetListOfFilesFrontend($pageId, $this->lang_id);
        if(count($array)>0) {
         ?><div class="leftBlockHead"><?=$this->multi['_TXT_FILES_TO_PAGE']?>:</div><?   
         $this->UploadFile->ShowListOfFilesFrontend($array, $pageId );
         }
    }


    // ================================================================================================
    // Function : DownloadCatalog()
    // Date : 30.05.2010
    // Parms : $pageId - id of the share
    // Returns : true,false / Void
    // Description : 
    // Programmer : Yaroslav Gyryn
    // ================================================================================================         
    function DownloadCatalog($pageId)
    {
        $array = $this->UploadFile->GetListOfFilesFrontend($pageId, $this->lang_id);
        if(count($array)>0) {
         $this->UploadFile->DownloadCatalogFrontend($array, $pageId );
         }
    }
        
    // ================================================================================================
    // Function : ShowUploadImageList()
    // Date : 30.05.2010
    // Parms : $pageId - id of the share
    // Returns : true,false / Void
    // Description : Show Upload Images List
    // Programmer : Yaroslav Gyryn
    // ================================================================================================         
    function ShowUploadImagesList($pageId)
    {
        $items = $this->UploadImages->GetPictureInArrayExSize($pageId, $this->lang_id,NULL,175,135,true,true,85);
        $items_keys = array_keys($items);
        $items_count = count($items);
        if($items_count>0) {
        ?><div class="leftBlockHead"><?= $this->Msg->show_text('SYS_IMAGE_GALLERY',TblSysTxt);?></div>
            <div class="imageBlock " align="center">
                <ul id="carouselLeft" class="vhidden jcarousel-skin-menu"><?
                for($j=0; $j<$items_count; $j++){   
                    $alt= $items[$items_keys[$j]]['name'][$this->lang_id];  // Заголовок
                    $title= $items[$items_keys[$j]]['text'][$this->lang_id]; // Описание 
                    $path = $items[$items_keys[$j]]['path'];                 // Путь уменьшенной копии
                    $path_org = $items[$items_keys[$j]]['path_original'];    // Путь оригинального изображения
                    ?><li>                            
                            <a href="<?=$path_org;?>" class="highslide" onclick="return hs.expand(this);">
                                <img src="<?=$path;?>" alt="<?=$alt?>" title="<?=$title;?>">
                             </a>
                             <div class="highslide-caption"><?=$title;?></div>
                     </li><?                
                }
                ?></ul>
            </div><?
         }        
        //$this->UploadImages->ShowMainPicture($pageId,$this->lang_id,'size_width=175 ', 85 ) ;
    }


    // ================================================================================================
    // Function : ShowRandomImage()
    // Date : 30.09.2010
    // Parms : $pageId - id of the share
    // Returns: void
    // Description :  Show Random Image
    // Programmer : Yaroslav Gyryn
    // ================================================================================================         
    function ShowRandomImage($pageId)
    {
        $share_txt = $this->GetShareData($pageId, $lang_id=NULL); 
        $name = stripslashes($share_txt['pname']);
        
       ?>
       <div class="leftMenuHead">
            <h3><?=$name?></h3>
       </div>
         <div class="imageBlock">
            <?
            $link = $this->Link($pageId);
            $items = $this->UploadImages->GetFirstRandomPicture($pageId, $this->lang_id, 'size_width= 232', null);
            $items_keys = array_keys($items);
            $items_count = count($items);
            if($items_count>0) {
                    /*$alt= $items[$items_keys]['name'][$this->lang_id];  // Заголовок
                    $title= $items[$items_keys]['text'][$this->lang_id];  // Описание */
                    $path = $items[$items_keys[0]]['path'];                    // Путь уменьшенной копии
                    //$path_org = $items[$items_keys['path_original'];   // Путь оригинального изображения
                    ?><a href="<?=$link;?>" title="<?=$name?>" alt="<?=$name?>"><img src="<?=$path;?>" alt="<?=$name?>" title="<?=$name?>"></a><?
            }                        
            /*?>
            <a href="<?=$link?>" title="<?=$this->multi['TXT_GALLERY_TITLE'];?>"><img src="/images/design/videoSmall.jpg"></a>*/?>
         </div>
         <?
       }
       
}// end of class ShareLayout
?>