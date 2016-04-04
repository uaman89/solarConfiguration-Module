<div class="categoryContent">
    <?
    foreach ($props as $prop):
            echo View::factory('/modules/mod_catalog/templates/tpl_prop_by_pages_single.php')
                ->bind('prop',$prop);

    endforeach; ?>

</div>



<?if(!empty($pagination)):?>
<div class="links">
    <?=$pagination?>
</div>
<?endif;?>