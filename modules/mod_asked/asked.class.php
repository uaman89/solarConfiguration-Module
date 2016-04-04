<?php
// ================================================================================================
// System : SEOCMS
// Module : asked.class.php
// Version : 1.0.0
// Date : 27.05.2009
//
// Purpose : Class definition for all actions with asked pages
//
// ================================================================================================

 include_once( SITE_PATH.'/modules/mod_asked/asked.defines.php' );

/**
 * Asked
 *
 * @package
 * @author Yaroslav
 * @copyright 2011
 * @version 1.0
 * @access public
 */
class Asked {

    var $db;
    var $Right;
    var $Form;
    var $Msg;
    var $Spr;

    var $Err = '';
    var $author;
    var $email;
    var $question;

    var $id;

    var $module;
    var $user_id;
    var $task;
    var $display;
    var $start;
    var $sort;
    var $fltr;
    var $keywords;
    var $description;
    var $lang_id;

    var $sel;

    /**
     * Asked::Asked()
     *
     * @return void
     */
    function Asked()
    {
        if (empty($this->Spr)) $this->Spr = &check_init('SysSpr', 'SysSpr');
        if (empty($this->db)) $this->db = DBs::getInstance();
        if( defined("_LANG_ID") ) $this->lang_id = _LANG_ID;
    }


    /**
     * Asked::SaveAsked()
     *
     * @return
     */
    function SaveAsked()
    {
        $q = "INSERT INTO `".TblModAsked."` SET
             `category` = '".$this->asked_category."',
             `author` = '".$this->author."',
             `email` = '".$this->email."',
             `question` = '".$this->question."',
             `answer` = '',
             `visible` = '0',
             `dttm` = '".date("Y.m.d H:m:i")."'
             ";

        $res = $this->db->db_Query($q);
        if( !$res OR !$this->db->result ) return false;
        return true;
    }


   /**
    * Asked::ConvertDate()
    *
    * @param mixed $date_to_convert
    * @param bool $showTimeOnly
    * @param bool $showMonth
    * @return
    */
   function ConvertDate($date_to_convert, $showTimeOnly = false, $showMonth = false){
        $tmp = explode("-", $date_to_convert);
        $tmp2 = explode(" ", $tmp[2]);
        $month = NULL;
        $day = NULL;
        $year = NULL;
        $month =  $tmp[1];
        $day = intval($tmp2[0]);
        $year = $tmp[0];
        if($showMonth) {
            $month = intval($month);
            if(!isset($this->month[$month]))
                $this->month[$month] = $this->Spr->GetShortNameByCod(TblSysSprMonth, $month, $this->lang_id, 1);
            $month =  $this->month[$month];
            return $day." ".$month;
        }
        if($showTimeOnly) {
            $time = $tmp2[1];
            $tmp3 = explode(":", $time);
            return $tmp3[0].':'.$tmp3[1];      //18:30
        }
        return $day.".".$month.".".$year;
    } // end of function ConvertDate()



    function Link( $cat = NULL)
    {
        if( !defined("_LINK")) {
            $Lang = new SysLang(NULL, "front");
            $tmp_lang = $Lang->GetDefFrontLangID();
            if( ($Lang->GetCountLang('front')>1 OR isset($_GET['lang_st'])) AND $this->lang_id!=$tmp_lang) {
                define("_LINK", "/".$Lang->GetLangShortName($this->lang_id)."/");
            }
            else {
                define("_LINK", "/");
            }
        }

        if( !empty($cat) ){
            $str_cat = $this->Spr->GetTranslitByCod( TblModAskedCat, $cat, $this->lang_id );
        }
        /*elseif(!empty($id)){
            $str_cat =  $this->Spr->GetTranslitByCod( TblModAskedCat, $this->GetIdCatByIdNews($id), $this->lang_id );
        }*/
        else{
            $str_cat = NULL;
        }

        if(!empty($str_cat))
            $link = _LINK.'ask/'.$str_cat.'/';
        else
            $link = _LINK.'ask/';
        return $link;
    } // end of function Link

}
?>