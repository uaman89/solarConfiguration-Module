var ConfigurationModel = function( paramsData ){

    var configurationId = angular.element(document.querySelector("[ng-app=solConfigApp]")).scope().configurations.length + 1;

    this.params = new ConfigurationParamsModel( configurationId, paramsData );
    this.painter = new ConfigurationDrawModel( this.params );

    this.update = function(){
        console.log('update');
        this.params.calculateData();
        this.painter.drawModel();
        this.painter.centerCamera();

    };

};
