var ConfigurationParamsModel = function( configurationId ) {

    //var id = ( angular.element(document.querySelector("[ng-app=solConfigApp]")).scope().configurations ) ?
    //    ( angular.element(document.querySelector("[ng-app=solConfigApp]")).scope().configurations + 1 ) : 0;

    this.configurationId = configurationId;
    this.designType = "2"; // 1 - одноопорная / 2 - двухопорная
    this.rows = "3";
    this.moduleOrientation = "vertical";
    this.modulesCount = 10;
    this.totalModulesCount = null;
    this.userModuleHeight = 1642;    //mm
    this.userModuleWidth  = 994;     //mm
    this.userModuleDepth  = 40;      //mm
    this.tableAngle   = 30;      //grad
    this.distanceToGround = 500; //mm
    this.modulePower = 250; //кВт
    this.singleConfigurationPower = null; //кВт
    this.B = null; //ширина стола, проекция на горизонтальную поверхность
    this.H = 0;    //высота стола, проекция на вертикальную поверхность
    this.L = null; //длина стола
    this.r = 12; //расстояние между модулями
    this.supports = {
        width   : 0,  // ширина балки
        count   : 0,  // количество опор
        interval: 0   // расстояние между опорами
    };
    
    this.configurationsCount = 1;
    this.totalConfigurationsPower = null;

    //helpers
    this.tableHeight = null;
    this.tableWidth = null;
    this._supportWidth = 60;  //mm
    this.moduleHeight = null; //mm
    this.moduleWidth  = null; //mm
    this.moduleDepth  = null; //mm

    var _this = this;
//--- end init vars -----------------------------------------------------------------------------------------


    this.calculateData = function () {
        console.log('calculateData()');

        //for internal logic
        _this.moduleHeight = _this.userModuleHeight;
        _this.moduleWidth  = _this.userModuleWidth;
        _this.moduleDepth  = _this.userModuleDepth;

        // "rotate" module
        if (_this.moduleOrientation == 'horizontal'){

            var temp = _this.moduleHeight;
            _this.moduleHeight = _this.moduleWidth;
            _this.moduleWidth = temp;
            console.log('*_this.moduleWidth',_this.moduleWidth);
            console.log('*_this.moduleHeight',_this.moduleHeight);///

        }
        else{
            _this.moduleHeight =  $('#configuration' + _this.configurationId).find('.module-height').val();
            _this.moduleWidth =  $('#configuration' + _this.configurationId).find('.module-width').val();
            console.log('**_this.moduleWidth',_this.moduleWidth);
            console.log('**_this.moduleHeight',_this.moduleHeight);///
        }

        //convert string data to Int:
        var rows = parseInt(_this.rows);
        var modulesCount = parseInt(_this.modulesCount);
        var modulePower = parseInt(_this.modulePower);
        var configurationsCount = parseInt(_this.configurationsCount);

        _this.tableHeight = _this.moduleHeight * _this.rows + ( rows - 1) * _this.r;
        _this.tableWidth = _this.moduleWidth * modulesCount + ( modulesCount - 1 ) * _this.r;
        console.log('_this.tableHeight',_this.tableHeight);
        console.log('_this.tableWidth',_this.tableWidth);

        var angle = _this.tableAngle * Math.PI / 180;

        _this.B =  Math.round( _this.tableHeight * Math.abs( Math.cos( angle ) ) );
        console.log('_this.B',_this.B);

        _this.H = Math.round( _this.tableHeight * Math.abs( Math.sin( angle ) ) + parseInt(_this.distanceToGround) );
        console.log('_this.H',_this.H);

        _this.L = _this.tableWidth; //cause 'tableWidth' much easier to understand than 'L'
        _this.supports = _getSupportsParams();

        _this.totalModulesCount = rows * modulesCount;

        _this.singleConfigurationPower = _this.totalModulesCount * modulePower;
        
        _this.totalConfigurationsPower = configurationsCount * _this.singleConfigurationPower;
    };

//--- end calculateData() -----------------------------------------------------------------------------------------


    function _getSupportsParams(){
        console.log('_getSupportsCount()');

        var supports = {
            width: _this._supportWidth,
            count: null, //количество опор
            interval: null  //расстояние между опорами
        };
        
        var minInterval, maxInterval; 
        
        switch( _this.moduleOrientation ){

            case 'horizontal':
                // min 3m ~ max 3.5m
                minInterval = 3000; //mm
                maxInterval = 3500; //mm
                break;

            default: //'vertical'
                // min 2.5m ~ max 3m
                minInterval = 2500;  //mm
                maxInterval = 3000;  //mm
                break;
        }

        //console.log('_this.tableWidth',_this.tableWidth);
        //console.log('maxInterval', maxInterval);
        //console.log('minInterval', minInterval);


        //проверяем max:
        if ( _this.tableWidth % maxInterval == 0 ){
            supports.count = _this.tableWidth / maxInterval;
            supports.interval = maxInterval;
        }
        //или min+ инервал меджу опорами
        else{
            supports.count = Math.floor( _this.tableWidth / minInterval );
            supports.interval = (_this.tableWidth - _this._supportWidth) / supports.count;
        }

        supports.count++; //+1 at the end

        //берем то тот интервал которого меньше остаток от деления, т.е. который ближе подходит по условию

       /* var diffMax = ( _this.tableWidth - _this._supportWidth ) % maxInterval;
        var diffMin = ( _this.tableWidth - _this._supportWidth ) % minInterval;
        var interval = ( diffMax < diffMin ) ? maxInterval : minInterval;

        supports.count = Math.floor( width / interval );
        supports.interval = width / supports.count;
*/

        console.log('supports',supports);
        return supports;
    }

//--- end calculateData() -----------------------------------------------------------------------------------------


};