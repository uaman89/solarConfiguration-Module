<?php
include_once( SITE_PATH.'/modules/mod_configuration/configuration.defines.php' );

class ConfigurationOrder {

    protected static $db;
    protected static $script;
    protected static $form;
    public static $arrTextLabel = [
        'designType' => [
            1 => 'одноопорная' ,
            2 => 'двухопорная',
        ],

        'moduleOrientation'=> [
            'vertical' => 'вертикально',
            'horizontal' => 'горизонтально',
        ],

        'systemType' => [
            1 => 'однорядная',
            2 => 'двухрядная',
            3 => 'трехрядная',
            4 => 'четырехрядная',
            5 => 'пятирядная',
        ]
    ];

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

    public function downloadPdf( $data ){

//        $filename = 'ConfigurationOrder#'.$idOrder;
//        header('Content-Transfer-Encoding: binary');  // For Gecko browsers mainly
//        //header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path)) . ' GMT');
//        header('Accept-Ranges: bytes');  // Allow support for download resume
//        //header('Content-Length: ' . filesize($path));  // File size
//        header('Content-Encoding: none');
//        header('Content-Type: application/pdf');  // Change the mime type if the file is not PDF
//        header('Content-Disposition: attachment; filename=' . $filename);  // Make the browser display the Save As dialog

        //$data = $this->getOrderDataById( $idOrder );
        
        //var_dump($data);


        //ini_set('display_errors', 0);

        ob_start();

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title></title>
        </head>
        <body>
        <div class="first-page" height="29cm">
        <p style="font-weight: bold; font-size:24px;">Заявка № <?=$data['idOrder']?></p>

        <hr>

        <p style="font-weight: bold; font-size:18px;">
            <? if ( !empty( $data['clientName']) ) : ?>
                Ф.И.О.: <?=$data['clientName']?><br>
            <? endif ?>
            <? if ( !empty( $data['clientName']) ) : ?>
                Местоположение: <?=$data['location']?><br>
            <? endif ?>
            <? if ( !empty( $data['clientName']) ) : ?>
                Дата: <?=$data['date']?>
            <? endif ?>
        </p>

        <br>

        <?
        foreach ( $data['configurations'] as $configuration ){
            ?>
            <p style="font-weight: bold; font-size:18px;">Конфигурация <?=$configuration['configurationId']?></p>
            <p align="center"><img src="<?=$configuration['image']?>'" ></p>
            <br>
            <br>

            <? if ($configuration['showLegend']): ?>
            <table cellpadding="5" border="1" cellspacing="0">
                <tbody><tr>
                    <td>H, мм</td>
                    <td class="ng-binding"><?=$configuration['H']?></td>
                </tr>
                <tr>
                    <td>h, мм</td>
                    <td class="ng-binding"><?=$configuration['h']?></td>
                </tr>
                <tr>
                    <td>L, мм</td>
                    <td class="ng-binding"><?=$configuration['L']?></td>
                </tr>
                <tr>
                    <td>B, мм</td>
                    <td class="ng-binding"><?=$configuration['B']?></td>
                </tr>
                <?/*<tr>
                    <td>α, град</td>
                    <td class="ng-binding"><?=$configuration['image']?>°</td>
                </tr>
                */?>
                </tbody></table>
            <? endif; ?>
            </div>


            <table cellpadding="5" border="1" cellspacing="0" width="100%">
                <tr><td>Конструкция:</td><td><?=$configuration['designType']?></td></tr>
                <tr><td>Тип системы:</td><td><?=$configuration['systemType']?></td></tr>
                <tr><td>Расположение модулей:</td><td><?=$configuration['moduleOrientation']?></td></tr>
                <tr><td>Модулей в ряду:</td><td><?=$configuration['modulesCount']?></td></tr>
                <tr><td>Количество модулей, шт.:</td><td><?=$configuration['modulesCount']?></td></tr>
                <tr><td>Размеры модуля, мм:</td>
                    <td>
                        <?=$configuration['moduleHeight']?> X <?=$configuration['moduleWidth']?> X <?=$configuration['moduleDepth']?>
                    </td>
                </tr>
                <tr><td>Угол наклона:</td><td><?=$configuration['tableAngle']?>°</td></tr>
                <tr><td>Расстояние до земной поверхности, мм:</td><td><?=$configuration['distanceToGround']?></td></tr>
                <tr><td>Количество опор, шт.:</td><td><?=$configuration['supportCount']?></td></tr>
                <tr><td>Установленная мощность модуля, Вт:</td><td><?=$configuration['modulePower']?></td></tr>
                <tr><td>Установленная мощность системы, кВт:</td><td><?=$configuration['systemPower']?></td></tr>
                <tr><td>Количество систем, шт.:</td><td><?=$configuration['systemCount']?></td></tr>
                <tr><td>Общая установленная мощность, кВт:</td><td><?=$configuration['totalPower']?></td></tr>
            </table>
            <?
        }

        ?>
        </body>
        </html>
        <?
        $html = ob_get_clean();

        $res = include_once( SITE_PATH.'/include/mpdf60/mpdf.php' );

        $mpdf = new mPDF('utf-8', 'A4', '8', '', 10, 10, 7, 7, 10, 10); /*задаем формат, отступы и.т.д.*/
//        $mpdf->charset_in = 'cp1251'; /*не забываем про русский*/
        $mpdf->charset_in = 'utf8'; /*не забываем про русский*/


        $mpdf->list_indent_first_level = 0;
        $mpdf->WriteHTML($html, 2); /*формируем pdf*/

        $file_name = 'generated_docs/document_configuration_'.$data['idOrder'].'.pdf';

        ob_start();
        $mpdf->Output($file_name, 'I');
        $content = ob_get_clean();
        file_put_contents($file_name, $content);

        //exit( '<a href="/modules/mod_configuration/'.$file_name.'">'.$file_name.'</a>');
        exit( '/modules/mod_configuration/'.$file_name);

    }

//--- end downloadPdf -------------------------------------------------------------------------------

    public function getJsonOrderDataById( $orderId=null ){

        header('Content-Type: application/json');
        return json_encode( $this->getOrderDataById( $orderId));
    }

//--- End getJsonOrderDataById -------------------------------------------------------------------------------


    public function getOrderDataById( $orderId ){

        $orderId = ( empty($orderId) ) ? $this->orderId : $orderId;

        if ( empty($orderId) ) return false;

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
            ORDER BY `configurationId` ASC
        ";
        $res = self::$db->db_Query($q);

        if (!$res){
            echo 'can\'t load order data';
            return false;
        }

        while ( $row = self::$db->db_FetchAssoc() ){
            $arrData['configurations'][] = $row;
        }

        return $arrData;
    }

//--- End getOrderDataById -------------------------------------------------------------------------------


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