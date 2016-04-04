<?
foreach ($props as $prop):
    $v = array();
        $v = $prop['props'];

        echo View::factory('/modules/mod_catalog/templates/tpl_prop_menu_single.php')
        ->bind('prop',$v);
endforeach;
?>
