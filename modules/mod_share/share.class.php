<?php
// ================================================================================================
// System : SEOCMS
// Module : pages.class.php
// Date : 04.10.2007
// Licensed To:
// Igor Trokhymchuk ihoru@mail.ru
// Purpose : Class definition for all actions with dynamic pages
// ================================================================================================

 include_once( SITE_PATH.'/modules/mod_share/share.defines.php' );

// ================================================================================================
//    Class                         : Share
//    Date              			: 04.10.2007
//    Constructor       			: Yes
//    Returns           			: None
//    Description       			: Pages Module
//    Programmer        			:  Igor Trokhymchuk
// ================================================================================================
class Share{

    var $Right;
    var $Form;
    var $Msg;
    var $Spr;
    
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
    var $preview = NULL;
    var $sel = NULL;
    var $is_image = 1;
    var $is_tags = 1;
    var $is_short_descr = 1;
    var $is_special_pos = 1;
    var $is_main_page = 1;     
     
    // ================================================================================================
    // Function : Share Constructor()
    // Date : 11.02.2005
    // Returns :      true,false / Void
    // Description :  Up position
    // Programmer : Igor Trokhymchuk
    // ================================================================================================
    function __construct() {
     $this->db =  DBs::getInstance(); 
     $this->Form =  &check_init('FormPages', 'Form', 'mod_pages');
     $this->Spr = &check_init('SysSpr', 'SysSpr');
     $this->width = '750';
     if(defined("_LANG_ID")) $this->lang_id = _LANG_ID;
    }//end Constructor
    
    
    // ================================================================================================
    // Function : IsSubLevels()
    // Date : 04.10.2007
    // Params :  $id - id of the current page
    //          $front_back - can be 'front' or 'back'
    // Returns :      true,false / Void
    // Description : return count of sublevels 
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function IsSubLevels($id, $front_back='front')
    {
        $db = DBs::getInstance();
        
        $q = "SELECT * FROM `".TblModShare."` WHERE `level`='".$id."'";
        if( $front_back=='front' ) $q = $q." AND `visible`='1'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $rows = $db->db_GetNumRows(); 
        return $rows; 
    }//end of function IsSubLevels()
    
    /**
     * Share::getTimeStamp()
     * 
     * @param mixed $date
     * @return timeStamp
     */
    function getTimeStamp($date){
    	$arrDate = explode(" ", $date);
    	$arrdate = explode("-", $arrDate[0]);
    	$arrtime = explode(":", $arrDate[1]);
    	$timestampEnd = (mktime($arrtime[0], $arrtime[1], 0, $arrdate[1],  $arrdate[2],  $arrdate[0]));
    }
    
    // ================================================================================================
    // Function : GetSubLevels()
    // Version : 1.0.0
    // Date : 21.07.2009
    // Params :  $id - id of the current page
    //          $front_back - can be 'front' or 'back'
    //          $return_type - can be 'arr' or 'str'
    // Returns :      true,false / Void
    // Description : return sublevels. If $return_type='arr' then you will see next:
    //              $ret[1]=''
    //              $ret[2]=''
    //              $ret[id]=''
    //              If $return_type='str' then you will see next: $ret = 1,2,id
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function GetSubLevels($id, $front_back='front', $return_type='arr')
    {
        $db = DBs::getInstance();
        
        $q = "SELECT * FROM `".TblModShare."` WHERE `level`='".$id."'";
        if( $front_back=='front' ) $q = $q." AND `visible`='1'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $rows = $db->db_GetNumRows();
        for($i=0;$i<$rows;$i++){
            $row = $db->db_FetchAssoc();
            if( $return_type=='arr' ){
                $ret[$row['id']]='';
            }
            elseif( $return_type=='str' ){
                if( empty($ret) ) $ret = $row['id'];
                else $ret .= ','.$row['id'];
            }
            else $ret = NULL;
        } 
        return $ret; 
    }//end of function GetSubLevels()
    
    function GetSettings()
   {       
    $q="select * from `".TblModCatalogSet."` where 1";
    $res = $this->db->db_Query( $q );
    if( !$this->db->result ) return false;
    //echo '$q = '.$q;
    $row = $this->db->db_FetchAssoc();
    return $row;         
   } // end of function GetSettings() 
    
