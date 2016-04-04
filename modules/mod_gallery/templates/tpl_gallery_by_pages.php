<div class="atricles-by-pages-box">
    <?php
        if (count($gallerys) == 0):
    ?>
            <div class="err"><?= $multi['MSG_NO_GALLERY']; ?></div>
    <?php
        return;
        endif;
    ?><div class="gallery-fon"><?php
    foreach ($gallerys as $gallery){
       echo View::factory('/modules/mod_gallery/templates/tpl_gallery_by_pages_single.php')
           ->bind('multi',$multi)
           ->bind('gallery',$gallery);
    }
    ?></div>

</div>



<?php if (!empty($pages)): ?>
<div class="pageNaviClass">
    <?php echo $pages; ?>
</div>
<?php endif; ?>