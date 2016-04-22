<?php
include_once( SITE_PATH.'/modules/mod_configuration/configuration.defines.php' );

class ConfigurationOrder {

    protected static $db;
    protected static $script;
    protected static $form;

    function __construct ($user_id=NULL, $module=NULL) {

        self::$db = new Rights($user_id, $module);
        self::$form = new Form('form_mod_catalog_ImpExp');
        self::$script = $_SERVER['PHP_SELF'].'?module='.$module;

//        $user_id  != "" ? $this->user_id = $user_id  : $this->user_id = NULL;
//        $module   != "" ? $this->module  = $module   : $this->module  = NULL;

//        $this->lang_id = _LANG_ID;

//        if (empty($this->db)) $this->db = new DB();
//        if (empty($this->Right)) $this->Right = new Rights($this->user_id, $this->module);
//        if (empty($this->Spr)) $this->Spr = new  SysSpr($this->user_id, $this->module);
//        if (empty($this->multi)) $this->multi = check_init_txt('TblBackMulti',TblBackMulti);

    }

//--- End Configuration Constructor -------------------------------------------------------------------------------


    public function getJsonOrderDataById( $orderId=null ){
        $orderId =  ( empty($orderId) ) ? $this->orderId : $orderId;

        $q = " SELECT * FROM `".TblModConfigurationSet."` WHERE `id_configuration_order` = '{$orderId}' ";
        $res = self::$db->db_Query($q);

        if (!$res){
            echo 'can\'t load order data';
            return false;
        }

        while ( $row = self::$db->db_FetchAssoc() ){
            $arrData[] = $row;
        }
        header('Content-Type: application/json');
        return json_encode($arrData);
    }

//--- End Configuration Constructor -------------------------------------------------------------------------------


    public static function showConfigurationOrderList(){

        $res = self::$db->db_Query( "SELECT * FROM `".TblModConfigurationOrder."` WHERE 1" );

        if ( !$res ) {
            echo 'can\'t load order list!';
            return false;
        }
        self::$form->WriteHeader( self::$script );

        self::$form->WriteTopPanel( self::$script, 1 );
        if ( self::$db->IsDelete() ){
            self::$form->WriteTopPanel( self::$script, 2 );
        }

        AdminHTML::TablePartH();
        ?>
            <tr>
                <td class="THead">*</td>
                <td class="THead">ID</td>
                <td class="THead">дата</td>
            </tr>
        <?
        //где-то здесь мне стало уже не интересно...
        $i=1;
        while ( $row = self::$db->db_FetchAssoc() ){

            ?>
           <tr class="TR<?=$i?>">
               <td><input type="checkbox" name="delete[]" value="<?=$row['id_configuration_order']?>"></td>
               <td>
                   <a href="<?=self::$script?>&task=edit#/?order_id=<?=$row['id_configuration_order']?>"><?=$row['id_configuration_order']?></a>
               </td>
               <td><?=$row['date']?></td>
            </tr>
            <?
            $i = ($i==1) ? 2 : 1;
        }

        AdminHTML::TablePartF();

        self::$form->WriteFooter();

    }

//--- end showConfigurationOrderList() ------------------------------------------------------------------------------------------------------------


    public function showConfigurationOrder()
    {
        $header = ($this->task == 'new') ? 'Новая заявка' : 'Заявка № '.$this->orderId;

        echo View::factory( '/modules/mod_configuration/spa/index.html' )
            ->bind('moduleID', $this->module)
            ->bind('script', self::$script)
            ->bind('header', $header);

    }

//--- end showConfigurationOrder() ------------------------------------------------------------------------------------------------------------


