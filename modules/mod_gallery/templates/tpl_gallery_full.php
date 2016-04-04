<div class="imageBlockDetail " align="center">
    <div id="imageLarge" class="image-large">
        <?if(!empty($img_Big['orig'])){?>
            <a href="<?=$img_Big['orig'];?>" class="fancybox">
        <?}?>
            <img src="<?= $img_Big['small'];?>" alt="<?=$img_Big['alt'];?>" title="<?=$img_Big['title'];?>"align="middle"/>
        <?if(!empty($img_Big['orig'])){?>
            </a>
        <?}?>
    </div>
    <?if(!empty($arr_Small) && is_array($arr_Small)){?>
        <div id="carouselBlock">
            <ul id="carousel" class="vhidden jcarousel-skin-portfolio"><?
                //$responce ='';
                $items_count = count($arr_Small);
                for ($j = 0; $j < $items_count; $j++) {
                    $row = $arr_Small[$j];
                    ?><li>
                        <a href="<?= $row['link']; ?>">
                            <img src="<?= $row['path']; ?>" alt="<?= $row['alt'] ?>" title="<?= $row['title']; ?>"/>
                        </a>
                    </li><?
                }
            ?></ul>
        </div>
    <?}?>
</div>
<div><?= $short; ?></div>