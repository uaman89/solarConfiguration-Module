
var moduleUrl = '/modules/mod_configuration/configuration.php?module='+moduleID;


function getCanvasData( selector ) {
    var canvasData;
    var canvas = $(selector)[0];

    //console.log(canvas);

    if ( Detector.webgl){
        canvasData = canvas.toDataURL();
        //canvasData = canvas.toDataURL("image/png");

    }
    else{
        canvasData = canvas.toDataURL("image/png");
    }

    return canvasData;
}

//$('#date').datepicker({ dateFormat: 'yy-mm-dd' });
