<?php

$img  = $_POST['imgData'];
$angle =  $_POST['angle'];

$html  = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
';
$html .= '<img src="'.$img.'">';
$html .= '
<table>
    <tr><td>Angle</td><td>'.$angle.'</td></tr>
    <tr><td>Random</td><td>'.rand(0,100500).'</td></tr>
</table>
';
$html  .= '
</body>
</html>
';

if ($_POST['fileType']=='pdf'){
    include("mpdf60/mpdf.php");

    $mpdf = new mPDF('utf-8', 'A4', '8', '', 10, 10, 7, 7, 10, 10); /*задаем формат, отступы и.т.д.*/
    $mpdf->charset_in = 'cp1251'; /*не забываем про русский*/


    $mpdf->list_indent_first_level = 0;
    $mpdf->WriteHTML($html, 2); /*формируем pdf*/

    $file_name = 'document_'.time().'.pdf';

    ob_start();
    $mpdf->Output($file_name, 'I');
    $content = ob_get_clean();
    file_put_contents($file_name, $content);


}
else{
    $file_name = 'document_'.time().'.doc';
    file_put_contents($file_name, $html);
}

exit( '<a href="'.$file_name.'">'.$file_name.'</a>');
?>
