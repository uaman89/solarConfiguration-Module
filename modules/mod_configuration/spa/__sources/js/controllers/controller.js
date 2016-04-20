solConfigApp.controller('MainCtrl', function ($scope) {

    $scope.test = [1,2,3];
    $scope.message = "under construction!";

    $scope.configurations = new Array();
    $scope.configurations.push( new ConfigurationModel() );

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
    }

});
//hello