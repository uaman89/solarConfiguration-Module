<?php
/* @var $PageUser PageUser */
?>
<!DOCTYPE html>
    <html lang="<?php echo $PageUser->LangShortName;?>">
    <head>
        <meta charset="<?php echo $PageUser->page_encode;  ?>" />
        <title><?php echo htmlspecialchars($PageUser->title);?></title>
        <meta name="Description" content="<?php  if( $PageUser->Description ) echo htmlspecialchars($PageUser->Description);else echo '';?>" />
        <meta name="Keywords" content="<?php  if( $PageUser->Keywords ) echo htmlspecialchars($PageUser->Keywords);else echo '';?>" />
        <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
        <?php
        //echo '<br>$_SERVER["QUERY_STRING"]='.$_SERVER["QUERY_STRING"];
        //если это страница каталога с фмльтрами, то для гугла указывем дополнительные параметры
        //if( strstr($_SERVER["QUERY_STRING"], "parcod")){
        //более того, проверяем, есть ли любые дополнительные параметры в УРЛ,
        //и если есть, то будем закрыать от индексации и прописыать каноникал.
        if( strstr($_SERVER['REQUEST_URI'], '?')){
            //закрываем от индексации страницы результатов работы фильтров каталога товаров
            ?>
            <meta name="robots" content="noindex, nofollow"/>
            <?php

            if(!isset($_SERVER['REDIRECT_URL'])) {
                $link = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')+1);
            }
            else{ $link = $_SERVER['REDIRECT_URL']; }
            $canonical = 'http://'.NAME_SERVER.$link;
            //echo '<br>$canonical='.$canonical;
            //Добавление этой ссылки и атрибута позволяет владельцам сайтов определять наборы идентичного содержания и сообщать Google:
            //"Из всех страниц с идентичным содержанием эта является наиболее полезной.
            //Установите для нее наивысший приоритет в результатах поиска."
            ?>
            <link rel="canonical" href="<?php echo $canonical;?>"/>
            <?php
        }
        ?>

        <link rel="icon" type="image/vnd.microsoft.icon"  href="/images/design/favicon.ico" />
        <link rel="SHORTCUT ICON" href="/images/design/favicon.ico" />
        <link href="/include/css/main.css" type="text/css" rel="stylesheet" />
        <!--[if IE ]>
        <link href="/include/css/browsers/ie.css" rel="stylesheet" type="text/css" media="screen" />
        <![endif]-->
        <!--[if lt IE 8]>
        <link href="/include/css/browsers/ie7.css" rel="stylesheet" type="text/css" media="screen" />
        <![endif]-->
        <!--[if lt IE 7]>
        <script type="text/javascript" src="/include/js/iepngfix_tilebg.js"></script>
        <![endif]-->
