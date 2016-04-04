<?php
// ================================================================================================
// System : SEOCMS
// Module : catalog.class.php
// Version : 1.0.0
// Date : 21.03.2006
// Licensed To:
// Igor Trokhymchuk ihoru@mail.ru
// Purpose : Class definition for all actions with managment of catalog
//
// ================================================================================================

include_once( SITE_PATH.'/modules/mod_catalog/catalog.defines.php' );

// ================================================================================================
//    Class             : Catalog_category
//    Date              : 21.03.2006
//    Constructor       : Yes
//    Parms             : session_id / session id
//                        usre_id    / UserID
//                        user_      /
//                        user_type  / id of group of user
//    Returns           : None
//    Description       : Class definition for all actions with managment of Catalog
//    Programmer        :  Igor Trokhymchuk
// ================================================================================================
 class Catalog_category extends Catalog {

   // ================================================================================================
   //    Function          : Catalog (Constructor)
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
   function Catalog_category ($user_id=NULL, $module=NULL, $display=NULL, $sort=NULL, $start=NULL, $width = NULL) {
        //Check if Constants are overrulled
        ( $user_id   !="" ? $this->user_id = $user_id  : $this->user_id = NULL );
        ( $module   !="" ? $this->module  = $module   : $this->module  = NULL );
        ( $display  !="" ? $this->display = $display  : $this->display = 20   );
        ( $sort     !="" ? $this->sort    = $sort     : $this->sort    = NULL );
        ( $start    !="" ? $this->start   = $start    : $this->start   = 0    );
        ( $width    !="" ? $this->width   = $width    : $this->width   = 750  );

        $this-> lang_id = _LANG_ID;

        if (empty($this->db)) $this->db = DBs::getInstance();
        if (empty($this->Right)) $this->Right = &check_init('RightsCatalog', 'Rights', "'".$this->user_id."', '".$this->module."'");
        //echo '<br>$this->Right->user='.$this->Right->user.' $this->Right->module='.$this->Right->module;
        if (empty($this->Msg)) $this->Msg = &check_init('ShowMsg', 'ShowMsg');
        if (empty($this->Form)) $this->Form = &check_init('FormCatalog', 'Form', '"form_mod_catalog"');
        if (empty($this->Spr)) $this->Spr = &check_init('SysSpr', 'SysSpr', "'$this->user_id', '$this->module'");
        if (empty($this->settings)) $this->settings = $this->GetSettings();
        //if (empty($this->multi)) $this->multi = $this->Spr>GetMulti(TblModCatalogSprTxt);
        if (empty($this->multi)) $this->multi = &check_init_txt('TblBackMulti',TblBackMulti);

        //load all data of categories to arrays $this->treeCat, $this->treeCatLevels, $this->treeCatData
        $this->loadTree();
        //echo '<br />treeCatLevels=';print_r($this->treeCatLevels);
        //echo '<br />treeCatData=';print_r($this->treeCatData);

        $this->UploadImages = &check_init('UploadImage', 'UploadImage', "'90', 'null', 'uploads/images/catalog', 'mod_catalog_file_img'");
        //$this->UploadImages->CreateTables();

            // add new tables
            //$this->AddTbl();
   } // End of Catalog Constructor

       // ================================================================================================
       // Function : AddTbl()
       // Date : 17.04.2007
       // Returns :      true,false / Void
       // Description :  Add tables
       // Programmer :  Igor Trokhymchuk
       // ================================================================================================
       function AddTbl()
       {
          $tmp_db = DBs::getInstance();

          if( defined("DB_TABLE_CHARSET")) $this->tbl_charset = DB_TABLE_CHARSET;
          else $this->tbl_charset = 'utf8';

           // create table for strore individual name of category
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogSprNameInd."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` char(255) NOT NULL default '',
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for store relations between categories
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogRelat."` (
              `id` int(11) unsigned NOT NULL auto_increment,
              `id_cat1` int(11) unsigned NOT NULL default '0',
              `id_cat2` int(11) unsigned NOT NULL default '0',
              `move` int(11) unsigned NOT NULL default '0',
              PRIMARY KEY  (`id`),
              KEY `id_cat1` (`id_cat1`,`id_cat2`),
              KEY `move` (`move`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for store relations between positions of catalogue
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogPropRelat."` (
              `id` int(11) unsigned NOT NULL auto_increment,
              `id_prop1` int(11) unsigned NOT NULL,
              `id_prop2` int(11) unsigned NOT NULL,
              `move` int(11) unsigned NOT NULL,
              PRIMARY KEY  (`id`),
              KEY `id_prop1` (`id_prop1`,`id_prop2`),
              KEY `move` (`move`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for store translit name of categories
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogTranslit."` (
              `id` int(11) unsigned NOT NULL auto_increment,
              `translit` varchar(255) NOT NULL,
              `id_cat` int(11) unsigned NOT NULL,
              `id_cat_parent` int(11) unsigned default NULL,
              `id_prop` int(11) unsigned default NULL,
              PRIMARY KEY  (`id`),
              KEY `id_cat` (`id_cat`),
              KEY `id_prop` (`id_prop`),
              KEY `id_cat_parent` (`id_cat_parent`),
              FULLTEXT KEY `translit` (`translit`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for store files
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogPropFiles."` (
              `id` int(11) unsigned NOT NULL auto_increment,
              `id_prop` int(11) unsigned NOT NULL default '0',
              `path` varchar(255) NOT NULL default '',
              `show` int(1) unsigned default NULL,
              `move` int(11) unsigned default NULL,
              PRIMARY KEY  (`id`),
              KEY `id_prop` (`id_prop`),
              KEY `show` (`show`),
              KEY `move` (`move`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for store files title
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogPropFilesTxt."` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `cod` int(10) unsigned NOT NULL default '0',
                `lang_id` int(10) unsigned NOT NULL default '0',
                `name` varchar(255) NOT NULL default '',
                `text` text NOT NULL,
                PRIMARY KEY  (`id`),
                KEY `cod` (`cod`),
                KEY `lang_id` (`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // add field visible to the table mod_catalog_prop
           if ( !$this->db->IsFieldExist(TblModCatalogProp, "visible") ) {
               $q = "ALTER TABLE `".TblModCatalogProp."` ADD `visible` INT( 1 ) UNSIGNED DEFAULT '2';";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;

               $q = "ALTER TABLE `".TblModCatalogProp."` ADD INDEX ( `visible` ) ;";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;
           }

           // create table for store catalog statistic logs
           $q = "CREATE TABLE IF NOT EXISTS `".TblModCatalogStatLog."` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `id_cat` int(11) unsigned default NULL,
                `id_prop` int(11) unsigned default NULL,
                `id_img` int(11) unsigned default NULL,
                `id_file` int(11) unsigned default NULL,
                `id_manufac` int(11) unsigned default NULL,
                `id_group` int(11) unsigned default NULL,
                `time_gen` float default NULL,
                `page` text,
                `refer` text,
                `dt` date default NULL,
                `tm` time default NULL,
                `ip` double default NULL,
                `host` text,
                `proxy` int(11) unsigned default NULL,
                `agent` text,
                `screen_res` text,
                `lang` char(4) default NULL,
                `country` char(10) default NULL,
                `cnt` int(11) default NULL,
                `id_user` int(11) unsigned default NULL,
                PRIMARY KEY ( `id` ) ,
                INDEX ( `id_cat` , `id_prop` , `id_img` , `id_file` , `time_gen` , `id_user` )
                ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset."; ";
           $res = $this->db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for catalog responses and comments and rating
           $q = "CREATE TABLE IF NOT EXISTS `".TblModCatalogResponse."` (
                  `id` int(11) unsigned NOT NULL auto_increment,
                  `id_prop` int(11) unsigned NOT NULL default '0',
                  `name` varchar(255) NOT NULL default '',
                  `email` varchar(255) NOT NULL default '',
                  `response` text NOT NULL,
                  `rating` int(2) unsigned NOT NULL default '0',
                  `dt` date NOT NULL default '0000-00-00',
                  `status` int(2) unsigned NOT NULL default '0',
                  `move` int(11) unsigned NOT NULL default '0',
                  PRIMARY KEY  (`id`),
                  KEY `id_prop` (`id_prop`,`rating`),
                  KEY `status` (`status`),
                  KEY `move` (`move`)
                ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";";
           $res = $this->db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // add field price_currency to the table mod_catalog_prop
           if ( !$this->db->IsFieldExist(TblModCatalogProp, "price_currency") ) {
               $q = "ALTER TABLE `".TblModCatalogProp."` ADD `price_currency` INT( 2 ) UNSIGNED DEFAULT NULL;";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;

               $q = "ALTER TABLE `".TblModCatalogProp."` ADD INDEX ( `price_currency` ) ;";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;
           }

           // add field opt_price_currency to the table mod_catalog_prop
           if ( !$this->db->IsFieldExist(TblModCatalogProp, "opt_price_currency") ) {
               $q = "ALTER TABLE `".TblModCatalogProp."` ADD `opt_price_currency` INT( 2 ) UNSIGNED DEFAULT NULL;";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;

               $q = "ALTER TABLE `".TblModCatalogProp."` ADD INDEX ( `opt_price_currency` ) ;";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;
           }


           // create table for catalog price levels
           $q = "CREATE TABLE IF NOT EXISTS `".TblModCatalogPriceLevels."` (
                `id` int(11) unsigned NOT NULL auto_increment,
                `id_prop` int(11) unsigned NOT NULL default '0',
                `qnt_from` varchar(30) NOT NULL default '',
                `qnt_to` varchar(30) NOT NULL default '',
                `price_level` varchar(30) NOT NULL default '',
                `currency` int(2) unsigned default NULL,
                `id_user` int(11) unsigned default NULL,
                PRIMARY KEY  (`id`),
                INDEX ( `id_prop` , `qnt_from` , `qnt_to` , `currency` , `id_user` )
                );";

           $res = $this->db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for catalog positions meta title
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogPropSprMTitle."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` char(255) NOT NULL default '',
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for catalog positions meta description
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogPropSprMDescr."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` char(255) NOT NULL default '',
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for catalog positions meta keywords
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogPropSprMKeywords."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` char(255) NOT NULL default '',
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for catalog categories meta title
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogSprMTitle."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` char(255) NOT NULL default '',
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for strore catalog categories meta description
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogSprMDescr."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` char(255) NOT NULL default '',
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for strore keywords of catalog category
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogSprKeywords."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` char(255) NOT NULL default '',
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // add field lang_id to the table mod_catalog_translit
           if ( !$this->db->IsFieldExist(TblModCatalogTranslit, "lang_id") ) {
               $q = "ALTER TABLE `".TblModCatalogTranslit."` ADD `lang_id` INT( 4 ) UNSIGNED NOT NULL ;";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;

               $q = "ALTER TABLE `".TblModCatalogTranslit."` ADD INDEX ( `lang_id` ) ;";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;
           }

           // create table for catalog parameters description
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogParamsSprDescr."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` text NOT NULL,
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for catalog parameters meta title
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogParamsSprMTitle."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` char(255) NOT NULL default '',
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for catalog parameters meta description
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogParamsSprMDescr."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` char(255) NOT NULL default '',
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for catalog parameters meta keywords
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogParamsSprMKeywords."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` char(255) NOT NULL default '',
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`,`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for catalog parameters influence of images
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogParamsPropImg."` (
              `id` int(11) unsigned NOT NULL auto_increment,
              `id_img` int(11) unsigned default NULL,
              `id_param` int(11) unsigned default NULL,
              `val` varchar(255) default NULL,
              PRIMARY KEY  (`id`),
              KEY `id_img` (`id_img`,`id_param`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for groups of positions
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogPropGroups."` (
              `id` int(11) unsigned NOT NULL auto_increment,
              `id_prop` int(11) unsigned default NULL,
              `id_group` int(11) unsigned default NULL,
              PRIMARY KEY  (`id`),
              KEY `id_prop` (`id_prop`),
              KEY `id_group` (`id_group`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;


           // add field new to the table mod_catalog_prop
           if ( !$this->db->IsFieldExist(TblModCatalogProp, "new") ) {
               $q = "ALTER TABLE `".TblModCatalogProp."` ADD `new` SET( '0', '1' ) NULL DEFAULT '0';";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;

               $q = "ALTER TABLE `".TblModCatalogProp."` ADD INDEX ( `new` ) ;";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;
           }

           // add field best to the table mod_catalog_prop
           if ( !$this->db->IsFieldExist(TblModCatalogProp, "best") ) {
               $q = "ALTER TABLE `".TblModCatalogProp."` ADD `best` SET( '0', '1' ) NULL DEFAULT '0';";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;

               $q = "ALTER TABLE `".TblModCatalogProp."` ADD INDEX ( `best` ) ;";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;
           }

           // add field art_num to the table mod_catalog_prop
           if ( !$this->db->IsFieldExist(TblModCatalogProp, "art_num") ) {
               $q = "ALTER TABLE `".TblModCatalogProp."` ADD `art_num` varchar(50) NULL DEFAULT '';";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;
           }

           // add field barcode to the table mod_catalog_prop
           if ( !$this->db->IsFieldExist(TblModCatalogProp, "barcode") ) {
               $q = "ALTER TABLE `".TblModCatalogProp."` ADD `barcode` varchar(50) NULL DEFAULT '';";
               $res = $this->db->db_Query( $q );
               //echo '<br>$q='.$q.' $res='.$res;
               if( !$res )return false;
           }

           // create table for catalog category additional info
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogSprDescr2."` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `cod` int(10) unsigned NOT NULL default '0',
              `lang_id` int(10) unsigned NOT NULL default '0',
              `name` text NOT NULL,
              PRIMARY KEY  (`id`),
              KEY `cod` (`cod`),
              KEY `lang_id` (`lang_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           // create table for catalog category additional info
           $q = "
            CREATE TABLE IF NOT EXISTS `".TblModCatalogPropMultiCategs."` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `id_prop` int(11) unsigned DEFAULT NULL,
              `id_cat` int(11) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `id_prop` (`id_prop`),
              KEY `id_cat` (`id_cat`)
            ) ENGINE=MyISAM DEFAULT CHARSET=".$this->tbl_charset.";
            ";
           $res = $tmp_db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res;
           if( !$res )return false;

           return true;
       } //end of fuinction AddTbl()

   // ================================================================================================
   // Function : GetContent
   // Date : 19.03.2008
   // Returns : true,false / Void
   // Description : execute SQL query
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function GetContent($limit='limit')
   {
    if( !$this->sort ) $this->sort='move';
    //$q = "SELECT * FROM ".TblModCatalog." WHERE `level`='".$this->level."'";
    /*
    $q = "SELECT `".TblModCatalog."`.*, `".TblModCatalogSprName."`.name as `prodname`, `".TblModCatalogSprNameInd."`.name as `nameind`
          FROM `".TblModCatalog."`, `".TblModCatalogSprName."`, `".TblModCatalogSprNameInd."`
          WHERE `level`='".$this->level."'
          AND `".TblModCatalog."`.id=`".TblModCatalogSprName."`.cod
          AND `".TblModCatalog."`.id=`".TblModCatalogSprNameInd."`.cod
          AND `".TblModCatalogSprName."`.lang_id='".$this->lang_id."'
          AND `".TblModCatalogSprNameInd."`.lang_id='".$this->lang_id."'
          ";
    $q = $q." ORDER BY ".$this->sort;
    */

    $q = "SELECT
                `".TblModCatalog."`.*,
                `".TblModCatalogSprName."`.`name` as `prodname`,
                `".TblModCatalogTranslit."`.`translit`";

        if ( isset($this->settings['cat_name_ind']) AND $this->settings['cat_name_ind']=='1' ){
            $q = $q.",`".TblModCatalogSprNameInd."`.`name` AS `nameind`";
        }
        if ( isset($this->settings['cat_descr']) AND $this->settings['cat_descr']=='1' ){
            $q = $q.",`".TblModCatalogSprDescr."`.`name` AS `descr`";
        }
        if ( isset($this->settings['cat_descr2']) AND $this->settings['cat_descr2']=='1' ){
            $q = $q.",`".TblModCatalogSprDescr2."`.`name` AS `descr2`";
        }

        $q = $q."FROM `".TblModCatalog."`
              LEFT JOIN `".TblModCatalogTranslit."` ON (
                    `".TblModCatalog."`.`id`=`".TblModCatalogTranslit."`.`id_cat`
                AND
                    `".TblModCatalogTranslit."`.`lang_id`='".$this->lang_id."'
                AND
                    `".TblModCatalogTranslit."`.`id_prop` IS NULL)
              LEFT JOIN `".TblModCatalogSprName."` ON (
                    `".TblModCatalog."`.`id`=`".TblModCatalogSprName."`.`cod`
                AND
                    `".TblModCatalogSprName."`.`lang_id`='".$this->lang_id."')";
              if ( isset($this->settings['cat_name_ind']) AND $this->settings['cat_name_ind']=='1' ){
                  $q = $q."
              LEFT JOIN `".TblModCatalogSprNameInd."` ON (
                    `".TblModCatalog."`.`id`=`".TblModCatalogSprNameInd."`.`cod`
                AND
                    `".TblModCatalogSprNameInd."`.`lang_id`='".$this->lang_id."')";
              }
              if ( isset($this->settings['cat_descr']) AND $this->settings['cat_descr']=='1' ){
                $q = $q."
              LEFT JOIN `".TblModCatalogSprDescr."` ON (
                    `".TblModCatalog."`.`id`=`".TblModCatalogSprDescr."`.`cod`
                AND
                    `".TblModCatalogSprDescr."`.`lang_id`='".$this->lang_id."')";
              }
              if ( isset($this->settings['cat_descr2']) AND $this->settings['cat_descr2']=='1' ){
                $q = $q."
              LEFT JOIN `".TblModCatalogSprDescr2."` ON (
                    `".TblModCatalog."`.`id`=`".TblModCatalogSprDescr2."`.`cod`
                AND
                    `".TblModCatalogSprDescr2."`.`lang_id`='".$this->lang_id."')";
              }

              $q = $q."
              WHERE `level`='".$this->level."'
              AND `".TblModCatalog."`.id = `".TblModCatalogTranslit."`.id_cat
              AND `".TblModCatalogTranslit."`.`lang_id`='".$this->lang_id."'
              AND `".TblModCatalogTranslit."`.`id_prop` IS NULL
              GROUP BY `".TblModCatalog."`.`id`
              ORDER BY `".TblModCatalog."`.`$this->sort` ASC
             ";

    if($limit=='limit') $q = $q." LIMIT ".$this->start.", ".$this->display;

    $DBPDO = DBPDO::getInstance();
    $DBPDO->Prepare($q);
    $DBPDO->Execute();
    $arr = $DBPDO->FetchAssocAll();
    //echo '<br>$q='.$q;
//echo '<br>$arr='.$arr;print_r($arr);
    /*
    $res = $this->Right->Query( $q, $this->user_id, $this->module );
    //echo '<br>$q='.$q.' $res='.$res.'$this->Right->result='.$this->Right->result.' $this->user_id='.$this->user_id;
    if( !$res )return false;
    $rows = $this->Right->db_GetNumRows();
    $arr = array();
    for( $i = 0; $i < $rows; $i++ ){
          $arr[$i] = $this->Right->db_FetchAssoc();
    }*/
    return $arr;
   }//end of function GetContent()



   // ================================================================================================
   // Function : microtime_diff
   // Date : 21.03.2006
   // Returns : true,false / Void
   // Description : Get microtime
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
    function microtime_diff($a, $b) {
       list($a_dec, $a_sec) = explode(" ", $a);
       list($b_dec, $b_sec) = explode(" ", $b);
       return (($b_sec - $a_sec) + ($b_dec - $a_dec));
    }

   // ================================================================================================
   // Function : show
   // Date : 21.03.2006
   // Returns : true,false / Void
   // Description : Show data from $module table
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function show()
   {
        $arr_rows = $this->GetContent('nolimit');
        $rows = count($arr_rows);

        /* Write Form Header */
        $this->Form->WriteHeader( $this->script );
        ?>
        <table border="0" cellpadding="5" cellspacing="0" width="100%">
         <tr>
          <td class="levels_tree">
           <table border="0">
               <tr>
                   <td>
                    <a href="<?=$_SERVER['PHP_SELF'].'?module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort.'&fltr='.$this->fltr.'&fln='.$this->fln;?>" title="<?=$this->multi['TXT_ROOT_CATEGORY'];?>"><img src="images/icons/categ.png" border="0" alt="<?=$this->multi['TXT_CATALOG_STRUCTURE'];?>" title="<?=$this->multi['TXT_CATALOG_STRUCTURE'];?>" /></a>
                   </td>
                   <td>
                    <a href="<?=$_SERVER['PHP_SELF'].'?module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&sort='.$this->sort.'&fltr='.$this->fltr.'&fln='.$this->fln;?>" title="<?=$this->multi['TXT_ROOT_CATEGORY'];?>"><h4 style="margin:0px; padding:0px;"><?=$this->multi['TXT_CATALOG_STRUCTURE'];?></h4></a>
                   </td>
               </tr>
           </table>

           <?=$this->show_levels_tree_back_end(0, $this->script);?>

           <img src="images/spacer.gif" width="200" height="1" />
          </td>
          <td valign="top">
           <?
           //$txt = $this->Spr->GetNameByCod( TblModCatalogSprName, $this->level );
           //if ( !empty($txt) ) echo $txt ;
           if ( $this->level>0 ) $this->ShowPathToLevel($this->level, NULL, $this->script );

           /* Write Table Part */
           AdminHTML::TablePartH();

           /* Write Links on Pages */
           ?>
           <tr>
            <td colspan="12">
             <?
             $script1 = 'module='.$this->module.'&fltr='.$this->fltr;
             $script1 = $_SERVER['PHP_SELF']."?$script1";
             $this->Form->WriteLinkPages( $this->script, $rows, $this->display, $this->start, $this->sort );
             ?>
            </td>
           </tr>
           <tr>
            <td colspan="9">
             <?
             $this->Form->WriteTopPanel( $this->script );
             /*?><a CLASS="toolbar" href="javascript:<?=$this->Form->name;?>.task.value='show_move_to_category';<?=$this->Form->name;?>.submit();" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('move','','images/icons/move_f2.png',1);"><img src="images/icons/move.png" alt="<?=$this->Msg->show_text('_BUTTON_MOVE_TO_CATEGORY');?>" title="<?=$this->Msg->show_text('_BUTTON_MOVE_TO_CATEGORY');?>" align="center" name="move" border="0" /><?=$this->Msg->show_text('_BUTTON_MOVE_TO_CATEGORY');?></a><?*/
             ?><a CLASS="toolbar" href="javascript:$('#task').val('show_move_to_category');$('#<?php echo $this->Form->name;?>').submit();" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('move','','images/icons/move_f2.png',1);"><img src="images/icons/move.png" alt="<?=$this->Msg->show_text('_BUTTON_MOVE_TO_CATEGORY');?>" title="<?=$this->Msg->show_text('_BUTTON_MOVE_TO_CATEGORY');?>" align="center" name="move" border="0" /><?=$this->Msg->show_text('_BUTTON_MOVE_TO_CATEGORY');?></a><?
             if( $this->level>0 ){
                if( $this->GetTreeCatData($this->level,'level')!=0 ){
                    $tmp = stripslashes($this->GetTreeCatData($this->GetTreeCatData($this->level,'level'), 'name'));
                }
                else{
                    $tmp = ' '.$this->multi['TXT_ROOT_CATEGORY'];
                }
                ?>
                &nbsp;&nbsp;&nbsp;
                <a CLASS="toolbar" href=<?=$this->script."&task=show&level=".$this->GetTreeCatData($this->level,'level');?> onmouseout="MM_swapImgRestore();" onmouseover="MM_swapImage('restore','','images/icons/restore_f2.png',1);">
                <IMG src='images/icons/restore.png' alt="Go to:" align="middle" border="0" name="restore">&nbsp;<?=$tmp?></a>
                <?
             }
             $script2 = 'module='.$this->module.'&display='.$this->display.'&start='.$this->start.'&task=show&fltr='.$this->fltr;
             $script2 = $_SERVER['PHP_SELF']."?$script2";
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
           ?>
          </td>
        </tr>
       </table>
       <?
       $this->Form->WriteFooter();
       return true;
   } //end of fuinction show()


   // ================================================================================================
   // Function : ShowContentHTML
   // Date : 05.01.2011
   // Returns : true,false / Void
   // Description : Show content of the catalog
   // Programmer : Yaroslav Gyryn
   // ================================================================================================
   function ShowContentHTML ()
   {
       $arr_rows = $this->GetContent();
       $rows = count($arr_rows);

       if(!isset($this->countArr))
            $this->countArr = $this->GetArrayContentCount(); // Количество товаров в каждой категории

        if($rows>$this->display)
            $ch = $this->display;
        else
            $ch = $rows;

         $editData = $this->multi['TXT_EDIT'];
         //$this->multi['FLD_INFO']
       ?>
       <table border="0" cellpadding="0" cellspacing="1">
        <tr>
         <Th class="THead"><input value="0" id="cAll" onclick="if (this.value == '1') {unCheckAll(<?=$ch;?>); this.value = '0';} else {checkAll(<?=$ch;?>); this.value = '1';}" type="checkbox"></Th>
         <Th class="THead"><?=$this->multi['FLD_ID']?></Th>
         <Th class="THead"><?=$this->multi['FLD_CATEGORY']?></Th>
         <?if ( isset($this->settings['cat_name_ind']) AND $this->settings['cat_name_ind']=='1' ) {?>
         <Th class="THead"><?=$this->multi['FLD_NAME_INDIVIDUAL']?></Th>
         <?}?>
         <?if ( isset($this->settings['cat_descr']) AND $this->settings['cat_descr']=='1' ) {?>
         <Th class="THead"><?=$this->multi['FLD_DESCRIPTION']?></Th>
         <?}?>
         <?if ( isset($this->settings['cat_descr2']) AND $this->settings['cat_descr2']=='1' ) {?>
         <Th class="THead"><?=$this->multi['FLD_DESCRIP2']?></Th>
         <?}?>
         <?if ( isset($this->settings['cat_img']) AND $this->settings['cat_img']=='1' ) {?>
         <Th class="THead"><?=$this->multi['FLD_IMG']?></Th>
         <?}?>
         <?if ( isset($this->settings['cat_sublevels']) AND $this->settings['cat_sublevels']=='1' ) {?>
         <Th class="THead"><?=$this->multi['FLD_SUBLEVEL']?></Th>
         <?}?>
         <?if ( isset($this->settings['cat_content']) AND $this->settings['cat_content']=='1' ) {?>
         <Th class="THead"><?=$this->multi['FLD_CONTENT'];?></Th>
         <?}?>
         <?if ( isset($this->settings['cat_params']) AND $this->settings['cat_params']=='1' ) {?>
         <Th class="THead"><?=$this->multi['FLD_PARAMS'];?></Th>
         <?}?>
         <Th class="THead"><?=$this->multi['FLD_VISIBLE']?></Th>
         <?if ( isset($this->settings['cat_relat']) AND $this->settings['cat_relat']=='1' ) {?>
         <Th class="THead"><?=$this->multi['FLD_RELAT_CATEGORIES']?></Th>
         <?}?>
         <Th class="THead"><?=$this->multi['FLD_DISPLAY']?></Th>
         <Th class="THead"><?=$this->multi['FLD_INFO']?></Th>
         <?
         $a = $rows;
         $up = 0;
         $down = 0;
         $row_arr = NULL;

         $style1 = 'TR1';
         $style2 = 'TR2';
         for( $i = 0; $i < $rows; $i++ ){
          $row = $arr_rows[$i];
          if( (float)$i/2 == round( $i/2 ) ) $class='TR1';
          else $class='TR2';
          ?>
          <tr class="<?=$class;?>">
           <td><?$this->Form->CheckBox( "id_del[]", $row['id'], null, "check".$i );?></td>
           <td><?=$this->Form->Link( $this->script."&task=edit&id=".$row['id'], stripslashes( $row['id'] ), $editData);?></td>
           <!--td><?//=stripslashes($this->Spr->GetNameByCod( TblModCatalogSprName, $row['id'], $this->lang_id, 1 ));?></td-->
           <td><?=stripslashes($row['prodname']);?></td>

           <!--td><?//=stripslashes($this->Spr->GetNameByCod( TblModCatalogSprNameInd, $row['id'], $this->lang_id, 1 ));?></td-->
           <?if ( isset($this->settings['cat_name_ind']) AND $this->settings['cat_name_ind']=='1' ) {?>
           <td><?=stripslashes($row['nameind']);?></td>
           <?}?>
           <?if ( isset($this->settings['cat_descr']) AND $this->settings['cat_descr']=='1' ) {?>
           <td align="center"><?if( strlen(trim( $this->Spr->GetNameByCod( TblModCatalogSprDescr, $row['id'], $this->lang_id, 1 ) ))>0 ) $this->Form->ButtonCheck();?>
           <?}?>
           <?if ( isset($this->settings['cat_descr2']) AND $this->settings['cat_descr2']=='1' ) {?>
           <td align="center"><?if( strlen(trim( $this->Spr->GetNameByCod( TblModCatalogSprDescr2, $row['id'], $this->lang_id, 1 ) ))>0 ) $this->Form->ButtonCheck();?>
           <?}?>
           <?if ( isset($this->settings['cat_img']) AND $this->settings['cat_img']=='1' ) {?>
           <td align="center"><?if( !empty($row['img_cat']) ) $this->Form->ButtonCheck();
           }
           if ( isset($this->settings['cat_sublevels']) AND $this->settings['cat_sublevels']=='1' ) {
               $tmp_rows = $this->isSubLevels( $row['id'] );
               if ( $tmp_rows>0 ) $tmp_name = $this->multi['FLD_SUBLEVEL'];
               else $tmp_name = $this->multi['TXT_CREATE_CONTENT'];
               ?><td><?=$this->Form->Link( $this->script."&task=show&level=".$row['id'], $tmp_name ); if ( $tmp_rows>0 ) echo ' ['.$tmp_rows.']';?></td><?
           }
           //$count_content = $this->IsContent( $row['id'], NULL, NULL, 'back' );
           if ( isset($this->settings['cat_content']) AND $this->settings['cat_content']=='1' ) {
               if(isset($this->countArr[$row['id']]))
                    $count_content = $this->countArr[$row['id']];
                 else
                    $count_content = 0;
               if ( $count_content>0 ) $tmp_name = $this->multi['FLD_CONTENT'];
               else $tmp_name = $this->multi['TXT_CREATE_CONTENT'];
               $script2 = "index.php?module=".$this->settings['content_func']."&task=show&id_cat=".$row['id']."&parent=1&parent_id=".$row['id']."&parent_module=".$this->module."&parent_display=".$this->display."&parent_start=".$this->start."&parent_sort=".$this->sort."&parent_task=show&parent_level=".$this->level;
               ?>
               <td><?=$this->Form->Link( $script2, $tmp_name ); if ( $count_content>0 ) echo ' ['.$count_content.']';?></td><?
           }
           if ( isset($this->settings['cat_params']) AND $this->settings['cat_params']=='1' ) {
               $tmp_rows = $this->IsParams( $row['id'] );
               if ( $tmp_rows>0 ) $tmp_name = $this->multi['FLD_PARAMS'];
               else $tmp_name = $this->multi['TXT_CREATE_CONTENT'];
               $script2 = "index.php?module=".$this->settings['params_func']."&task=show&id_cat=".$row['id']."&parent=1&parent_id=".$row['id']."&parent_module=".$this->module."&parent_display=".$this->display."&parent_start=".$this->start."&parent_sort=".$this->sort."&parent_task=show&parent_level=".$this->level;
               ?>
               <td><?=$this->Form->Link( $script2, $tmp_name ); if ( $tmp_rows>0 ) echo ' ['.$tmp_rows.']';?></td>
           <?}?>
           <td align="center">
            <?
            if( $row['visible'] == 0 ) $this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/publish_x.png', $this->multi['TXT_UNVISIBLE'], 'border=0' );
            if( $row['visible'] == 1 ) $this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/publish_r.png', $this->multi['TXT_VISIBLE_ONLY_ON_BACKEND'], 'border=0' );
            if( $row['visible'] == 2 ) $this->Form->Img( 'http://'.NAME_SERVER.'/admin/images/icons/publish_g.png', $this->multi['TXT_VISIBLE'], 'border=0' );
            ?>
           </td>
           <?if ( isset($this->settings['cat_relat']) AND $this->settings['cat_relat']=='1' ) {?>
           <td>
            <?
            $arr_relat_categs = $this->GetRelatCategs( $row['id'] );
            $script2 = $this->script."&task=control_relat_categs_form&amp;id_cat1=".$row['id'];
            if ( !is_array($arr_relat_categs) OR count($arr_relat_categs)==0) $this->Form->Link( $script2, $this->multi['TXT_ADD'] );
            else {
                $this->ShowRelatCategs($arr_relat_categs, $row['id'] );
                $this->Form->Link( $script2, $this->multi['TXT_EDIT'] );
            }
            ?>
           </td>
           <?}?>
           <td align="center" nowrap><?
            $url = '/modules/mod_catalog/catalog.backend.php?'.$this->script_ajax;
            if( $up!=0 ){
                $this->Form->ButtonUpAjax($this->script, $row['id'], $url, 'debug', 'move', $row['move']);
                /*?><a href=<?=$this->script?>&task=up&move=<?=$row['move']?> title="UP"><?=$this->Form->ButtonUp( $row['id'] );?></a><?*/
            }
            else{ ?><img src="images/spacer.gif" width="12"/><?}
            //for replace
            $this->Form->TextBoxReplace($url, 'debug', 'move', $row['move'], $row['id']);
            if( $i!=($rows-1) ){
                $this->Form->ButtonDownAjax($this->script, $row['id'], $url, 'debug', 'move', $row['move']);
                /*?><a href=<?=$this->script?>&task=down&move=<?=$row['move']?> title="DOWN"><?=$this->Form->ButtonDown( $row['id'] );?></a><?*/
            }
            else{?><img src="images/spacer.gif" width="12"/><?}

            $up=$row['id'];
            $a=$a-1;
            ?>
           </td>
           <td>
            <img src="images/icons/info2.gif" onmouseover="return overlib('<?=$this->multi['FLD_TRANSLIT'].': '.$this->GetTranslitById( $row['id'], NULL, $this->lang_id );?>',WRAP);" onmouseout="nd();">
           </td>
           <?
         } //-- end for
         ?>
         </table>
         <?
   }//end of function ShowContentHTML()



   // ================================================================================================
   // Function : ShowControlRelatCategsForm()
   // Date : 01.05.2007
   // Returns : true,false / Void
   // Description : show relations (similar) categories
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function ShowControlRelatCategsForm()
   {
    $txt = $this->Msg->show_text('TXT_CONTROL_RELAT_CATEGS').' <u><strong>'.$this->Spr->GetNameByCod(TblModCatalogSprName, $this->id_cat1).'</strong></u>';

    AdminHTML::PanelSubH( $txt );

    //-------- Show Error text for validation fields --------------
    $this->ShowErrBackEnd();
    //-------------------------------------------------------------

    AdminHTML::PanelSimpleH();
   ?>
    <table border="0" class="EditTable" width="100%">
     <tr>
      <td width="50%" valign="top"><?$this->ShowAddRelatCategForm();?></td>
      <?
      if( $this->GetRelatCategs($this->id_cat1)>0 ){ ?>
       <td width="50%" valign="top"><?$this->ShowRelatCategForm();?></td>
      <?}?>
     </tr>
    </table>
     <a CLASS="toolbar" href=<?=$this->script."&task=show";?> onmouseout="MM_swapImgRestore();" onmouseover="MM_swapImage('restore','','../admin/images/icons/restore_f2.png',1);">
     <IMG src='../admin/images/icons/restore.png' alt="Go to:" align="middle" border="0" name="restore">&nbsp;<?=$this->Msg->show_text('TXT_RETURN_BACK');?></a>
    <?
    AdminHTML::PanelSimpleF();
    AdminHTML::PanelSubF();

    $this->Form->WriteFooter();
    return true;
   } //end of function ShowControlRelatCategsForm()


   // ================================================================================================
   // Function : ShowAddRelatCategForm()
   // Date : 01.05.2007
   // Returns : true,false / Void
   // Description : show relations (similar) categories
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function ShowAddRelatCategForm()
   {
   /* Write Form Header */
    $this->Form->WriteHeader( $this->script );

    $this->Form->Hidden( 'group', $this->group );
    $this->Form->Hidden( 'level', $this->level );
    $this->Form->Hidden( 'display', $this->display );
    $this->Form->Hidden( 'start', $this->start );
    $this->Form->Hidden( 'sort', $this->sort );
    $this->Form->Hidden( 'fln', $this->fln );
    $this->Form->Hidden( 'srch', $this->srch );
    $this->Form->Hidden( 'fltr', $this->fltr );
    $this->Form->Hidden( 'id_cat1', $this->id_cat1 );
    $this->Form->Hidden( 'task', 'add_relat_categs' );

    $arr_categs = $this->GetCatalogInArray(NULL, '--- '.$this->Msg->show_text('TXT_SELECT_CATEGORY').' ---', NULL, NULL, 0, 'back');
    //print_r($arr_categs);
    //$arr_categs['']=$this->Msg->show_text('TXT_ROOT_CATEGORY');
    //print_r($arr_categs);
    $scriplink = $this->script;
    ?>
    <?=AdminHTML::PanelSimpleH();?>
    <table border="0" cellspacing="1" cellpading="0" class="EditTable" width="100%">
     <tr>
      <td valign="top"><b><?=$this->Msg->show_text('FLD_ADD_RELAT_CATEGORIES')?>:</b></td>
     </tr>
     <tr>
      <td valign="top">
      <?
      for($i=0; $i<COUNT_ADD_RELAT_CATEGS; $i++){
          ?><div><?$this->Form->Select( $arr_categs, 'arr_relat_categs[]', 'categ=' );?></div><?
      }
      ?>
      </td>
     </tr>
     <tr>
      <td><?=$this->Form->Button('submit', $this->Msg->show_text('TXT_ADD'), 50);?></td>
    </table>
    <?
    AdminHTML::PanelSimpleF();
    $this->Form->WriteFooter();
   } //end of function ShowAddRelatCategForm()


   // ================================================================================================
   // Function : ShowRelatCategForm()
   // Date : 01.05.2007
   // Returns : true,false / Void
   // Description : show relations (similar) categories
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function ShowRelatCategForm()
   {
    $q = "SELECT * FROM `".TblModCatalogRelat."` WHERE (`id_cat1`='$this->id_cat1' OR `id_cat2`='$this->id_cat1') ORDER BY `move` asc";
    $res = $this->Right->db_Query( $q );
    //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
    if ( !$res OR !$this->Right->result ) return false;
    $rows = $this->Right->db_GetNumRows();
    $arr_data = array();
    for ($i=0;$i<$rows;$i++) {
         $arr_data[$i] = $this->Right->db_FetchAssoc();
    }

    /* Write Form Header */
    $this->Form->WriteHeader( $this->script );
    $this->Form->Hidden( 'group', $this->group );
    $this->Form->Hidden( 'level', $this->level );
    $this->Form->Hidden( 'display', $this->display );
    $this->Form->Hidden( 'start', $this->start );
    $this->Form->Hidden( 'sort', $this->sort );
    $this->Form->Hidden( 'fln', $this->fln );
    $this->Form->Hidden( 'srch', $this->srch );
    $this->Form->Hidden( 'fltr', $this->fltr );
    $this->Form->Hidden( 'id_cat1', $this->id_cat1 );
    $this->Form->Hidden( 'task', 'del_relat_categs' );

    ?>
    <?=AdminHTML::PanelSimpleH();?>
    <table border="0" cellspacing="1" cellpading="0" class="EditTable">
     <tr>
      <td colspan="4"><b><?=$this->Msg->show_text('TXT_CONTROL_RELAT_CATEGS').' <u>'.$this->Spr->GetNameByCod(TblModCatalogSprName, $this->id_cat1).'</u>';?>:</b></td>
     </tr>
     <?
     $a = $rows;
     $up = 0;
     $down = 0;
     for ($i=0;$i<$rows;$i++) {
         $row = $arr_data[$i];
         if ( (float)$i/2 == round( $i/2 ) ) $class = "TR1";
         else $class = "TR2";
     ?>
     <tr class="<?=$class;?>">
      <td><?=$this->Form->CheckBox( "id_del[]", $row['id'] );?></td>
      <td>
       <?

       if ($row['id_cat1']==$this->id_cat1) $id_relat_cat = $row['id_cat2'];
       else $id_relat_cat = $row['id_cat1'];
       echo $this->GetPathToLevel($id_relat_cat);
       ?>
      </td>
      <td class="alignCenter">
       <?
       if( $up!=0 )
       {
       ?>
        <a href="<?=$this->script?>&task=up_relat_categ&amp;id_cat1=<?=$this->id_cat1;?>&amp;move=<?=$row['move']?>"><?=$this->Form->ButtonUp( $row['id'] );?></a>
       <?
       }

       if( $i!=($rows-1) )
       {
       ?>
         <a href="<?=$this->script?>&task=down_relat_categ&amp;id_cat1=<?=$this->id_cat1;?>&amp;move=<?=$row['move']?>"><?=$this->Form->ButtonDown( $row['id'] );?></a>
       <?
       }

       $up=$row['id'];
       $a=$a-1;
      ?>
      </td>
      <td><?=$this->Form->Link( $this->script."&amp;task=del_relat_categs&amp;id_cat1=$this->id_cat1&amp;id_del[$i]=".$row['id'], $this->Msg->show_text('TXT_DELETE') );?></td>
     </tr>
      <?
     }
      ?>
     <tr>
      <td colspan="2"><?=$this->Form->Button('submit', $this->Msg->show_text('TXT_DELETE_SELECTED'), 50);?></td>
     </tr>
    </table>
    <?
    AdminHTML::PanelSimpleF();
    $this->Form->WriteFooter();
   } //end of function ShowRelatCategForm()


   // ================================================================================================
   // Function : ShowRelatCategs()
   // Date : 01.05.2007
   // Parms :   $arr    - array with relatuin categories
   //           $level  - id of the category
   // Returns : true,false / Void
   // Description : show relations (similar) categories
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function ShowRelatCategs( $arr, $level )
   {
    foreach($arr as $key=>$value) {
        if( $value['id_cat1']==$level ) $val = $this->Spr->GetNameByCod(TblModCatalogSprName, $value['id_cat2']);
        else $val = $this->Spr->GetNameByCod(TblModCatalogSprName, $value['id_cat1']);
        ?><div style="border-bottom: solid 1px #ACACAC; margin: 5px;"><?=$val;?></div><?
    }
   } //end of function ShowRelatCategs()


   // ================================================================================================
   // Function : AddRelatCategs()
   // Date : 01.05.2007
   // Parms :   $level      -  id of the category
   // Returns : true,false / Void
   // Description : add relations (similar) categories
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function AddRelatCategs()
   {
       //print_r($this->arr_relat_categs);
       for ($i=0;$i<count($this->arr_relat_categs);$i++) {
           if( empty($this->arr_relat_categs[$i]) ) continue;

           $q = "SELECT * FROM `".TblModCatalogRelat."` WHERE (`id_cat1`='$this->id_cat1' AND `id_cat2`='".$this->arr_relat_categs[$i]."') OR (`id_cat1`='".$this->arr_relat_categs[$i]."' AND `id_cat2`='$this->id_cat1')";
           $res = $this->Right->Query( $q, $this->user_id, $this->module );
           //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
           if ( !$res OR !$this->Right->result ) return false;
           $rows = $this->Right->db_GetNumRows();
           if ($rows>0) continue;
           $move = ($this->GetMaxValueOfFieldMove( TblModCatalogRelat ) + 1);
           $q = "INSERT INTO `".TblModCatalogRelat."` VALUES( NULL, '$this->id_cat1', '".$this->arr_relat_categs[$i]."', '$move')";
           $res = $this->Right->Query( $q, $this->user_id, $this->module );
           //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
           if ( !$res OR !$this->Right->result ) return false;
       }
       return true;
   } //end of function AddRelatCategs()


   // ================================================================================================
   // Function : DelRelatCategs()
   // Date : 02.05.2007
   // Parms :   $user_id, $module_id, $id_del
   // Returns : true,false / Void
   // Description :  Remove data from the table
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function DelRelatCategs( $id_del )
   {
    $del = 0;
    $kol = count( $id_del );
    //print_r($id_del);
    //for( $i=0; $i<$kol; $i++ ){
    foreach( $id_del as $key=>$value ){
        //secho '<br>$key='.$key.' $value='.$value;
        $u=$value;
        $q = "delete from ".TblModCatalogRelat." where `id`='$u'";
        $res = $this->Right->Query( $q, $this->user_id, $this->module );
        //echo '<br> $q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
        if ( $res ) $del=$del+1;
        else return false;
    }
    return $del;
   } //end of function DelRelatCategs()

   // ================================================================================================
   // Function : DelRelatCategsByIdCategory()
   // Date : 09.05.2007
   // Parms :   $id_cat - id of the category
   // Returns : true,false / Void
   // Description :  Remove all relattions  with category $id_cat
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function DelRelatCategsByIdCategory( $id_cat )
   {
        $tmp_db = new DB();
        $q = "DELETE FROM `".TblModCatalogRelat."` WHERE (`id_cat1`='$id_cat' OR `id_cat2`='$id_cat')";
        $res =$tmp_db->db_Query( $q );
        //echo '<br> $q='.$q.' $res='.$res.' $tmp_db->result='.$tmp_db->result;
        if ( !$res OR !$tmp_db->result ) return false;
        return true;
   } //end of function DelRelatCategsByIdCategory()


   // ================================================================================================
   // Function : ShowMoveToCategoryForm()
   // Date : 04.12.2009
   // Parms :  $id_del - array with categories
   // Returns : true,false / Void
   // Description : show form for move categories from one category to another
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function ShowMoveToCategoryForm( $id_del=NULL )
   {
       //echo '<br>$this->id_cat='.$this->id_cat.' $this->id_cat_move_from='.$this->id_cat_move_from;
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
        //$this->Form->WriteHeader( $this->script );
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
        $arr_categs['categ=0'] = $this->Msg->show_text('TXT_ROOT_CATEGORY');
        //ksort($arr_categs);
        //print_r($arr_categs);

        AdminHTML::PanelSubH( $this->Msg->show_text('TXT_MOVE_FROM_CATEGORY_TO_CATEGORY' ) );
        AdminHTML::PanelSimpleH();
        ?>
        <table border="0" cellspacing="1" cellpading="0" class="EditTable">
         <tr>
          <td><b><?=$this->Msg->show_text('TXT_SELECT_CATEGORY_FROM_MOVE');?>:</b></td>
          <td>
           <?
           if( !isset($this->id_cat_move_from) OR empty($this->id_cat_move_from) ) $this->id_cat_move_from = $this->level;
           if( empty($this->id_cat_move_from) ) $this->id_cat_move_from=0;
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
               echo ($i+1).'. '.$this->Spr->GetNameByCod(TblModCatalogSprName, $id_del[$i]);?><br/><?
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
         <tr>
          <td></td>
          <td>
           <?=$this->Form->Button('submit', $this->Msg->show_text('BTN_MOVE'), 50);?>
          </td>
         </tr>
        </table>
        <?
        AdminHTML::PanelSimpleF();
        if( empty($this->level) ) $txtback = $this->Msg->show_text('TXT_ROOT_CATEGORY');
        else $txtback = $this->Spr->GetNameByCod(TblModCatalogSprName, $this->level);
        $txtback = $this->Msg->show_text('TXT_BACK_TO').' '.$txtback;
        ?><a CLASS="toolbar" href="<?=$this->script;?>&task=show" onmouseout="MM_swapImgRestore();"  onmouseover="MM_swapImage('move','','images/icons/restore_f2.png',1);"><img src="images/icons/restore.png" alt="<?=$txtback;?>" title="<?=$txtback;?>" align="center" name="move" border="0" /><?=$txtback;?></a><?
        AdminHTML::PanelSubF();
        $this->Form->WriteFooter();
   } //end of function ShowMoveToCategoryForm()

    // ================================================================================================
    // Function : MoveToCategory
    // Version : 1.0.0
    // Date : 04.12.2009
    //
    // Parms : $id_del - array with categories
    // Returns : $res / Void
    // Description : move categories to selected category
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 04.12.2009
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function MoveToCategory( $id_del )
    {
        $tmp_db = DBs::getInstance();
        $this->del = 0;
        $kol = count( $id_del );

        echo $this->Msg->show_text('TXT_MOVED_POSITIONS');?>:<?
        ?>
        <textarea readonly="readonly" style="width:100%; height: 200px;">
        <?
        if($kol>0){
            for( $i=0; $i<$kol; $i++ ){
                $u=$id_del[$i];
                $q = "UPDATE ".TblModCatalog." SET `level`='".$this->id_cat_move_to."' WHERE `id`='".$u."' AND `level`='".$this->id_cat_move_from."'";
                $res = $this->Right->Query( $q, $this->user_id, $this->module );
                echo "\n".($i+1).'. ['.$u.'] '.$this->Spr->GetNameByCod(TblModCatalogSprName, $u, $this->lang_id, 1);
                //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
                if( !$res OR !$this->Right->result ) return false;

                //--- update category in Translit table ---
                $q = "UPDATE ".TblModCatalogTranslit." SET `id_cat_parent`='".$this->id_cat_move_to."' WHERE `id_cat`='".$u."'";
                $res = $this->Right->Query( $q, $this->user_id, $this->module );
                //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
                if( !$res OR !$this->Right->result ) return false;
                echo ' - OK';
                $this->del=$this->del+1;
            }//end for
        }
        else{
            $q = "SELECT `id` FROM ".TblModCatalog." WHERE `level`='".$this->id_cat_move_from."'";
            $res = $tmp_db->db_Query( $q );
            $rows = $tmp_db->db_GetNumRows();
            for( $i=0; $i<$rows; $i++ ){
                $row = $tmp_db->db_FetchAssoc();
                $q = "UPDATE ".TblModCatalog." SET `level`='".$this->id_cat_move_to."' WHERE `id`='".$row['id']."' AND `level`='".$this->id_cat_move_from."'";
                $res = $this->Right->Query( $q, $this->user_id, $this->module );
                if( !$res OR !$this->Right->result ) return false;
                echo "\n".($i+1).'. ['.$row['id'].'] '.$this->Spr->GetNameByCod(TblModCatalogSprName, $row['id'], $this->lang_id, 1);

                //--- update category in Translit table ---
                $q = "UPDATE ".TblModCatalogTranslit." SET `id_cat_parent`='".$this->id_cat_move_to."' WHERE `id_cat`='".$row['id']."'";
                $res = $this->Right->Query( $q, $this->user_id, $this->module );
                //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
                if( !$res OR !$this->Right->result ) return false;
                echo ' - OK';
                $this->del=$this->del+1;
            }
        }
        ?></textarea><?
        return true;
    }//end of function MoveToCategory()

   // ================================================================================================
   // Function : edit()
   // Date : 21.03.2006
   // Parms : id/id of the record
   // Returns : true,false / Void
   // Description : Show data from $spr table for editing
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function edit()
   {
   $Panel = new Panel();
   $ln_sys = new SysLang();
   $mas = NULL;
   if( $this->id!=NULL ){
       $this->ShowJS();
       $q="SELECT * FROM `".TblModCatalog."` WHERE `id`='".$this->id."'";
       $res = $this->Right->Query( $q, $this->user_id, $this->module );
       if( !$this->Right->result ) return false;
       $mas = $this->Right->db_FetchAssoc();
   }

   /* Write Form Header */
   $this->Form->WriteHeaderFormImg( $this->script );
   $settings=SysSettings::GetGlobalSettings();
   $this->Form->textarea_editor = $settings['editer']; //'tinyMCE';
   $this->Form->IncludeSpecialTextArea( $this->Form->textarea_editor );

   if( $this->id!=NULL ) $txt = $this->multi['TXT_EDIT'];
   else $txt = $this->multi['TXT_ADD'];

   AdminHTML::PanelSubH( $txt );

   //-------- Show Error text for validation fields --------------
   $this->ShowErrBackEnd();
   //-------------------------------------------------------------

   AdminHTML::PanelSimpleH();
   ?>
    <tr>
     <td width="20%">
      <b><?=$this->multi['FLD_ID'];?>: </b>
      <?
       if( $this->id!=NULL ){
          echo $mas['id'];
          $this->Form->Hidden( 'id', $mas['id'] );
          if ( isset($this->settings['cod_pli']) AND $this->settings['cod_pli']=='1' ) {
              ?><br/><b><?=$this->multi['FLD_COD_PLI']?>: </b><?
              echo $mas['cod_pli'];
          }
      }
      else $this->Form->Hidden( 'id', '' );
      $this->Form->Hidden( 'group', $this->group );
      $this->Form->Hidden( 'level', $this->level );
      $this->Form->Hidden( 'move', $mas['move'] );
      //echo '<br>$this->level='.$this->level;
      ?>
     </td>
     <td>
      <b><?=$this->multi['FLD_VISIBLE'];?>:</b>
      <?
      $arr_v[0]=$this->multi['TXT_UNVISIBLE'];
      //$arr_v[1]=$this->multi['TXT_VISIBLE_ONLY_ON_BACKEND'];
      $arr_v[2]=$this->multi['TXT_VISIBLE'];

      if( $this->id!=NULL ) $this->Err!=NULL ? $visible=$this->visible : $visible=$mas['visible'];
      else $this->Err!=NULL ? $visible=$this->visible : $visible=2;
      $this->Form->Select( $arr_v, 'visible', $visible );
      ?>
     </td>
    </tr>
    <?/*
    <tr>
     <td colspan="2" nowrap="nowrap">
      <?$parent_level = $this->GetParentLevel($mas['id']);?>
      <b><?=$this->Msg->show_text('FLD_PAGE_URL')?>:</b> <span style="font-size:10px;">..<?if( !empty($parent_level) ) echo '/'.$this->GetTranslitById($parent_level);?>/<?
      if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->translit : $val=$this->GetTranslitById( $mas['id'] );
      else $val=$this->translit;
      $this->Form->TextBox( 'translit', stripslashes($val), 50, 'style="font-size:10px; "' );?>/
      </span>
     </td>
    </tr>
    <tr>
     <td colspan="2"><?=$this->Msg->show_text('HELP_FLD_PAGE_URL')?><br/><br/></td>
    </tr>
    */?>
    <tr>
     <td><b><?=$this->multi['FLD_ADD_TO_CATEGORY'];?>:</b></td>
     <td><b>
      <?
      //$arr_categs['0']=$this->multi['TXT_ROOT_CATEGORY'];
      $arr_categs = $this->GetCatalogInArray(NULL, $this->multi['TXT_ROOT_CATEGORY'], NULL, NULL, 0, 'back');

      //print_r($arr_categs);
      //echo '<br>$this->level='.$this->level;
      $this->Form->Select( $arr_categs, 'new_id_cat', 'categ='.$this->level );
      ?>
     </td>
    </tr>
    <?if( isset($this->settings['sizes']) AND $this->settings['sizes']=='1' ){
        ?>
    <tr>
     <td><b><?=$this->multi['FLD_ADD_CATEGORY_SIZE'];?>:</b></td>
     <td><b>
      <?
      $arr_sizes=$this->Spr->GetStructureInArray(TblModCatalogSprSizes,0,$this->lang_id,NULL,NULL,0,1,0,'back');
      $this->Form->Select( $arr_sizes, 'category_sizes',$mas['id_size'] );

      ?>
     </td>
    </tr>
    <?}?>
    <tr>
     <td colspan="2">
      <?
       if( $this->id!=NULL ) $parent_level = $mas['level'];
       else $parent_level = $this->level;

      $Panel->WritePanelHead( "SubPanel_" );

      $ln_arr = $ln_sys->LangArray( _LANG_ID );
      while( $el = each( $ln_arr ) )
      {
         $lang_id = $el['key'];
         $lang = $el['value'];
         $mas_s[$lang_id] = $lang;

         $Panel->WriteItemHeader( $lang );

         echo "\n <table border='0' class='EditTable'>";
         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_PAGE_URL'].":</b>";
         echo "\n <br>";
         ?><span style="font-size:10px;">..<?if( !empty($parent_level) ) echo '/'.$this->GetTranslitById($parent_level, NULL, $lang_id);?>/<?
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->translit[$lang_id] : $val=$this->GetTranslitById( $mas['id'], NULL, $lang_id );
         else $this->Err!=NULL ? $val=$this->translit[$lang_id] : $val='';
         if( $this->id ){
            $params = 'disabled';
            $this->Form->Hidden( 'translit['.$lang_id.']', stripslashes($val) );
         }
         else{
            $params="onkeyup=\"CheckTranslitField('translit".$lang_id."','tbltranslit".$lang_id."');\"";
         }
         $this->Form->TextBox( 'translit['.$lang_id.']', stripslashes($val), 50, 'id="translit'.$lang_id.'"; style="font-size:10px; "'.$params );?></span><?
         $this->Form->Hidden( 'translit_old['.$lang_id.']', stripslashes($val) );
         if( $this->id ){?>&nbsp;<?$this->Form->ButtonSimple("btn", $this->multi['TXT_EDIT'], NULL, "id='button".$lang_id."' onClick=\"EditTranslit('translit".$lang_id."','button".$lang_id."');\"");}
         echo "\n <br><table><tr><td><img src='images/icons/info.png' alt='' title='' border='0' /></td><td class='info'>".$this->multi['HELP_FLD_PAGE_URL'];?></td></tr></table><?

         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_NAME'].":</b>";
         echo "\n <br>";
         $row = $this->Spr->GetByCod( TblModCatalogSprName, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->description[$lang_id] : $val=$row[$lang_id];
         else $val=$this->description[$lang_id];
         $this->Form->TextBox( 'description['.$lang_id.']', stripslashes($val), 70 );
         //if( $this->id!=NULL ) $this->Form->TextBox( 'description['.$lang_id.']', stripslashes($mas['description'][$lang_id]), 80 );
         //else $this->Form->TextBox( 'description['.$lang_id.']', stripslashes($row[$lang_id]), 80 );
         echo "\n <br><br>";
         echo "\n </td>";
         echo "\n </tr>";

         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['_FLD_H1'].":</b>";
         echo "\n <br>";
         echo '<div class="help">'.$this->multi['_HELP_MSG_H1'].'</div>';
         $row = $this->Spr->GetByCod( TblModCatalogSprH1, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->h1[$lang_id] : $val=$row[$lang_id];
         else $val=$this->h1[$lang_id];
         $this->Form->TextBox( 'h1['.$lang_id.']', stripslashes($val), 70 );
         //if( $this->id!=NULL ) $this->Form->TextBox( 'description['.$lang_id.']', stripslashes($mas['description'][$lang_id]), 80 );
         //else $this->Form->TextBox( 'description['.$lang_id.']', stripslashes($row[$lang_id]), 80 );
         echo "\n <br><br>";
         echo "\n </td>";
         echo "\n </tr>";

         if ( isset($this->settings['cat_name_ind']) AND $this->settings['cat_name_ind']=='1' ) {
         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_NAME_INDIVIDUAL'].":</b>";
         echo "\n <br>";
         $row = $this->Spr->GetByCod( TblModCatalogSprNameInd, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->name_ind[$lang_id] : $val=$row[$lang_id];
         else $val=$this->name_ind[$lang_id];
         $this->Form->TextBox( 'name_ind['.$lang_id.']', stripslashes($val), 70 );
         //if( $this->id!=NULL ) $this->Form->TextBox( 'name_ind['.$lang_id.']', stripslashes($mas['name_ind'][$lang_id]), 80 );
         //else $this->Form->TextBox( 'name_ind['.$lang_id.']', stripslashes($row[$lang_id]), 80 );
         echo "\n <br><br>";
         echo "\n </td>";
         echo "\n </tr>";
         }

         if ( isset($this->settings['cat_descr']) AND $this->settings['cat_descr']=='1' ) {
         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_DESCRIPTION'].":</b>";
         echo "\n <br>";
         $row = $this->Spr->GetByCod( TblModCatalogSprDescr, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->descr[$lang_id] : $val=$row[$lang_id];
         else $val=$this->descr[$lang_id];
         //$this->Form->HTMLTextArea( 'descr['.$lang_id.']', stripslashes($val), 10, 70 );
         $this->Form->SpecialTextArea(NULL, 'descr['.$lang_id.']', stripslashes($val), 15, 70, 'class="contentInput"', $lang_id, 'descr' );
         echo "\n <br>";
         }

         if ( isset($this->settings['cat_descr2']) AND $this->settings['cat_descr2']=='1' ) {
         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_DESCRIP2'].":</b>";
         echo "\n <br>";
         $row = $this->Spr->GetByCod( TblModCatalogSprDescr2, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->descr2[$lang_id] : $val=$row[$lang_id];
         else $val=$this->descr2[$lang_id];
         //$this->Form->HTMLTextArea( 'descr['.$lang_id.']', stripslashes($val), 10, 70 );
         $this->Form->SpecialTextArea(NULL, 'descr2['.$lang_id.']', stripslashes($val), 15, 70, 'class="contentInput"', $lang_id, 'descr2' );
         echo "\n <br><br>";
         }

         echo "\n<fieldset title='".$this->multi['TXT_META_DATA']."'> <legend><span style='vetical-align:middle; font-size:15px;'><img src='images/icons/meta.png' alt='".$this->multi['TXT_META_DATA']."' title='".$this->multi['TXT_META_DATA']."' border='0' /> ".$this->multi['TXT_META_DATA']."</span></legend>";
         echo "\n <table border=0 class='EditTable'>";
         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_PAGES_TITLE'].":</b>";
         echo "\n <br>";
         echo '<span class="help">'.$this->multi['HELP_MSG_PAGE_TITLE'].'</span>';
         echo "\n <br>";
         $row = $this->Spr->GetByCod( TblModCatalogSprMTitle, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->mtitle[$lang_id] : $val=$row[$lang_id];
         else $val=$this->mtitle[$lang_id];
         $this->Form->TextBox( 'mtitle['.$lang_id.']', stripslashes($val), 70 );

         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_PAGES_DESCR'].":</b>";
         echo "\n <br>";
         echo '<span class="help">'.$this->multi['HELP_MSG_PAGE_DESCRIPTION'].'</span>';
         echo "\n <br>";
         $row = $this->Spr->GetByCod( TblModCatalogSprMDescr, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->mdescr[$lang_id] : $val=$row[$lang_id];
         else $val=$this->mdescr[$lang_id];
         $this->Form->TextArea( 'mdescr['.$lang_id.']', stripslashes($val), 3, 70 );

         echo "\n <tr>";
         echo "\n <td><b>".$this->multi['FLD_KEYWORDS'].":</b>";
         echo "\n <br>";
         echo '<span class="help">'.$this->multi['HELP_MSG_PAGE_KEYWORDS'].'</span>';
         echo "\n <br>";
         $row = $this->Spr->GetByCod( TblModCatalogSprKeywords, $mas['id'], $lang_id );
         if( $this->id!=NULL ) $this->Err!=NULL ? $val=$this->keywords[$lang_id] : $val=$row[$lang_id];
         else $val=$this->keywords[$lang_id];
         $this->Form->TextArea( 'keywords['.$lang_id.']', stripslashes($val),3, 70 );
         echo "\n<tr><td><table><tr><td><img src='images/icons/info.png' alt='' title='' border='0' /></td><td class='info'>".$this->multi['HELP_MSG_META_TAGS']."</td></tr></table>";
         echo "\n </table>";
         echo "</fieldset><br>";

         echo   "\n </table>";
         $Panel->WriteItemFooter();
      }
      $Panel->WritePanelFooter();
      ?>
     </td>
    </tr>
    <?if ( isset($this->settings['cat_img']) AND $this->settings['cat_img']=='1' ) {?>
    <tr>
     <td><b><?echo $this->multi['FLD_IMG'];?>:</b></td>
     <td>
      <table border="0" cellpadding="0" cellspacing="1">
       <tr>
        <td>
         <?
         if ( !empty($mas['img_cat']) ) {
          $this->Form->Hidden( 'img_cat', $mas['img_cat'] );
          ?><img src="http://<?=NAME_SERVER;?>/thumb.php?img=<?=$this->settings['img_path'].'/categories/'.$mas['img_cat']?>&size_auto=100" border=0 alt="<?=$this->Spr->GetNameByCod( TblModCatalogSprName, $mas['id'], $this->lang_id ); ?>">
         <?
         }
         ?>
        </td>
        <td>

         <input type="file" name="filename" size="40" value="<?=$this->img_cat?>">
         <br>
         <?
         if( !empty($mas['img_cat']) ) {?><span class="EditTable"><?=SITE_PATH.$this->settings['img_path'].'/categories/'.$mas['img_cat'];?></span><?}
         if ( !empty($mas['img_cat']) ) { ?><br><? $this->Form->Button( 'delimg', $this->multi['TXT_DELETE_IMG'], 50 ); }?>

        </td>
       </tr>
      </table>
     </td>
    </tr>
    <tr>
     <td colspan="2">
      <?$this->UploadImages->ShowFormToUpload(NULL,$this->id);?>
     </td>
    </tr>
     <?
     }

    AdminHTML::PanelSimpleF();
    $this->Form->WriteSaveAndReturnPanel( $this->script );?>&nbsp;<?
    $this->Form->WriteSavePanel( $this->script );?>&nbsp;<?
    $this->Form->WriteCancelPanel( $this->script );?>&nbsp;<?
    if( !empty($this->id) ){
       $CatalogLayout = &check_init('CatalogLayout', 'CatalogLayout');
       $CatalogLayout->mod_rewrite=1;
       //echo '<br>$publish='.$publish;
       $this->Form->WritePreviewPanelNewWindow( "http://".NAME_SERVER.$CatalogLayout->Link($mas['id']) );
    }
    AdminHTML::PanelSubF();
    $this->Form->WriteFooter();
    return true;
   } // end of function edit()

   // ================================================================================================
   // Function : ShowJS()
   // Date : 08.08.2007
   // Returns : true,false / Void
   // Description : show form with rating from users about goods
   // Programmer : Igor Trokhymchuk
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
        } // end of function EditTranslit
        </script>
        <?
   }//end of function ShowJS()

   // ================================================================================================
   // Function : save()
   // Date : 22.03.2006
   // Parms :   $user_id, $module, $id, $group_menu, $level, $description, $function, $move
   // Returns : true,false / Void
   // Description : Store data to the table
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function save()
   {
    $q="select * from `".TblModCatalog."` where `id`='".$this->id."'";
    $res = $this->Right->Query( $q, $this->user_id, $this->module );
    if( !$this->Right->result ) return false;
    $rows = $this->Right->db_GetNumRows();
    $row = $this->Right->db_FetchAssoc();
    //phpinfo();

    if($rows>0)
    {
      if (!empty($row['img_cat']) AND $row['img_cat']!=$this->img_cat) $this->DelImg();

      $q="update `".TblModCatalog."` set
          `group`='".$this->group."',
          `level`='".$this->new_id_cat."',
          `move`='".$this->move."',
          `visible`='".$this->visible."',
          `id_size`='".$this->category_sizes."',
          `img_cat`='".$this->img_cat."'";
      $q=$q." where `id`='".$this->id."'";
      $res = $this->Right->Query( $q, $this->user_id, $this->module );
      //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
      if( !$res OR !$this->Right->result ) return false;

      //echo '<br>$this->id_cat='.$this->id_cat.' $this->new_id_cat='.$this->new_id_cat.' $this->level='.$this->level;
      //if change parent categoty then change it in translit table for all records with current category $this->id
      if($this->level!=$this->new_id_cat){
          $q = "UPDATE `".TblModCatalogTranslit."` SET
                `id_cat_parent`='".$this->new_id_cat."'
                WHERE `id_cat`='".$this->id."'";
          $res = $this->Right->Query( $q, $this->user_id, $this->module );
          //echo '<br>$q='.$q.' $res='.$res.' $this->Right->result='.$this->Right->result;
          if( !$res OR ! $this->Right->result ) return false;
      }
    }
    else
    {
      $q="select MAX(`move`) as `maxx` from `".TblModCatalog."` where 1";
      $res = $this->Right->Query( $q, $this->user_id, $this->module );
      $rows = $this->Right->db_GetNumRows();
      $my = $this->Right->db_FetchAssoc();
      $maxx=$my['maxx']+1;

      $q="INSERT INTO `".TblModCatalog."` SET
          `group`='".$this->group."',
          `level`='".$this->new_id_cat."',
          `move`='".$maxx."',
          `visible`='".$this->visible."',
          `id_size`='".$this->category_sizes."',
          `img_cat`='".$this->img_cat."'";
      $res = $this->Right->Query( $q, $this->user_id, $this->module );
      //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
      if( !$this->Right->result) return false;
    }

     if ( empty($this->id) ){
        $this->id = $this->Right->db_GetInsertID();
        if ( isset($this->settings['cod_pli']) AND $this->settings['cod_pli']=='1' ) {
            $q="UPDATE `".TblModCatalog."` SET
                  `cod_pli`='".$this->id."'
                  WHERE `id`='".$this->id."'
                  ";
              $res = $this->Right->Query( $q, $this->user_id, $this->module );
              //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
              if( !$this->Right->result)
                return false;
        }
    }

    // Save Description on different languages
    $res=$this->Spr->SaveNameArr( $this->id, $this->description, TblModCatalogSprName );
    if( !$res ) return false;
    $res=$this->Spr->SaveNameArr( $this->id, $this->name_ind, TblModCatalogSprNameInd );
    if( !$res ) return false;
    $res=$this->Spr->SaveNameArr( $this->id, $this->h1, TblModCatalogSprH1 );
    if( !$res ) return false;
    $res=$this->Spr->SaveNameArr( $this->id, $this->descr, TblModCatalogSprDescr );
    if( !$res ) return false;
    $res=$this->Spr->SaveNameArr( $this->id, $this->descr2, TblModCatalogSprDescr2 );
    if( !$res ) return false;

    //---------------- save META DATA START -------------------
    $res=$this->Spr->SaveNameArr( $this->id, $this->mtitle, TblModCatalogSprMTitle );
    if( !$res ) return false;
    $res=$this->Spr->SaveNameArr( $this->id, $this->mdescr, TblModCatalogSprMDescr );
    if( !$res ) return false;
    $res=$this->Spr->SaveNameArr( $this->id, $this->keywords, TblModCatalogSprKeywords );
    if( !$res ) return false;
    //---------------- save META DATA END ---------------------

    // save translit of category name
    //if( !empty($this->translit)) $field_for_translit = $this->translit;
    //else $field_for_translit = $this->name_ind;
    $field_for_translit = $this->translit;
    $res = $this->SaveTranslit($this->id, $this->new_id_cat, $field_for_translit, $this->description, $this->level, $this->translit_old );
    if( !$res ) return false;

    return true;
   } // end of function save()

   // ================================================================================================
   // Function : del()
   // Date : 22.03.2006
   // Parms :   $user_id, $module_id, $id_del
   // Returns : true,false / Void
   // Description :  Remove data from the table
   // Programmer : Igor Trokhymchuk
   // ================================================================================================
   function del( $id_del )
   {
    $tmpdb = DBs::getInstance();
    $ModulesPlug = &check_init('ModulesPlug', 'ModulesPlug');
    $id_module = $ModulesPlug->GetModuleIdByPath ( 'mod_catalog/catalogcontent.backend.php' );
    $CatalogContent = &check_init('Catalog_content', 'Catalog_content', "'$this->user_id', '$id_module'");

    $del = 0;
    $kol = count( $id_del );
    for( $i=0; $i<$kol; $i++ )
    {
     $u=$id_del[$i];

     //--- select sublevels of curent category ---
     $q="select * from ".TblModCatalog." where `level`='".$u."'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     $rowsaaa = $this->Right->db_GetNumRows();
     //echo '<br>q='.$q.' res='.$res.' $this->db->result='.$this->db->result.' $rowsaaa='.$rowsaaa;

     //--- create array with sublevels ---
     $id_del_l = NULL;
     for( $i_ = 0; $i_ < $rowsaaa; $i_++ )
     {
      $row = $this->Right->db_FetchAssoc();
      $id_del_l[$i_] = $row['id'];
     }

     $cnt = count($id_del_l);
     //--- delete content from sublevels---
     for( $i_ = 0; $i_ < $cnt; $i_++ )
     {
      $q="select * from ".TblModCatalogProp." where `id_cat`='".$id_del_l[$i_]."'";
      $res = $this->db->db_Query( $q );
      //echo '<br>q='.$q.' res='.$res.' $this->db->result='.$this->db->result;
      if( !$res )return false;
      $rows = $this->db->db_GetNumRows();
      if($rows>0){
          $id_del_2=NULL;
          for( $j = 0; $j < $rows; $j++ )
          {
           $row_cnt = $this->db->db_FetchAssoc();
           $id_del_2[$j] = $row_cnt['id'];
          }
          $res = $CatalogContent->DelContent($id_del_2);
          //echo '<br>Del Content $res'.$res;
      }
     }


     //--- delete sublevels ---
     if( $rowsaaa>0 )$this->del( $id_del_l );

     //--- delete image for curent category ---
     $res = $this->DelImg($u);
     //echo 'Del Img $res='.$res;
     if( !$res )return false;

     //--- delete parameters for curent category ---
     $res = $this->DelParamsByIdCategory($u);
     //echo 'Del Params By Cat $res='.$res;
     if( !$res )return false;

     //--- delete relations categories for curent category ---
     $res = $this->DelRelatCategsByIdCategory($u);
     //echo 'Del Relat Cat $res='.$res;
     if( !$res )return false;

     //--- delete translit for curent category ---
     $res = $this->DelTranslit($u, NULL);
     //echo 'Del Translit $res='.$res;
     if( !$res )return false;

     //--- delete current category ---
     $q = "delete from ".TblModCatalog." where id='$u'";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>111 $q='.$q.' $res='.$res;
     $res = $this->Spr->DelFromSpr( TblModCatalogSprName, $u );
     //echo '<br>Del TblModCatalogSprName $res='.$res;
     if( !$res )return false;
     $res = $this->Spr->DelFromSpr( TblModCatalogSprNameInd, $u );
     //cho '<br>Del TblModCatalogSprNameInd $res='.$res;
     if( !$res )return false;
     $res = $this->Spr->DelFromSpr( TblModCatalogSprDescr, $u );
     //echo '<br>Del TblModCatalogSprDescr $res='.$res;
     if( !$res )return false;
     $res = $this->Spr->DelFromSpr( TblModCatalogSprDescr2, $u );
     //echo '<br>Del TblModCatalogSprDescr2 $res='.$res;
     if( !$res )return false;

     //---------------- delete META DATA START -------------------
     $res = $this->Spr->DelFromSpr( TblModCatalogSprMTitle, $u );
     //echo '<br>Del TblModCatalogSprMTitle $res='.$res;
     if( !$res ) return false;
     $res = $this->Spr->DelFromSpr( TblModCatalogSprMDescr, $u );
     //echo '<br>Del TblModCatalogSprMDescr $res='.$res;
     if( !$res ) return false;
     $res = $this->Spr->DelFromSpr( TblModCatalogSprKeywords, $u );
     //echo '<br>Del TblModCatalogSprKeywords $res='.$res;
     if( !$res ) return false;
     //---------------- delete META DATA END ---------------------

     //--- delete content from curent category ---
     $q="select * from ".TblModCatalogProp." where `id_cat`='$u'";
     $res = $this->db->db_Query( $q );
     $rows = $this->db->db_GetNumRows();
     if($rows>0){
         $id_del_2 = NULL;
         for( $j = 0; $j < $rows; $j++ )
         {
           $row_cnt = $this->db->db_FetchAssoc();
           $id_del_2[$j] = $row_cnt['id'];
         }
         $res = $CatalogContent->DelContent($id_del_2);
         //echo 'Del content cat $res='.$res;
     }

     if ( $res )
       $del=$del+1;
     else
       return false;
    }

    return $del;
   } //end of function del()

    // ================================================================================================
    // Function : DelImg()
    // Date : 13.04.2006
    // Returns :      true,false / Void
    // Description :  Up FAQ
    // Programmer :  Andriy Lykhodid
    // ================================================================================================
    function DelImg( $id = NULL )
    {
       if ( !empty($id) ) $this->id = $id;
       $tmp_db = new DB();

       $q="SELECT * FROM `".TblModCatalog."` WHERE `id`='$this->id'";
       $res = $tmp_db->db_Query( $q );
       //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
       if( !$res) return false;
       if( !$tmp_db->result ) return false;
       $row = $tmp_db->db_FetchAssoc();

       if ( !empty($row['img_cat']) ){
          $path = $_SERVER['DOCUMENT_ROOT'].$this->settings['img_path'].'/categories/'.$row['img_cat'];
          //echo '<br>$path='.$path;
          if ( file_exists($path) ) {
             $res = unlink ($path);
             if( !$res ) return false;
          }
       }
       $q="update `".TblModCatalog."` set `img_cat`='' where `id`='$this->id'";
       $res = $tmp_db->db_Query( $q );
       if( !$res )return false;

       return true;
    } // end function DelImg()

   // ================================================================================================
   // Function : CheckFields()
   // Date : 21.03.2006
   // Parms :        $id - id of the record in the table
   // Returns :      true,false / Void
   // Description :  Checking all fields for filling and validation
   // Programmer :  Igor Trokhymchuk
   // ================================================================================================
   function CheckFields()
   {
    $this->Err=NULL;

    if (empty( $this->description[_LANG_ID] )) {
        $this->Err=$this->Msg->show_text('MSG_FLD_DESCRIP_EMPTY').'<br>';
    }

    //echo '<br>$this->Err='.$this->Err.' $this->Msg->table='.$this->Msg->table;
    return $this->Err;
   } //end of fuinction CheckFields()



    // ================================================================================================
    // Function : up_relat_categ()
    // Date : 02.05.2007
    // Returns :      true,false / Void
    // Description :  Up position
    // Programmer :  Ihor Trokhymchuk
    // ================================================================================================
    function up_relat_categ($table)
    {
     $q="SELECT * FROM `$table` WHERE `move`='$this->move'";
     $q = $q." AND (`id_cat1`='$this->id_cat1' OR `id_cat2`='$this->id_cat1')";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res )return false;
     $rows = $this->Right->db_GetNumRows();
     $row = $this->Right->db_FetchAssoc();
     $move_down = $row['move'];
     $id_down = $row['id'];


     $q="SELECT * FROM `$table` WHERE `move`<'$this->move'";
     $q = $q." AND (`id_cat1`='$this->id_cat1' OR `id_cat2`='$this->id_cat1')";
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
    } // end of function up_relat_categ()


    // ================================================================================================
    // Function : down_relat_categ()
    // Date : 02.05.2007
    // Returns :      true,false / Void
    // Description :  Down position
    // Programmer : Ihor Trokhymchuk
    // ================================================================================================
    function down_relat_categ($table)
    {
     $q="select * from `$table` where `move`='$this->move'";
     $q = $q." AND (`id_cat1`='$this->id_cat1' OR `id_cat2`='$this->id_cat1')";
     $res = $this->Right->Query( $q, $this->user_id, $this->module );
     //echo '<br>q='.$q.' res='.$res.' $this->Right->result='.$this->Right->result;
     if( !$res )return false;
     $rows = $this->Right->db_GetNumRows();
     $row = $this->Right->db_FetchAssoc();
     $move_up = $row['move'];
     $id_up = $row['id'];


     $q="select * from `$table` where `move`>'$this->move'";
     $q = $q." AND (`id_cat1`='$this->id_cat1' OR `id_cat2`='$this->id_cat1')";
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
    } // end of function down_relat_categ()


     function ClearCategoryCache (){
            Cache::instance()->delete('Catalog.Categories.treeCatData');
            Cache::instance()->delete('Catalog.Categories.treeCatLevels');
            Cache::instance()->delete('Catalog.Categories.treeCatList');

     }
 } //end of class Catalog_category