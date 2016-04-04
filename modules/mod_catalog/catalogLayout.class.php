<?php
/**
 * CatalogLayout.class.php
 * class for display interface of Catalog module
 * @package Catalog Package of SEOCMS
 * @author Igor Trokhymchuk  <ihor@seotm.com>
 * @version 1.1, 05.04.2011
 * @copyright (c) 2010+ by SEOTM
 */

include_once(SITE_PATH . '/modules/mod_catalog/catalog.defines.php');

/**
 * Class CatalogLayout
 * class for display interface of Catalog module.
 * @author Igor Trokhymchuk  <ihor@seotm.com>
 * @version 1.1, 05.04.2011
 * @property FrontSpr $Spr
 * @property FrontForm $Form
 * @property db $db
 * @property SystemCurrencies $Currency
 * @property PageUser $PageUser
 *
 */
class CatalogLayout extends CatalogModel
{

    public $db = NULL;
    public $Msg = NULL;
    public $Form = NULL;
    public $Spr = NULL;
    public $Currency = NULL;
    public $is_tags = NULL;
    public $is_comments = NULL;
    public $PageUser = NULL;

    public $task = NULL;


    /**
     * Class Constructor
     *
     * @param $user_id - id of the user
     * @param $module - id of the module
     * @return true/false
     * @author Igor Trokhymchuk  <ihor@seotm.com>
     * @version 1.1, 05.04.2011
     */
    function __construct($user_id = NULL, $module = NULL)
    {
        //Check if Constants are overrulled
        ($user_id != "" ? $this->user_id = $user_id : $this->user_id = NULL);
        ($module != "" ? $this->module = $module : $this->module = 21);

        $this->lang_id = _LANG_ID;
        (defined("USE_TAGS") ? $this->is_tags = USE_TAGS : $this->is_tags = 0);
        (defined("USE_COMMENTS") ? $this->is_comments = USE_COMMENTS : $this->is_comments = 0);

        if (empty($this->db)) $this->db = DBs::getInstance();
        //if (empty($this->Msg)) $this->Msg = &check_init('ShowMsg', 'ShowMsg');
        if (empty($this->Form)) $this->Form = & check_init('FormCatalog', 'FrontForm', '"form_mod_catalogLayout"');
        if (empty($this->Spr)) $this->Spr = & check_init('FrontSpr', 'FrontSpr', "'$this->user_id', '$this->module'");
        if (empty($this->Currency)) $this->Currency =  & check_init('SystemCurrencies', 'SystemCurrencies');
        if (empty($this->settings)) $this->settings = $this->GetSettings(1);

        $this->multi = & check_init_txt('TblFrontMulti', TblFrontMulti);
        // for folders links
        $this->mod_rewrite = 1;

        //load all data of categories to arrays $this->treeCat, $this->treeCatLevels, $this->treeCatData
        $this->loadTree();
        //echo '<br />treeCatLevels=';print_r($this->treeCatLevels);
        //echo '<br />treeCatData=';print_r($this->treeCatData);

    } // End of CatalogLayout Constructor


    /**
     * Class method ShowCatalogTree
     * Checking show tree of catalog
     * @return html
     * @author Igor Trokhymchuk  <ihor@seotm.com>
     * @version 1.1, 05.01.2011
     */
    function ShowCatalogTree()
    {
        $this->main_top_level = $this->getTopLevel($this->id_cat);
        $this->showTree();
    } //end of function ShowCatalogTree()

    /**
     * Class method showTree
     * Write in html tree of catalog
     * @param array $tree - pointer to array with index as counter
     * @param integer $level - level of catalog
     * @param bool $flag - flag for lyaout
     * @param integer $cnt_sub - count of sublevels
     * @return array with index as counter
     * @author Igor Trokhymchuk  <ihor@seotm.com>
     * @version 1.1, 05.01.2011
     */
    function showTree($level = 0, $flag = 0, $cnt_sub = 0)
    {
        if (!$this->GetTreeCatLevel($level)) return $flag;
        $a_tree = $this->GetTreeCatLevel($level);
        //print_r($a_tree);
        if (empty($a_tree)) return $flag;
        $punkt = '';
        $class_li = 'close';
        $parent_level = 0;
        if ($flag == 0)
            $class = "";
        else {
            $class = "hidden";
            if (!empty($this->id_cat)) {
                $res = $this->isCatASubcatOfLevel($this->id_cat, $level);
                //echo '<br />$res = '.$res;
                if ($res) $class = "active";
            }
        }

            echo "<ul>\r\n";
            if ($this->id_cat > 0) $parent_level = $this->treeCatData[$this->id_cat]['level'];
            //echo '<br/>$parent_level = '.$parent_level;
            //echo '<br/>$class='.$class;
            $keys = array_keys($a_tree);
            $n = count($keys);
        $top_cat = "";
            for ($i = 0; $i < $n; $i++) {
                $row = $this->treeCatData[$keys[$i]];
                //echo '<br />$keys[$i]='.$keys[$i];
                if($row['id']==388){
                    $top_cat = "top-cat";
                }
                if($row['id']!=391 and $row['id']!=392 ){

                //echo '<br />$row=';print_r($row);
                if ($row['id'] == $this->main_top_level) {
                    $class_li = "open";
                } else {
                    if ($row['id'] == $this->id_cat OR $row['id'] == $parent_level) $class_li = "active" . $cnt_sub;
                }
                //echo '<br/>$class_li='.$class_li;
                //$href = $this->Link($a_tree[$i]['id']);
                $href = $this->getUrlByTranslit($row['path']);
                $name = $row['name'];
                echo '<li class="' . $class_li . '">';
                $class_a = '';
                if ($class_li == 'open') $class_a = 'openA';
                $class_li = '';
                if ($this->id_cat == $row['id']) {
                    //echo '<br>$cnt_sub='.$cnt_sub;

                    if ($cnt_sub > 0) echo '<a class="selected ' . $class_a .' '.$top_cat.'" href="' . $href . '">' . $name . '</a>';
                    else
                        echo '<a class="selected ' . $class_a .' '.$top_cat.'" href="' . $href . '">' . $name . '</a>';
                } else
                    echo $punkt . '<a class="' . $class_a .' '.$top_cat. '" href="' . $href . '">' . $name . '</a>';
                //echo '<br>$level='.$level.' $this->id_cat='.$this->id_cat.' $a_tree['.$i.'][level]='.$a_tree[$i]['level'];
                $flag = $this->showTree($row['id'], 1, ($cnt_sub + 1));
                }else{

                    if($row['id']!=392){
                        echo "<li class='prop-first'>";
                    }else{
                        echo "<li class='main-prop'>";
                    }
                }
                echo $this->ShowPropForMenu($row['id']);
                echo "</li>\r\n";
            }
            //echo '<br />$flag='.$flag;

                echo "</ul>\r\n";

        return $flag;
    } //end of function showTree()


    // ================================================================================================
    // Function : ShowPathToLevel()
    // Version : 1.0.0
    // Date : 21.03.2006
    //
    // Parms :        $id - id of the record in the table
    // Returns :      $str / string with name of the categoties to current level of catalogue
    // Description :  Return as links path of the categories to selected level of catalogue
    // ================================================================================================
    // Programmer :  Igor Trokhymchuk
    // Date : 10.01.2006
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowPathToLevel($level, $str = NULL, $make_link = NULL)
    {
        $devider = '<span>/</span>';
        if ($level > 0) {
            $tmp_db = DBs::getInstance();
            $row = $this->treeCatData[$level];
            $name = stripslashes($row['name']);
            $link = $this->getUrlByTranslit($row['path']);

            if (!empty($str)) {
                $str = '<a href="' . $link . '">' . $name . '</a> ' . $devider . ' <span class="spanShareName">' . $str . "</span>";
            } else {
                if ($make_link == 1) {
                    $str = '<a href="' . $link . '">' . $name . '</a>';
                } else $str = $name;
            }
            if ($row['level'] > 0) {
                return $this->ShowPathToLevel($row['level'], $str, $make_link = NULL);
            }
            //$str = '<a href="'.$script.'&level=0">'.$this->Msg->show_text('TXT_ROOT_CATEGORY').' > </a>'.$str;
            $str = '<a href="' . _LINK . '">' . $this->multi['TXT_FRONT_HOME_PAGE'] . '</a> ' . $devider . ' <a href="' . _LINK . 'catalog/">' . $this->multi['TXT_CATALOG'] . '</a> ' . $devider . ' <span class="spanShareName">' . $str . "</span>";
        } else {
            $str = '<a href="' . _LINK . '">' . $this->multi['TXT_FRONT_HOME_PAGE'] . '</a> ' . $devider . ' ' . $this->multi['TXT_CATALOG'] . '';
        }
        //echo $str;
        return $str;

        //echo '<a href="'._LINK.'">'.$this->multi['TXT_FRONT_HOME_PAGE'].'</a> '.$devider.' <a href="'._LINK.'catalog/">'.$this->multi['TXT_CATALOG'].'</a> '.$devider.' '.$str;
    } // end of function ShowPathToLevel()


    // ================================================================================================
    // Function : ShowMainCategories()
    // Version : 1.0.0
    // Date : 21.10.2009
    // Parms: $level
    // Returns : true,false / Void
    // Description : show main levels (categories) of catalogue on the front-end
    // Programmer : Yaroslav Gyryn
    // Date : 21.10.2009
    // ================================================================================================
    function ShowMainCategories($cols = 5, $id_cat=0)
    {

        $this->PageUser->breadcrumb = $this->ShowPathToLevel(0);
       // $this->Form->WriteContentHeader($this->multi['TXT_FRONT_CATALOG_MAIN_TEXT'], false, $path);
        if (is_array($this->GetTreeCatLevel($id_cat))) {

            $keys = array_keys($this->GetTreeCatLevel($id_cat));
            $n = count($keys);
            for ($i = 0; $i < $n; $i++) {

                $row = $this->GetTreeCatData($keys[$i]);
                if($row['id'] ==392){
                    $this->ShowContentCurentLevelMenu(392);
                    continue;
                }
                if($row['id'] ==391){
                    $this->ShowContentCurentLevelMenu(391);
                    continue;
                }
                $img_cat = $row['img_cat'];
                $name = stripslashes($row['name']);
                $link = $this->getUrlByTranslit($row['path']);
                ?>
              <div class="bg-item">
                <div class="item">
                    <a href="<?=$link;?>" title="<?=htmlspecialchars($name);?>">
                        <?if($img_cat!=""){
                        if(!is_file(SITE_PATH.$this->settings['img_path'].'/categories/exsize182'.$img_cat)){
                        $imageG = ImageK::factory(SITE_PATH.$this->settings['img_path'].'/categories/'.$img_cat)->resize(182, NULL, ImageK::WIDTH)->save(SITE_PATH.$this->settings['img_path'].'/categories/exsize182'.$img_cat);
                        }
                        ?>

                        <img src="<?=$this->settings['img_path'].'/categories/exsize182'.$img_cat?>">


                        <?}else{?>
                            <img src="">
                        <?}?>
                        <h3><?=$name;?></h3>
                    </a>
                    <?
                    if ($this->GetTreeCatLevel($keys[$i])) {
                        ?>
                        <ul><?
                            $keys2 = array_keys($this->GetTreeCatLevel($keys[$i]));
                            $n2 = count($keys2);
                            for ($j = 0; $j < $n2; $j++) {
                                $row2 = $this->GetTreeCatData($keys2[$j]);
                                $name = stripslashes($row2['name']);
                                $link = $this->getUrlByTranslit($row2['path']);
                                if($row2['id']!=391){
                                   ?><li><a href="<?=$link;?>" title="<?=htmlspecialchars($name);?>"><?=$name;?></a><br><?
                                }
                                echo $this->ShowPropForMenu($row2['id']);
                                ?>
                                </li><?
                            }
                       ?></ul><?
                    }else{

                        echo $this->ShowPropForMenu($row['id']);
                    }
                    ?>
                </div>
               </div>
                <?
            }// end for
            ?>

        <?
        } else {
            ?>

        <?
        }


    } // end of function ShowMainCategories()



        function ShowPropForMenu($id_cat){
            $q = "SELECT
                        `mod_catalog_prop` . * ,
                        `mod_catalog_prop_spr_name`.name,
                        `mod_catalog_translit`.`translit`
                FROM
                    `mod_catalog_prop` ,
                    `mod_catalog_prop_spr_name` ,
                    `mod_catalog_translit`
                WHERE  `mod_catalog_prop`.`id_cat` =".$id_cat."
                AND  `mod_catalog_prop`.id =  `mod_catalog_prop_spr_name`.cod
                AND  `mod_catalog_prop_spr_name`.lang_id =  ".$this->lang_id."
                AND  `mod_catalog_prop`.id =  `mod_catalog_translit`.`id_prop`
                AND  `mod_catalog_translit`.`lang_id` =  ".$this->lang_id."";


            $res = $this->db->db_Query( $q );
            //echo '<br>'.$q.'<br> res='.$res.'<br> $this->db->result='.$this->db->result;
            if( !$res or !$this->db->result) return false;
            $rows = $this->db->db_GetNumRows($res);
            $class = "";
            if($id_cat==391)
                $class = "prop-in-menu";
            $k = "<ul class='prop-menu ".$class."'>";
            for($i=0; $i<$rows; $i++) {
                $row=  $this->db->db_FetchAssoc();
                $cat_data = $this->GetTreeCatData($row['id_cat']);
                $row['link']= $this->getUrlByTranslit($cat_data['path'], $row['translit']);
                $k .="<li><a href ='". $row['link']."'>". $row['name']."</a></li>";
            }
            $k .= "</ul>";
            return $k;


        }

