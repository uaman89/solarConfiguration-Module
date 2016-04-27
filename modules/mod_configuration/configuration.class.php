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

        $q = "
            SELECT
                `clientName`,
                `location`,
                `date`
            FROM
                `".TblModConfigurationOrder."`
            WHERE
                `id_configuration_order` = '{$orderId}'
            LIMIT 1
        ";

        $res = self::$db->db_Query($q);

        if (!$res){
            echo 'can\'t load order data';
            return false;
        }

        while ( $row = self::$db->db_FetchAssoc() ){
            $arrData['orderInfo'] = $row;
        }


        $q = "
            SELECT
                `configurationId`,
                `designType`,
                `rows`,
                `moduleOrientation`,
                `modulesCount`,
                `userModuleHeight`,
                `userModuleWidth`,
                `userModuleDepth`,
                `tableAngle`,
                `distanceToGround`,
                `modulePower`,
                `configurationsCount`
            FROM
                `".TblModConfigurationSet."`
            WHERE
                `id_configuration_order` = '{$orderId}'
        ";
        $res = self::$db->db_Query($q);

        if (!$res){
            echo 'can\'t load order data';
            return false;
        }

        while ( $row = self::$db->db_FetchAssoc() ){
            $arrData['configurations'][] = $row;
        }

        header('Content-Type: application/json');
        return json_encode($arrData);
    }

//--- End Configuration Constructor -------------------------------------------------------------------------------


    public static function showConfigurationOrderList(){

        $q = "
          SELECT
              `o`.*,
              `s`.`image`
          FROM
            `".TblModConfigurationOrder."` o
            INNER JOIN `".TblModConfigurationSet."` s ON ( `s`.`id_configuration_order` = `o`.`id_configuration_order` )
          WHERE 1
          GROUP BY `id_configuration_order`
          ORDER BY `date` DESC
        ";
        //echo '<br>$q:'.$q;
        
        $res = self::$db->db_Query( $q );

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
                <td class="THead" width="25">*</td>
                <td class="THead" width="60"></td>
                <td class="THead">№ заявки</td>
                <td class="THead">дата</td>
            </tr>
        <?
        //где-то здесь мне стало уже не интересно...
        $i=1;
        while ( $row = self::$db->db_FetchAssoc() ){

            ?>
           <tr class="TR<?=$i?>" align="center">
               <td><input type="checkbox" name="delete[]" value="<?=$row['id_configuration_order']?>"></td>
               <td><img src="<?=$row['image']?>" alt="" title="" width="50" height="40"></td>
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

        if ( empty($idConfigurationOrder) ){

            $idConfigurationOrder = self::createNewConfigurationOrder();

            if (!$idConfigurationOrder){
                echo 'can\'t create new configuration order!';
                return false;
            }

            $isNewOrder = true;
        }

        //save configurations
        if ( !$isNewOrder ){
            //delete old data
            $q = "DELETE FROM `".TblModConfigurationSet."` WHERE `id_configuration_order` = '{$idConfigurationOrder}' ";
            if ( !($res = self::$db->db_Query($q)) ){
                echo 'can\'t delete exist configurations';
                return false;
            }
        }

        foreach ($data['configurations'] as $configuration ) {

            $q = "
                INSERT INTO
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
                    `image` = '{$configuration['image']}',
                    `id_configuration_order` = '{$idConfigurationOrder}',
                    `configurationId` = '{$configuration['configurationId']}'
            ";

//            echo $q;
            $res = self::$db->db_Query( $q );
//            var_dump($res);

            if (!$res){
                echo 'can\'t save configuration set params!';
                return false;
            }
        }//endforeach

        $orderData = array(
            'id_configuration_order' => $idConfigurationOrder,
            'clientName' => $data['clientName'],
            'location' => $data['location'],
            'date' => $data['date'],
        );
        self::updateConfigurationOrder( $orderData );

        echo $idConfigurationOrder;
        return;
    }

//--- end of save() ------------------------------------------------------------------------------------------------------------------------------------

    public function deleteOrders(){
        if ( !empty($this->delete) ){
            $q = "
                DELETE o, s
                FROM
                  `".TblModConfigurationOrder."` o
                   INNER JOIN `".TblModConfigurationSet."` s
                WHERE
                    `o`.`id_configuration_order` IN ('".implode("','", $this->delete)."')
                    AND
                    `o`.`id_configuration_order` = `s`.`id_configuration_order`
            ";
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

    function deleteConfigurationInOrder( $confId, $orderId ){

        if ( empty($confId) || empty( $orderId) ){
            echo 'wrong params!';
            echo '<br/>'.__FILE__.' line: '.__LINE__;
            return false;
        }

        $q = " DELETE FROM `".TblModConfigurationSet."` WHERE `configurationId` = '{$confId}' AND `id_configuration_order` = '{$orderId}' ";
        var_dump($q);
        $res = self::$db->db_Query($q);

        if ( !$res ){
            echo 'не удалось удалить!';
            return false;
        }
        else {
            echo 'удалено!';
        }

    }

//--- end deleteConfigurationInOrder() ------------------------------------------------------------------------------------------------------------------------------------


    protected static function updateConfigurationOrder( $orderData ){

        $date_expression = empty($orderData['date']) ? 'NOW()' : "'{$orderData['date']}'";
        $q = "
            UPDATE
                `".TblModConfigurationOrder."`
            SET
                `clientName` = '{$orderData['clientName']}',
                `location` = '{$orderData['location']}',
                `date` = {$date_expression}
            WHERE
                `id_configuration_order` = '{$orderData['id_configuration_order']}'
        ";
//        echo '<br>$q:'.$q;
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






/*    public static function saveOld( $data ){
        if (empty($data)){
            echo ('empty data');
            return false;
        }

        $isNewOrder = $isNewConfiguration = false;

        $idConfigurationOrder = $data['idOrder'];

        if ( empty($idConfigurationOrder) ){

            $idConfigurationOrder = self::createNewConfigurationOrder();

            if (!$idConfigurationOrder){
                echo 'can\'t create new configuration order!';
                return false;
            }

            $isNewOrder = true;
        }

        //save configurations
        if ( !$isNewOrder ){
            //check exist order configurations
            //configurationId
            $q = "SELECT `configurationId` FROM `".TblModConfigurationSet."` WHERE `id_configuration_order` = '{$idConfigurationOrder}' ";
            if ( !($res = self::$db->db_Query($q)) ){
                echo 'can\'t get exist configurations';
                return false;
            }

            while( $row = self::$db->db_FetchAssoc($q) ){
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


            if  ( !isset( $arrOrderConfigurations[ $configurationId ] ) ){
                $q = "
                    INSERT INTO
                        ".$set."
                        ,
                        `id_configuration_order` = '{$idConfigurationOrder}',
                        `configurationId` = '{$configuration['configurationId']}'
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
//            echo $q;
            $res = self::$db->db_Query( $q );
//            var_dump($res);

            if (!$res){
                echo 'can\'t save configuration set params!';
                return false;
            }
        }//endforeach

        $orderData = array(
            'id_configuration_order' => $idConfigurationOrder,
            'clientName' => $data['clientName'],
            'location' => $data['location'],
            'date' => $data['date'],
        );
        self::updateConfigurationOrder( $orderData );

        echo $idConfigurationOrder;
        return;
    }

//--- end of save() ------------------------------------------------------------------------------------------------------------------------------------*/


?>