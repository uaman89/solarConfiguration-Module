var ConfigurationModel = function(){

    var configurationId = angular.element(document.querySelector("[ng-app=solConfigApp]")).scope().configurations.length;

    this.params = new ConfigurationParamsModel( configurationId );
    this.painter = new ConfigurationDrawModel( this.params );

    this.update = function(){
        console.log('update');
        this.params.calculateData();
        this.painter.drawModel();
        this.painter.centerCamera();

    };

};