    // ================================================================================================
    // Function : ShowContentCurentLevel()
    // Date : 05.04.2006
    // Returns : true,false / Void
    // Description : show content of curent level of catalogue on the front-end
    // Programmer : Igor Trokhymchuk
    // ================================================================================================
    function ShowContentCurentLevel()
    {
        $cat_data = $this->GetTreeCatData($this->id_cat);
        $cat_level = $this->GetTreeCatLevel($this->id_cat);


        $this->PageUser->breadcrumb = $this->GetTreeCatData($this->id_cat);

        $this->PageUser->h1 = $cat_data['name'];
        $this->PageUser->title = '';
        $this->PageUser->breadcrumb = $this->ShowPathToLevel($this->id_cat);

        $params = '';
        $descr1 = '';
        $descr2 = '';
        $props = '';
        $levelsShort = '';


        if(!$cat_level){
            if (!isset($this->isContent))
                $this->isContent = $this->IsContent($this->id_cat);
            if ($this->isContent > 0) {
                ob_start();
                if (empty($this->search_keywords)) {
                    echo $this->ShowSelectedFilters();
                    $this->ParamShowPricePanel();
                    echo $this->ShowAllFilters();
                    //$this->ParamSortPanel();
                }
                $params = ob_get_clean();

                //показывем описание категории только для первой странцы. Если же при постраничности перешли на вторую страницу и далее,
                //то описание не показывать, что бы один и тот же текст не дублитровался при постраничености.
                if (isset($this->settings['cat_descr']) AND $this->settings['cat_descr'] == '1' AND $this->page < 2) {
                    $descr1 = stripslashes($this->treeCatData[$this->id_cat]['descr']);
                }
                $levelsShort = $this->ShowLevelsNameShort($this->treeCatLevels[$this->id_cat], 4);
                $props = $this->ShowListOfContentByPages($this->GetListPositionsSortByDate($this->id_cat, 'nolimit', $this->sort, $this->asc_desc, true, $this->id_param));

                //показывем доп. описание категории только для первой странцы. Если же при постраничности перешли на вторую страницу и далее,
                //то описание не показывать, что бы один и тот же текст не дублитровался при постраничености.
                if (isset($this->settings['cat_descr2']) AND $this->settings['cat_descr2'] == '1' AND $this->page < 2) {
                    $descr2 = stripslashes($this->treeCatData[$this->id_cat]['descr2']);
                }
                ?>

            <?
            } else {
                $props = View::factory('/modules/mod_catalog/templates/tpl_catalog_error.php')
                    ->bind('msq', $this->multi['MSG_ERR_NO_ANY_POSITIONS_IN_CATEGORY'])
                    ->bind('back', $this->multi['TXT_FRONT_GO_BACK']);
            }

            echo View::factory('/modules/mod_catalog/templates/tpl_catalog_current_level.php')
                ->bind('params', $params)
                ->bind('descr1', $descr1)
                ->bind('levelsShort', $levelsShort)
                ->bind('props', $props)
                ->bind('descr2', $descr2);
    }else{
            if (isset($this->settings['cat_descr']) AND $this->settings['cat_descr'] == '1' AND $this->page < 2) {
                $descr1 = stripslashes($this->treeCatData[$this->id_cat]['descr']);
            }

            echo View::factory('/modules/mod_catalog/templates/tpl_catalog_current_level.php')
                ->bind('PageUser', $this->PageUser)
                ->bind('descr1', $descr1)
                ->bind('id_cat', $this->id_cat);
             }




    } //end of function ShowContentCurentLevel()


    function ShowContentCurentLevelMenu($id_cat)
    {
        $cat_data = $this->GetTreeCatData($id_cat);
        $cat_level = $this->GetTreeCatLevel($id_cat);
        if(!$cat_level){
                $props = $this->ShowListOfContentByPages($this->GetListPositionsSortByDate($id_cat, 'nolimit', $this->sort, $this->asc_desc, true, $this->id_param));
                ?>

            <?
            }


            echo View::factory('/modules/mod_catalog/templates/tpl_catalog_prop_menu.php')
                ->bind('props', $props);

    } //end of function ShowContentCurentLevel()

    // ================================================================================================
    // Function : ShowLevelsName()
    // Date : 04.05.2006
    // Returns : true,false / Void
    // Description : show levels (categories) of catalogue on the front-end
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowLevelsName(&$tree, $cols = 5)
    {
        if (!is_array($tree)) return;

        $settings = $this->settings;
        switch ($cols) {
            case '1':
                $width = 'width100';
                break;
            case '2':
                $width = 'width50';
                break;
            case '3':
                $width = 'width32';
                break;
            case '4':
                $width = 'width25';
                break;
            case '5':
                $width = 'width20';
                break;

        }
        ?>
    <div class="floatContainer">
        <?
        $rows = count($tree);
        $keys = array_keys($tree);
        for ($i = 0; $i < $rows; $i++) {
            $id_cat = $keys[$i];
            //echo '<br />$id_cat='.$id_cat;
            $cat_data = $this->GetTreeCatData($id_cat);
            $name = stripslashes($cat_data['name']);
            $img_cat = stripslashes($cat_data['img_cat']);
            $descr = stripslashes($cat_data['descr']);
            //$descr2 = stripslashes($row['descr2']);
            ?>
            <!-- show Name of the category -->
            <?
            $link = $this->getUrlByTranslit($cat_data['path'], NULL);
            ?>
            <div class="item floatToLeft <?=$width;?>">
                <a href="<?=$link;?>" title="<?=addslashes($name);?>"><?=$name;?></a>
            </div>
            <!-- show Image of the category -->
            <? //if (!empty($img_cat)) { echo $this->ShowCurrentImage($settings['img_path']."/categories/".$img_cat, 'size_auto=75', 85, NULL, "border=0");}?>
            <!-- show Description of the category -->
            <? //=$descr;?>
            <?
        }// end for
        ?>
    </div>
    <?
    } // end of function  ShowLevelsName()


    // ================================================================================================
    // Function : ShowLevelsNameShort()
    // Date : 04.05.2006
    // Returns : true,false / Void
    // Description : show levels (categories) of catalogue on the front-end
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowLevelsNameShort(&$tree, $cols = 5)
    {
        if (!is_array($tree)) return;

        $settings = $this->settings;
        switch ($cols) {
            case '1':
                $width = 'width100';
                break;
            case '2':
                $width = 'width50';
                break;
            case '3':
                $width = 'width32';
                break;
            case '4':
                $width = 'width25';
                break;
            case '5':
                $width = 'width20';
                break;

        }
        $rows = count($tree);
        $cat_data = array();
        $keys = array_keys($tree);
        for ($i = 0; $i < $rows; $i++) {
            $id_cat = $keys[$i];
            //echo '<br />$id_cat='.$id_cat;
            $cat_data[$i] = $this->GetTreeCatData($id_cat);
            $cat_data[$i]['name'] = stripslashes($cat_data[$i]['name']);
            $cat_data[$i]['img_cat'] = stripslashes($cat_data[$i]['img_cat']);
            //$descr = stripslashes($cat_data['descr']);
            //$descr2 = stripslashes($row['descr2']);
            ?>
        <!-- show Name of the category -->
        <?
            if (!empty($cat_data[$i]['href'])) $cat_data[$i]['href'] = _LINK . $cat_data[$i]['href'];
            else $cat_data[$i]['href'] = $this->getUrlByTranslit($cat_data[$i]['path'], NULL);
            ?>
        <
        <!-- show Image of the category -->
        <? //if (!empty($img_cat)) { echo $this->ShowCurrentImage($settings['img_path']."/categories/".$img_cat, 'size_auto=75', 85, NULL, "border=0");}?>
        <!-- show Description of the category -->
        <? //=$descr;?>
        <?
        }
        // end for
        return View::factory('/modules/mod_catalog/templates/tpl_catalog_levels_name.php')
            ->bind('width', $width)
            ->bind('rows', $rows)
            ->bind('cat_data', $cat_data);
    } // end of function  ShowLevelsNameShort()


    /*************************************************************************************************************/

    /**
     * CatalogLayout::ParamShowPricePanel()
     * Show Price Panel filter
     * @author Yaroslav Gyryn
     * @return void
     */
    function ParamShowPricePanel()
    {
        $btnSaveChanges = 'Ок';
        ?>
    <div class="paramBlock">
        <div class="paramName">Цена:</div>
        <form action="" method="post" name="priceLevels">
            <table cellpadding="2" cellspacing="0" border="0" class="tblPriceLevel">
                <tr>
                    <td title="Грн"> От:<input type="text" value="<?=$this->from;?>" name="from" maxlength="8" size="4"
                                               onkeypress="return me()"/></td>
                    <td title="Грн"> до:<input type="text" value="<?=$this->to;?>" name="to" maxlength="8" size="4"
                                               onkeypress="return me()"/></td>
                    <td>&nbsp;<input type="image" src="/images/design/btnOk.gif" alt="<?=$btnSaveChanges;?>"
                                     title="<?=$btnSaveChanges;?>"/></td>
                </tr>
            </table>
        </form>
    </div>
    <?
    }


    /**
     * CatalogLayout::ShowSelectedFilters()
     *
     * @return
     */
    function ShowSelectedFilters()
    {
        $str = NULL;
        $param_str = NULL;
        $this->url_param = NULL;
        $param = NULL;
        $filtr = NULL;
        $id_cat = $this->id_cat;

        if (!isset($this->params_row))
            $this->params_row = $this->GetParams($id_cat);
        //print_r($this->params_row);
        if (!empty($this->treeCatData[$id_cat]['href']))
            $this->catLink = $this->getUrlByTranslit($this->treeCatData[$id_cat]['href']);
        else
            $this->catLink = $this->getUrlByTranslit($this->treeCatData[$id_cat]['path']);

        if (!isset($this->propArrNoLimit))
            $this->propArrNoLimit = $this->generateIdPropArra();
        $this->countOfPropNoLimit = count($this->propArrNoLimit[0]);
        $IdOfProps = $this->makeIdPropStr($this->propArrNoLimit[0]);

        $n = count($this->params_row);
        $counter = 0;
        for ($i = 0; $i < $n; $i++) {
            if ($this->params_row[$i]['modify'] != 1) //Отображать в блоке параметров
                continue;
            $val = NULL;
            switch ($this->params_row[$i]['type']) {
                case '1':
                    //$val = $v;
                    break;
                case '2':
                    $val = $this->Spr->GetListName(TblSysLogic, $this->lang_id, 'array', 'move', 'asc', 'all');
                    break;
                case '3':
                case '4':
                    if (isset($this->propArrNoLimit[$this->params_row[$i]['id']])) {
                        $IdOfProps1 = $this->makeIdPropStr($this->propArrNoLimit[$this->params_row[$i]['id']]);
                        $val = $this->GetParamsPropVal($this->lang_id, $this->params_row[$i]['id_categ'], $this->params_row[$i]['id'], $IdOfProps1);
                    } else {
                        $val = $this->GetParamsPropVal($this->lang_id, $this->params_row[$i]['id_categ'], $this->params_row[$i]['id'], $IdOfProps);
                    }
                    break;
                /*  case '5':
                    $val = $v;
                    break;*/
            }
            $prefix = '';
            $sufix = '';
            /*$prefix = $this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($this->params_row[$i]['id']), $this->lang_id, 1);
          $sufix = $this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($this->params_row[$i]['id']), $this->lang_id, 1);*/
            if (is_array($val)) {

                $showAll = false;
                // Формирование строки параметров
                $param_arr = array();
                if (is_array($this->arr_current_img_params_value)) {
                    $param_str = NULL;
                    foreach ($this->arr_current_img_params_value as $key => $value) {
                        $param_arr[$key] = $value;

                        // Формирование ссылки для постраничности
                        if ($key != $this->params_row[$i]['id'] AND !empty($value)) {
                            $param = '&' . PARAM_VAR_NAME . PARAM_VAR_SEPARATOR . $key . '=' . $value;
                            $param_str .= $param;
                            //echo '<br/>$param = '.$param.'<br/>';
                            if (substr_count($this->url_param, $param) == 0)
                                $this->url_param .= $param;
                        } elseif ($key == $this->params_row[$i]['id'] AND !empty($value) and count($this->arr_current_img_params_value) == 1) {
                            $this->url_param = PARAM_VAR_NAME . PARAM_VAR_SEPARATOR . $key . '=' . $value;
                        }
                        if (!empty($this->url_param) and $str == null) {
                            $str = '<div class="selectedParams">';
                            $str .= '<div id="allCount"></div>';
                            /*$tow = $this->countOfPropNoLimit;
                        if($tow==1 or $tow==21 or $tow==31 or $tow==41 or $tow==51  or $tow==61 or $tow==71 or $tow==81 or $tow==91)
                            $tovar = $this->multi['FLD_PRODUCT'];
                        elseif ($tow==2 or $tow==3 or $tow==4 or $tow==22 or $tow==23 or $tow==24 or $tow==32 or $tow==33 or $tow==34 or $tow==42 or $tow==43 or $tow==44 )
                               $tovar = $this->multi['FLD_PRODUCTA'];
                            else
                                $tovar = $this->multi['FLD_PRODUCTOV'];

                        $str.='<div class="countParams">Выбрано <b>'.$tow.'</b> '.$tovar.'</div>';*/
                        }
                    }
                }

                foreach ($val as $k => $v) {

                    // Форматированный вывод текста либо ссылки параметра
                    $checked = false;
                    if (is_array($this->arr_current_img_params_value)) {
                        foreach ($this->arr_current_img_params_value as $key => $value) {
                            $subArr = explode(",", $value);
                            foreach ($subArr as $key1 => $value1) {
                                if ($key == $this->params_row[$i]['id'] AND $value1 == $v['cod']) {
                                    $checked = true;
                                    //break;
                                }
                            }
                        }
                    }
                    //echo '<br>$v[countOfProp]='.$v['countOfProp'];
                    if (isset($v['countOfProp'])) {
                        if (isset($this->arr_current_img_params_value[$this->params_row[$i]['id']]))
                            $countOfProp = "+" . ($v['countOfProp']);
                        else $countOfProp = $v['countOfProp'];
                    } else $countOfProp = 0;
                    if ($checked == true) {
                        $paramLink = $this->makeParamLink($param_arr, $params_row[$i]['id'], $v['cod']);
                        if (strlen($paramLink) > 0) $paramLink[0] = "?";
                        $str .= '<a class="btnDeleteParam" title="Сбросить" href="' . $this->catLink . $paramLink . '">' . $prefix . ' ' . $v['name'] . ' ' . $sufix . '</a><br/> ';
                    }
                }
            }
        }

        if (isset($this->from) and isset($this->to)) {
            $str .= '<a class="btnDeleteParam" title="Сбросить" href="">' . 'от ' . $this->from . ' до ' . $this->to . ' грн.</a><br/>';
        }

        // Вывод ссылки "Все"
        if (!empty($this->url_param)) {
            $str .= '<a class="filters_off" href="' . $this->catLink . '">Сбросить все фильтры</a>';
            $str .= '</div>';
        }
        return $str;
    } //end of function ShowSelectedFilters()


