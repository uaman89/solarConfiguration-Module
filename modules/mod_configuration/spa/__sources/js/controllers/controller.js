/*
*   moduleID assigned in /modules/mod_configuration/spa/index.html
*/

solConfigApp.controller('MainCtrl', function ($scope, $http, $location, $log, $timeout) {

    $scope.clientName = "";
    $scope.Location = "";
    $scope.date = "";

    $scope.configurations = new Array();

    var idOrder = $location.search().order_id;

    if ( idOrder == undefined ){
        $scope.header = "Новая заявка";
        $scope.configurations.push( new ConfigurationModel() );
    }
    else   {
        //$log.info( 'id_order: ', idOrder );

        $scope.header = 'Заявка № ' + idOrder;
        loadConfigurationByOrderId(idOrder);
    }


    $scope.configurationParamSet = {

        design: [
            { id:1, label: 'одноопорная' },
            { id:2, label: 'двухопорная'},
        ],

        moduleOrientation: [
            { val: 'vertical', label: 'вертикально' },
            { val: 'horizontal', label: 'горизонтально' },
        ],

        systemType: [
            { rows:1, label: 'однорядная' },
            { rows:2, label: 'двухрядная' },
            { rows:3, label: 'трехрядная' },
            { rows:4, label: 'четырехрядная' },
            { rows:5, label: 'пятирядная' },
        ]

    };

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    $scope.addNewConfiguration = function(){
        $scope.configurations.push( new ConfigurationModel() );
    };

    //---  end addNewConfiguration() ------------------------------------------------------------------------------------------------------------------------------------------------

    $scope.saveMsgReport = '';


    $scope.saveConfigurationsOrder = function() {

        $('#preloader').html('<img src="/admin/images/progress_bar1.gif">');

        var postData = {
            idOrder: idOrder,
            clientName: $scope.clientName,
            location: $scope.location,
            date: $scope.date,
            configurations: []
        };
        for (key in $scope.configurations){
            var conf = $scope.configurations[key];

            postData.configurations.push({
                configurationId: conf.params.configurationId,
                designType: conf.params.designType,
                rows: conf.params.rows,
                moduleOrientation: conf.params.moduleOrientation,
                modulesCount: conf.params.modulesCount,
                userModuleHeight: conf.params.userModuleHeight,
                userModuleWidth: conf.params.userModuleWidth,
                userModuleDepth: conf.params.userModuleDepth,
                tableAngle: conf.params.tableAngle,
                distanceToGround: conf.params.distanceToGround,
                modulePower: conf.params.modulePower,
                configurationsCount: conf.params.configurationsCount,
                image: getCanvasData('#configuration' + conf.params.configurationId + ' canvas')
            })
        }

        $http({
            method: 'post',
            url: '/modules/mod_configuration/configuration.php?module=' + moduleID + '&task=save',
            data: postData,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(
            function successCallback(response) {
                // this callback will be called asynchronously
                // when the response is available
                //$log.info(response);
                $scope.header = 'Заявка № ' + response.data;

                showMsgReport('сохранено!');
            },
            function errorCallback(response) {
                // called asynchronously if an error occurs
                // or server returns response with an error status.
                showMsgReport('Не удалось сохранить!');
            }
        );
    }


    //---  end saveConfigurationsOrder ------------------------------------------------------------------------------------------------------------------------------------------------

    $scope.delConfiguration = function ( configurationId ){
        //$log.warn('delete ' + configurationId );
        //$log.info( $scope.configurations );

        var newId = 1;
        for ( index in $scope.configurations ){
            if ( $scope.configurations[index].params.configurationId == configurationId ) {
                $log.info('index', index);
                $scope.configurations.splice(index, 1);
            }

            //reset confId
            $scope.configurations[index].params.configurationId = newId++;

        }

    }

    //--- end delConfiguration ------------------------------------------------------------------------------------------------------------------------------------------------

    function loadConfigurationByOrderId( orderId ){
        $http({
            method: 'GET',
            url: '/modules/mod_configuration/configuration.php?module='+moduleID+'&task=getOrderData'+'&order_id='+orderId,
        }).then(
            function successCallback(response) {
                // this callback will be called asynchronously
                // when the response is available
                $scope.clientName = response.data.orderInfo.clientName;
                $scope.location = response.data.orderInfo.location;
                $scope.date = response.data.orderInfo.date;

                for ( key in response.data.configurations ){
                    //$log.info(response.data);
                    var configuration = new ConfigurationModel( response.data.configurations[key] );
                    $scope.configurations.push( configuration );
                }
            },
            function errorCallback(response) {
                // called asynchronously if an error occurs
                // or server returns response with an error status.
                $scope.header = "Не удалось загрузить заявку № " + orderId
            }
        );
    }

    //--- end loadConfigurationByOrderId -------------------------------------------------

    function showMsgReport( text ){
        $scope.saveMsgReport = text;
            $('#preloader').html('');
            $timeout(
                function(){
                    $scope.saveMsgReport = '';
                },
                2000
            );
    }
    //--- end showMsgReport -------------------------------------------------


});