    function SetMetaData($page){
	if(empty($this->share_id)) {
            if(!isset ($this->FrontendPages)) 
                $this->FrontendPages = &check_init('FrontendPages', 'FrontendPages');
            $this->FrontendPages->page_txt = $this->FrontendPages->GetPageTxt($page);
            
            $this->title = $this->FrontendPages->GetTitle();
            if(empty($this->title))
                $this->title = $this->Spr->GetNameByCod( TblModNewsSetSprTitle, 1, $this->lang_id, 1 );
            
            $this->description = $this->FrontendPages->GetDescription();
            if(empty($this->description))
                $this->description = $this->Spr->GetNameByCod( TblModNewsSetSprDescription, 1, $this->lang_id, 1 );
            
            $this->keywords = $this->FrontendPages->GetKeywords();
            if(empty($this->keywords))
                $this->keywords = $this->Spr->GetNameByCod( TblModNewsSetSprKeywords, 1, $this->lang_id, 1 );
            return;
        }else{
	    
	    if(!empty($this->share_txt['mtitle']))
	     $title = $this->share_txt['mtitle'];
	    else $title =$this->share_txt['pname'];
	    
	    if( !empty($this->title) ) $this->title = $title.' | '.$this->title;
	    else $this->title = $title;
	    
	    if(!empty($this->share_txt['mdescr']))
	     $descr = $this->share_txt['mdescr'];
	    else $descr = $this->share_txt['pname'];
	    
            if( !empty($descr) AND !empty($this->description) ) $this->description = $descr.'. '.$this->description;
            else {
                if( !empty($descr) ) $this->description = $descr;
                elseif( !empty($this->title) ) $this->description = $this->title.'. '.$this->description;
            }
	    if(!empty($this->share_txt['mkeywords']))
	     $keywrds = $this->share_txt['mkeywords'];
	    else $keywrds = $this->share_txt['pname'];
	    
            if( !empty($keywrds) AND !empty($this->keywords) ) $this->keywords = $keywrds.', '.$this->keywords;
            elseif( !empty($keywrds) ) $this->keywords = $keywrds;
	}
    }
    
    // ================================================================================================
    // Function : IsContent()
    // Version : 1.0.0
    // Date : 04.10.2007
    // Params : $id - id of the current page
    // Returns :      true,false / Void
    // Description : return exist content or not 
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function IsContent($id)
    {
        $res = $this->Spr->GetNameByCod(TblModPagesSprContent, $id, $this->lang_id, 1);
        if(!empty($res)) return true;
        else return false;
    }//end of function IsContent()    

    // ================================================================================================
    // Function : Disable()
    // Returns : true,false / Void
    // Programmer : Yaroslav Gyryn
    // Date : 21.02.2010
    // ================================================================================================
     function Disable($field=null)
    {     
        if($field==null)
            return false;
        $db = DBs::getInstance();
        $q="UPDATE `".TblModShare."` 
              SET `".$field."`= 2 
              WHERE `".$field."`= 1";
        $res = $db->db_Query($q);
        $rows = $db->db_GetNumRows($res);
        if(!$rows)
            return false;
        return true;
    }
    // ================================================================================================
    // Function : Enable()
    // Returns : true,false / Void
    // Programmer : Yaroslav Gyryn
    // Date : 21.02.2010
    // ================================================================================================
     function Enable($field=null)
    {  
        if($field==null)
            return false;
        $db = DBs::getInstance();
        $q="UPDATE `".TblModShare."` 
              SET `".$field."`= 1 
              WHERE `".$field."`= 2";
        $res = $db->db_Query($q);
        $rows = $db->db_GetNumRows($res);
        if(!$rows)
            return false;
        return true;
    }  
        