    /**
     * CatalogLayout::ShowAllFilters()
     * Description : return names & values of parameters in string
     * @return $str
     */
    function ShowAllFilters()
    {
        $str = NULL;
        $param_str = NULL;
        $this->url_param = NULL;
        $param = NULL;
        $filtr = NULL;
        $sorting = '';
        $this->priceLevels = '';
        $id_cat = $this->id_cat;
        if (!isset($this->params_row))
            $this->params_row = $this->GetParams($id_cat);
        //$this->catLink = $this->Link($this->id_cat);
        if (!isset($this->catLink)) {
            if (!empty($this->treeCatData[$id_cat]['href']))
                $this->catLink = $this->getUrlByTranslit($this->treeCatData[$id_cat]['href']);
            else
                $this->catLink = $this->getUrlByTranslit($this->treeCatData[$id_cat]['path']);
        }

        if (!isset($this->propArrNoLimit))
            $this->propArrNoLimit = $this->generateIdPropArra();
        $this->countOfPropNoLimit = count($this->propArrNoLimit[0]);
        $IdOfProps = $this->makeIdPropStr($this->propArrNoLimit[0]);

        if (!empty($this->sort))
            $sorting = '&sort=' . $this->sort . '&asc_desc=' . $this->asc_desc . '&exist=' . $this->exist;

        if (!empty($this->from)  and !empty($this->to))
            $this->priceLevels = '&from=' . $this->from . '&to=' . $this->to;

        $n = count($this->params_row);
        $counter = 0;

        for ($i = 0; $i < $n; $i++) {
            if ($this->params_row[$i]['modify'] != 1) //Отображать в блоке параметров
                continue;
            $val = NULL;
            $paramName = $this->Spr->GetNameByCod(TblModCatalogParamsSprName, ($this->params_row[$i]['id']), $this->lang_id, 1);

            $str .= '<div class="paramBlock"><div class="paramName">' . $paramName . ':</div>';
            //$tblname = $this->BuildNameOfValuesTable($this->params_row[$i]['id_categ'], $this->params_row[$i]['id']);
            switch ($this->params_row[$i]['type']) {
                case '1':
                    //$val = $v;
                    break;
                case '2':
                    $val = $this->Spr->GetListName(TblSysLogic, $this->lang_id, 'array', 'move', 'asc', 'all');
                    break;
                case '3':
                case '4':
                    if (isset($this->propArrNoLimit[$this->params_row[$i]['id']])) {
                        $IdOfProps1 = $this->makeIdPropStr($this->propArrNoLimit[$this->params_row[$i]['id']]);
                        $val = $this->GetParamsPropVal($this->lang_id, $this->params_row[$i]['id_categ'], $this->params_row[$i]['id'], $IdOfProps1);
                    } else {

                        $val = $this->GetParamsPropVal($this->lang_id, $this->params_row[$i]['id_categ'], $this->params_row[$i]['id'], $IdOfProps);
                    }
                    break;
                /*  case '5':
                        $val = $v;
                        break;*/
            }

            $prefix = $this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix, ($this->params_row[$i]['id']), $this->lang_id, 1);
            $sufix = $this->Spr->GetNameByCod(TblModCatalogParamsSprSufix, ($this->params_row[$i]['id']), $this->lang_id, 1);

            $str .= '<div class="paramKey">';
            if (is_array($val)) {
                $showAll = false;

                // Формирование строки параметров
                $param_arr = array();
                if (is_array($this->arr_current_img_params_value)) {
                    $param_str = NULL;
                    foreach ($this->arr_current_img_params_value as $key => $value) {
                        $param_arr[$key] = $value;

                        // Формирование ссылки для постраничности
                        if ($key != $this->params_row[$i]['id'] AND !empty($value)) {
                            $param = '&' . PARAM_VAR_NAME . PARAM_VAR_SEPARATOR . $key . '=' . $value;
                            $param_str .= $param;
                            //echo '<br/>$param = '.$param.'<br/>';
                            if (substr_count($this->url_param, $param) == 0)
                                $this->url_param .= $param;
                        } elseif ($key == $this->params_row[$i]['id'] AND !empty($value) and count($this->arr_current_img_params_value) == 1) {
                            $this->url_param = PARAM_VAR_NAME . PARAM_VAR_SEPARATOR . $key . '=' . $value;
                        }
                    }
                }

                foreach ($val as $k => $v) {
                    // Форматированный вывод текста либо ссылки параметра
                    $checked = false;
                    if (is_array($this->arr_current_img_params_value)) {
                        foreach ($this->arr_current_img_params_value as $key => $value) {
                            $subArr = explode(",", $value); //print_r($subArr);
                            foreach ($subArr as $key1 => $value1) {
                                if ($key == $this->params_row[$i]['id'] AND $value1 == $v['cod']) {
                                    $checked = true;
                                    //break;
                                }
                            }
                        }
                    }
                    if (isset($v['countOfProp'])) {
                        if (isset($this->arr_current_img_params_value[$this->params_row[$i]['id']]))
                            $countOfProp = "+" . ($v['countOfProp']);
                        else $countOfProp = $v['countOfProp'];
                    } else $countOfProp = 0;
                    $paramLink = $this->makeParamLink($param_arr, $this->params_row[$i]['id'], $v['cod']);
                    if (strlen($paramLink) > 0) $paramLink[0] = "?";
                    if ($countOfProp > 0) {
                        if ($checked == true) {
                            $showAll = true;
                            $str .= '<a class="paramSelected" href="' . $this->catLink . $paramLink . '">' . $prefix . ' ' . $v['name'] . ' ' . $sufix . '</a><br/> ';
                        } else $str .= '<a href="' . $this->catLink . $paramLink . '">' . $prefix . ' ' . $v['name'] . ' ' . $sufix . ' (' . $countOfProp . ')</a><br/> ';
                    } else {
                        if ($checked) {
                            $str .= '<a class="paramSelected" href="' . $this->catLink . $paramLink . '">' . $prefix . ' ' . $v['name'] . ' ' . $sufix . '</a><br/> ';
                        } else $str .= '<span class="param_all" href="' . $this->catLink . $paramLink . '">' . $prefix . ' ' . $v['name'] . ' ' . $sufix . ' (0)</span><br/> ';
                    }
                }
            }

            $str .= '</div></div>';
        }

