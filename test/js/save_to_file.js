
function convertToDoc(fileType) {
    var canvas = document.getElementsByTagName('canvas')[0];
    var imgData = canvas.toDataURL("image/png");
    angle = $('input[name=angleX]').val();
    console.log('angle',angle);

    $.ajax({
        url: 'save_file.php',
        data: {
            imgData: imgData,
            angle: angle,
            fileType: fileType
        },
        type: 'POST',
        success: function (res) {
            $('#docLink').html(res);
        }
    })

}