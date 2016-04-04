<?php
/**
 * Created by PhpStorm.
 * User: olexandr
 * Date: 21.02.2016
 * Time: 1:48
 */

if (isset($GLOBALS["HTTP_RAW_POST_DATA"]))
{
    // Get the data
    $imageData=$GLOBALS['HTTP_RAW_POST_DATA'];

    $filteredData=substr($imageData, strpos($imageData, ",")+1);

    $unencodedData=base64_decode($filteredData);
    $fp = fopen( 'test_'.time().'.png', 'wb' );
    fwrite( $fp, $unencodedData);
    fclose( $fp );
    echo "saved";
}
else{

    echo "no raw data";
}