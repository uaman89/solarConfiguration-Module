<?
 if(is_array($prop)) {
    foreach($prop as $val){
        ?>
        <div class="bg-item">
        <div class="item">
        <a href="<?=$val['link']?>" title="<?=$val['name']?>">
            <img src="<?=$val['image']?>" alt="<?=$val['name']?>" title="<?=$val['name']?>">

            <h3><?=$val['name']?></h3>
        </a>
       </div>
       </div>
        <?
    }
 }
?>