    // ================================================================================================
    // Function : GetIdByFolderName()
    // Date : 05.02.2008
    // Params :  $q - string with path
    // Returns :      true,false / Void
    // Description : return id of the page 
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function GetIdByFolderName($q=NULL, $level=0)
    {
        $tmp=explode("/",$q);
        for($i=0;$i<count($tmp);$i++){
            $result=array();
            if(preg_match('/^page([0-9]+)/', $tmp[$i],$result)){
                $this->page=$result[1];
                unset($tmp[$i]);
                //print_r($result);
            }
        }
        //print_r($tmp);
        $cnt = count($tmp);
        if( $cnt==0 ) return false;
        
        $db = DBs::getInstance();;
        if( empty($tmp[$cnt-1]) ) $cnt=$cnt-1;
        $q = "SELECT `id` FROM `".TblModShare."` WHERE BINARY `name`= BINARY '".$this->PrepareLink($tmp[$cnt-1])."'";
        //if link like http://cms.seotm/Napravleniya
        if( $cnt==1 ) $q =$q." AND `level`='".$level."'";
        //============ block for search uniqe link start ==============
        //if link like http://cms.seotm/Napravleniya/steel/services/services
        if( $cnt>3 AND !empty($tmp[$cnt-1]) ) $q = $q." AND `level`=(SELECT `id` FROM `".TblModShare."` WHERE BINARY `name`= BINARY '".$this->PrepareLink($tmp[$cnt-2])."' AND `level`=(SELECT `id` FROM `".TblModShare."` WHERE BINARY `name`= BINARY '".$this->PrepareLink($tmp[$cnt-3])."' AND `level`=(SELECT `id` FROM `".TblModShare."` WHERE BINARY `name`= BINARY '".$this->PrepareLink($tmp[$cnt-4])."' AND `level`='".$level."')))";
        //if link like http://cms.seotm/Napravleniya/steel/services
        elseif( $cnt>2 AND !empty($tmp[$cnt-1]) ) $q = $q." AND `level`=(SELECT `id` FROM `".TblModShare."` WHERE BINARY `name`= BINARY '".$this->PrepareLink($tmp[$cnt-2])."' AND `level`=(SELECT `id` FROM `".TblModShare."` WHERE BINARY `name`= BINARY '".$this->PrepareLink($tmp[$cnt-3])."' AND `level`='".$level."'))";
        //if link like http://cms.seotm/Napravleniya/steel
        elseif($cnt>1 AND !empty($tmp[$cnt-1]) ) $q = $q." AND `level`=(SELECT `id` FROM `".TblModShare."` WHERE BINARY `name`= BINARY '".$this->PrepareLink($tmp[$cnt-2])."' AND `level`='".$level."')";
        //============ block for search uniqe link end ==============
        $res = $db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $row = $db->db_FetchAssoc(); 
        return  $row['id']; 
    }//end of function GetIdByFolderName()    
    
    // ================================================================================================
    // Function : GetIdByName()
    // Date : 05.02.2008
    // Params :  $pn - name of the link
    // Returns :      true,false / Void
    // Description : return id of the page 
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function GetIdByName($pn)
    {
        $db = DBs::getInstance();
        $q = "SELECT `id` FROM `".TblModShare."` WHERE `name`='".$pn.".html'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $row = $db->db_FetchAssoc(); 
        return $row['id']; 
    }//end of function GetIdByName()
    
    // ================================================================================================
    // Function : GetNameById()
    // Date : 09.02.2008
    // Params :  $id - id of the page
    // Returns :      true,false / Void
    // Description : return name of the page 
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function GetNameById($id)
    {
        $db = DBs::getInstance();
        $q = "SELECT `name` FROM `".TblModShare."` WHERE `id`='".$id."'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $row = $db->db_FetchAssoc(); 
        return $row['name']; 
    }//end of function GetIdByName()    

    // ================================================================================================
    // Function : GetLevel()
    // Date : 19.02.2008
    // Params :  $id - id of the page
    // Returns :      true,false / Void
    // Description : return level of the page 
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function GetLevel($id)
    {
        $db = DBs::getInstance();;
        $q = "SELECT `level` FROM `".TblModShare."` WHERE `id`='".$id."'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $row = $db->db_FetchAssoc(); 
        return $row['level']; 
    }//end of function GetLevel()
    
