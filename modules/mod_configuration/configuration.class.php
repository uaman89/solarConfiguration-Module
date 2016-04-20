<?php
include_once( SITE_PATH.'/modules/mod_configuration/configuration.defines.php' );

class Configuration {

    public $var1;

    function Configuration ($user_id=NULL, $module=NULL, $display=NULL, $sort=NULL, $start=NULL, $width = NULL) {
        //Check if Constants are overrulled

        $user_id  != "" ? $this->user_id = $user_id  : $this->user_id = NULL;
        $module   != "" ? $this->module  = $module   : $this->module  = NULL;

        $this-> lang_id = _LANG_ID;

        if (empty($this->db)) $this->db = new DB();
        if (empty($this->Right)) $this->Right = new Rights($this->user_id, $this->module);
        if (empty($this->Form)) $this->Form = new Form('form_mod_catalog_ImpExp');
        if (empty($this->Spr)) $this->Spr = new  SysSpr($this->user_id, $this->module);

        if (empty($this->multi)) $this->multi = check_init_txt('TblBackMulti',TblBackMulti);
    }
//--- End of Catalog_content Constructor -------------------------------------------------------------------------------


    function showFrom()
    {
        $script = 'module='.$this->module;
        $script = $_SERVER['PHP_SELF']."?$script";

        AdminHTML::PanelSimpleH();

        include ( SITE_PATH.'/modules/mod_configuration/spa/index.html' );

        AdminHTML::PanelSimpleF();
    }
//--- end of function show ------------------------------------------------------------------------------------------------------------



}
// end of class OrderImpExp
?>