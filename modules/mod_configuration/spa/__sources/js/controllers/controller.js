/*
*   moduleID assigned in /modules/mod_configuration/spa/index.html
*/

solConfigApp.controller('MainCtrl', function ($scope, $http, $location, $log) {


    $scope.configurations = new Array();

    var idOrder = $location.search().order_id;

    if ( idOrder == undefined ){
        $scope.header = "Новая заявка";
        $scope.configurations.push( new ConfigurationModel() );
    }
    else   {
        //$log.info( 'id_order: ', idOrder );

        $scope.header = 'Заявка №' + idOrder;
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

    $scope.addNewConfiguration = function(){
        $scope.configurations.push( new ConfigurationModel() );
    };

    $scope.saveConfigurationsOrder = function() {
        var postData = {
            idOrder: idOrder,
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
        });
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------

    function loadConfigurationByOrderId( orderId ){
        $http({
            method: 'GET',
            url: '/modules/mod_configuration/configuration.php?module='+moduleID+'&task=getOrderData'+'&order_id='+orderId,
        }).then(
            function successCallback(response) {
                // this callback will be called asynchronously
                // when the response is available
                for ( key in response.data ){
                    var configuration = new ConfigurationModel( response.data[key] );
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

});
