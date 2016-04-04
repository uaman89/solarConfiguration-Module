<?php
// ================================================================================================
// System : SEOCMS
// Module : ClientsLayout.class.php
// Version : 1.0.0
// Date : 14.01.2010
// Licensed To:
// Igor Trokhymchuk ihoru@mail.ru
// Andriy Lykhodid las_zt@mail.ru
//
// Purpose : Class definition for all actions with Layout of Clients on the Front-End
//
// ================================================================================================

include_once( SITE_PATH.'/modules/mod_clients/clients.defines.php' );

// ================================================================================================
//    Class             : ClientsLayout
//    Version           : 1.0.0
//    Date              : 14.01.2010
//
//    Constructor       : Yes
//    Parms             : usre_id    / UserID
//                        module     / id of the module
//                        display    / how mane rows to show on the page
//                        sort       / sorting row
//                        start      / from witch record starto to show rows
//                        width      / width of the panel on the page where are dispaling content of this module
//    Returns           : None
//    Description       : Class definition for all actions with Layout of Clients on the Front-End
// ================================================================================================
//    Programmer        :  Igor Trokhymchuk
//    Date              :  14.01.2010
//    Reason for change :  Creation
//    Change Request Nbr:  N/A
// ================================================================================================
 class ClientsLayout extends Response {
   var $db = NULL;
   var $Msg = NULL;
   var $Spr = NULL;
   var $Form = NULL;
   var $lang_id = NULL;

   var $title = NULL;
   var $description = NULL;
   var $keywords = NULL;
   var $group_id = NULL;
   var $item = NULL;
   var $task=NULL;

   // ================================================================================================
   //    Function          : ClientsLayout (Constructor)
   //    Version           : 1.0.0
   //    Date              : 14.01.2010
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
   function ClientsLayout($user_id=NULL, $module=NULL) {
            //Check if Constants are overrulled
            ( $user_id  !="" ? $this->user_id = $user_id  : $this->user_id = NULL );
            ( $module   !="" ? $this->module  = $module   : $this->module  = NULL );

            if(defined("_LANG_ID")) $this->lang_id = _LANG_ID;

            if (empty($this->db)) $this->db = DBs::getInstance();
            if (empty($this->Msg)) $this->Msg = new ShowMsg();
            $this->Msg->SetShowTable(TblModClientsSprTxt);
            if (empty($this->Spr)) $this->Spr = new FrontSpr();

            $this->GetMultiTxtInArr();
            //if (empty($this->Form)) $this->Form = new FrontForm('form_clients');
   } // End of CommentsLayout Constructor

   // ================================================================================================
   // Function : GetMultiTxtInArr()
   // Version : 1.0.0
   // Date : 14.01.2010
   //
   // Parms :
   // Returns :      true,false / Void
   // Description :  retutn array with all multilangues for Clients
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 14.01.2010
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function GetMultiTxtInArr()
   {
       $q = "SELECT `".TblModClientsSprTxt."`.*
             FROM `".TblModClientsSprTxt."`
             WHERE `".TblModClientsSprTxt."`.`name`!=''
             AND `".TblModClientsSprTxt."`.`lang_id`='".$this->lang_id."'
            ";
       $res = $this->db->db_Query( $q );
       //echo '<br>$q='.$q.' $res='.$res.' $dbr->result='.$dbr->result;
       if ( !$res or !$this->db->result) return false;
       $rows = $this->db->db_GetNumRows();
       //echo '<br>rows='.$rows;
       $arr = NULL;
       for( $i = 0; $i < $rows; $i++ ){
           $row=$this->db->db_FetchAssoc();
           $this->multi[$row['cod']] = $row['name'];
       }
       return true;
   }//end of function GetMultiTxtInArr()

   // ================================================================================================
   // Function : GetClientsAll()
   // Version : 1.0.0
   // Date : 14.01.2010
   //
   // Parms :
   // Returns :      true,false / Void
   // Description :  retutn array with data of Clients
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 14.01.2010
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function GetClientsAll($group_id='')
   {
       $q = "SELECT `".TblModClients."`.*
             FROM `".TblModClients."`
             WHERE `".TblModClients."`.`short`!=''
             AND `".TblModClients."`.`img`!=''
             AND `".TblModClients."`.`lang_id`='".$this->lang_id."'
            ";
       $q = $q."ORDER BY `".TblModClients."`.`move`";

       $res = $this->db->db_Query( $q );
       //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
       if ( !$res or !$this->db->result) return false;
       $rows =  $this->db->db_GetNumRows();
       //echo '<br>rows='.$rows;

       $arr = NULL;
       for( $i = 0; $i < $rows; $i++ ){
           $row=$this->db->db_FetchAssoc();
           $arr[$i] = $row;
       }
       return $arr;

   } //end of fuinction GetClientsAll()

   // ================================================================================================
   // Function : ShowJS()
   // Version : 1.0.0
   // Date : 14.01.2010
   //
   // Parms :   $arr - arr with the data of Dealers
   // Returns :      true,false / Void
   // Description :  show list of Clients on the front-end
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 14.01.2010
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowJS()
   {
       ?>
        <script type="text/javascript">
        $(document).ready(function() {
            /*
            $(".clients").hover(
                function() {
                    var thumb = $(this).find("img").attr("src");
                    $(this).css({"background" : "url(" + thumb + ") no-repeat left bottom"});
                    $(this).find("img").stop().fadeTo("normal", 0 , function() { $(this).hide() });
                },
                function() {
                    $(this).find("img").stop().fadeTo("normal", 1).show();
                }
            );
            */
            $("ul.clients li a.goto_dtl").hover(
                function() {
                    var src = $(this).children().attr("src");
                    var height = $(this).css("height");
                    $(this).css({"background" : "url(" + src + ") no-repeat scroll 0 -" + height});
                    $(this).children().stop().fadeTo("normal", 0);
                },
                function() {
                    $(this).children().stop().fadeTo("normal", 1);
                }
            );
            $(".img_inter").hover(
                function() {
                    //alert('111');
                    $(this).attr("src", '/images/style2/inter.gif');
                },
                function() {
                    $(this).attr("src", '/images/style2/info.png');
                }
            );
        });
        </script>
       <?
   }//end of function ShowJS();

   // ================================================================================================
   // Function : ShowAll()
   // Version : 1.0.0
   // Date : 14.01.2010
   //
   // Parms :   $arr - arr with the data of Dealers
   // Returns :      true,false / Void
   // Description :  show list of Clients on the front-end
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 14.01.2010
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowAll()
   {
       $Comments = new ResponseLayout();

       $comm = $this->GetClientsAll();
       //print_r($comm);
       $cnt_comm = count($comm);
       $this->ShowPathToLevel();
       $this->ShowJS();
       ?>
       <ul class="clients">
       <?
       for( $i = 0; $i < $cnt_comm; $i++ ){
           $link = $this->Link($comm[$i]['translit'], 'item');
           $short = stripslashes($comm[$i]['short']);

           $q = "SELECT `".TblModComments."`.*, `".TblModCommentsSprName."`.`name` AS `comm_name`
             FROM `".TblModClients."`, `".TblModComments."`, `".TblModCommentsSprName."`
             WHERE `".TblModClients."`.`cod`='".$comm[$i]['cod']."'
             AND `".TblModClients."`.`lang_id`='".$this->lang_id."'
             AND `".TblModClients."`.`cod`=`".TblModComments."`.`client_id`
             AND `".TblModComments."`.`visible`='1'
             AND `".TblModCommentsSprName."`.`name`!=''
             AND `".TblModCommentsSprName."`.`cod`=`".TblModComments."`.`id`
             AND `".TblModCommentsSprName."`.`lang_id`='".$this->lang_id."'
            ";

           $res = $this->db->db_Query( $q );
           //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
           if ( !$res or !$this->db->result) return false;
           $rows =  $this->db->db_GetNumRows();
           //echo '<br>$rows='.$rows;
           ?>
            <li>
             <a class="goto_dtl" href="<?=$link;?>" title="<?=$short;?>"><img src="<?=$this->Spr->GetImgPath($comm[$i]['img'], TblModClients, $this->lang_id);?>" alt="<?=$short;?>" title="<?=$short;?>" border="0"/></a>
             <?
             if($rows>0){
                 $row=$this->db->db_FetchAssoc();
                 ?><a class="interview" href="<?=$Comments->Link($row['id'], 'item');?>" title="<?=$this->multi['TXT_FRONT_READ_INTERVIEW'];?>"><img class="img_inter" src="/images/style2/info.png" alt="" title="" ></a><?
             }
             ?>
            </li>
           <?
       }//end for
       ?>
       </ul>
       <?
   } //end of fuinction ShowAll()

   // ================================================================================================
   // Function : ShowClientDetails()
   // Version : 1.0.0
   // Date : 28.09.2009
   //
   // Parms :
   // Returns :      true,false / Void
   // Description :  show comments details
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 28.09.2009
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowClientDetails()
   {
       $Comments = new ResponseLayout();

       $q = "SELECT `".TblModClients."`.*
             FROM `".TblModClients."`
             WHERE `".TblModClients."`.`cod`='".$this->item."'
             AND `".TblModClients."`.`short`!=''
             AND `".TblModClients."`.`lang_id`='".$this->lang_id."'
            ";

       $res = $this->db->db_Query( $q );
       //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
       if ( !$res or !$this->db->result) return false;

       $this->ShowJS();

       $row=$this->db->db_FetchAssoc();
       $short = stripslashes($row['short']);
       $this->ShowPathToLevel($short);

       //--- get info about interview of clients
       $q = "SELECT `".TblModComments."`.*, `".TblModCommentsSprName."`.`name` AS `comm_name`
             FROM `".TblModClients."`, `".TblModComments."`, `".TblModCommentsSprName."`
             WHERE `".TblModClients."`.`cod`='".$this->item."'
             AND `".TblModClients."`.`lang_id`='".$this->lang_id."'
             AND `".TblModClients."`.`cod`=`".TblModComments."`.`client_id`
             AND `".TblModComments."`.`visible`='1'
             AND `".TblModCommentsSprName."`.`name`!=''
             AND `".TblModCommentsSprName."`.`cod`=`".TblModComments."`.`id`
             AND `".TblModCommentsSprName."`.`lang_id`='".$this->lang_id."'
            ";

       $res = $this->db->db_Query( $q );
       //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
       if ( !$res or !$this->db->result) return false;
       $rows =  $this->db->db_GetNumRows();

       ?>
       <h1><?=$short;?></h1>
       <div class="client_dtl">
        <p>
        <?if( !empty($row['img'])){
            //echo $this->Spr->ShowImage(TblModClients, $this->lang_id, $row['img'], "size_width=200", 85, NULL, 'align="left" style="margin: 0px 10px 10px 0px;"', NULL);
            ?><a class="clients" href="#"><img src="<?=$this->Spr->GetImgPath($row['img'], TblModClients, $this->lang_id);?>" alt="" title="" border="0"/></a><?
        }?>
        <?
        echo strip_tags(stripslashes($row['name']),'<a><b><strog><i><br>');
        if($rows>0) {?><a style="float:left;" href="<?=$Comments->Link($row['id'], 'item');?>" title="<?=$this->multi['TXT_FRONT_READ_INTERVIEW'];?>"><?=$this->multi['TXT_FRONT_READ_INTERVIEW'];?></a><?};
        ?>
        </p>
       </div>
       <div>
        <?
        $q = "SELECT `".TblModCatalogProp."`.*, `".TblModCatalogPropSprName."`.`name` AS `folioname`
             FROM `".TblModClients."`, `".TblModCatalogProp."`, `".TblModCatalogPropSprName."`
             WHERE `".TblModClients."`.`cod`='".$this->item."'
             AND `".TblModClients."`.`lang_id`='".$this->lang_id."'
             AND `".TblModClients."`.`cod`=`".TblModCatalogProp."`.`id_group`
             AND `".TblModCatalogProp."`.`visible`='2'
             AND `".TblModCatalogPropSprName."`.`name`!=''
             AND `".TblModCatalogPropSprName."`.`cod`=`".TblModCatalogProp."`.`id`
             AND `".TblModCatalogPropSprName."`.`lang_id`='".$this->lang_id."'
             ORDER BY `".TblModCatalogProp."`.`move` asc
            ";

        $res = $this->db->db_Query( $q );
        //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
        if ( !$res or !$this->db->result) return false;
        $rows =  $this->db->db_GetNumRows();
        //echo '<br>rows='.$rows;
        if($rows>0){
            $Catalog = new CatalogLayout();
            ?>
            <h3><?=$this->multi['TXT_FRONT_READY_WORKS']?>:</h3>
            <ul id="serv_new">
            <?
            for($i=0;$i<$rows;$i++){
               $row=$this->db->db_FetchAssoc();
               $foliolink = $Catalog->Link($row['id_cat'], $row['id']);
               ?><li><a href="<?=$foliolink;?>"><?=$row['folioname'];?></a></li><?
            }
            ?>
            </ul>
            <br/>
            <?
        }
        ?>
        <div id="back"><a href="<?=$this->Link();?>" linkindex="84"><?=$this->multi['TXT_FRONT_BACK'];?></a></div>
       </div>
       <?
   }//end of function ShowClientDetails()

   // ================================================================================================
   // Function : ShowPathToLevel()
   // Version : 1.0.0
   // Date : 14.01.2010
   //
   // Parms :        $path- string with name of the page
   // Returns :      $str / string with name of the categoties to current level of catalogue
   // Description :  Return as links path of the categories to selected level of catalogue
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 14.01.2010
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function ShowPathToLevel($path=NULL)
   {
       ?>
       <div class="path">
        <div class="t">
         <div class="angles l"></div>
         <div class="angles r"></div>
        </div>
        <div class="inside2">
         <?
         $res = '<a href="'._LINK.'">'.$this->Msg->show_text('TXT_FRONT_HOME_PAGE', TblModPagesSprTxt).'</a> <span class="span14"><img src="/images/style/path.gif" alt="" /></span> <a href="'._LINK.'company/">'.$this->Spr->GetNameByCod(TblModPagesSprName, 52).'</a> <span class="span14"><img src="/images/style/path.gif" alt="" /></span> ';
         if(!empty($path)) $res.= '<a href="'.$this->Link().'">'.$this->multi['TXT_FRONT_CLIENTS'].'</a>';
         else $res.= ' '.$this->multi['TXT_FRONT_CLIENTS'];
         echo $res;
         ?>
        </div>
        <div class="b">
         <div class="angles l"></div>
         <div class="angles r"></div>
        </div>
       </div><?
   } // end of function ShowPathToLevel()

   // ================================================================================================
   // Function : Link()
   // Version : 1.0.0
   // Date : 14.01.2010
   //
   // Parms :       $translit - $translit of the client
   // Returns :      true,false / Void
   // Description :  return link to the page
   // ================================================================================================
   // Programmer :  Igor Trokhymchuk
   // Date : 14.01.2010
   // Reason for change : Creation
   // Change Request Nbr:
   // ================================================================================================
   function Link($translit=NULL, $task=NULL)
   {
       $link='';
       if( empty($translit) AND !empty($task) ) return '';
       //echo '<br>$task='.$task;
       switch($task){
           case 'item':
                $link =  _LINK.'clients/'.$translit;
                break;
           default:
                $link =  _LINK.'clients';

       }
       if( !strstr($link, '.htm') ) $link .= '/';
       return $link;
   }//end of function Link();

   // ================================================================================================
   // Function : SetMetaData()
   // Version : 1.0.0
   // Date : 28.09.2009
   // Parms :
   //           $id = 0  - level of menu  (0 - first level)
   // Returns : true,false / Void
   // Description : set title, description and keywords for this module or for current category or posirion
   //               of catalogue
   // ================================================================================================
   // Programmer : Igor Trokhymchuk
   // Date : 28.09.2009
   // Reason for change : Reason Description / Creation
   // Change Request Nbr:
   // ================================================================================================
  function SetMetaData()
   {
       //echo '<br>$this->item='.$this->item.' $this->group_id='.$this->group_id;
       if( !empty($this->item)){
           $row = $this->Spr->GetMetaDataByCod(TblModClients, $this->item, $this->lang_id );
           $this->title = stripslashes($row['mtitle']);
           $this->description = stripslashes($row['mdescr']);
           $this->keywords = stripslashes($row['mkeywords']);
       }
       else{
           $this->title = $this->multi['META_DATA_TITLE'];
           $this->description = $this->multi['META_DATA_DESCRIPTION'];
           $this->keywords = $this->multi['META_DATA_KEYWORDS'];
       }
   } //end of function  SetMetaData()

} // End of class CommentsLayout
