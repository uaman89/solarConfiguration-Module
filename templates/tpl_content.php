<?php if(!empty($breadcrumb)):?>
<div class="path"><?php echo $breadcrumb; ?></div>
<?php endif; ?>
<?php if(!empty($h1)):?>
    <h1><?php echo $h1; ?></h1>
<?php endif;?>

<?php if(!empty($title)):?>
    <div class="title"><?php echo $title; ?></div>
<?php endif; ?>

<?php
    echo $content;
?>