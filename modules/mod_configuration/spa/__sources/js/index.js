//there is nothing here

function getCanvasData( selector) {
    var canvasData;
    var canvas = $(selector)[0];

    console.log(canvas);

    if ( Detector.webgl){
        canvasData = canvas.toDataURL();
    }
    else{
        canvasData = canvas.toDataURL("image/png");
    }
    return canvasData;
}