        return $str;
    } //end of function ShowAllFilters()


    /**
     * CatalogLayout::ShowListOfContentByPages()
     * Show list of positions by pages
     * @author Yaroslav
     * @param mixed $arr
     * @return void
     */
    function ShowListOfContentByPages($arr = NULL)
    {
        $rows = count($arr);
        //echo '<br>$rows='.$rows;
        if ($rows == 0 or !is_array($arr)) {
            if ($this->task == 'make_advansed_search' or $this->task == 'quick_search' or $this->task == 'make_search_by_params') {
                return View::factory('/modules/mod_catalog/templates/tpl_catalog_error.php')
                    ->bind('msq', $this->multi['MSG_ERR_NO_ANY_POSITIONS_BY_REQUEST'])
                    ->bind('back', $this->multi['TXT_FRONT_GO_BACK']);
            } else {
                if (isset($this->treeCatData[$this->id_cat]['name'])) {
                    $category_name = stripslashes($this->treeCatData[$this->id_cat]['name']);
                    if (!$this->isSubLevels($this->id_cat, $this->treeCatLevels, $this->id_cat)) {
                        return View::factory('/modules/mod_catalog/templates/tpl_catalog_error.php')
                            ->bind('msq', $this->multi['MSG_ERR_NO_ANY_POSITIONS_BY_REQUEST'])
                            ->bind('back', $this->multi['TXT_FRONT_GO_BACK']);
                    } else {
                        // Выбор по параметрам фильтра
                        return View::factory('/modules/mod_catalog/templates/tpl_catalog_error.php')
                            ->bind('msq', $this->multi['MSG_ERR_NO_POSITIONS_BY_PARAM_IN_CATEGORY'])
                            ->bind('back', $this->multi['TXT_FRONT_GO_BACK']);
                    }
                } else {
                    return View::factory('/modules/mod_catalog/templates/tpl_catalog_error.php')
                        ->bind('msq', $this->multi['MSG_ERR_NO_ANY_POSITIONS_IN_CATEGORY'])
                        ->bind('back', $this->multi['TXT_FRONT_GO_BACK']);
                }
            }
        } else {
            $pagination = '';
            if (empty($this->search_keywords)) {
                if ($rows >= $this->display or $this->page > 1) {
                    $link = $this->Link($this->id_cat, NULL);
                    $rows = count($this->GetListPositionsSortByDate($this->id_cat, 'nolimit', null, 'asc', true, $this->id_param));
                    //echo '$this->url_param ='.$this->url_param;
                    if (!empty ($this->url_param))
                        $this->url_param = '?' . $this->url_param;
                    else
                        $this->url_param = '?';

                    if (!empty ($this->id_param))
                        $this->url_param .= '&id_param=' . $this->id_param;

                    if (isset($this->priceLevels))
                        $this->url_param .= $this->priceLevels;

                    if (!empty($this->sort)) {
                        if ($this->asc_desc == 'asc')
                            $asc_desc = 'desc';
                        else
                            $asc_desc = 'asc';
                        if (!empty ($this->url_param))
                            $this->url_param .= '&sort=' . $this->sort . '&asc_desc=' . $asc_desc . '&exist=' . $this->exist;
                        else
                            $this->url_param = '?sort=' . $this->sort . '&asc_desc=' . $asc_desc . '&exist=' . $this->exist;
                    }

                    $pagination = $this->Form->WriteLinkPagesStatic($link, $rows, $this->display, $this->start, $this->sort, $this->page, $this->url_param);
                }
            } //показываем постраничность для результатов поиска в каталоге
            elseif ($this->task == 'quick_search') {
                $rows = count($this->QuickSearch($this->search_keywords, 'nolimit'));
                $link = _LINK . 'catalog/search/result/' . htmlentities(urlencode($this->search_keywords)) . '/';
                $pagination = $this->Form->WriteLinkPagesStatic($link, $rows, $this->display, $this->start, $this->sort, $this->page);
            }

            return View::factory('/modules/mod_catalog/templates/tpl_catalog_props_by_pages.php')
                ->bind('pagination', $pagination)
                ->bind('props', $arr);
        }
    } //--- end of ShowListOfContentByPages()


    // ================================================================================================
    // Function : ShowListOfContentByPages()
    // Version : 1.0.0
    // Date : 03.03.2008
    // Parms : $id - id of the position
    // Returns : true,false / Void
    // Description : show list of positions by pages
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 17.02.2011
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowListShortByPages($arr = NULL)
    {
        $rows = count($arr);
        //echo '<br>$rows='.$rows;
        $cat_data = $this->GetTreeCatData($this->id_cat);
        if ($rows == 0) {
            ?>
        <div class="err" align="center"><?
            $category_name = stripslashes($cat_data['name']);
            if ($this->task == 'make_advansed_search' or $this->task == 'quick_search' or $this->task == 'make_search_by_params') {
                $this->ShowErr($this->multi['MSG_ERR_NO_ANY_POSITIONS_BY_REQUEST'] . '<br /><a href="javascript:history.back()">' . $this->multi['TXT_FRONT_GO_BACK'] . '</a>');
            } else {
                if (!$this->isCatASubcatOfLevel($this->id_cat, $this->treeCatLevels, $this->id_cat)) {
                    echo $this->multi['MSG_ERR_NO_ANY_POSITIONS_IN_CATEGORY'] . ' <strong>' . $category_name . '</strong><br/><a href="javascript:history.back()">' . $this->multi['TXT_FRONT_GO_BACK'] . '</a>';
                }
            }
            ?></div><?
        } else {
            $settings = $this->settings;
            ?>
        <ul class="categoryContent">
            <?
            for ($i = 0; $i < $rows; $i++) {
                $row = $arr[$i];
                $name = stripslashes($row['name']);
                $price = stripslashes($row['price']);
                $old_price = stripslashes($row['opt_price']);
                $link = $this->getUrlByTranslit($this->treeCatData[$row['id_cat']]['path'], $row['translit']);
                $cur_from = $row['price_currency'];
                $price = $this->Currency->Converting($cur_from, _CURR_ID, $price, 2);
                ?>
                <!-- Show Name of Position -->
                <li><a href="<?=$link;?>" title="<?=htmlspecialchars($name);?>"><?=$name;?></a>
                    <span><?=$this->Currency->ShowPrice($price);?></span></li>
                <?
            }
            ?>
        </ul>
        <?
            /*
            $arr = $this->GetListPositionsSortByDate($this->id_cat, 'nolimit', true);
            $rows = count($arr);
            $link = $this->Link($this->id_cat, NULL);
            */

            //РїРѕРєР°Р·С‹РІР°РµРј РїРѕСЃС‚СЂР°РЅРёС‡РЅРѕСЃС‚СЊ РґР»СЏ СЂРµР·СѓР»СЊС‚Р°С‚РѕРІ РїРѕРёСЃРєР° РІ РєР°С‚Р°Р»РѕРіРµ
            if ($this->task == 'quick_search') {
                $rows_all = $this->QuickSearch($this->search_keywords, 'nolimit');
                $link = _LINK . 'catalog/search/result/' . htmlentities(urlencode($this->search_keywords)) . '/';
                ?>
            <div style="margin-top:30px; text-align:center;"><?$this->FrontForm->WriteLinkPagesStatic($link, $rows_all, $this->display, $this->start, $this->sort, $this->page);?></div><?
            }
        }
    } //--- end of ShowListShortByPages()

    // ================================================================================================
    // Function : ShowRatingInfo()
    // Version : 1.0.0
    // Date : 08.04.2006
    // Parms :
    // Returns : true,false / Void
    // Description : show details of curent position of catalogue on the front-end
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 08.04.2006
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowRatingInfo($id)
    {
        $rating = $this->GetAverageRatingByIdProp($id, 'front');
        if ($rating < 1) $rating = 0;
        ?>
    <span class="rat01">
        Р’СЃРµРіРѕ Р±Р°Р»РѕРІ: <?=$this->GetRatingByIdProp($id);?>
        <br/>Р“РѕР»РѕСЃРѕРІ: <?=$this->GetVotesByIdProp($id);?>
        <br/><?=$this->Msg->show_text('FLD_RATING') . ': ' . $rating;?>
        </span>
    <?

    }

    //end of function ShowRatingInfo()

    // ================================================================================================
    // Function : ShowDetailsCurrentPosition()
    // Version : 1.0.0
    // Date : 08.04.2006
    // Parms :
    // Returns : true,false / Void
    // Description : show details of curent position of catalogue on the front-end
    // ================================================================================================
    // Programmer : Yaroslav Gyryn
    // Date : 25.10.2009
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    // ================================================================================================
    // Function : ShowDetailsCurrentPosition()
    // Version : 1.0.0
    // Date : 08.04.2006
    // Parms :
    // Returns : true,false / Void
    // Description : show details of curent position of catalogue on the front-end
    // ================================================================================================
    // Programmer : Yaroslav Gyryn
    // Date : 25.10.2009
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowDetailsCurrentPosition($id_img = NULL)
    {
        $settings = $this->settings;
        $tmp_db = DBs::getInstance();
        $catData = $this->GetTreeCatData($this->id_cat);
        $cls = "";


        $filed_list = ", `" . TblModCatalogPropSprH1 . "`.`name` AS `h1`";
        $left_join = "\n LEFT JOIN `" . TblModCatalogPropSprH1 . "` ON (`" . TblModCatalogProp . "`.`id`=`" . TblModCatalogPropSprH1 . "`.`cod` AND `" . TblModCatalogPropSprH1 . "`.`lang_id`='" . $this->lang_id . "')";

        if (isset($settings['short_descr']) AND $settings['short_descr'] == '1') {
            $filed_list .= ", `" . TblModCatalogPropSprShort . "`.`name` AS `short`";
            $left_join .= "\n LEFT JOIN `" . TblModCatalogPropSprShort . "` ON (`" . TblModCatalogProp . "`.`id`=`" . TblModCatalogPropSprShort . "`.`cod` AND `" . TblModCatalogPropSprShort . "`.`lang_id`='" . $this->lang_id . "')";
        }
        if (isset($settings['full_descr']) AND $settings['full_descr'] == '1') {
            $filed_list .= ", `" . TblModCatalogPropSprFull . "`.`name` AS `full`";
            $left_join .= "\n LEFT JOIN `" . TblModCatalogPropSprFull . "` ON (`" . TblModCatalogProp . "`.`id`=`" . TblModCatalogPropSprFull . "`.`cod` AND `" . TblModCatalogPropSprFull . "`.`lang_id`='" . $this->lang_id . "')";
        }
        if (isset($settings['specif']) AND $settings['specif'] == '1') {
            $filed_list .= ", `" . TblModCatalogPropSprSpecif . "`.`name` AS `specif`";
            $left_join .= "\n LEFT JOIN `" . TblModCatalogPropSprSpecif . "` ON (`" . TblModCatalogProp . "`.`id`=`" . TblModCatalogPropSprSpecif . "`.`cod` AND `" . TblModCatalogPropSprSpecif . "`.`lang_id`='" . $this->lang_id . "')";

        }
        if (isset($settings['reviews']) AND $settings['reviews'] == '1') {
            $filed_list .= ", `" . TblModCatalogPropSprReviews . "`.`name` AS `reviews`";
            $left_join .= "\n LEFT JOIN `" . TblModCatalogPropSprReviews . "` ON (`" . TblModCatalogProp . "`.`id`=`" . TblModCatalogPropSprReviews . "`.`cod` AND `" . TblModCatalogPropSprReviews . "`.`lang_id`='" . $this->lang_id . "')";

        }
        if (isset($settings['support']) AND $settings['support'] == '1') {
            $filed_list .= ", `" . TblModCatalogPropSprSupport . "`.`name` AS `support`";
            $left_join .= "\n LEFT JOIN `" . TblModCatalogPropSprSupport . "` ON (`" . TblModCatalogProp . "`.`id`=`" . TblModCatalogPropSprSupport . "`.`cod` AND `" . TblModCatalogPropSprSupport . "`.`lang_id`='" . $this->lang_id . "')";
        }

        $q = "SELECT
                `" . TblModCatalogProp . "`.*,
                `" . TblModCatalogPropSprName . "`.name
                $filed_list
             FROM `" . TblModCatalogProp . "`
                $left_join ,
                `" . TblModCatalogPropSprName . "`
             WHERE
                `" . TblModCatalogProp . "`.id = `" . TblModCatalogPropSprName . "`.cod
             AND
                `" . TblModCatalogPropSprName . "`.lang_id='" . $this->lang_id . "'
             AND
                `" . TblModCatalogProp . "`.id ='" . $this->id . "'";

        $res = $tmp_db->db_Query($q);
        //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
        if (!$res or !$tmp_db->result)
            return false;

        $row = $tmp_db->db_FetchAssoc();
        if (isset($settings['img']) AND $settings['img'] == '1') $row_img = $this->GetPicture($row['id']);
        if (isset($settings['files']) AND $settings['files'] == '1') $row_files = $this->GetFiles($row['id']);
        $name = stripslashes($row['name']);
        //$manufac = $this->Spr->GetNameByCod( TblModCatalogSprManufac, $row['id_manufac'], $this->lang_id, 1 );
        if(!isset($row['h1']) OR empty($row['h1'])){
            $this->PageUser->h1 = $name;
        }else{
            $this->PageUser->h1 = stripslashes($row['h1']);;
        }
        $this->PageUser->title = '';
        $this->PageUser->breadcrumb = $this->ShowPathToLevel($this->id_cat, NULL, 1);
        ?>
    <div class="body">
        <div class="short-desc">
            <?=$row['short']?>
        </div>
        <!-- display image start-->
        <?
        if (isset($row_img['0']['id'])) {
            if (empty($id_img)) $id_img = $row_img[0]['id'];
            $path = "http://" . NAME_SERVER . $settings['img_path'] . "/" . $this->id . "/" . $row_img[0]['path'];
            $alt = htmlspecialchars(stripslashes($row_img[0]['alt']));
            $title = htmlspecialchars(stripslashes($row_img[0]['title']));
            ?>

            <div class="images-prop">

                    <?
                    //echo SITE_PATH.$settings['img_path']."/".$this->id.'/ex-size-597'.$row_img[0]['path'];
                    if(!is_file(SITE_PATH.$settings['img_path']."/".$this->id.'/ex-size597'.$row_img[0]['path'])){
                        $imageG = ImageK::factory(SITE_PATH.$settings['img_path']."/".$this->id."/".$row_img[0]['path'])->resize(597, NULL, ImageK::WIDTH)->save(SITE_PATH.$settings['img_path']."/".$this->id.'/ex-size597'.$row_img[0]['path']);
                    }
                    //$imgSmall = ImageK::getResizedImg($this->getPictureRelPath($this->id, $row_img[0]['path']), 'size_width=597', 85, NULL);

                $cnt = count($row_img);

                if($cnt==1){
                    $cls = "one-img";
                }?>

                <div class="full-prop-img <?=$cls?>">
                    <img src="<?=$settings['img_path']."/".$this->id.'/ex-size597'.$row_img[0]['path'];?>" alt="<?=$alt;?>" title="<?=$title?>"/>
                </div>
                <?$cnt = count($row_img);

                    if($cnt>1){
                ?>
                <div class="thumb">
                    <?
                    $cnt = count($row_img);
                    for ($i = 0; $i < $cnt; $i++) {
                        if($i==4) break;
                        $path = "http://" . NAME_SERVER . $settings['img_path'] . "/" . $this->id . "/" . $row_img[$i]['path'];
                        $alt = htmlspecialchars(stripslashes($row_img[$i]['alt']));
                        $title = htmlspecialchars(stripslashes($row_img[$i]['title']));

                        if(!is_file(SITE_PATH.$settings['img_path']."/".$this->id.'/ex-size597'.$row_img[$i]['path'])){
                            $imageG = ImageK::factory(SITE_PATH.$settings['img_path']."/".$this->id."/".$row_img[$i]['path'])->resize(597, NULL, ImageK::WIDTH)->save(SITE_PATH.$settings['img_path']."/".$this->id.'/ex-size597'.$row_img[$i]['path']);
                        }
                        if(!is_file(SITE_PATH.$settings['img_path']."/".$this->id.'/ex-size101'.$row_img[$i]['path'])){
                            $imageG = ImageK::factory(SITE_PATH.$settings['img_path']."/".$this->id."/".$row_img[$i]['path'])->resize(101, NULL,ImageK::WIDTH)->save(SITE_PATH.$settings['img_path']."/".$this->id.'/ex-size101'.$row_img[$i]['path']);
                        }
                        ?>
                        <img class="min-slide" src="<?=$settings['img_path']."/".$this->id.'/ex-size101'.$row_img[$i]['path'];?>" data-img="<?=$settings['img_path']."/".$this->id.'/ex-size597'.$row_img[$i]['path'];?>">
                        <?
                    }
                    ?>
                </div>
                <?}?>

            </div>


            <?

        } else {

        }
        ?>
        <!--display image end-->


        <!-- fullDescr-->

    <!--TovarDetail-->
    <div class="clear"></div>
    <div class="prop-param">
        <?
        $this->ShowParamsOfProp($this->id);
        ?>


    </div>
        <div class="fullDesc">
            <!-- description -->
            <?
            /*  if ( isset($settings['short_descr']) AND $settings['short_descr']=='1' ) {
                      $val = stripslashes($row['short']);
                      if ( !empty($val) ) {
                          ?><h3><?=$this->Msg->show_text('FLD_SHORT_DESCR');?></h3>
                          <p><?=$val;?></p><?
                      }
                  } */
            if (isset($settings['full_descr']) AND $settings['full_descr'] == '1') {
                $val = stripslashes($row['full']);
                if (!empty($val)) {
                    ?>
                    <?=$val;?><?
                }
            }


            ?>
        </div>
        <?
        $arr_fiels = $this->GetFiles($this->id, $front_back = 'front');
        //print_r($arr_fiels);
        $cnt = count($arr_fiels);
        if ($cnt > 0) {
            ?>
        <div class="file-prop">
            <div class="h-files">Сертификаты:</div>
            <?
            for ($i = 0; $i < $cnt; $i++) {
                $files = $arr_fiels[$i];
                $file_filename = stripslashes($files['path']);
                $file_path = 'http://' . NAME_SERVER . Catalog_Upload_Files_Path . '/' . $this->id . '/' . $file_filename;
                $file_title = stripslashes($files['name']);
                $file_text = stripslashes($files['text']);
                if (!empty($file_title)) $file_name = $file_title;
                else $file_name = $file_filename;
                ?><a href="<?=$file_path;?>" title="<?=$file_name;?>"><?=$file_name;?></a><?

                ?><br/><?
            }
            ?>
        </div>

            <?
        }






        $this->id_img = NULL;
        ?>
    </div>
    <?

    } //end of function ShowDetailsCurrentPosition()


    // ================================================================================================
    // Function : ShowPrintVersion()
    // Version : 1.0.0
    // Date : 23.07.2008
    // Parms :
    // Returns : true,false / Void
    // Description : show print version of page
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 23.07.2008
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowPrintVersion()
    {
        $title = NULL;
        $description = NULL;
        $keywords = NULL;
        ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <!-- <html xmlns="http://www.w3.org/1999/xhtml" lang="ru" xml:lang="ru"> -->
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta http-equiv='Content-Type' content="application/x-javascript; charset=utf-8"/>
        <meta http-equiv="Content-Language" content="ru"/>
        <title>$title</title>
        <meta name="Description" content="<?=$description;?>"/>
        <meta name="Keywords" content="<?=$keywords;?>"/>
        <link href="/include/css/main.css" type="text/css" rel="stylesheet"/>
        <link href="/include/css/screen.css" type="text/css" rel="stylesheet" media="screen"/>
        <!--[if IE ]>
        <link href="/include/css/browsers/ie.css" rel="stylesheet" type="text/css" media="screen"/>
        <![endif]-->
        <!--[if lt IE 8]>
        <link href="/include/css/browsers/ie7.css" rel="stylesheet" type="text/css" media="screen"/>
        <![endif]-->
        <!--[if lt IE 7]>
        <link href="/include/css/browsers/ie6.css" rel="stylesheet" type="text/css" media="screen"/>
        <script type="text/javascript" src="/include/js/iepngfix_tilebg.js"></script>
        <![endif]-->
        <!--[if lt IE 6]>
        <script src="/include/js/ie5.js" type="text/javascript"></script>
        <![endif]-->
    </head>

    <body style="background-color: white;">
        <?
        $settings = $this->GetSettings();
        $q = "SELECT
                `" . TblModCatalogProp . "`.id,
                `" . TblModCatalogProp . "`.id_cat,
                `" . TblModCatalogProp . "`.id_manufac,
                `" . TblModCatalogProp . "`.number_name,
                `" . TblModCatalogProp . "`.price,
                `" . TblModCatalogProp . "`.opt_price,
                `" . TblModCatalogProp . "`.art_num,
                `" . TblModCatalogProp . "`.barcode,
                `" . TblModCatalogPropSprName . "`.name
             FROM `" . TblModCatalogProp . "`, `" . TblModCatalogPropSprName . "`
             WHERE
                `" . TblModCatalogProp . "`.id = `" . TblModCatalogPropSprName . "`.cod
             AND
                `" . TblModCatalogPropSprName . "`.lang_id='" . $this->lang_id . "'
             AND
                `" . TblModCatalogProp . "`.id ='" . $this->id . "'";

        $res = $this->db->db_Query($q);
        //echo '<br> $q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
        if (!$res or !$this->db->result) return false;

        $rows = $this->db->db_GetNumRows();
        $row = $this->db->db_FetchAssoc();
        $row_img = $this->GetPicture($row['id']);
        $row_files = $this->GetFiles($row['id']);
        $name = stripslashes($row['name']);
        ?>
    <h1 class="bgrnd"><?=$name;?></h1>

    <div class="subBody">
        <div class="path"><?$this->ShowPathToLevel($this->id_cat, NULL, 1);?></div>
        <div class="tovarImage floatToLeft">
            <!-- display image start-->
            <?
            if (isset($row_img['0']['id'])) {
                if (empty($id_img)) $id_img = $row_img['0']['id'];
                $path = "http://" . NAME_SERVER . $settings['img_path'] . "/" . $this->id . "/" . $row_img['0']['path'];
                ?>
                <div class="floatToLeft"><a href="<?=$path;?>" rel="itemImg" title="<?=$name;?>"
                                            target="_blank"><?=$this->ShowCurrentImage($id_img, 'size_auto=300', 85, NULL, "");?></a>
                </div>
                <div id="thumb">
                    <?
                    $cnt = count($row_img);
                    for ($i = 1; $i < $cnt; $i++) {
                        $path = "http://" . NAME_SERVER . $settings['img_path'] . "/" . $this->id . "/" . $row_img[$i]['path'];
                        ?>
                        <a href="<?=$path;?>" rel="itemImg" title="<?=$name;?>"
                           target="_blank"><?=$this->ShowCurrentImage($row_img[$i]['id'], 'size_auto=50', 85, NULL, "");?></a>
                        <br/>
                        <?
                    }
                    ?>
                </div>
                <script type="text/javascript">
                    $("a[rel='itemImg']").colorbox();
                </script>
                <?

            } else {
                ?><img src="/images/design/no-photo<?=_LANG_ID;?>.gif" alt="no-photo" title="no-photo" border="0"/><?
            }
            ?>
            <!--display image end-->
        </div>

        <div class="tovarDetail">
            <?
            echo $this->Spr->GetNameByCod(TblModCatalogPropSprShort, $this->id, $this->lang_id, 1);
            if (!empty($row['art_num'])) {
                ?><br/><?= $this->multi['FLD_ART_NUM']
                ; ?> <?=
                stripslashes($row['art_num'])
                ;
            }
            if (!empty($row['barcode'])) {
                ?><br/><?= $this->multi['FLD_BARCODE']
                ; ?> <?=
                stripslashes($row['barcode'])
                ;
            }

            if (isset($settings['price']) AND $settings['price'] == '1') {
                $price = $this->Currency->Converting($this->GetPriceCurrency($row['id']), _CURR_ID, $row['price'], 2);
                ?>
                <span class="price"><?=$this->Currency->ShowPrice($price);?></span>
                <br/>
                <?
            }
            ?>
        </div>
        <hr/>

        <div class="fullDesc">
            <!-- description -->
            <?
            if (isset($settings['full_descr']) AND $settings['full_descr'] == '1') {
                $val = $this->Spr->GetNameByCod(TblModCatalogPropSprFull, $this->id, $this->lang_id, 1);
                if (!empty($val)) {
                    ?><h3><?=$this->multi['FLD_FULL_DESCR'];?></h3>
                    <div><?=$val;?></div><?
                }
            }
            ?>
            <!-- description -->
            <?
            if (isset($settings['full_descr']) AND $settings['full_descr'] == '1') {
                $val = $this->Spr->GetNameByCod(TblModCatalogPropSprSpecif, $this->id, $this->lang_id, 1);
                if (!empty($val)) {
                    ?><h3><?=$this->multi['FLD_SPECIF'];?></h3>
                    <div><?=$val;?></div>
                    <hr/>
                    <?
                }
            }
            ?>
        </div>
    </div>
    <a href="javascript:window.close()"><u><?=$this->multi['TXT_CLOSE'];?></u></a>

    </body>
    </html>

    <?
    } // end of function ShowPrintVersion()


    // ================================================================================================
    // Function : GetLinksToParamsNames ()
    // Version : 1.0.0
    // Programmer : Yaroslav Gyryn
    // Date : 15.06.2009
    // Parms :   $id_cat         // id of current category
    // Returns : str
    // Description : return names & values of parameters in string for current catalogue
    // ================================================================================================
    function GetLinksToParamsNames($id_cat, $spacer = ' - ', $showLink = true)
    {
        // echo '<br>$params='.$params.' $id_cat='.$id_cat;
        //if ( $params==0 ) return;
        $str = NULL;
        $params_row = $this->GetParams($id_cat);
        $link = $this->Link($id_cat);
        $param = NULL;
        //echo '<br>$params_row=';print_r($params_row);

        for ($i = 0; $i < count($params_row); $i++) {
            if ($params_row[$i]['modify'] != 1)
                continue;
            $paramCategory = $this->Spr->GetNameByCod(TblModCatalogParamsSprName, ($params_row[$i]['id']), $this->lang_id, 1);
            if ($paramCategory != 'РџСЂРѕРёР·РІРѕРґРёС‚РµР»СЊ')
                continue;
            $val = NULL;
            $str .= '';
            //echo '<br>$params_row['.$i.'][id]='.$params_row[$i]['id'];
            $tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);
            switch ($params_row[$i]['type']) {
                case '2':
                    $val = $this->Spr->GetListName(TblSysLogic, $this->lang_id, 'array', 'move', 'asc', 'all');
                    break;
                case '3':
                case '4':
                    $val = $this->Spr->GetListName($tblname, $this->lang_id, 'array', 'move', 'asc', 'all');
                    break;
            }

            //$prefix = $this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($params_row[$i]['id']), $this->lang_id, 1);
            //$sufix = $this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($params_row[$i]['id']), $this->lang_id, 1);
            //echo '<br> $val='.$val;print_r($val);
            if (is_array($val))
                foreach ($val as $k => $v) {
                    // Р¤РѕСЂРјР°С‚РёСЂРѕРІР°РЅРЅС‹Р№ РІС‹РІРѕРґ С‚РµРєСЃС‚Р° Р»РёР±Рѕ СЃСЃС‹Р»РєРё РїР°СЂР°РјРµС‚СЂР°
                    if ($str == '') {
                        if ($showLink)
                            $str = ' <a href="' . $link . '?' . PARAM_VAR_NAME . PARAM_VAR_SEPARATOR . $params_row[$i]['id'] . '=' . $v['cod'] . '">' . $v['name'] . '</a>';
                        else
                            $str = ' ' . $v['name'];
                    } else {
                        if ($showLink)
                            $str .= $spacer . '<a href="' . $link . '?' . PARAM_VAR_NAME . PARAM_VAR_SEPARATOR . $params_row[$i]['id'] . '=' . $v['cod'] . '">' . $v['name'] . '</a>';
                        else
                            $str .= $spacer . $v['name'];
                    }
                }
            $str .= ' ';
        }
        return $str;
    } //end of function GetLinksToParamsNames ()


    // ================================================================================================
    // Function : GetParamsNamesValuesOfPropInStr()
    // Version : 1.0.0
    // Programmer : Yaroslav Gyryn
    // Date : 15.06.2009
    // Parms :   $id_cat         // id of current category
    // Returns : str
    // Description : return names & values of parameters in string for current catalogue
    // ================================================================================================
    function GetParamsNamesValuesOfPropInStr($id_cat)
    {
        //$params = $this->IsParams( $id_cat );
        // echo '<br>$params='.$params.' $id_cat='.$id_cat;
        //if ( $params==0 ) return;
        $str = NULL;
        $params_row = $this->GetParams($id_cat);
        $link = $this->Link($this->id_cat);
        $param_str = NULL;
        $this->url_param = NULL;
        $param = NULL;
        $filtr = NULL;
        $sorting = '';
        //echo '<br>$params_row=';print_r($params_row);
        if (!empty($this->sort)) {
            $sorting = '&sort=' . $this->sort . '&asc_desc=' . $this->asc_desc . '&exist=' . $this->exist;
        }
        $n = count($params_row);
        for ($i = 0; $i < $n; $i++) {
            if ($params_row[$i]['modify'] != 1) continue;
            $val = NULL;
            $paramName = $this->Spr->GetNameByCod(TblModCatalogParamsSprName, ($params_row[$i]['id']), $this->lang_id, 1);
            /*if($paramName=="РџСЂРѕРёР·РІРѕРґРёС‚РµР»СЊ")
                continue;*/
            $str .= '<div class="paramBlock"><div class="paramName">' . $paramName . ':</div>';
            //echo '<br>$params_row['.$i.'][id]='.$params_row[$i]['id'];
            $tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);
            switch ($params_row[$i]['type']) {
                case '1':
                    //$val = $v;
                    break;
                case '2':
                    $val = $this->Spr->GetListName(TblSysLogic, $this->lang_id, 'array', 'move', 'asc', 'all');
                    break;
                case '3':
                case '4':
                    $val = $this->Spr->GetListName($tblname, $this->lang_id, 'array', 'move', 'asc', 'all');
                    break;
                /*  case '5':
                        $val = $v;
                        break;*/
            }

            $prefix = $this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix, ($params_row[$i]['id']), $this->lang_id, 1);
            $sufix = $this->Spr->GetNameByCod(TblModCatalogParamsSprSufix, ($params_row[$i]['id']), $this->lang_id, 1);
            //echo '<br> $val='.$val;print_r($val);
            $str .= '<div class="paramKey">';
            if (is_array($val)) {
                $showAll = false;

                // Р¤РѕСЂРјРёСЂРѕРІР°РЅРёРµ СЃС‚СЂРѕРєРё РїР°СЂР°РјРµС‚СЂРѕРІ
                //print_r($this->arr_current_img_params_value);
                if (is_array($this->arr_current_img_params_value)) {
                    $param_str = NULL;
                    //echo' <br>$params_row[$i][id] ='.$params_row[$i]['id'];
                    foreach ($this->arr_current_img_params_value as $key => $value) {
                        //echo' $key ='.$key;
                        if ($key != $params_row[$i]['id']) {
                            $param = '&' . PARAM_VAR_NAME . PARAM_VAR_SEPARATOR . $key . '=' . $value;
                            $param_str .= $param;
                            if (substr_count($this->url_param, $param) == 0)
                                $this->url_param .= $param;
                        }
                    }
                }

                foreach ($val as $k => $v) {

                    // РџСЂРѕРІРµСЂРєР° РёР»Рё РІС‹Р±СЂР°РЅ РєРѕРЅРєСЂРµС‚РЅС‹Р№ РїР°СЂР°РјРµС‚СЂ
                    $checked = false;
                    if (is_array($this->arr_current_img_params_value))
                        foreach ($this->arr_current_img_params_value as $key => $value)
                            if ($key == $params_row[$i]['id'] AND $value == $v['cod']) {
                                $checked = true;
                                break;
                            }

                    // Р¤РѕСЂРјР°С‚РёСЂРѕРІР°РЅРЅС‹Р№ РІС‹РІРѕРґ С‚РµРєСЃС‚Р° Р»РёР±Рѕ СЃСЃС‹Р»РєРё РїР°СЂР°РјРµС‚СЂР°
                    if ($checked == true) {
                        $str .= '<span class="paramSelected">' . $prefix . ' ' . $v['name'] . ' ' . $sufix . '</span> | ';
                        $showAll = true;
                    } else if ($param_str != NULL)
                        $str .= '<a href="' . $link . '?' . $param_str . '&' . PARAM_VAR_NAME . PARAM_VAR_SEPARATOR . $params_row[$i]['id'] . '=' . $v['cod'] . $sorting . '">' . $prefix . ' ' . $v['name'] . ' ' . $sufix . '</a> | ';
                    else
                        $str .= '<a href="' . $link . '?' . PARAM_VAR_NAME . PARAM_VAR_SEPARATOR . $params_row[$i]['id'] . '=' . $v['cod'] . $sorting . '">' . $prefix . ' ' . $v['name'] . ' ' . $sufix . '</a> | ';
                }

                // Р’С‹РІРѕРґ СЃСЃС‹Р»РєРё "Р’СЃРµ"
                if ($showAll == true) {
                    if ($param_str != NULL)
                        $str .= '<a href="' . $link . '?' . $param_str . $sorting . '">Р’СЃРµ</a>';

                    else
                        $str .= '<a href="' . $link . '?' . $sorting . '">Р’СЃРµ</a>';
                    $filtr = true;
                } else
                    $str .= '<span class="param_all">Р’СЃРµ</span>';
            }

            $str .= '</div></div><div class="next_line"></div>';
        }
        if ($filtr)
            $str .= '<div class="paramClear" align="right"><a href="' . $link . '?' . $sorting . '"<img src="/images/design/paramClearBtn.gif"</a></div>';
        return $str;
    } //end of function GetParamsNamesValuesOfPropInStr()

    // ================================================================================================
    // Function : ShowParamsOfProp()
    // Version : 1.0.0
    // Date : 21.04.2006
    // Parms :
    // Returns : true,false / Void
    // Description : show details parameters of curent position of catalogue on the front-end
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 08.04.2006
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowParamsOfProp($id)
    {
        //--------------------------------------------------------------------------------------------------
        //------------------------------------ SHOW PARAMETERS ---------------------------------------------
        //--------------------------------------------------------------------------------------------------
        $this->id = $id;

        $params = $this->IsParams($this->id_cat);
        if ($params == 0) return true;
        ?>
    <table border="0" cellspacing="0" cellpadding="0" class="p-param">
        <tr><td colspan="2" class="grey">Основные характеристики системы</td></tr>
        <?

        $style1 = 't';
        $style2 = 't2';
        $params_row = $this->GetParams($this->id_cat);
        $value = $this->GetParamsValuesOfProp($this->id);
        for ($i = 0; $i < count($params_row); $i++) {

            if ((float)$i / 2 == round($i / 2)) {
                echo '<TR CLASS="' . $style1 . '">';
            } else echo '<TR CLASS="' . $style2 . '">';

            isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;
            if ($id != NULL) $this->Err != NULL ? $val = $this->arr_params[$params_row[$i]['id']] : $val = $val_from_table;
            else $val = $this->arr_params[$params_row[$i]['id']];
            if (count($val) == 0 OR empty($val)) continue;

            ?>
            <td class="first"><?=stripslashes($params_row[$i]['name']);?><?
            ?><td><?
            $tblname = TblModCatalogParamsVal; //$this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);
            //echo '<br> $tblname='.$tblname;

            switch ($params_row[$i]['type']) {
                case '1':
                    ?>
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td><?=stripslashes($params_row[$i]['prefix']);?></td>
                                <td><?=$val . stripslashes($params_row[$i]['sufix']);?>
                            </tr>
                        </table>
                        <?
                    //$this->Form->TextBox( 'arr_params['.$params_row[$i]['id'].']', $val, 15 );
                    break;
                case '2':
                    ?>
                        <table>
                            <tr>
                                <td><?=stripslashes($params_row[$i]['prefix']);?></td>
                                <td><?=$this->Spr->GetNameByCod(TblSysLogic, $val, $this->lang_id, 1) . stripslashes($params_row[$i]['sufix']);?>
                            </tr>
                        </table>
                        <?
                    //$this->Spr->ShowInComboBox( TblSysLogic, 'arr_params['.$params_row[$i]['id'].']', $val, 50 );
                    break;
                case '3':
                    ?>
                        <table>
                            <tr>
                                <td><?=stripslashes($params_row[$i]['prefix']);?></td>
                                <td><?=strip_tags($this->GetNameOfParamVal($params_row[$i]['id_categ'], $params_row[$i]['id'], $val, $this->lang_id, 1));?>
                                    <?=stripslashes($params_row[$i]['sufix']);?>
                            </tr>
                        </table>
                        <?
                    //$this->Spr->ShowInComboBox( $tblname, 'arr_params['.$params_row[$i]['id'].']', $val, 50 );
                    break;
                case '4':
                    //echo '<br> count($val)='.count($val);
                    //if ( count($val)==0 ) {
                    ?>
                        <table>
                            <tr>
                                <td><?=stripslashes($params_row[$i]['prefix']);?></td>
                                <td><?=$this->GetNameOfParamMultiplesVal($params_row[$i]['id_categ'], $params_row[$i]['id'], $val, $this->lang_id, 1);?>
                                <td><?=stripslashes($params_row[$i]['sufix']);?>
                            </tr>
                        </table>
                        <?
                    //}
                    //echo $this->Spr->GetNameByCod($tblname,$val, $this->lang_id, 1);
                    break;
                case '5':
                    ?>
                        <table>
                            <tr>
                                <td><?=stripslashes($params_row[$i]['prefix']);?></td>
                                <td><?=$val;?>
                                    <?=stripslashes($params_row[$i]['sufix']);?>
                            </tr>
                        </table>
                        <?
                    //$this->Form->TextBox( 'arr_params['.$params_row[$i]['id'].']', $val, 40 );
                    break;
            }
        }
        ?></table><?
        //--------------------------------------------------------------------------------------------------
        //---------------------------------- END SHOW PARAMETERS -------------------------------------------
        //--------------------------------------------------------------------------------------------------
    } // end of function ShowParamsOfProp()

    // ================================================================================================
    // Function : GetParamsValuesOfPropInTable()
    // Version : 1.0.0
    // Date : 18.04.2006
    // Parms :   $id         / id of curent position
    //           $divider    / symbol to divide parameters one from one. (default defider is <br>)
    //           $id_img     / id of the image (for image influence on parameters)
    // Returns : true,false / Void
    // Description : return values of parameters in string for current position of catalogue
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 18.04.2006
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function GetParamsValuesOfPropInTable($id, $id_img = NULL)
    {
        $id_cat = $this->GetCategory($id);
        $params = $this->IsParams($id_cat);
        if ($params == 0) return;

        $params_row = $this->GetParams($id_cat);
        $value = $this->GetParamsValuesOfProp($id);
        $str = NULL;
        ?>
    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td></td>
        </tr>
        <?
        $j = 0;
        for ($i = 0; $i < count($params_row); $i++) {
            $tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);

            if (!empty($id_img)) {
                $value_param_img = $this->GetParamsValuesOfPropForImg($id_img, $params_row[$i]['id']);
                //echo '<br> $value_param_img='; print_r($value_param_img);
                isset($value_param_img[$params_row[$i]['id']]) ? $val_from_table = $value_param_img[$params_row[$i]['id']] : $val_from_table = NULL;
                if (empty($val_from_table)) {
                    isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;
                }
            } else {
                isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;
            }
            $val = $val_from_table;

            //echo '<br> $val='.$val;

            $prefix = stripslashes($params_row[$i]['prefix']);
            $sufix = stripslashes($params_row[$i]['sufix']);
            switch ($params_row[$i]['type']) {
                case '1':
                    $val = $val;
                    break;
                case '2':
                    $val = $this->Spr->GetNameByCod(TblSysLogic, $val, $this->lang_id, 1);
                    break;
                case '3':
                    $val = $this->Spr->GetNameByCod($tblname, $val, $this->lang_id, 1);
                    break;
                case '4':
                    $val = $this->Spr->GetNamesInStr($tblname, _LANG_ID, $val, ',');
                    break;
                case '5':
                    $val = str_replace("\n", "<br>", $val);
                    break;
            }
            if (empty($val)) continue;
            $j++;
            ?>
            <tr>
                <td><?=stripslashes($params_row[$i]['name']);?>:&nbsp;<?=$prefix;?></td>
                <td><img src="/images/design/spacer.gif" width="5" alt="" title=""/></td>
                <td><?=$val . ' ' . $sufix;?></td>
            </tr><?
        }
        ?></table><?
        if ($j == 0) return false;
        //echo '<br> $str='.$str;
    } //end of function  GetParamsValuesOfPropInTable()


