solConfigApp.directive('configuration', function(){
    return{
        restrict: 'E',

        link: function(scope, elem, attrs){
            var initDiretcive = function(){
                scope.configuration.painter.init();
                scope.configuration.update();
                scope.$apply();//refresh scope
                console.log('init the Configuration');
            };
            setTimeout( initDiretcive, 0 ); //to init after directive HTML complete render
        },

        templateUrl: '/modules/mod_configuration/spa/templates/configuration.html',

        //scope: {
        //    params: '='
        //},
        /*
        compile: function compile(tElement, tAttrs, transclude) {
            //return {
            //    //pre: function preLink(scope, iElement, iAttrs, controller) { ... },
            //    post: function postLink(scope, iElement, iAttrs, controller) {
            //        scope.configuration.painter.init();
            //        scope.configuration.painter.drawModel();
            //    }
            //}
            // or
            return function postLink(scope, iElement, iAttrs, controller) {
                //scope.configuration.painter.init();
                //scope.configuration.painter.drawModel();
                console.log('here1');
            }
        },
        */
    };
});