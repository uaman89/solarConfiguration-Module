
<?$class="";?>
<?if($images==""){$class="width100";}

?>
<div class="pages-full-content <?=$class?>">
    <?=$content?>
</div>

<? if($images!=""):?>
<?=$images?>
<? endif; ?>

<? if(!empty($files)):?>
    <div class="pages-files-box">
        <?=$files?>
    </div>
<? endif; ?>