// ================================================================================================
// Function : ShowSearchForm()
// Version : 1.0.0
// Date : 05.04.2006
// Parms :
// Returns : true,false / Void
// Description : show search form of catalogue on the front-end
// ================================================================================================
// Programmer : Igor Trokhymchuk
// Date : 05.04.2006
// Reason for change : Reason Description / Creation
// Change Request Nbr:
// ================================================================================================
    function ShowSearchForm()
    {
        ?>
    <h1 class="bgrnd"><?=$this->multi['TXT_SEARCH_CATALOG'];?></h1>
    <div class="body">
        <form name="quick_find" method="post" action="<?=_LINK?>catalog/search/result/">
            <input type="hidden" name="task" value="quick_search">
            <!--input type="hidden" name="categ" value=""-->

            <?if (!empty($this->search_keywords))
            $value = $this->search_keywords;
        else
            $value = 'РЈРєР°Р¶РёС‚Рµ РЅР°РёРјРµРЅРѕРІР°РЅРёРµ';
            ?>
            <div>
                <input type="text" onblur="if(this.value=='') { this.value='РЈРєР°Р¶РёС‚Рµ РЅР°РёРјРµРЅРѕРІР°РЅРёРµ'; }"
                       onfocus="if(this.value=='РЈРєР°Р¶РёС‚Рµ РЅР°РёРјРµРЅРѕРІР°РЅРёРµ') { this.value=''; }"
                       name="search_keywords" value="<?=$value;?>" size="50" maxlength="50">
                <input type="submit" title="<?=$this->multi['TXT_SEARCH'];?>" value="<?=$this->multi['TXT_SEARCH'];?>">
            </div>
        </form>
    </div>
    <?
        return true;
    } //end of function ShowSearchForm()


