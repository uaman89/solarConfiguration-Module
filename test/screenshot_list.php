<?php
if ($handle = opendir('.')) {

    while (false !== ($entry = readdir($handle))) {

        if ($entry != "." && $entry != ".." && strpos($entry,'.png')) {

            echo '<a href="'.$entry.'"><img src="'.$entry.'" alt="" width=100 height=50></a>&nbsp;&nbsp;';
        }
//        echo '<a href="">'.$entry."</a>";

    }

    closedir($handle);
}