<div class="atricles-by-pages-box">
    <?php
        if (count($articles) == 0):
    ?>
            <div class="err"><?= $this->multi['MSG_NO_ARTICLES']; ?></div>
    <?php
        return;
        endif;
    ?>

    <?php
    foreach ($articles as $article){
       echo View::factory('/modules/mod_article/templates/tpl_article_by_pages_single.php')
           ->bind('multi',$multi)
           ->bind('article',$article);
    }
    ?>

</div>



<?php if (!empty($pages)): ?>
<div class="pageNaviClass">
    <?php echo $pages; ?>
</div>
<?php endif; ?>