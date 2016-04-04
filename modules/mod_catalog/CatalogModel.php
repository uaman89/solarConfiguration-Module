<?php

include_once( SITE_PATH . '/modules/mod_catalog/catalog.defines.php' );
/**
 * User: sergey
 * Date: 08.02.13
 * Time: 14:09
 */
class CatalogModel extends Catalog
{




    // ================================================================================================
    // Function : GetSubLevelsLayout()
    // Version : 1.0.0
    // Returns : true,false / Void
    // Description : show sublevels (categories) of catalogue on the front-end
    // Programmer : Yaroslav Gyryn
    // Date : 26.05.2010
    // ================================================================================================
    function GetSubLevelsLayout($level){
        $tmp_db = DBs::getInstance();
        $q = "SELECT
                    `".TblModCatalogSprName."`.name
                FROM
                    `".TblModCatalog."`, `".TblModCatalogSprName."`
                WHERE
                    `".TblModCatalog."`.id=`".TblModCatalogSprName."`.cod
                AND
                    `".TblModCatalogSprName."`.lang_id='"._LANG_ID."'
                AND
                    `".TblModCatalog."`.level = '$level'
                AND
                    `".TblModCatalog."`.visible = '2'
                ORDER BY
                    `".TblModCatalog."`.move ";
        $res = $tmp_db->db_Query( $q );
        //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
        //if ( !$res OR !$tmp_db->result ) return false;
        $rows = $tmp_db->db_GetNumRows();
        $arr_row='';
        for ($i=0; $i<$rows; $i++) {
            $row = $tmp_db->db_FetchAssoc();
            $name = stripslashes($row['name']);    //$this->Spr->GetNameByCod( TblModCatalogSprName, $row ['id'] );
            if ( empty($arr_row) )
                $arr_row = $name ;
            else
                $arr_row = $arr_row.', '.$name ;
        }
        return $arr_row;
    }//end of function GetSubLevelsLayout()


    /**
     * CatalogLayout::makeIdPropStr()
     *
     * @param mixed $propArrNoLimit
     * @return
     */
    function makeIdPropStr($propArrNoLimit){
        $str="";
        if($propArrNoLimit!=false){
            $str=implode(",", $propArrNoLimit);
//       foreach ($propArrNoLimit as $key=>$value) {
//           $str.=",".$value['id'];
//           echo $value;
//       }
//       $str[0]=" ";
        }else return false;
        return $str;
    }

