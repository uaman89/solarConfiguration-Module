<? if(count($arr)!=0):
$cls = ""
?>
<ul>
<? foreach ($arr as $row):
    if($row['main_page']==2){
        $cls = "current-left";
    }
    ?>
    <li>
        <a href="<?=$row['link'];?>" class="sub_levels <?=$cls?>"><?=stripslashes($row['pname']);?></a>
    </li>
<?$cls=""; endforeach; ?>
</ul>
<? endif; ?>