<!--        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>-->
        <!--Include AJAX scripts-->
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <?php /*<script type="text/javascript" src='http://<?php echoNAME_SERVER."/sys/js/jQuery/jquery.js";?>'></script>*/?>
        <script type="text/javascript" src='http://<?php echo NAME_SERVER."/sys/js/jQuery/jquery.form.js";?>'></script>
        <script type="text/javascript" src="/include/js/jquery.carouFr.js"></script>
        <script type="text/javascript" src="/include/js/jquery.jcarousel.min.js"></script>
        <script type="text/javascript" src="/include/js/highslide/highslide.js"></script>
        <script type="text/javascript" src="/player/flowplayer-3.2.6.min.js"></script>
        <!-- Комментарий вконтакте В тег <head> на странице Вашего сайта необходимо добавить следующий код: -->
        <script src="http://userapi.com/js/api/openapi.js" type="text/javascript" charset="windows-1251"></script>
        <script src="/include/js/cms_lib/popup.js" type="text/javascript" charset="windows-1251"></script>
        <script src="/include/js/cms_lib/comments.js" type="text/javascript" charset="windows-1251"></script>
        <link rel="stylesheet" type="text/css" href="/include/css/comments.css" media="screen" />
        <?php if($PageUser->FrontendPages->page==$PageUser->FrontendPages->main_page OR $PageUser->FrontendPages->page==74 OR $PageUser->FrontendPages->page==75 ):?>
            <link rel="stylesheet" type="text/css" href="/include/css/slide-main.css" media="screen" />
        <?php endif;?>
        <?php if($PageUser->FrontendPages->page!=$PageUser->FrontendPages->main_page AND $PageUser->FrontendPages->page!=74 AND $PageUser->FrontendPages->page!=75):?>
            <link rel="stylesheet" type="text/css" href="/include/css/slide-o.css" media="screen" />
        <?php endif;?>
        <link rel="stylesheet" type="text/css" href="/include/css/carusel-skin.css" media="screen" />

        <!-- Старт валидации -->
        <script type="text/javascript" src="/include/js/validator/js/jquery.validationEngine.js"></script>
        <script type="text/javascript" src="/include/js/validator/js/languages/jquery.validationEngine-ru.js"></script>
        <link href="/include/js/validator/css/validationEngine.jquery.css" type="text/css" rel="stylesheet" media="screen"/>
        <!-- Конец валидации -->

        <script src="/include/js/cms_lib/lib.js" type="text/javascript" charset="windows-1251"></script>
        <!-- Enable HTML5 tags for old browsers -->
        <script type="text/javascript" src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        
        <!-- для публичних обявлений -->
        <script src="/include/js/public.js" type="text/javascript"></script>
        
        <!-- увеличалка -->
        <script src="/include/js/fancybox/jquery.fancybox.js" type="text/javascript"></script>
        <link href="/include/js/fancybox/jquery.fancybox.css" type="text/css" rel="stylesheet" media="screen"/>
        <?php if($PageUser->FrontendPages->page!=74):?>
        <script type="text/javascript">
        $(document).ready(function(){
        var frames = document.getElementsByTagName("iframe");
        for (var i = 0; i < frames.length; i++) {
        frames[i].src += "?wmode=transparent";
        }
        });
        </script>
    <?php endif;?>
    </head>

    <body>
    <!--[if lt IE 8]>
    <div style=" margin:10px auto 0px auto; padding:20px; background:#DDDDDD; border:1px solid gray; width:980px; font-size:14px;">
        Уважаемый Пользователь!</br>
        Вы используете <span class="red">устаревший WEB-браузер</span>.</br>
        Предлагаем Вам установить и использовать последние версии WEB-браузеров, например:<br/>
        <ul>
            <li>Google Chrome <a href="https://www.google.com/chrome">https://www.google.com/chrome</a></li>
            <li>Mozilla Firefox <a href="http://www.mozilla.org/ru/firefox/new/">http://www.mozilla.org/ru/firefox/new/</a></li>
            <li>Opera <a href="http://www.opera.com/download/">http://www.opera.com/download/</a></li>
        </ul>
        Последние версии WEB-браузеров доступны для установки на сайтах разработчиков и содержат улучшенные свойства безопасности, повышенную скорость работы, меньшее количество ошибок. Эти простые действия помогут Вам максимально использовать функциональность сайта, избежать ошибок в работе, повысить уровень безопасности.
    </div>
    <![endif]-->

    <script type="text/javascript">
        $(document).ready(function() {
            $("a.fan").fancybox({type: 'ajax'});

        });
    </script>
    <div class="wrapper-top">
        <header>
            <a href="/" alt="Солар" class="logo"></a>
            <nav class="top-menu"><?php $PageUser->FrontendPages->ShowHorisontalMenu()?>
<!--                --><?php //phpinfo();?>
<!--                --><?php //var_dump($PageUser->Logon);?>
                <?php $PageUser->Logon->LoginForm();?></nav>

        </header>
    </div>
    <div class="wrapper">

        <?php if($PageUser->FrontendPages->page!=''){?>
        <aside class="top-line">
            <?php if($PageUser->FrontendPages->page!=$PageUser->FrontendPages->main_page AND $PageUser->FrontendPages->page!=74 AND $PageUser->FrontendPages->page!=75):?>

            <nav class="left-pge <?php  if($PageUser->FrontendPages->page==72) echo" height-m";?>">
               <?php

                 echo $PageUser->sublevel;
               ?>
                <?php
                if($PageUser->FrontendPages->page==72){
                   $PageUser->Catalog->ShowCatalogTree();

                }
                ?>
            </nav>
            <?php endif;?>
            <?php $PageUser->Spr->ShowSlider()?>

        </aside>
        <section class="left <?php  if($PageUser->FrontendPages->page==72) echo" margintop";?>">
            <aside>
                <a href="/video/" class="h-blue">Видео</a>
                <?php $PageUser->Video->VideoLast();?>
                <a href="<?php echo "/".$PageUser->FrontendPages->ShowUploadFileList(14, true)?>" class="h-blue">Каталоги</a>
                <a href="<?php echo "/".$PageUser->FrontendPages->ShowUploadFileList(14, true)?>"><img class="cat-file " src="/images/design/catalog.jpg"></a>
            </aside>
        </section>



        <section class="main inside">
            <?php
                echo $contentHtml;
            ?>
        </section>
        <?php }else{?>
            <section class="profile-wr">
            <?php
                echo $contentHtml;
            ?>
            </section>
        <?php }?>
    </div>
    <div class="footer-wrap">
        <footer>
            <?php $PageUser->Spr->ShowLogo()?>
            <span>
                © 2013 ООО «<a href="/">Украинские системы Солар</a>». Разработка <a href="http://seotm.com/" target="_blank">Seotm</a>
            </span>
            <?php $PageUser->Spr->ShowSoc()?>
        </footer>
    </div>
    </body>
</html>
<?php
if( defined("MAKE_DEBUG") AND MAKE_DEBUG==1 ){
    $PageUser->time_end = $PageUser->getmicrotime();
    ?><div style="font-size:9px; color:#797979;"><?php
    printf ("<br/>TIME:%2.3f", $PageUser->time_end - $PageUser->time_start);
    if( isset($_SESSION['cnt_db_queries'])) echo '<br/>QUERIES: '.$_SESSION['cnt_db_queries'];
    ?></div><?php
}
?>