    // ================================================================================================
    // Function : GetTopMainLevel()
    // Date : 19.02.2008
    // Params :  $id - id of the page
    // Returns :      true,false / Void
    // Description : return level of the page 
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function GetTopMainLevel($id)
    {
        $a= $this->GetLevel($id);
        $b=$a;
        if ($a==0) return $id;
        while ($b!=0)
        {
             $b= $this->GetLevel($a);
             if($b!=0) $a=$b;
        }
        return $a;
    }//end of function GetLevel() 
    
    // ================================================================================================
    // Function : GetPath()
    // Date : 19.02.2008
    // Params :  $id - id of the page
    //           $path - string with path for recursive execute
    // Returns :      true,false / Void
    // Description : return path to the page like services/services_new/1/opp1.html 
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function GetPath($id, $path=NULL)
    {
            $db = DBs::getInstance();
            $q = "SELECT `name`, `level`, `ctrlscript` FROM `".TblModShare."` WHERE `id`='".$id."'";
            $res =$db->db_Query( $q );
            //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
            if( !$res OR !$db->result ) return false;
            $row = $db->db_FetchAssoc();
            //echo '<br>$row[name]='.$row['name'];
            if( empty($row['name']) ) return false;
            if(! empty($path) ) $path = $this->PrepareLink($row['name']).$path;
            else $path = $row['name'];
            if($row['ctrlscript']==1){
                if( $row['level']>0 ) $ret = _LINK.'share/'.$this->GetPath($row['level'], $path);
                else $ret = _LINK.'share/'.$path;
            }
            else $ret = _LINK.'share/'.$path;
        //echo '<br>$ret='.$ret; 
        return $ret; 
    }//end of function GetPath()      

    // ================================================================================================
    // Function : SetPath()
    // Date : 27.06.2008
    // Params :  $id - id of the page
    // Returns :      true,false / Void
    // Description : set path (tranlit) to the page
    // Programmer : Igor Trokhymchuk
    // ================================================================================================    
    function SetPath($id)
    {
        $db = DBs::getInstance();
        $urlname = NULL;
        $q = "SELECT `name`, `level`, `main_page` FROM `".TblModShare."` WHERE `id`='".$id."'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $row = $db->db_FetchAssoc();
        //generate translit for empty URL only if this is not main page of the site 
        if( empty($row['name']) AND $row['main_page']==0 ){
            $urlname = $this->GenerateTranslit($row['level'], $id, $this->Spr->GetByCod(TblModPagesSprName, $id));
            $q = "UPDATE `".TblModShare."` SET
                  `name`='".$urlname."'
                  WHERE `id`='".$id."'";
            $res =$db->db_Query( $q );
            //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
            if( !$res OR !$db->result ) return false;
        }        
        return $urlname; 
    }//end of function SetPath()  
    