    public static function save( $data ){
        if (empty($data)){
            echo ('empty data');
            return false;
        }

        $isNewOrder = $isNewConfiguration = false;

        $idConfigurationOrder = $data['idOrder'];

        if ( !isset($idConfigurationOrder) || empty($idConfigurationOrder) ){

            $idConfigurationOrder = self::createNewConfigurationOrder();

            if (!$idConfigurationOrder){
                echo 'can\'t create new configuration order!';
                return false;
            }

            $isNewOrder = true;

        }

        if ( !$isNewOrder ){
            //check exist order configurations
            $q = "SELECT `configurationId` FROM `".TblModConfigurationSet."` WHERE `id_configuration_order` = '{$idConfigurationOrder}' ";
            if ( !($res = self::$db->db_Query($q)) ){
                echo 'can\'t get exist configurations';
                return false;
            }

            while( $row = self::$db->db_Query($q) ){
                $arrOrderConfigurations[ $row['configurationId'] ] = 1;
            }
        }


        foreach ($data['configurations'] as $configuration ) {

            $configurationId = $configuration['configurationId'];

            $set = "
                `".TblModConfigurationSet."`
                SET
                `designType` = '{$configuration['designType']}',
                `rows` = '{$configuration['rows']}',
                `moduleOrientation` = '{$configuration['moduleOrientation']}',
                `modulesCount` = '{$configuration['modulesCount']}',
                `userModuleHeight` = '{$configuration['userModuleHeight']}',
                `userModuleWidth` = '{$configuration['userModuleWidth']}',
                `userModuleDepth` = '{$configuration['userModuleDepth']}',
                `tableAngle` = '{$configuration['tableAngle']}',
                `distanceToGround` = '{$configuration['distanceToGround']}',
                `modulePower` = '{$configuration['modulePower']}',
                `configurationsCount` = '{$configuration['configurationsCount']}',
                `image` = '{$configuration['image']}'
            ";

            $ids = "
                ,
                `id_configuration_order` = '{$idConfigurationOrder}',
                `configurationId` = '{$configurationId}',
            ";

            if  ( !isset( $arrOrderConfigurations[ $configurationId ] ) ){
                $q = "
                    INSERT INTO
                        ".$set."
                        `id_configuration_order` = '{$idConfigurationOrder}',
                        `configurationId` = '{$configuration['configurationId']}',
                ";
            }
            else{
                $q = "
                    UPDATE ".$set."
                    WHERE
                        `id_configuration_order` = '{$idConfigurationOrder}' AND
                        `configurationId` = '{$configuration['configurationId']}'
                ";
            }
            echo $q;
            $res = self::$db->db_Query( $q );
//            var_dump($res);

            if (!$res){
                echo 'can\'t save configuration set params!';
                return false;
            }

            self::updateConfigurationOrder( $idConfigurationOrder );
        }//endforeach

    }

//--- end of save() ------------------------------------------------------------------------------------------------------------------------------------

    public function deleteOrders(){
        if ( !empty($this->delete) ){
            $q = "DELETE FROM `".TblModConfigurationOrder."` WHERE `id_configuration_order` IN ('".implode("','", $this->delete)."') ";
            $res = self::$db->db_Query( $q );
            //echo '<br>$q:'.$q;
            $msg =  (!$res) ? 'Не удалось удалить записи!' : 'Записи успешно удалены.';
            ?>
            <b><?=$msg?></b>
            <br>
            <br>
            <?
            self::showConfigurationOrderList();
        }
    }


//--- end deleteOrders() ------------------------------------------------------------------------------------------------------------------------------------


    protected static function updateConfigurationOrder( $idConfigurationOrder ){
        $q = "
            UPDATE `".TblModConfigurationOrder."`
            SET `date` = NOW()
            WHERE `id_configuration_order` = '{$idConfigurationOrder}'
        ";
        $res = self::$db->db_Query($q);

        if (!$res){
            echo 'can\'t update configuration order!';
        }

        return $res;
    }

//--- end of updateConfigurationOrder() ------------------------------------------------------------------------------------------------------------------------------------



    public static function createNewConfigurationOrder(){
        $orderIdStr = self::getNewOrderId();
        $q = "
              INSERT INTO `".TblModConfigurationOrder."` SET
              `id_configuration_order` = '{$orderIdStr}'
        ";
        $res = self::$db->db_Query($q);

        return ($res) ? $orderIdStr : false;
    }

//--- end of createNewConfigurationOrder() ------------------------------------------------------------------------------------------------------------


    /*protected static function dbQuery( $query ){
        $res = self::$db->db_Query($query);
        if ( !$res ) echo 'can\'t execute query: <pre>'.$query.'</pre>';
        return $res;
    }*/

// ================================================================================================
// Function : GetNewOrderId()
// Version : 1.0.0
// Date : 05.05.2010
// Description :  return new order Id
// ================================================================================================
// Programmer :   Igor Trokhymchuk
// Date : 05.05.2010
// Reason for change : Creation
// Change Request Nbr:
// ================================================================================================
    protected static function getNewOrderId()
    {
        //====== set next max value for id_order START ============
        $mask = 'c-'.date("ymd");
        $q = "SELECT `id_configuration_order` FROM `".TblModConfigurationOrder."` WHERE `id_configuration_order` LIKE '".$mask."%' ORDER BY `id` desc LIMIT 1";
        //echo '<br>$q='.$q;
        $id_order = NULL;
        $res = self::$db->db_Query($q);
        $rows = self::$db->db_GetNumRows();
//        echo '<br>$rows='.$rows;
        if($rows>0){
            $row = self::$db->db_FetchAssoc();
            //формирую номер нового заказа, как самый больший за этот день + 1
            $tmp = explode("-", $row['id_configuration_order']);
            $id_order = $tmp[0].'-'.$tmp[1].'-'.($tmp[2]+1);
        }
        else{ $id_order = $mask.'-1';}
        //====== set next max value for id_order END ============
        return $id_order;
    }
//--- end of function GetNewOrderId() --------------------------------------------------------------------------------------------------


}
// end of class OrderImpExp
?>