// ================================================================================================
// Function : ShowSearchResult()
// Version : 1.0.0
// Date : 25.04.2006
// Parms :  $rows - rows with data of result of search
// Returns : true,false / Void
// Description : show all images of current position of catalogue
// ================================================================================================
// Programmer : Igor Trokhymchuk
// Date : 25.04.2006
// Reason for change : Reason Description / Creation
// Change Request Nbr:
// ================================================================================================
    function ShowSearchResult($rows, $search_keywords = NULL)
    {
        ?>
    <div class="catalogBorder">
        <div class="categoryContent">
            <div class="categoryCaptionRed">
                <div class="categoryTxt"></div>
            </div>
            <?
            //$this->ShowListOfContentByPages($rows, $search_keywords);
            $this->ShowListShortByPages($rows, $search_keywords);
            ?>
        </div>
    </div>
    <?
    } //end of function ShowSearchResult()


    /**
     * Class method ShowCatalogMap
     * show catalog map for sitemap
     * @param $topLevel - level of category
     * @return true/false
     * @author Yaroslav Gyryn  <yaroslav@seotm.com>
     * @version 1.0, 17.01.2011
     */
    function ShowCatalogMap($topLevel = 0)
    {
        if (!isset($this->treeCatLevels[$topLevel])) return;
        $a_tree = $this->treeCatLevels[$topLevel];
        ?>
    <ul><?
        $keys = array_keys($a_tree);
        $n = count($keys);
        for ($i = 0; $i < $n; $i++) {
            $row = $this->treeCatData[$keys[$i]];
            $href = $this->getUrlByTranslit($row['path']);
            $name = stripslashes($row['name']);
            ?>
            <li><a href="<?=$href;?>"><?=$name;?></a><?
                $this->ShowCatalogMap($row['id']);

                //----------------- show content of the level ----------------------
//                if (array_key_exists($row['id'], $this->catalogProducts)) {
//                    ?>
<!--                    <ul>--><?//
//                        $keys2 = array_keys($this->catalogProducts[$row['id']]);
//                        $n2 = count($keys2);
//                        //foreach($this->catalogProducts[$row['id']] as $k=>$v){
//                        for ($j = 0; $j < $n2; $j++) {
//                            $v = $this->catalogProducts[$row['id']][$keys2[$j]];
//                            $link = $this->getUrlByTranslit($row['path'], $v['translit']);
//                            $name = stripslashes($v['name']);
//                            if (!empty($name)) {
//                                ?>
<!--                                <li><a href="--><?//=$link;?><!--" title="--><?//=$name?><!--">--><?//=$name;?><!--</a>--><?//
//                            }
//                        }
//                        ?><!--</ul>--><?//
//                }
                //------------------------------------------------------------------
                ?></li><?
        }
        ?></ul><?
    }

    // end of function ShowCatalogMap()


    // ================================================================================================
    // Function : ShowErr()
    // Version : 1.0.0
    // Date : 10.01.2006
    //
    // Parms :
    // Returns :      true,false / Void
    // Description :  Show errors
    // ================================================================================================
    // Programmer :  Igor Trokhymchuk
    // Date : 10.01.2006
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowErr($txt = NULL)
    {
        if (empty($txt)) $txt = $this->Err;
        if ($txt) {
            echo '
        <table border=0 cellspacing=0 cellpadding=0 class="err" width="98%" align=center>
         <tr><td>' . $txt . '</td></tr>
        </table>';
        }
    } //end of fuinction ShowErr()


    // ================================================================================================
    // Function : ShowLastPositions
    // Version : 1.0.0
    // Date : 14.05.2007
    //
    // Parms :  $rows - count of rows
    // Returns : $res / Void
    // Description : show last positions from catalogue
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 14.05.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowLastPositions($rows)
    {

        if (count($rows) == 0 or !is_array($rows)) return false;
        $settings = $this->GetSettings();
        $cols_in_row = 2;
        //echo '<br> count($rows)='.count($rows);
        //print_r($rows);

        ?>
    <table border="0" cellspacing="0" cellpadding="0">
    <tr>
        <?
        $j = 0;
        $i = 0;
        foreach ($rows as $key => $value) {
            $i++;
            $img = $this->GetFirstImgOfProp($value['id']);

            if ($j == $cols_in_row) {
                ?></tr><tr valign="top"><?
                $j = 0;
            }
            $name = $value['name'];

            // for folders links
            if ($this->mod_rewrite == 1) $link = $this->Link($value['id_cat'], NULL);
            else $link = "catalogcat_" . $value['id_cat'] . "_" . $this->lang_id . ".html";

            //count($rows)>2 ? $width="34%" : $width="50%";
            ?>

            <td>
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td>
                            <a href="<?=$link;?>" title="<?=addslashes($name);?>">
                                <?
                                if (!empty($img)) {
                                    echo $this->ShowCurrentImage($img, 'size_auto=150', '85', NULL, "border=0");
                                }
                                ?>
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
            <?
            $j++;
        } //end foreach

        ?>
    </tr>
    </table>
    <?
    } //end of function ShowLastPositions()

    // ================================================================================================
    // Function : ShowRelatCategs()
    // Version : 1.0.0
    // Date : 07.05.2007
    // Parms :
    // Returns : true,false / Void
    // Description : show relation categories for current category
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 07.05.2007
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowRelatCategs($arr)
    {
        if (!is_array($arr) OR count($arr) == 0) return false;
        $col_in_row = 3;
        count($arr) == 1 ? $width = "100%" : (count($arr) > 2 ? $width = "33%" : $width = "50%");

        ?>
    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td><h3>Р РЋР ??Р С•РЎвЂљРЎР‚Р С‘РЎвЂљР Вµ РЎвЂљР В°Р С”Р В¶Р Вµ:</h3></td>
        </tr>
    <tr>
        <?
        $i = 0;
        foreach ($arr as $key => $value) {
            if ($i == $col_in_row) {
                ?></tr><tr><?
                $i = 0;
            }
            if ($value['id_cat1'] == $this->id_cat) $id_relat_cat = $value['id_cat2'];
            else $id_relat_cat = $value['id_cat1'];
            $str = $this->GetPathToLevel($id_relat_cat);
            ?>
            <td width="<?=$width;?>" align="center" valign="middle">
                <?=$str;?>
                <?
                $this->ShowRandomContent($this->GetRandomContent2($id_relat_cat, 1, 100000));?>
            </td>
            <?
            $i++;
        }
        ?>
    </tr>
    </table>
    <?
    } //end of function ShowRelatCategs()


    // ================================================================================================
    // Function : ShowRelatProp()
    // Version : 1.0.0
    // Date : 14.05.2007
    // Parms :
    // Returns : true,false / Void
    // Description : show relation positiona for current positionf of catalog
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 07.05.2007
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowRelatProp($arr)
    {
        //echo '<br>$arr='; print_r($arr);
        if (!is_array($arr) OR count($arr) == 0) return false;
        $col_in_row = 2;
        count($arr) == 1 ? $width = "100%" : (count($arr) > 2 ? $width = "33%" : $width = "50%");
        ?>
    <h3>Р РЋР ??Р С•РЎвЂљРЎР‚Р С‘РЎвЂљР Вµ РЎвЂљР В°Р С”Р В¶Р Вµ:</h3>
    <table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <?
        $i = 0;
        foreach ($arr as $key => $value) {
            if ($i == $col_in_row) {
                ?></tr><tr><?
                $i = 0;
            }
            //echo '<br>$value[id_prop1]='.$value['id_prop1'].' $value[id_prop2]='.$value['id_prop2'].' $this->id='.$this->id;
            if ($value['id_prop1'] == $this->id) $id_relat = $value['id_prop2'];
            else $id_relat = $value['id_prop1'];
            //echo '<br>$id_relat='.$id_relat;
            //$str = $this->GetPathToLevel($id_relat_prop);
            ?>
            <td width="<?=$width;?>" align="center" valign="bottom">
                <?/*<div align="center"><?=$this->GetPathToLevel($this->GetCategory($id_relat), ' -> ', NULL).' -> '.$this->Spr->GetNameByCod(TblModCatalogPropSprName, $id_relat);?></div>*/?>
                <div align="center"><?=$this->Spr->GetNameByCod(TblModCatalogPropSprName, $id_relat);?></div>
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" valign="bottoom">
                            <?
                            // for folders links
                            if ($this->mod_rewrite == 1) $link = $this->Link($this->GetCategory($id_relat), $id_relat);
                            else $link = "catalog_" . $this->GetCategory($id_relat) . "_" . $id_relat . "_" . $this->lang_id . ".html";
                            ?>
                            <a href="<?=$link;?>">
                                <?
                                $img = $this->GetFirstImgOfProp($id_relat);
                                if (!empty($img)) echo $this->ShowCurrentImage($img, 'size_auto=150', '85', NULL, 'border=0');
                                else echo $this->Spr->GetNameByCod(TblModCatalogPropSprName, $id_relat);
                                ?>
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
            <?
            $i++;
        }
        ?>
    </tr>
    </table>
    <?
    } //end of function ShowRelatProp()


    // ================================================================================================
    // Function : ShowResponsesByIdProp()
    // Version : 1.0.0
    // Date : 08.08.2007
    // Parms : $id_prop - id of the position
    // Returns : true,false / Void
    // Description : show form with responses from users about goods
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 08.08.2007
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowResponsesByIdProp($id_prop)
    {
        $tmp_db = DBs::getInstance();
        if (empty($id_prop)) return;

        $q = "SELECT * FROM `" . TblModCatalogResponse . "` WHERE `id_prop`='$id_prop' AND `status`='3' order by `dt` desc";
        $res = $tmp_db->db_Query($q);
        //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
        if (!$res) return false;
        if (!$tmp_db->result) return false;
        $rows = $tmp_db->db_GetNumRows();
        if ($rows > 0) {
            ?>
        <h2><?=$this->Msg->show_text('TXT_FRONT_USERS_RESPONSES');?></h2>
        <table border="0" cellpadding="0" cellspacing="0">
            <?
            for ($i = 0; $i < $rows; $i++) {
                $row = $tmp_db->db_FetchAssoc();
                ?>
                <tr>
                    <td>
                        [<?=$row['dt']?>]&nbsp;<?=stripslashes($row['name']);
                        if ($row['rating'] > 0) {
                            echo $this->Msg->show_text('TXT_FRONT_USER_RATING_IS'); ?><b><?=$row['rating'];?></b><?
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><?=stripslashes($row['response'])?></td>
                </tr>
                <tr>
                    <td height="10"></td>
                </tr>
                <?
            }
            ?>
        </table>
        <?
        }
        return true;
    } //end of function ShowResponsesByIdProp()

    // ================================================================================================
    // Function : ShowResponses()
    // Version : 1.0.0
    // Date : 08.08.2007
    // Parms :
    // Returns : true,false / Void
    // Description : show form with responses from users about goods
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 08.08.2007
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowResponses()
    {
        $tmp_db = DBs::getInstance();
        ?><h1><?=$this->Msg->show_text('TXT_FRONT_USERS_RESPONSES');?></h1><?
        $mas = $this->GetCatalogInArray(NULL, '--- ' . $this->Msg->show_text('TXT_SELECT_POSITIONS') . ' ---', NULL, NULL, 1, 'front');
        $name_fld = 'val';

        $scriplink = '/response.php?task=show_responses'; //'onChange="CheckCatalogPosition(this, this.value, '."'".$this->Msg->show_text('ERR_SELECT_POSITION')."'".'); location='.$scriplink.'&'.$name_fld.'=this.value"'
        ?>
    <div><?$this->Form->SelectAct($mas, $name_fld, 'curcod=' . $this->id, "onChange=\"ret = CheckCatalogPosition(this, this.value, '" . $this->Msg->show_text('ERR_SELECT_POSITION') . "'); if( ret== true) {location='$scriplink&$name_fld='+this.value} \"");?></div><?


        if (empty($this->id)) return;

        $q = "SELECT * FROM `" . TblModCatalogResponse . "` WHERE `id_prop`=$this->id AND `status`='3' order by `dt` desc";
        $res = $tmp_db->db_Query($q);
        //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
        if (!$res) return false;
        if (!$tmp_db->result) return false;
        $rows = $tmp_db->db_GetNumRows();
        ?>
    <table border="0" cellpadding="0" cellspacing="0">
        <?
        if ($rows == 0) {
            ?>
            <tr>
                <td><?=$this->Msg->show_text('TXT_FRONT_NO_RESPONSES');?></td>
            </tr><?
        }
        /*if ($this->task=="save_response") {?><tr><td><?=$this->Msg->show_text('TXT_FRONT_RESPONSES_IS_ADDED');?></td></tr><?}*/
        if ($this->task == "save_response") {
            ?>
            <tr>
                <td><?=$this->Msg->show_text('TXT_FRONT_RESPONSES_IS_ADDED_NOW');?></td>
            </tr><?
        }

        for ($i = 0; $i < $rows; $i++) {
            $row = $tmp_db->db_FetchAssoc();
            ?>
            <tr>
                <td>
                    [<?=$row['dt']?>]&nbsp;<?=stripslashes($row['name']);
                    if ($row['rating'] > 0) {
                        echo $this->Msg->show_text('TXT_FRONT_USER_RATING_IS'); ?><b><?=$row['rating'];?></b><?
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><?=stripslashes($row['response'])?></td>
            </tr>
            <tr>
                <td height="10"></td>
            </tr>
            <?
        }
        ?>
    </table>
        <?= $this->ShowResponseForm()
        ; ?>
    <?
        return true;
    } //end of function ShowResponses()

    // ================================================================================================
    // Function : ShowResponseForm()
    // Version : 1.0.0
    // Date : 08.08.2007
    // Parms :
    // Returns : true,false / Void
    // Description : show form to leave responses and rating
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 08.08.2007
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowResponseForm()
    {
        $settings = $this->GetSettings();
        if (isset($settings['responses']) AND $settings['responses'] == '1') $is_response = true;
        else $is_response = 0;
        if (isset($settings['rating']) AND $settings['rating'] == '1') $is_rating = true;
        else $is_rating = 0;

        // for folders links
        if ($this->mod_rewrite == 1) $link = $this->Link($this->GetCategory($this->id), $this->id, 'response');
        else $link = "leave_comments.html";

        $v1 = rand(1, 9);
        $v2 = rand(1, 9);
        $sum = $v1 + $v2;

        $this->ShowJS();
        $this->Form->WriteFrontHeader('save_response', $link, $task = 'save_response', 'onsubmit="return check_form_response(this, this.my_gen_v.value, ' . $is_response . ', ' . $is_rating . ' );"')
        ?>
    <table border="0" cellpadding="1" cellspacing="0">
        <input type="hidden" name="curcod" value="<?=$this->id?>">
        <input type="hidden" name="my_gen_v" value="<?=$sum;?>"/>
        <tr>
            <td><h2><?=$this->Msg->show_text('TXT_FRONT_LEAVE_RESPONSES');?></h2></td>
        </tr>
        <tr>
            <td>
                <table border="0" cellpadding="2" cellspacing="2">
                    <tr>
                        <td><?=$this->Msg->show_text('TXT_FRONT_USER_NAME');?>:&nbsp;<span
                                class="inputRequirement">*</span></td>
                        <td><?$this->Form->TextBox('name', $this->name, 'size="40"');?></td>
                    </tr>
                    <tr>
                        <td><?=$this->Msg->show_text('TXT_FRONT_USER_EMAIL');?>:&nbsp;<span
                                class="inputRequirement">*</span></td>
                        <td><?$this->Form->TextBox('email', $this->email, 'size="40"');?></td>
                    </tr>
                    <?
                    if ($is_response) {
                        ?>
                        <tr>
                            <td><?=$this->Msg->show_text('TXT_FRONT_USER_RESPONSE');?>:&nbsp;<span
                                    class="inputRequirement">*</span></td>
                            <td><?$this->Form->TextArea('response', $this->response, 9, 60, NULL);?></td>
                        </tr>
                        <? }?>
                    <?
                    if ($is_rating) {
                        ?>
                        <tr>
                            <td><?=$this->Msg->show_text('TXT_FRONT_USER_RATING');?>:&nbsp;<span
                                    class="inputRequirement">*</span></td>
                            <td>
                                <?
                                $this->Form->Radio('rating', 1, "0", "1");?>&nbsp;&nbsp;&nbsp;<?
                                $this->Form->Radio('rating', 2, "0", "2");?>&nbsp;&nbsp;&nbsp;<?
                                $this->Form->Radio('rating', 3, "0", "3");?>&nbsp;&nbsp;&nbsp;<?
                                $this->Form->Radio('rating', 4, "0", "4");?>&nbsp;&nbsp;&nbsp;<?
                                $this->Form->Radio('rating', 5, "0", "5");
                                ?>
                            </td>
                        </tr>
                        <? }?>
                    <tr>
                        <td colspan="2"><b><?=$this->Msg->show_text('TXT_FRONT_SPAM_PROTECTION');?>:&nbsp;<span
                                class="inputRequirement">*</span></b>
                            <b><?=$this->Msg->show_text('TXT_FRONT_SPAM_PROTECTION_SPECIFY_SUM');?>&nbsp;<?=$v1;?>
                                +<?=$v2;?>?</b> <?$this->Form->TextBox('usr_v', NULL, 'size="2"');?></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="left"><span
                                class="inputRequirement">*</span> <?=$this->Msg->show_text('TXT_FRONT_REQUIREMENT_FIELDS');?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td><?$this->Form->Button('save_response', $this->Msg->show_text('TXT_FRONT_ADD_RESPONSE'));?></td>
        </tr>
        </form>
    </table>
    <?
        $this->Form->WriteFrontFooter();
    } //end of function ShowResponseForm()


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
        var form = "";
        var submitted = false;
        var error = false;
        var error_message = "";

        function check_input(field_name, field_size, message) {
            if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
                var field_value = form.elements[field_name].value;

                if (field_value == '' || field_value.length < field_size) {
                    error_message = error_message + "* " + message + "\n";
                    error = true;
                }
            }
        }

        function check_radio(field_name, message) {
            var isChecked = false;

            if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
                var radio = form.elements[field_name];

                for (var i = 0; i < radio.length; i++) {
                    if (radio[i].checked == true) {
                        isChecked = true;
                        break;
                    }
                }

                if (isChecked == false) {
                    error_message = error_message + "* " + message + "\n";
                    error = true;
                }
            }
        }

        function check_select(field_name, field_default, message) {
            if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
                var field_value = form.elements[field_name].value;

                if (field_value == field_default) {
                    error_message = error_message + "* " + message + "\n";
                    error = true;
                }
            }
        }

        function check_antispam(field_name, usr_v, message) {
            if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
                var field_value = form.elements[field_name].value;

                if (field_value == '' || field_value != usr_v) {
                    error_message = error_message + "* " + message + "\n";
                    error = true;
                }
            }
        }

        function check_form_response(form_name, my_gen_v, response, rating) {
            error_message = '';
            if (submitted == true) {
                alert("<?=$this->Msg->show_text('MSG_FRONT_ERR_FORM_ALREADY_SUBMITED');?>");
                return false;
            }

            error = false;
            form = form_name;

            check_input("name", 2, "<?=$this->Msg->show_text('MSG_FRONT_ERR_SPECIFY_YOUR_NAME');?>");
            check_input("email", 2, "<?=$this->Msg->show_text('MSG_FRONT_ERR_SPECIFY_YOUR_EMAIL');?>");
            if (response == true) check_input("response", 5, "<?=$this->Msg->show_text('MSG_FRONT_ERR_SPECIFY_YOUR_RESPONSE');?>");
            if (rating == true) check_radio("rating", "<?=$this->Msg->show_text('MSG_FRONT_ERR_SPECIFY_YOUR_RATING');?>");
            check_antispam("usr_v", my_gen_v, "<?=$this->Msg->show_text('MSG_FRONT_ERR_SPECIFY_ANTISMAP_SUM');?>");

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
    } // end of functin ShowJS()


    // ================================================================================================
    // Function : BestProducts()
    // Date : 01.17.2011
    // Programmer : Yaroslav Gyryn
    // Description : Shows best products
    // ================================================================================================
    function BestProducts($limit = null, $fltr_id = 2)
    {
        $title = $this->multi['TXT_TOP_PRODUCT'];
        switch ($fltr_id) {
            case '1':
                $ftl = "`" . TblModCatalogProp . "`.new ='1'"; // Display new
                $title = $this->multi['FLD_NEW'];
                break;
            case '2':
                $ftl = "`" . TblModCatalogProp . "`.best ='1'"; // Display best
                $title = $this->multi['FLD_BEST'];
                break;
        }
        $str = "";
        ?>
    <div class="contenttitle1"><span><?=$title?></span></div>
    <?
        if ($this->id_cat != "") $str = $this->getSubLevels($this->id_cat);
        else $str = implode($this->GetTreeCatList());

        $q = "SELECT
                `" . TblModCatalogProp . "`.id,
                `" . TblModCatalogProp . "`.id_cat,
                `" . TblModCatalogProp . "`.price,
                `" . TblModCatalogProp . "`.price_currency,
                `" . TblModCatalogPropSprName . "`.name,
                `" . TblModCatalogSprName . "`.name as cat_name,
                `" . TblModCatalogTranslit . "`.`translit`,
                `" . TblModCatalogPropImg . "`.`path` AS `first_img`,
                `" . TblModCatalogPropImgTxt . "`.`name` AS `first_img_alt`,
                `" . TblModCatalogPropImgTxt . "`.`text` AS `first_img_title`
              FROM `" . TblModCatalogProp . "`
                LEFT JOIN `" . TblModCatalogPropImg . "` ON (`" . TblModCatalogProp . "`.`id`=`" . TblModCatalogPropImg . "`.`id_prop` AND `" . TblModCatalogPropImg . "`.`id`= (
                    SELECT
                    `" . TblModCatalogPropImg . "`.`id`
                    FROM `" . TblModCatalogPropImg . "`
                    WHERE
                    `" . TblModCatalogPropImg . "`.`id_prop`=`" . TblModCatalogProp . "`.id
                    AND `" . TblModCatalogPropImg . "`.`show`='1'
                    ORDER BY `" . TblModCatalogPropImg . "`.`move` asc LIMIT 1
                    ) )
                LEFT JOIN `" . TblModCatalogPropImgTxt . "` ON (`" . TblModCatalogPropImg . "`.`id`=`" . TblModCatalogPropImgTxt . "`.`cod` AND `" . TblModCatalogPropImgTxt . "`.lang_id='" . $this->lang_id . "'),
                `" . TblModCatalogPropSprName . "`,`" . TblModCatalogSprName . "`, `" . TblModCatalog . "`, `" . TblModCatalogTranslit . "`
              WHERE `" . TblModCatalogProp . "`.`id_cat`=`" . TblModCatalog . "`.`id`
              AND `" . TblModCatalogProp . "`.visible='2'
              AND `" . TblModCatalog . "`.`visible`='2'
              AND `" . TblModCatalogProp . "`.id=`" . TblModCatalogPropSprName . "`.cod
              AND `" . TblModCatalogProp . "`.id_cat=`" . TblModCatalogSprName . "`.cod
              AND `" . TblModCatalogPropSprName . "`.lang_id='" . $this->lang_id . "'
              AND `" . TblModCatalogSprName . "`.lang_id='" . $this->lang_id . "'
              AND `" . TblModCatalogProp . "`.id=`" . TblModCatalogTranslit . "`.`id_prop`
              AND `" . TblModCatalogTranslit . "`.`lang_id`='" . $this->lang_id . "'
             ";

        if (!empty($str))
            $q = $q . " AND " . TblModCatalogProp . ".`id_cat` IN (" . $str . ") ";
        $q = $q . " AND " . $ftl;

        $q = $q . " GROUP BY `" . TblModCatalogProp . "`.id ";
        if ($limit) $q = $q . " limit " . $limit;


        $res = $this->db->db_Query($q);
        //echo '<br>'.$q.'<br/> $res='.$res.' $this->db->result='.$this->db->result;
        if (!$res)
            return false;
        $rows = $this->db->db_GetNumRows();
        if ($rows == 0) {
            ?>
        <div class="err" align="center">
            <?=$title?> РЅРµ РѕРїСЂРµРґРµР»РµРЅС‹
        </div>
        <?
        } else {
            $cols_in_row = 3;
            $currentValuta = $this->Spr->GetNameByCod(TblSysCurrenciesSprShort, _CURR_ID, $this->lang_id, 1);
            for ($i = 0, $j = 1; $i < $rows; $i++, $j++) {
                if ($j == 1) {
                    ?><div class="prodline"><?
                }
                $class = '';
                if ($j == ($cols_in_row)) {
                    $j = 0;
                    $class = "Last";
                }
                $row = $this->db->db_FetchAssoc();
                $name = stripslashes($row['name']);
                $img = stripslashes($row['first_img']);
                $alt = stripcslashes($row['first_img_alt']);
                $title = stripcslashes($row['first_img_title']);
                if (empty($alt)) $alt = $name;
                if (empty($title)) $title = $name;
                $link = $this->getUrlByTranslit($this->treeCatData[$row['id_cat']]['path'], $row['translit']);
                ?>
                <div class="prod">
                    <span class="nameprod"><?=$name;?></span>

                    <div class="priceprod">Р¦РµРЅР°:
                        <?if ($row['price'] != 0) {
                            $cur_from = $row['price_currency'];
                            if ($cur_from == 0)
                                $cur_from = $this->def_currency;
                            $price = $this->Currency->Converting($cur_from, _CURR_ID, stripslashes($row['price']), 0);
                            ?><span><?=$price?></span> <?= $currentValuta
                            ; ?>.<?
                        }?>
                    </div>
                    <?if (isset($img) AND !empty($img)) { ?>
                    <img src="<?=$this->ShowCurrentImageExSize($img, 135, 135, 'center', 'center', '85', NULL, NULL, 'alt="' . htmlspecialchars($alt) . '" title="' . htmlspecialchars($title) . '"', true, $row['id']);?>"/>
                    <?
                } else {
                    ?><a href="<?=$link;?>" alt="<?=$name;?>" title="<?=$this->multi['TXT_NO_IMAGE']?>"><img
                            src="/images/design/no-image.gif"/></a><?
                }
                    ?>
                    <br/><br><br>
                    <a href="<?=$link;?>">РћРїРёСЃР°РЅРёРµ С‚РѕРІР°СЂР°</a>


                    <form action="#" method="post" name="catalog<?=$row['id']?>" id="catalog<?=$row['id']?>">
                        <input type="hidden" size="2" value="1" class="quantity" onkeypress="return me()"
                               id="productId[<?=$row['id']?>]" name="productId[<?=$row['id']?>]" maxlength="2"/>
                        <?/*<a href="#" onclick="addToCart('catalog<?=$row['id']?>', 'cart');return false;">
                                        <img src="/images/design/icoCart.gif" alt="<?=$this->multi['TXT_BUY'];?>" title="<?=$this->multi['TXT_BUY'];?>" />
                                    </a> */?>
                        <div class="buybutton"><a style="margin: 0; color: #FFFFFF; text-decoration: none;" href="#"
                                                  onclick="addToCart('catalog<?=$row['id']?>', 'cart');return false;"
                                                  alt="<?=$this->multi['TXT_BUY'];?>"
                                                  title="<?=$this->multi['TXT_BUY'];?>"><?=$this->multi['TXT_BUY']?></a>
                        </div>
                </div>
                </form>
                <? if ($j == 0 and $i < $rows - 1) { ?>
                                </div>
                              <img class="prl" src="/images/design/prodline.png">
                <?
                } else if ($i == $rows - 1) {
                    ?>
                </div>
                <div class="emt"></div>
                <?
                }
            } //end foreach
        } //end if
    } //end of function BestProducts

    // ================================================================================================
    // Function : ShowActionsProducts()
    // Version : 1.0.0
    // Date : 20.10.2009
    //
    // Programmer : Yaroslav Gyryn
    // Params :
    // Returns : $res / Void
    // Description : Shows best products
    // ================================================================================================
    function ShowActionsProducts($limit = null)
    {
        $q = "SELECT
                    `" . TblModCatalogProp . "`.id,
                    `" . TblModCatalogProp . "`.id_cat,
                    `" . TblModCatalogProp . "`.price,
                    `" . TblModCatalogProp . "`.price_currency,
                    `" . TblModCatalogProp . "`.opt_price,
                    `" . TblModCatalogProp . "`.opt_price_currency,
                    `" . TblModCatalogPropSprName . "`.name,
                    `" . TblModCatalogSprName . "`.name as category
                 FROM
                    `" . TblModCatalogProp . "`, `" . TblModCatalogPropSprName . "`, `" . TblModCatalogSprName . "`
                 WHERE
                    `" . TblModCatalogProp . "`.id = `" . TblModCatalogPropSprName . "`.cod
                 AND
                    `" . TblModCatalogPropSprName . "`.lang_id='" . $this->lang_id . "'
                 AND
                    `" . TblModCatalogProp . "`.id_cat = `" . TblModCatalogSprName . "`.cod
                 AND
                    `" . TblModCatalogSprName . "`.lang_id='" . $this->lang_id . "'
                 AND
                    `" . TblModCatalogProp . "`.visible ='2'
        ";

        $q = $q . " AND ABS(`" . TblModCatalogProp . "`.opt_price) >0 AND ABS(`" . TblModCatalogProp . "`.opt_price) > ABS(`" . TblModCatalogProp . "`.price)";
        $q = $q . " ORDER BY RAND()";
        if ($limit) $q = $q . " limit " . $limit;

        $res = $this->db->db_Query($q);
        //echo '<br> $q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
        if (!$res) return false;
        $rows = $this->db->db_GetNumRows();
        //echo '<br>$rows='.$rows;
        //$Currency = &check_init('SystemCurrencies', ''();
        $currentValuta = $this->Spr->GetNameByCod(TblSysCurrenciesSprSufix, _CURR_ID, $this->lang_id, 1);
        ?>
    <!--Begin: list1-->
    <div class="list1">
        <h2>
            <img src="/images/design/list1.png" alt="" title=""/>
        </h2>

        <div class="body">
            <?
            for ($i = 0; $i < $rows; $i++) {
                $row = $this->db->db_FetchAssoc();
                $name = stripslashes($row['name']);
                $price = stripslashes($row['price']);
                $old_price = stripslashes($row['opt_price']);
                $link = $this->Link($row['id_cat'], $row['id']);
                ?>
                <form action="#" method="post" name="catalog" id="catalog<?=$row['id']?>">
                    <input type="hidden" name="productId[<?=$row['id']?>]" value="1"/>

                    <div class="item">
                        <div class="left_2">
                            <h3><?=$name;?></h3>

                            <div class="text">
                            </div>
                            <div class="items">
                                <div class="left_3">
                                    <div class="old_price">
                                        <?
                                        if (!empty($old_price)) {
                                            $cur_from = $row['price_currency'];
                                            if ($cur_from == 0) $cur_from = $this->def_currency;
                                            $old_price = $this->Currency->Converting($cur_from, _CURR_ID, $old_price, 2);
                                            echo $this->Currency->ShowPrice($old_price);
                                        }
                                        ?>
                                    </div>
                                    <div class="price">
                                        <?
                                        if (!empty($price)) {
                                            $cur_from = $row['opt_price_currency'];
                                            if ($cur_from == 0) $cur_from = $this->def_currency;
                                            $price = $this->Currency->Converting($cur_from, _CURR_ID, $price, 2);
                                            echo $this->Currency->ShowPrice($price);
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="right_3">
                                    <ul>
                                        <li>
                                            <a href="#"
                                               onclick="addToCart('catalog<?=$row['id']?>', 'cart');return false;"
                                               title="Р—Р°РєР°Р·Р°С‚СЊ"><img src="/images/design/zakaz.png"
                                                                             alt="Р—Р°РєР°Р·Р°С‚СЊ"
                                                                             title="Р—Р°РєР°Р·Р°С‚СЊ"/></a>
                                        </li>
                                        <li>
                                            <a href="<?=$link;?>" title="РџРѕРґСЂРѕР±РЅРµРµ"><img
                                                    src="/images/design/all.png" alt="РџРѕРґСЂРѕР±РЅРµРµ"
                                                    title="РџРѕРґСЂРѕР±РЅРµРµ"/></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="right_2">
                            <table cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr>
                                    <td>
                                        <?
                                        $img = $this->GetFirstImgOfProp($row['id']);
                                        if ($img) echo $this->ShowCurrentImageSquare($img, true, 100, 85);
                                        else echo 'РќРµС‚ С„РѕС‚Рѕ';
                                        ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <?/*
                                <div class="action">
                                    <img class="png" src="/images/design/action.png" alt="" title="" />
                                </div>
                                */?>
                        </div>
                    </div>
                </form>
                <?
            }
            ?>
        </div>
    </div>
    <!--End: list1-->
    <?
    }
    //end of function ShowActionsProducts


} // end of class CatalogLayout
?>