    /**
     * CatalogLayout::GetParamsPropVal()
     *
     * @param mixed $table
     * @param mixed $lang_id
     * @param mixed $paramId
     * @param mixed $IdOfProps
     * @return
     */
    function GetParamsPropVal($lang_id, $id_cat, $paramId,$IdOfProps){
        $q="SELECT `".TblModCatalogParamsVal."`.*";
        if(strlen($IdOfProps)!=0){
            $q.="  , (
                SELECT count(*)
                FROM `".TblModCatalogParamsProp."`
                WHERE (`".TblModCatalogParamsProp."`.`id_param`=".$paramId."
                AND `".TblModCatalogParamsProp."`.`val`=`".TblModCatalogParamsVal."`.`cod`)
                AND `".TblModCatalogParamsProp."`.`id_prop` IN (".$IdOfProps.")
                AND `".TblModCatalogParamsVal."`.`id_cat`='".$id_cat."'
                AND `".TblModCatalogParamsVal."`.`id_param`='".$paramId."'
             ) AS countOfProp ";
        }
        $q.= "FROM
                `".TblModCatalogParamsVal."`
           WHERE
                `lang_id`=".$lang_id."
                AND `id_cat`='".$id_cat."'
                AND `id_param`='".$paramId."'
           ";
        $res = $this->db->db_Query( $q );
        //echo $q."<br/><br/><br/>";
        //echo '<br> $q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
        if ( !$res or !$this->db->result ) return false;
        $rows = $this->db->db_GetNumRows();
        $arr=array();
        for($i=0;$i<$rows;$i++){
            $row = $this->db->db_FetchAssoc();
            $arr[$row['move']]['cod']=$row['cod'];
            $arr[$row['move']]['name']=$row['name'];
            $arr[$row['move']]['move']=$row['move'];
            $arr[$row['move']]['mtitle']=$row['mtitle'];
            $arr[$row['move']]['mdescr']=$row['mdescr'];
            if(isset($row['countOfProp']))
                $arr[$row['move']]['countOfProp']=$row['countOfProp'];

        }
        //print_r($arr);
        return $arr;

    }


    /**
     * CatalogLayout::generateIdPropArra()
     *
     * @return
     */
    function generateIdPropArra(){
        $array[0]=$this->GetListPositionsSortByDateCount($this->id_cat, 'nolimit', true);

        if(is_array($this->arr_current_img_params_value)){
            foreach($this->arr_current_img_params_value as $key=>$value) {
                $array[$key]=$this->GetListPositionsSortByDateCount($this->id_cat, 'nolimit', true,$key);
            }
        }
        return $array;
    }

    /**
     * CatalogLayout::makeParamLink()
     *
     * @param mixed $arr
     * @param mixed $paramId
     * @param mixed $paramVal
     * @return
     */
    function makeParamLink($arr,$paramId,$paramVal){
        //print_r($arr);
        $param_str="";
        if(isset($arr[$paramId])){
            //  echo '$paramId='.$paramId.' $paramVal='.$paramVal;
            $subarr=explode(",", $arr[$paramId]);
            $flag=false;
            foreach ($subarr as $key => $value) {
                if($value==$paramVal){
                    unset($subarr[$key]);
                    $flag=true;
                }
            }
            $arr[$paramId]=implode(",", $subarr);
            if(!$flag)
                $arr[$paramId].=",".$paramVal;
        }
        else{
            $arr[$paramId]=$paramVal;
        }
        //print_r($arr);
        foreach ($arr as $key => $value) {
            if(!empty($value)){
                $param ='&'.PARAM_VAR_NAME.PARAM_VAR_SEPARATOR.$key.'='.$value;
                $param_str .= $param;
            }
        }
        return $param_str;
    }



    // ================================================================================================
    // Function : GetListPositionsSortByDateCount()
    // Date : 23.05.2010
    // Parms :       $level - id of the category
    //               $limit - select all rows or with limit (for show by pages)
    //               $show_sublevels - select posotion from sublevels of $level or not (can be treu or false)
    // Returns :      true/false
    // Description :  get list of positions sort by date
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function GetListPositionsSortByDateCount($level=0, $limit='limit', $show_sublevels=false , $vithoutParam=NULL)
    {
        $paramQuery=NULL;
        $flag=true;
        if( is_array($this->arr_current_img_params_value) ) { // Выборка по параметрам
            if($vithoutParam!=NULL && count($this->arr_current_img_params_value)==1 && isset($this->arr_current_img_params_value[$vithoutParam])) $flag=false;
            if($flag){
                $paramQuery = "
                SELECT `".TblModCatalogParamsProp."`.id_prop
                    FROM  `".TblModCatalogParamsProp."`
                    WHERE ";
                $i=0;
                foreach($this->arr_current_img_params_value as $key=>$value) {
                    //if($vithoutParam!=NULL && $vithoutParam==$key && count($this->arr_current_img_params_value)==1) $flag=false;
                    if($vithoutParam==$key) continue;
                    if($i==0) {
                        $paramQuery.=" (`".TblModCatalogParamsProp."`.id_param='".$key."' AND
                                        `".TblModCatalogParamsProp."`.val in (".$value."))";
                    }
                    else {
                        $paramQuery.=" AND `".TblModCatalogParamsProp."`.id_prop IN (
                        SELECT `".TblModCatalogParamsProp."`.id_prop
                        FROM  `".TblModCatalogParamsProp."`
                        WHERE `".TblModCatalogParamsProp."`.id_param='".$key."' AND
                              `".TblModCatalogParamsProp."`.val in (".$value."  ))";
                    }
                    $i++;

                }
                $res = $this->db->db_Query( $paramQuery );
                //echo '<br>'.$paramQuery.'<br> res='.$res.'<br> $this->db->result='.$this->db->result;
//                if( !$res) return false;
                $num_rows = $this->db->db_GetNumRows($res);

                $my_id_prop ='';
                for($i=0;$i<$num_rows;$i++){
                    $value = $this->db->db_FetchAssoc();

                    if($i!=0)
                        $my_id_prop = $my_id_prop.', ';
                    $my_id_prop .= $value['id_prop'];
                }
            }
        }
        //echo $my_id_prop."<br/>";
        //echo '$paramQuery="'.$paramQuery.'"';
        if(isset($this->settings['multi_categs']) AND $this->settings['multi_categs']==1){
            $multi_levels_left_join = " LEFT JOIN `".TblModCatalogPropMultiCategs."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropMultiCategs."`.`id_prop`)";
        }
        else $multi_levels_left_join = '';
        $q = "SELECT DISTINCT
                `".TblModCatalogProp."`.id
              FROM `".TblModCatalogProp."`
                $multi_levels_left_join,
                `".TblModCatalogPropSprName."`,`".TblModCatalogSprName."`, `".TblModCatalog."`, `".TblModCatalogTranslit."`
             WHERE `".TblModCatalogProp."`.`id_cat`=`".TblModCatalog."`.`id`
              AND `".TblModCatalogProp."`.visible='2'
              AND `".TblModCatalog."`.`visible`='2'
              AND `".TblModCatalogProp."`.id=`".TblModCatalogPropSprName."`.cod
              AND `".TblModCatalogProp."`.id_cat=`".TblModCatalogSprName."`.cod
              AND `".TblModCatalogPropSprName."`.lang_id='".$this->lang_id."'
              AND `".TblModCatalogSprName."`.lang_id='".$this->lang_id."'
              AND `".TblModCatalogProp."`.id=`".TblModCatalogTranslit."`.`id_prop`
              AND `".TblModCatalogTranslit."`.`lang_id`='".$this->lang_id."'
             ";

        if(!empty($paramQuery))
            $q.="AND `".TblModCatalogProp."`.id IN (".$my_id_prop.") ";
        if($show_sublevels) {
            $str_sublevels = $this->getSubLevels($level);
            //echo '<br />$str_sublevels='.$str_sublevels;
            if(empty($str_sublevels)) $str_sublevels = $level;
            else $str_sublevels = $level.','.$str_sublevels;
            $categ_filter = " `".TblModCatalogProp."`.id_cat IN (".$str_sublevels.")";
        }
        elseif($level>0) $categ_filter = " `".TblModCatalogProp."`.id_cat='".$level."'";
        else $categ_filter = '';

        if( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']==1 ){
            if( !empty($categ_filter) ) $q = $q."  AND (".$categ_filter." OR `".TblModCatalogPropMultiCategs."`.`id_cat`='".$level."') ";
            else $q = $q."  AND  `".TblModCatalogPropMultiCategs."`.`id_cat`='".$level."' ";
        }
        else $q = $q." AND ".$categ_filter;

        $q = $q." ORDER BY `".TblModCatalogProp."`.`dt` desc, `".TblModCatalogProp."`.`move` asc ";
        if($limit=='limit') $q = $q." LIMIT ".$this->start.", ".($this->display);
        $res = $this->db->db_Query( $q );
        //echo '<br>'.$q.'<br> res='.$res.'<br> $this->db->result='.$this->db->result;
        if( !$res or !$this->db->result) return false;
        $rows = $this->db->db_GetNumRows($res);
        //echo '<br>$rows='.$rows;
        $arr=array();
        for($i=0;$i<$rows;$i++){
            $row = $this->db->db_FetchAssoc();
            $arr[$i]=$row['id'];
        }

        return $arr;
    }//end of function GetListPositionsSortByDateCount()




    // ================================================================================================
    // Function : GetListPositionsSortByDate()
    // Date : 23.05.2010
    // Parms :       $level - id of the category
    //               $limit - select all rows or with limit (for show by pages)
    //               $show_sublevels - select posotion from sublevels of $level or not (can be treu or false)
    // Returns :      true/false
    // Description :  get list of positions sort by date
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function GetListPositionsSortByDate($level=0, $limit='limit',  $sort = NULL, $asc_desc = "asc", $show_sublevels=false, $idParam = NULL)
    {
        $paramQuery=NULL;
        if( is_array($this->arr_current_img_params_value) ) { // Выборка по параметрам
            $paramQuery = "
                SELECT `".TblModCatalogParamsProp."`.id_prop
                    FROM  `".TblModCatalogParamsProp."`
                    WHERE ";
            $i=0;
            foreach($this->arr_current_img_params_value as $key=>$value) {
                if($i==0) {
                    $paramQuery.=" (`".TblModCatalogParamsProp."`.id_param='".$key."' AND
                                        `".TblModCatalogParamsProp."`.val in (".$value."))";
                }
                else {
                    $paramQuery.=" AND `".TblModCatalogParamsProp."`.id_prop IN (
                        SELECT `".TblModCatalogParamsProp."`.id_prop
                        FROM  `".TblModCatalogParamsProp."`
                        WHERE `".TblModCatalogParamsProp."`.id_param='".$key."' AND
                              `".TblModCatalogParamsProp."`.val in (".$value."  ))";
                }
                $i++;

            }
            $res = $this->db->db_Query( $paramQuery );
            //echo '<br>'.$paramQuery.'<br> res='.$res.'<br> $this->db->result='.$this->db->result;

            if( !$res) return false;
            $num_rows = $this->db->db_GetNumRows($res);

            $my_id_prop ='';
            for($i=0;$i<$num_rows;$i++){
                $value = $this->db->db_FetchAssoc();

                if($i!=0)
                    $my_id_prop = $my_id_prop.', ';
                $my_id_prop .= $value['id_prop'];
            }
        }
        if(isset($this->settings['multi_categs']) AND $this->settings['multi_categs']==1){
            $multi_levels_left_join = " LEFT JOIN `".TblModCatalogPropMultiCategs."` ON (`".TblModCatalogProp."`.`id` = `".TblModCatalogPropMultiCategs."`.`id_prop`)";
        }
        else $multi_levels_left_join = '';

        if( !empty($this->id_param)){
            $select_param_val = ", `".TblModCatalogParamsProp."`.val";
            $left_join_param_val = " LEFT JOIN `".TblModCatalogParamsProp."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogParamsProp."`.`id_prop` AND `".TblModCatalogParamsProp."`.`id_param`='".$this->id_param."')";
        }
        else {
            $select_param_val = '';
            $left_join_param_val = '';
        }



        //echo $q;

        $q = "SELECT DISTINCT
                `".TblModCatalogProp."`.*,
                `".TblModCatalogPropSprName."`.name,
                `".TblModCatalogSprName."`.name as cat_name,
                `".TblModCatalogTranslit."`.`translit`,
                `".TblModCatalogPropImg."`.`path` AS `first_img`,
                `".TblModCatalogPropImgTxt."`.`name` AS `first_img_alt`,
                `".TblModCatalogPropImgTxt."`.`text` AS `first_img_title`,
                `".TblModCatalogPropSprShort."`.`name` AS `desc`
                $select_param_val
              FROM `".TblModCatalogProp."`
                LEFT JOIN `".TblModCatalogPropImg."` ON (`".TblModCatalogProp."`.`id`=`".TblModCatalogPropImg."`.`id_prop` AND `".TblModCatalogPropImg."`.`move`='1' AND `".TblModCatalogPropImg."`.`show`='1')
                LEFT JOIN `".TblModCatalogPropImgTxt."` ON (`".TblModCatalogPropImg."`.`id`=`".TblModCatalogPropImgTxt."`.`cod` AND `".TblModCatalogPropImgTxt."`.lang_id='".$this->lang_id."')
                $multi_levels_left_join
                $left_join_param_val,
                `".TblModCatalogPropSprName."`,`".TblModCatalogSprName."`, `".TblModCatalog."`, `".TblModCatalogTranslit."`,`".TblModCatalogPropSprShort."`
              WHERE `".TblModCatalogProp."`.`id_cat`=`".TblModCatalog."`.`id`
              AND `".TblModCatalogProp."`.visible='2'
              AND `".TblModCatalog."`.`visible`='2'
              AND `".TblModCatalogProp."`.id=`".TblModCatalogPropSprName."`.cod
              AND `".TblModCatalogProp."`.id_cat=`".TblModCatalogSprName."`.cod
              AND `".TblModCatalogPropSprName."`.lang_id='".$this->lang_id."'
              AND `".TblModCatalogSprName."`.lang_id='".$this->lang_id."'
              AND `".TblModCatalogProp."`.id=`".TblModCatalogTranslit."`.`id_prop`
              AND `".TblModCatalogTranslit."`.`lang_id`='".$this->lang_id."'
              AND `".TblModCatalogPropSprShort."`.cod = `".TblModCatalogProp."`.id
             ";

        $str_sublevels = '';
        if($show_sublevels) {
            $str_sublevels = $this->getSubLevels($level);
            //echo '<br />$str_sublevels='.$str_sublevels;
            if(empty($str_sublevels)) $str_sublevels = $level;
            else $str_sublevels = $level.','.$str_sublevels;
            $categ_filter = " `".TblModCatalogProp."`.id_cat IN (".$str_sublevels.")";
            $categ_multi_filter = " `".TblModCatalogPropMultiCategs."`.`id_cat` IN (".$str_sublevels.")";
        }
        elseif($level>0){
            $categ_filter = " `".TblModCatalogProp."`.id_cat='".$level."'";
            $categ_multi_filter = " `".TblModCatalogPropMultiCategs."`.`id_cat`='".$level."'";
        }
        else{
            $categ_filter = '';
            $categ_multi_filter = '';
        }

        if( isset($this->settings['multi_categs']) AND $this->settings['multi_categs']==1 ){
            if( !empty($categ_filter) ) $q = $q."  AND (".$categ_filter." OR ".$categ_multi_filter.") ";
            else $q = $q."  AND  `".TblModCatalogPropMultiCategs."`.`id_cat`='".$level."' ";
        }
        else $q = $q." AND ".$categ_filter;

        if(!empty($paramQuery))
            $q.="AND `".TblModCatalogProp."`.id IN (".$my_id_prop.") ";

        $q .= " GROUP BY `".TblModCatalogProp."`.`id`";

        if($this->sort=='name')
            $q .= ' ORDER BY `'.TblModCatalogPropSprName.'`.name '.$asc_desc;
        else
            $q = $q." ORDER BY `".TblModCatalogProp."`.`move` asc ";

        if($this->sort!=='price') {
            if($limit=='limit') $q = $q." LIMIT ".$this->start.", ".($this->display);
        }

       // echo $q;
        $res = $this->db->db_Query( $q );
        //echo '<br>'.$q.'<br> res='.$res.'<br> $this->db->result='.$this->db->result;
        if( !$res or !$this->db->result) return false;
        $rows = $this->db->db_GetNumRows($res);
        for($i=0; $i<$rows; $i++) {
            $arrRows[] = $this->db->db_FetchAssoc();
        }
        // Если есть фильтр по уровню цен
        if(isset($this->from) and isset($this->to)) {
            $from = $this->from*1000;
            $to = $this->to*1000;
        }
        else {
            $from=null;
            $to = null;
        }

        $arr = array();
        if($this->sort=='price' and  $limit=='limit')  {
            $tmpArr = array();
            $offset = array();
            for($i=0; $i<$rows; $i++){
                $row = $arrRows[$i];
                $price = $this->Currency->Converting($row['price_currency'], _CURR_ID, $row['price'], 2)*1000;

                if(isset($from) and isset($to)) {
                    if($price >= $from and $price <= $to )
                        ; // Фильтрация по диапазону цен
                    else
                        continue;
                }
                if($price==0)
                    $price = 999999999;
                if($row['exist']==2)
                    $price+=99999999;
                if(!isset($offset[$price]))
                    $offset[$price] = 0;
                $tmpArr[$price+$offset[$price]] = $row;
                $offset[$price]+=1;
            }

            if($asc_desc !='asc') {
                ksort($tmpArr); // возростание
            }
            else
                krsort($tmpArr); // в обратном порядке убывание

            $keys = array_keys($tmpArr);
            $limit = $this->start+$this->display;
            if($rows < $limit)
                $limit = $rows;
            for($i=$this->start; $i<$limit; $i++) {
                if(isset($keys[$i]))
                    $arr[$keys[$i]] = $tmpArr[$keys[$i]];
            }
            return $arr;
        }
        elseif($this->sort=='param' and  $limit=='limit')  { // Сортировка по параметру с типом 1 - числовое значение либо 5 - текстовое значение

            $paramData =  $this->GetParamById($idParam);
            if($paramData['type'] == 1) { // Число
                $tmpArr=array();
                for($i=0; $i<$rows; $i++) {
                    $offset = 0.0001;
                    $row = $arrRows[$i];
                    $index  = doubleval(str_replace(',','.',$row['val']))*1000;
                    //echo '    <br/>'.$index;
                    if(!isset($tmpArr[$index]))
                        $tmpArr[$index] = $row;
                    else {
                        while(isset($tmpArr[$index+$offset])) {
                            $offset+=0.0001;
                        }
                        $tmpArr[$index+$offset] = $row;
                    }

                }
                //if($asc_desc == "asc")
                ksort($tmpArr);
                /*elseif($asc_desc == "desc")
                    rsort($tmpArr);*/
                $keys = array_keys($tmpArr);
                //print_r($keys);
                $rows = count($keys);
                $limit = $this->start + $this->display;
                if($rows < $limit)
                    $limit = $rows;
                for($i=$this->start; $i<$limit; $i++) {
                    if(isset($keys[$i]))
                        $arr[$keys[$i]] = $tmpArr[$keys[$i]];
                }
            }
            elseif($paramData['type'] == 3 or $paramData['type'] == 4)  {   // 3 - Выбор из списка,  4 - Множество значений
                $tblname = $this->BuildNameOfValuesTable($paramData['id_cat'], $paramData['id']);
                //$val = $this->Spr->GetListName( TblSysLogic, $this->lang_id, 'array', 'move', 'asc', 'all' );
                $paramSpr = $this->Spr->GetListName( $tblname, $this->lang_id, 'array', 'move', 'asc', 'all' );
                $paramSprKeys = array_keys($paramSpr);
                //print_r($paramSprKeys);

                $tmpArr=array();
                //echo '$rows ='.$rows;
                for($i=0; $i<$rows; $i++) {
                    $offset = 0.0001;
                    $row = $arrRows[$i];
                    $index  = doubleval(str_replace(',','.',$row['val']))*1000;
                    if(!isset($tmpArr[$index]))
                        $tmpArr[$index] = $row;
                    else {
                        while(isset($tmpArr[$index+$offset])) {
                            $offset+=0.0001;
                        }
                        $tmpArr[$index+$offset] = $row;
                    }

                }
                //if($asc_desc == "asc")
                ksort($tmpArr);
                /*elseif($asc_desc == "desc")
                    rsort($tmpArr);*/
                $keys = array_keys($tmpArr);
                $rows = count($keys);
                $limit = $this->start + $this->display;
                if($rows < $limit)
                    $limit = $rows;
                for($i=$this->start; $i<$limit; $i++) {
                    if(isset($keys[$i]))
                        $arr[$keys[$i]] = $tmpArr[$keys[$i]];
                }

            }
            return $arr;
        }

        for($i=0; $i<$rows; $i++){
            $row = $arrRows[$i];
            if(defined("_CURR_ID")) $curr_id = _CURR_ID;
            else $curr_id = DEBUG_CURR;
            $price = $this->Currency->Converting($row['price_currency'], $curr_id, $row['price'], 2)*1000;
            if(isset($from) and isset($to)) {
                if($price >= $from and $price <= $to )
                    ; // Фильтрация по диапазону цен
                else
                    continue;
            }
            $img = stripslashes($row['first_img']);
            if($img!=""){
            if(!is_file(SITE_PATH.'/images/mod_catalog_prod/'.$row['id'].'/182_zoom_'.$img)){
                $imageG = ImageK::factory(SITE_PATH.'/images/mod_catalog_prod/'.$row['id'].'/'.$img)->resize(182, 182, ImageK::WIDTH)->save(SITE_PATH.'/images/mod_catalog_prod/'.$row['id'].'/182_zoom_'.$img);
            }
            $row['image'] = '/images/mod_catalog_prod/'.$row['id'].'/182_zoom_'.$img;
            }else{
                $row['image'] ="";
            }
            //$row['image']=$this->ShowCurrentImageExSize($img, 182, 182, 'center', 'center', '85', NULL, NULL, '', true, $row['id']);
            $row['fullimage']= $this->ShowCurrentImage($img, NULL, 95, NULL, NULL, $row['id'], true);
            $cur_from = $row['price_currency'];
            $price = $this->Currency->Converting($cur_from, _CURR_ID, $price, 2 );
            $row['price']=$this->Currency->ShowPrice($price);
            $cat_data = $this->GetTreeCatData($row['id_cat']);
            $row['link']= $this->getUrlByTranslit($cat_data['path'], $row['translit']);

            $arr[$i]=$row;
        }
        return $arr;
    }//end of function GetListPositionsSortByDate()




    /**
     * Class method LoadCatParams
     * load all parameters for category $id_cat in class property
     * @param $id_cat - id of the category
     * @param $use_parent_params - use or not parametrs of parent categories
     * @return true/false or arrays:
     * @author Igor Trokhymchuk  <ihor@seotm.com>
     * @version 1.1, 05.04.2011
     */


    function LoadCatParams($id_cat, $use_parent_params=true)
    {
        if($use_parent_params==1) $arr_top_levels = $this->get_top_levels_in_array( $id_cat, NULL );
        else $arr_top_levels[$id_cat]='';
        //echo '<br>$arr_top_levels='.$arr_top_levels;
        foreach($arr_top_levels as $v=>$k){
            $q = "SELECT * FROM `".TblModCatalogParams."` WHERE `id_cat`='".$v."' order by `move`";
            $res = $this->db->db_Query( $q );
            //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
            if ( !$res OR !$this->db->result ) return false;
            $rows = $this->db->db_GetNumRows();
            for ($i=$rows;$i<$rows;$i++){
                $row = $this->db->db_FetchAssoc();
                $this->paramsList[$row['id']] = $this->db->db_FetchAssoc();
                //echo '<br>$row['.$i.']='.$row[$i];
            }
        }
        //print_r($this->paramsList);
        return true;
    }//end of funcion LoadCatParams()

    /**
     * Class method CheckExistOfParams
     * chekc exist or not list of param/ If not exist one o more parametres - return false
     * @return true/false or arrays:
     * @author Igor Trokhymchuk  <ihor@seotm.com>
     * @version 1.1, 01.02.2012
     */
    function CheckExistOfParamsFilter($arr_params)
    {
        $rows = NULL;
        $keys = array_keys($arr_params);
        $cnt = count($keys);
        $q = "SELECT * FROM `".TblModCatalogParams."` WHERE `modify`='1' AND ";
        for($i=0;$i<$cnt;$i++){
            if($i==0) $q .= "`id`='".$keys[$i]."'";
            else $q .= " OR `id`='".$keys[$i]."'";
        }
        $res = $this->db->db_Query( $q );
        //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
        if ( !$res OR !$this->db->result ) return false;
        $rows = $this->db->db_GetNumRows();
        if($rows!=count($arr_params)) return false;
        //$row = $tmp_db->db_FetchAssoc();
        return true;
    }//end of function CheckExistOfParams()


    // ================================================================================================
    // Function : GetParamsValuesOfPropInStr()
    // Version : 1.0.0
    // Date : 18.04.2006
    // Parms :   $id / id of curent position
    //           $divider /  symbol to divide parameters one from one. (default defider is <br>)
    // Returns : true,false / Void
    // Description : return values of parameters in string for current position of catalogue
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 18.04.2006
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function GetParamsValuesOfPropInStr( $id, $divider='<br>' )
    {
        $id_cat = $this->GetCategory($id);
        $params = $this->IsParams( $id_cat );
        if ( $params==0 ) return;

        $params_row = $this->GetParams($id_cat);
        $value=$this->GetParamsValuesOfProp( $id );
        $str=NULL;
        for ($i=0;$i<count($params_row);$i++){
            $tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);

            isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;

            if( $id!=NULL ) $this->Err!=NULL ? $val=$this->arr_params[$params_row[$i]['id']] : $val=$val_from_table;
            else $val=$this->arr_params[$params_row[$i]['id']];

            $prefix = stripslashes($params_row[$i]['prefix']);
            $sufix = stripslashes($params_row[$i]['sufix']);
            switch ($params_row[$i]['type'] ) {
                case '1':
                    $val = $val;
                    break;
                case '2':
                    $val = $this->Spr->GetNameByCod(TblSysLogic,$val, $this->lang_id, 1);
                    break;
                case '3':
                    $val = $this->Spr->GetNameByCod($tblname,$val, $this->lang_id, 1);
                    break;
                case '4':
                    $val = $this->Spr->GetNamesInStr( $tblname, _LANG_ID, $val, ',' );
                    break;
                case '5':
                    $val = $val;
                    break;
            }
            $tmp_str = '<b>'.stripslashes($params_row[$i]['name']).':</b>&nbsp;'.$prefix.' '.$val.' '.$sufix;
            if ( empty($str) ) $str = $tmp_str;
            else $str = $str.$divider.$tmp_str;
        }
        //echo '<br> $str='.$str;
        return $str;
    } //end of function  GetParamsValuesOfPropInStr()


    /**
     * Class method MAP
     * create catalog map for sitemap
     * @return true/false
     * @author Yaroslav Gyryn  <yaroslav@seotm.com>
     * @version 1.0, 17.01.2011
     */
    function MAP()
    {
        $this->catalogProducts = $this->GetProductsArrForSiteMap();  // РЎРїРёСЃРѕРє С‚РѕРІР°СЂРѕРІ РІ РєР°Р¶РґРѕР№ РєР°С‚РµРіРѕСЂРёРё РєР°С‚Р°Р»РѕРіР°
        $this->ShowCatalogMap();
    } // end of function  MAP()

    // ================================================================================================
    // Function : GetPathToLevel()
    // Version : 1.0.0
    // Date : 07.05.2007
    //
    // Parms :        $level - id of the category
    // Returns :      $str / string with name of the categoties to current level of catalogue
    // Description :  Return a path to current category
    // ================================================================================================
    // Programmer :  Igor Trokhymchuk
    // Date : 07.05.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function GetPathToLevel($level, $devider = ' > ', $str=NULL)
    {
        $name = $this->Spr->GetNameByCod( TblModCatalogSprName, $level, $this->lang_id, 1 );
        //echo '<br>$str='.$str.' $name='.$name.' <br>';
        if ( !empty($str) ) $str = $name.$devider.$str;
        else $str = '<a href="catalogcat_'.$level.'.html" title="'.addslashes($name).'" > '.$name.'</a>'.$str;

        $tmp_db = DBs::getInstance();
        $q="SELECT * FROM ".TblModCatalog." WHERE `id`='$level'";
        $res = $tmp_db->db_Query( $q );
        //echo '<br>q='.$q.' res='.$res.' $tmp_db->result='.$tmp_db->result;
        if( !$res OR !$tmp_db->result ) return false;
        $row = $tmp_db->db_FetchAssoc();
        if ( $row['level']>0 ) {
            $str = $this->GetPathToLevel($row['level'], $devider, $str);
        }
        //$str = '<a href="'.$script.'&level=0">'.$this->Msg->show_text('TXT_ROOT_CATEGORY').' > </a>'.$str;
        return $str;
    } // end of function GetPathToLevel()

    // ================================================================================================
    // Function : Link()
    // Version : 1.0.0
    // Date : 19.05.2007
    // Parms :  $id_cat     - id of the category
    //          $id_prop    - id of the current position
    //          $param      - parameter for build link (may be for example 'print', 'zoom', 'goto')
    //          $id_img     - id of the image or path od the image
    //          $watermark  - watermark for image
    // Returns : true,false / Void
    // Description :  build link with translit name
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 19.05.2007
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function Link($id_cat = NULL, $id_prop = NULL, $param = NULL, $id_img = NULL, $watermark = NULL, $id_file = NULL )
    {
        if( $this->mod_rewrite==1){
            //$arr_categs = $this->get_top_levels_in_array($id_cat);
            $arr_categs = $this->GetTopLevelsTranslit($id_cat, $this->lang_id);

            //echo '<br>$arr_categs=';print_r($arr_categs);
            $link=NULL;
            $translit_str = NULL;
            foreach($arr_categs as $key=>$value){
                //echo '<br>$key='.$key.' $value='.$value;
                if( empty($key)) continue;
                // get translit name for category
                $translit_categ = $value;
                $translit_str = $translit_categ.'/'.$translit_str;
            }
            //echo '<br>$translit_str ='.$translit_str ;

            $link = $translit_str;
            if( !empty($id_prop) ) {
                // get translit name for current position
                $translit_prop = $this->GetTranslitById($id_cat, $id_prop, $this->lang_id);
                //echo '<br>$translit_prop='.$translit_prop.' $id_prop='.$id_prop;
                if(CATALOG_TRASLIT){
                    $link = $translit_prop.'.html';
                }else{
                    $link = $link.$translit_prop.'.html';
                }
            }

            if( !defined("_LINK")) {
                $Lang = &check_init('SysLang', 'SysLang', 'NULL, "front"');
                if( _LANG_ID!=$Lang->GetDefFrontLangID() ) define("_LINK", "/".$Lang->GetLangShortName(_LANG_ID)."/");
                else define("_LINK", "/");
            }

            switch($param){
                case 'goto':
                    $link = _LINK.'goto/'.$id_cat.'/'.$id_prop;
                    break;
                case 'show_files':
                    //$Logon = new UserAuthorize();
                    $link = NULL;
                    //echo '<br>$id_file='.$id_file;
                    if( !empty($id_file) ){
                        if( !empty($this->Logon->user_id) AND !empty($id_file) ){
                            $tmp = $this->GetFileData($id_file);
                            $link = _LINK.'catalog/'.$translit_str.$translit_prop.'/files/'.$id_file;
                            //$link = _LINK.Catalog_Upload_Files_Path.'/'.$id_prop.'/'.$tmp['path'];
                        }
                        else {
                            //$referer_page = str_replace('&','AND',$_SERVER['REQUEST_URI']);
                            //$link = _LINK.'login.php?referer_page='.$referer_page.'';
                            $link = _LINK.'catalog/'.$translit_str.$translit_prop.'/files/'.$id_file;
                        }
                    }
                    else $link='#111111';
                    //echo '<br>$id_prop='.$id_prop.' $translit_prop='.$translit_prop.' $link='.$link;
                    break;
                case 'print':
                    $link = _LINK.'print-it/catalog/'.$id_cat.'/'.$id_prop.'.html';
                    break;
                default:
                    if(CATALOG_TRASLIT){
                        $link = _LINK.$link;
                    }else{
                        $link = _LINK.'catalog/'.$link;
                    }
            }
        }//end if
        else{
            if( !empty($id_cat) AND empty($id_prop) ) $link = "catalogcat_".$id_cat.'_'.$this->lang_id.'.html';
            elseif( !empty($id_cat) AND !empty($id_prop) ) $link = "catalog_".$id_cat.'_'.$id_prop.'_'.$this->lang_id.'.html';
            else $link = 'catalog.html';
        }

        return $link;
    }// end of function Link()


    // ================================================================================================
    // Function : GetLink()
    // Version : 1.0.0
    // Date : 23.05.2010
    // Parms :  $id_cat     - id of the category
    //          $translit_prop - translit of the position
    //          $param      - parameter for build link (may be for example 'print', 'zoom', 'goto')
    // Returns : true,false / Void
    // Description :  build link with translit name
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 23.05.2010
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function GetLink($id_cat, $translit_prop = NULL, $param = NULL )
    {
        //echo '<br>$translit_prop='.$translit_prop.' $id_prop='.$id_prop;
        if( !isset($this->arrCategsTranslit[$id_cat]) ) $this->arrCategsTranslit[$id_cat] = $this->GetTopLevelsTranslit($id_cat, $this->lang_id);

        $link=NULL;
        $translit_str = NULL;
        foreach($this->arrCategsTranslit[$id_cat] as $key=>$value){
            //echo '<br>$key='.$key.' $value='.$value;
            if( empty($key)) continue;
            // get translit name for category
            $translit_categ = $value;
            $translit_str = $translit_categ.'/'.$translit_str;
        }
        //echo '<br>$translit_str ='.$translit_str ;

        $link = $translit_str;
        $link = $link.$translit_prop.'.html';


        if( !defined("_LINK")) {
            $Lang = &check_init('SysLang', 'SysLang', 'NULL, "front"');
            if( _LANG_ID!=$Lang->GetDefFrontLangID() ) define("_LINK", "/".$Lang->GetLangShortName(_LANG_ID)."/");
            else define("_LINK", "/");
        }

        switch($param){
            default:
                $link = _LINK.'catalog/'.$link;
        }
        return $link;
    }// end of function GetLink()



    // ================================================================================================
    // Function : BuildNumberNameByParams()
    // Version : 1.0.0
    // Date : 28.08.2007
    // Parms :
    // Returns : true,false / Void
    // Description : save parameters values of current position in catalogue to the field numder_name
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 28.08.2007
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function BuildNumberNameByParams()
    {
        $str_out = NULL;
        $params_row = $this->GetParams($this->id_cat);
        $value=$this->GetParamsValuesOfProp( $this->id );
        for ($i=0;$i<count($params_row);$i++){
            $tblname = $this->BuildNameOfValuesTable($params_row[$i]['id_categ'], $params_row[$i]['id']);

            isset($value[$params_row[$i]['id']]) ? $val_from_table = $value[$params_row[$i]['id']] : $val_from_table = NULL;

            if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->arr_params[$params_row[$i]['id']] : $val=$val_from_table;
            else $val=addslashes($this->arr_params[$params_row[$i]['id']]);

            $prefix = $this->Spr->GetNameByCod(TblModCatalogParamsSprPrefix,($params_row[$i]['id']), $this->lang_id, 1);
            $sufix = $this->Spr->GetNameByCod(TblModCatalogParamsSprSufix,($params_row[$i]['id']), $this->lang_id, 1);

            switch ($params_row[$i]['type'] ) {
                case '1':
                    $val = $val;
                    break;
                case '2':
                    $val = $this->Spr->GetShortNameByCod(TblSysLogic,$val, $this->lang_id, 1);
                    break;
                case '3':
                    $val = $this->Spr->GetShortNameByCod($tblname,$val, $this->lang_id, 1);
                    break;
                case '4':
                    $val = $this->Spr->GetShortNamesInStr( $tblname, _LANG_ID, $val, '' );
                    break;
                case '5':
                    $val = $val;
                    break;
            }//end switch
            if( !empty($val)) $str_out = $str_out.$prefix.$val.$sufix;
        }//end for
        //echo '<br>$str_out='.$str_out;
        if ( !empty($str_out) ) $str_out = 'LTW'.$str_out;
        return $str_out;
    }// end of function BuildNumberNameByParams()


    // ================================================================================================
    // Function : SaveParamsValuesToNumberName()
    // Version : 1.0.0
    // Date : 27.08.2007
    // Parms :
    // Returns : true,false / Void
    // Description : save parameters values of current position in catalogue to the field numder_name
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 27.08.2007
    // Reason for change : Reason Description / Creation
    // Change Request Nbr:
    // ================================================================================================
    function SaveParamsValuesToNumberName()
    {
        $tmp_db = DBs::getInstance();
        $this->number_name = $this->BuildNumberNameByParams();
        $q = "UPDATE `".TblModCatalogProp."` set
    `number_name`='".$this->number_name."' WHERE `id`='$this->id'";
        $res = $tmp_db->db_Query( $q );
//    echo '<br>q='.$q.' res='.$res.' $tmp_db->result='.$tmp_db->result;
        if ( !$res OR !$tmp_db->result ) return false;
        return $this->number_name;
    }//end of function  SaveParamsValuesToNumberName()

    /**
     * Catalog::SetMetaData()
     * Set title, description and keywords for current category or position of catalog
     * @author Ihor Trokhymchuk
     * @return void
     */
    function SetMetaData() {
        //for current product page
        if (!empty($this->id)) {
            $q2 = "SELECT
                 `" . TblModCatalogPropSprName . "`.`name`,
                 `" . TblModCatalogPropSprMTitle . "`.`name` AS `title`,
                 `" . TblModCatalogPropSprMDescr . "`.`name` AS `descr`,
                 `" . TblModCatalogPropSprMKeywords . "`.`name` AS `keywords`
                 FROM `" . TblModCatalogPropSprName . "`,`" . TblModCatalogProp . "`
                 LEFT JOIN `" . TblModCatalogPropSprMTitle . "` ON (`" . TblModCatalogProp . "`.`id` = `" . TblModCatalogPropSprMTitle . "`.`cod` AND `" . TblModCatalogPropSprMTitle . "`.`lang_id`='" . $this->lang_id . "')
                 LEFT JOIN `" . TblModCatalogPropSprMDescr . "` ON (`" . TblModCatalogProp . "`.`id` = `" . TblModCatalogPropSprMDescr . "`.`cod` AND `" . TblModCatalogPropSprMDescr . "`.`lang_id`='" . $this->lang_id . "')
                 LEFT JOIN `" . TblModCatalogPropSprMKeywords . "` ON (`" . TblModCatalogProp . "`.`id` = `" . TblModCatalogPropSprMKeywords . "`.`cod` AND `" . TblModCatalogPropSprMKeywords . "`.`lang_id`='" . $this->lang_id . "')
                 WHERE `" . TblModCatalogProp . "`.`id`='" . $this->id . "'
                 AND `" . TblModCatalogProp . "`.`id` = `" . TblModCatalogPropSprName . "`.`cod`
                 AND `" . TblModCatalogPropSprName . "`.`lang_id`='" . $this->lang_id . "'
                ";
            $res = $this->db->db_Query($q2);
            //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
            if (!$res OR !$this->db->result)
                return false;
            $row = $this->db->db_FetchAssoc();
            $item_name = stripslashes($row['name']);
            $this->tovarName = $item_name;
            $item_title = stripslashes($row['title']);
            $item_descr = stripslashes($row['descr']);
            $item_keywords = stripslashes($row['keywords']);
            //echo '<br>$row[name]='.$row['name'].' $item_name='.$item_name;

            if (empty($item_title))
                $this->title = strtoupper($item_name) . ' ' . $this->multi['TXT_META_PRICE'] . ' | ' . $this->multi['TXT_META_BUY'] . ' ' . $item_name . ' | ' . NAME_SERVER;
            else
                $this->title = $item_title;

            if (empty($item_descr))
                $this->description = $item_name;
            else
                $this->description = $item_descr;

            if (empty($item_keywords))
                $this->keywords = '';
            else
                $this->keywords = $item_keywords;
        }
        //for current category page
        elseif (!empty($this->id_cat)) {
            $q = "SELECT
                 `" . TblModCatalogSprName . "`.`name`,
                 `" . TblModCatalogSprMTitle . "`.`name` AS `title`,
                 `" . TblModCatalogSprMDescr . "`.`name` AS `descr`,
                 `" . TblModCatalogSprKeywords . "`.`name` AS `keywords`
                 FROM `" . TblModCatalogSprName . "`,`" . TblModCatalog . "`
                 LEFT JOIN `" . TblModCatalogSprMTitle . "` ON (`" . TblModCatalog . "`.`id` = `" . TblModCatalogSprMTitle . "`.`cod` AND `" . TblModCatalogSprMTitle . "`.`lang_id`='" . $this->lang_id . "')
                 LEFT JOIN `" . TblModCatalogSprMDescr . "` ON (`" . TblModCatalog . "`.`id` = `" . TblModCatalogSprMDescr . "`.`cod` AND `" . TblModCatalogSprMDescr . "`.`lang_id`='" . $this->lang_id . "')
                 LEFT JOIN `" . TblModCatalogSprKeywords . "` ON (`" . TblModCatalog . "`.`id` = `" . TblModCatalogSprKeywords . "`.`cod` AND `" . TblModCatalogSprKeywords . "`.`lang_id`='" . $this->lang_id . "')
                 WHERE `" . TblModCatalog . "`.`id`='" . $this->id_cat . "'
                 AND `" . TblModCatalog . "`.`id` = `" . TblModCatalogSprName . "`.`cod`
                 AND `" . TblModCatalogSprName . "`.`lang_id`='" . $this->lang_id . "'
                ";
            $res = $this->db->db_Query($q);
            //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
            if (!$res OR !$this->db->result)
                return false;
            $row = $this->db->db_FetchAssoc();
            $cat_name = stripslashes($row['name']);
            $this->categoryName = $cat_name;
            $cat_title = stripslashes($row['title']);
            $cat_descr = stripslashes($row['descr']);
            $cat_keywords = stripslashes($row['keywords']);

            //set title
            if (empty($cat_title))
                $this->title = strtoupper($cat_name) . ' | ' . $this->multi['TXT_META_BUY'] . ' ' . $cat_name . ' | ' . $cat_name . ' ' . $this->multi['TXT_META_PRICE'] . ' | ' . NAME_SERVER;
            else
                $this->title = $cat_title;
            if ($this->page > 1)
                $this->title .= ' - ' . $this->multi['TXT_META_PAGING'] . $this->page;

            //set description
            if (empty($cat_descr))
                $this->description = $cat_name;
            else
                $this->description = $cat_descr;

            //set keywords
            if (empty($cat_keywords))
                $this->keywords = '';
            else
                $this->keywords = $cat_keywords;
        }
        //for catalog main page
        else{
            $this->title = '';
            $this->description = '';
            $this->keywords = '';
        }
        return true;
    } //end of function  SetMetaData()

    /**
     * CatalogLayout::ShowHeaderSEO()
     * @author Yaroslav
     * @return string $upSEOMsg
     */
    function ShowHeaderSEO() {
        $upSEOMsg ='';
        if(!empty($this->id)) {
            $upSEOMsg = $this->tovarName.' купить в Сумах';
            //Название_подкатегории название_продукта купить в Сумах.
        }
        elseif(isset($this->id_cat)) {
            if(isset($this->treeCatLevels[$this->id_cat])) { // категория, подкатегория
                $count = count($this->treeCatLevels[$this->id_cat]);
                $keys = array_keys($this->treeCatLevels[$this->id_cat]);
                $this->strCategories = '';
                for($i = 0; $i<$count; $i++ ){
                    if($i==0)
                        $this->strCategories = $this->treeCatLevels[$this->id_cat][$keys[$i]];
                    else
                        $this->strCategories .= ', '.$this->treeCatLevels[$this->id_cat][$keys[$i]];
                }

                if($this->parent_level== 0 ) {
                    $upSEOMsg = '"SEOCMS" (Житомир): '.$this->categoryName.' купить '.$this->strCategories.' в Житомире ';
                }
                else {
                    $upSEOMsg = '"SEOCMS" (Житомир): '.$this->categoryName.' купить в Житомире '.$this->strCategories;
                }
            }
        }
        return $upSEOMsg;
    }

    /**
     * CatalogLayout::ShowFooterSEO()
     * @author Yaroslav
     * @return void
     */
    function ShowFooterSEO() {
        $dnSEOMsg ='';
        if(!empty($this->id)) {
            $dnSEOMsg = $this->tovarName.' купить с доставкой по всей Украине '.$this->tovarName.' - "SEOCMS", Житомир';
        }
        elseif(isset($this->id_cat)) {
            if(isset($this->treeCatLevels[$this->id_cat])) { // категория, подкатегория
                if($this->parent_level== 0 ) {
                    $dnSEOMsg = 'Интернет магазин "SEOCMS" (Житомир) -  '.$this->categoryName.' купить в Житомире с доставкой '.$this->strCategories;
                }
                else {
                    $dnSEOMsg = 'Интернет магазин "SEOCMS" (Житомир) -  '.$this->categoryName.' купить в Житомире с доставкой. '.$this->categoryName.' '.$this->strCategories;
                }
            }

        }
        return $dnSEOMsg;
    }

}