    // ================================================================================================
    // Function : GetPagesInArray()
    // Date : 04.04.2006
    // Returns : true,false / Void
    // Description : Show structure of pages in Combo box
    // Programmer : Igor Trokhymchuk 
    // ================================================================================================
    function GetShareInArray($level = NULL, $default_val = NULL, $mas = NULL, $spacer = NULL, $show_content = 1, $front_back = 'back', $show_sublevels = 1)
    {
        $db = DBs::getInstance();
        $tmp_db = DBs::getInstance();
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
        for( $i = 0; $i < $rows; $i++ )
        {
            $arr_data[$i]=$db->db_FetchAssoc();
        }
        //echo '<br> $rows='.$rows;
        //echo '<br> $show_content='.$show_content; 
        $mas[''] = $default_val; 
        for( $i = 0; $i < $rows; $i++ )
        {
            $row=$arr_data[$i];
            $mas[''.$row['id']] = $spacer.'- '.stripslashes($row['pname']);
             
            $tmp_q = "SELECT `id` FROM ".TblModShare." WHERE `level`=".$row['id'];
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
    } // end of function GetPagesInArray() 
       
    // ================================================================================================
    // Function : GetPageDataInArr()
    // Date : 12.11.2007
    // Returns : true,false / Void
    // Programmer : Yaroslav Gyryn
    // ================================================================================================      
    function GetShareDataInArr($page){
        $db = DBs::getInstance();
        //$arr = array();
        
        $q = "select * from `".TblModShare."` where 1 and `id`='".$page."' order by `move` ";
        $res = $db->db_Query($q);
        //echo '<br>$q='.$q.' $res='.$res;
        $rows = $db->db_GetNUmRows($res);
        for($i=0; $i<$rows; $i++)
        {
         $arr_data[$i] = $db->db_FetchAssoc($res);
        }
        //echo $this->page;
        for($i=0; $i<$rows; $i++)
        {
         $row = $arr_data[$i];
         $sublevels = $this->IsSubLevels($row['id'], 'back');
         //$count_content = $this->IsContent($row['id']);
         //echo '<br>$count_content='.$count_content;
         //echo '<br>$this->level='.$this->level.' $row[id]='.$row['id'];  
         $j++;
         $arr['id'] = $row['id'];
         $arr['name'] = $this->Spr->GetNameByCod( TblModPagesSprName, $row['id'], $this->lang_id, 0);
         if($sublevels>0) {
          $arr2 = $this->GetShareDataInArr($row['id'], $j, $arr);
          if(is_array($arr)) $arr = array_merge($arr, $arr2);
         }
        } //end for
        //print_r($arr);
        return $arr; 
    } // end of funfiotn GetPageDataInArr() 
    
    // ================================================================================================
    // Function : IsDynamicPage()
    // Date : 26.03.2008
    // Parms : $id - id of the page
    // Returns : true,false / Void
    // Description : is this page a dynamic page or external module
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================    
    function IsSharePage($id){
        $db = DBs::getInstance();
        $q = "SELECT `ctrlscript` FROM `".TblModShare."` WHERE `id`='".$id."'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $row = $db->db_FetchAssoc();
        //echo '<br>$row[ctrlscript]='.$row['ctrlscript'];
        return $row['ctrlscript'];
    } // end of funfiotn IsDynamicPage() 
    
    // ================================================================================================
    // Function : GetImgWithPath()
    // Date : 29.05.2008
    // Parms : $id - id of the page
    // Returns : true,false / Void
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================ 
    function GetImgWithPath($img, $lang_id){
        return Pages_Img_Path_Small.$lang_id.'/'.$img;
    } // end of funfiotn GetImgWithPath()

    // ================================================================================================
    // Function : GetImgWithPathFull()
    // Date : 29.05.2008
    // Parms : $id - id of the page
    // Returns : true,false / Void
    // Description : 
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================ 
    function GetImgWithPathFull($img, $lang_id){
        return Pages_Img_Path.$lang_id.'/'.$img;
    } // end of funfiotn GetImgWithPathFull()
    
    // ================================================================================================
    // Function : GenerateTranslit()
    // Date : 20.02.2008
    // Parms :  $id_cat - if of the level
    //          $id     - id of the current position
    //          $name    -  name of the current position
    // Returns : true,false / Void
    // Description :  generate translit name of current position
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function GenerateTranslit($id_cat, $id, $name = NULL)
    {
        $Crypt = &check_init('Crypt', 'Crypt');
        $lang = &check_init('SysLang', 'SysLang');
        $default_lang = $lang->GetDefFrontLangID();
        $translit = NULL;

        if( is_array($name)){
            //fisrt try to get name in English
            if( isset($name[1]) AND !empty($name[1]) AND $default_lang!=1){
                $tmp_name = stripslashes(trim($name[1]));
            }
            //if not exist name in English then use name on default language of front-end
            elseif( isset($name[$default_lang]) AND !empty($name[$default_lang]) ){
                $tmp_name = stripslashes(trim($name[$default_lang]));
            }
            //if not exist name on default language  then try to use name on language of admin-part
            elseif( isset($name[$this->lang_id]) AND !empty($name[$this->lang_id]) ){
                $tmp_name = stripslashes(trim($name[$this->lang_id]));
            }
            else $tmp_name=NULL;
            //echo '<br>$tmp_name='.$tmp_name; 
            
            // crop last sympol "/" if it is exist in the link.
            if( (strrpos($tmp_name, "/")+1) == strlen($tmp_name) ){
                $tmp_name = substr($tmp_name, 0, strrpos($tmp_name, "/") );
            }

            
            //get translited string
            $translit_no_last_slash = $Crypt->GetTranslitStr(stripslashes(trim($tmp_name)));
            //echo '<br>$tmp_name='.$tmp_name.' $translit_no_last_slash='.$translit_no_last_slash;
        }
        else{
            //get translited string
            $translit_no_last_slash = $Crypt->GetTranslitStr(stripslashes(trim($name)));
        }
        //before check tranlsit in the database add last symbol "/" to translitted string $translit_no_last_slash
        $translit = $this->PrepareLink($translit_no_last_slash);
        
        //chek if already exist record with same translit. 
        $res_id = $this->IsExistTranslit($translit, $id_cat);
        //echo '<br>$res_id='.$res_id.' $id='.$id;
        if( $res_id!='' AND $res_id!=$id ){
            $translit = $this->PrepareLink($translit_no_last_slash.'-'.$id);
        }
        //echo '<br>$translit='.$translit;
        return $translit;
    }// end of function GenerateTranslit()

    // ================================================================================================
    // Function : IsExistTranslit()
    // Date : 20.02.2008
    // Parms :  $translit - translit name
    //          $id_cat - if of the category
    // Returns : true,false / Void
    // Description :  save translit name of current position
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function IsExistTranslit($translit, $id_cat)
    {
        $db = DBs::getInstance();
        $q = "SELECT `id` FROM `".TblModShare."` WHERE `level`='".$id_cat."' AND `name`='".$translit."'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $row = $db->db_FetchAssoc();
        return $row['id'];
    }// end of function IsExistTranslit()
 

    // ================================================================================================
    // Function : PrepareLink()
    // Date : 18.07.2009
    // Parms :  $name     - url of the current position
    //          $ctrlscript - inner or outer link to page 
    // Returns : true,false / Void
    // Description : prepare translit str to link
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function PrepareLink($name, $ctrlscript=true)
    {
        //echo '<br>$name='.$name.' (strrpos($name, "/")+1)='.(strrpos($name, "/")+1).' strlen($name)='.strlen($name);
        if( $ctrlscript AND !empty($name) AND !(strstr($name, ".htm")) AND (strrpos($name, "/")+1) != strlen($name) ) $name = $name."/";
        return $name;
    }// end of function PrepareLink()
    
    
    // ================================================================================================
    // Function : GetShareTxt()
    // Date : 24.11.2011
    // Parms :  $id - id of the current position
    // Returns : true,false / Void
    // Description : return all text files for page $id
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function GetShare($id, $lang_id=NULL)
    {
        if( isset($this->share_txt) AND is_array($this->share_txt) AND $id==$this->page) return $this->share_txt;
        $share_txt = $this->GetShareData($id, $lang_id=NULL); 
        return $share_txt;  
    }// end of function GetPageTxt()   
   
   // ================================================================================================
   // Function : GetPageData()
   // Date : 18.05.2010
   // Returns : true,false / Void
   // Description : get dat of $page in array 
   // Programmer : Igor Trokhymchuk 
   // ================================================================================================
   function GetShareData($share, $lang_id=NULL)
   {
        $db = DBs::getInstance();
        if(empty($lang_id)) $lang_id = $this->lang_id;
        $q = "SELECT `".TblModShare."`.*, `".TblModShareTxt."`.*,`".TblModShareFileImg."`.`path` AS `img`  FROM `".TblModShare."`
              LEFT JOIN `".TblModShareTxt."` ON (`".TblModShare."`.`id`=`".TblModShareTxt."`.`cod` AND `".TblModShareTxt."`.`lang_id`='".$lang_id."')
              LEFT JOIN `".TblModShareFileImg."` ON (`".TblModShare."`.`id`=`".TblModShareFileImg."`.`id_position` AND `".TblModShareFileImg."`.`visible`='1' AND `".TblModShareFileImg."`.`move`='0')
              WHERE `".TblModShare."`.`id`='".$share."'";
        $res =$db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $db->result='.$db->result;
        if( !$res OR !$db->result ) return false;
        $row = $db->db_FetchAssoc();
        return $row;
   } //end of function GetPageData()
    
    // ================================================================================================
    // Function : QuickSearch()
    // Date : 27.03.2008
    // Returns : true,false / Void
    // Programmer : Yaroslav Gyryn
    // ================================================================================================    
    function QuickSearch($search_keywords)
    {
        $search_keywords = stripslashes($search_keywords);
        $sel_table = NULL;
        $str_like = NULL;
        $filter_cr = ' OR ';

        $str_like = $this->build_str_like(TblModShareTxt.'.pname', $search_keywords);
        $str_like .= $filter_cr.$this->build_str_like(TblModShareTxt.'.content', $search_keywords);
        
        $q ="SELECT `".TblModShare."`.id, `".TblModShare."`.id_categ, `".TblModShare."`.visible, `".TblModShare."`.level, `".TblModShare."`.move, `".TblModShareTxt."`.*
            FROM `".TblModShare."`, `".TblModShareTxt."`
            WHERE (".$str_like.")
            AND `".TblModShare."`.id = `".TblModShareTxt."`.cod
            AND `".TblModShareTxt."`.lang_id = '".$this->lang_id."'
            AND `".TblModShare."`.visible = '1' 
            ORDER BY `".TblModShare."`.level, `".TblModShare."`.move";
        $res =  $this->db->db_Query( $q );
        //echo '<br>q='.$q.' res='.$res;
        //echo '<br>q='.$q.' res='.$res.'  $this->db->result='. $this->db->result;
        if ( !$res OR ! $this->db->result ) return false;  
        $rows = $this->db->db_GetNumRows();
        return $rows;
    } // end of function QuickSearch    
  
    // ================================================================================================
    // Function : build_str_like
    // Date : 19.01.2005
    // Parms : $find_field_name - name of the field by which we want to do search
    //         $field_value - value of the field
    // Returns : str_like_filter - builded string with special format;
    // Description : create the string for SQL-command SELECT for search in the text field by any word
    // Programmer : Igor Trokhymchuk
    // ================================================================================================
    function build_str_like($find_field_name, $field_value)
    {
        $str_like_filter=NULL;
        // cut unnormal symbols
        $field_value=preg_replace("/[^\w\x7F-\xFF\s]/", " ", $field_value);
        // delete double spacebars
        $field_value=str_replace(" +", " ", $field_value);
        $wordmas=explode(" ", $field_value);

        for ($i=0; $i<count($wordmas); $i++){
              $wordmas[$i] = trim($wordmas[$i]);
              if (EMPTY($wordmas[$i])) continue;
              if (!EMPTY($str_like_filter)) $str_like_filter=$str_like_filter." AND ".$find_field_name." LIKE '%".$wordmas[$i]."%'";
              else $str_like_filter=$find_field_name." LIKE '%".$wordmas[$i]."%'";
        }
        if ($i>1) $str_like_filter="(".$str_like_filter.")";
        //echo '<br>$str_like_filter='.$str_like_filter;
        return $str_like_filter;
    } //end of function build_str_like() 
    
     // ================================================================================================
     // Function : GetPagesNameLinkForId()
     // Date : 11.09.2009
     // Returns :      true,false / Void
     // Description :  Get Pages Name Link For Id()  
     // Programmer : Ihor Trokhymchuk
     // ================================================================================================
     function GetSharesNameLinkForId($str = null) {
          $q = "SELECT 
                `".TblModShare."`.id,
                `".TblModShare."`.level,
                `".TblModShare."`.name,
                `".TblModShareTxt."`.name AS `pagename`
            FROM 
                `".TblModShare."`, `".TblModShareTxt."`
            WHERE 
                `".TblModShareTxt."`.cod = `".TblModShare."`.id
            AND 
                `".TblModShareTxt."`.lang_id='".$this->lang_id."'
            AND
                `".TblModShare."`.id IN (".$str.")
            ";
            $res = $this->db->db_Query( $q );
            //echo "<br> ".$q." <br/> res = ".$res;
            $rows = $this->db->db_GetNumRows($res);
            
            $arrNews = array();
            for( $i=0; $i<$rows; $i++ ) {
                $row = $this->db->db_FetchAssoc($res);
                $id = $row['id'];
                if(!isset($arrNews[$id])) {
                    $arr[$id]['name'] = $row['pagename'];
                    $arr[$id]['link'] = $this->Link($id);
                }
            }
            return $arr;
     } //end of function GetPagesNameLinkForId()      
   
} //end of class Share
?>