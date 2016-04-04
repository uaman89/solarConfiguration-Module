/**
 * Created by olexandr on 19.02.2016.
 */

function convertCanvasToImage(canvas, isWebGL) {

    var image = document.getElementById('screenShot');
    //if (isWebGL)
    //    canvasData = canvas.toDataURL();
    //else
        canvasData = canvas.toDataURL("image/png");

    image.src = canvasData;


    //sent to server
    var ajax = new XMLHttpRequest();
    ajax.open("POST",'imgsave.php',true);
    ajax.setRequestHeader('Content-Type', 'canvas/upload');
    ajax.onreadystatechange = function() {
        if (ajax.readyState == 4) {
            if(ajax.status == 200) {
                //alert('done!');
                loadLastScreenShots();
            }
        }
    };
    ajax.send("canvasData"+canvasData );
}


function convertSVGToImage() {
    var svg = document.querySelector( "svg" );
    var svgData = new XMLSerializer().serializeToString( svg );

    var canvas = document.createElement( "canvas" );
    var ctx = canvas.getContext( "2d" );

    var img = document.getElementById('screenShot');
    img.setAttribute( "src", "data:image/svg+xml;base64," + btoa( svgData ) );

    img.onload = function() {
        ctx.drawImage( img, 0, 0 );

        // Now is done
        console.log( canvas.toDataURL( "image/png" ) );
    };}


function loadLastScreenShots(){
    $.ajax({
        url: 'screenshot_list.php',
        before:function (data) {
            $('#lastScreenshots').html( 'loading' );
        },
        success: function (data) {
            $('#lastScreenshots').html( data );
        }
    });
}