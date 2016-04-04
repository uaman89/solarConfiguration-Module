<?if(empty($PageUser)){?>
<div class="desc-cat"><?=$descr1?></div>
<?=$props?>
<?=$descr2?>

<div id="sort_content">
    <?=$levelsShort?>
</div>
<?}else{
    if(!empty($descr1)):?>
    <div class="desc-cat">
        <?=$descr1?>
    </div>
    <?endif;
$PageUser->Catalog->ShowMainCategories(3, $id_cat);
}
?>
