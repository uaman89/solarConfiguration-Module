<ul id="mycarousel" class="jcarousel jcarousel-skin-tango"><?
        for ($j = 0; $j < $items_count; $j++) {
            $alt = $items[$items_keys[$j]]['name'][$lang_id]; // Заголовок
            $title = $items[$items_keys[$j]]['text'][$lang_id]; // Описание
            $path = $items[$items_keys[$j]]['path']; // Путь уменьшенной копии
            $path_org = $items[$items_keys[$j]]['path_original']; // Путь оригинального изображения
            ?>
            <li>
                <a href="<?=$path_org;?>" class="fancy">
                    <img src="<?=$path;?>" alt="<?=$alt?>" title="<?=$title;?>">
                </a>
            </li><?
        }
?></ul>
