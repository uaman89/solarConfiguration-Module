<?php
// ================================================================================================
// System : SEOCMS
// Module : ArticleLayout.class.php
// Version : 2.0.0
// Date : 28.01.2009
// Licensed To:
// Igor  Trokhymchuk  ihoru@mail.ru
// Andriy Lykhodid    las_zt@mail.ru
//
// Purpose : Class definition for all actions with Layout of Article on the Front-End
//
// ================================================================================================
include_once(SITE_PATH . '/modules/mod_article/article.defines.php');

/**
 * Class ArticleLayout
 * @property db $db
 * @property TblFrontMulti $multi
 * @property PageUser $PageUser
 */
class ArticleLayout extends Article
{

    var $id = NULL;
    var $title = NULL;
    var $fltr = NULL;
    var $PageUser = NULL;

    // ================================================================================================
    //    Function          : ArticleLayout (Constructor)
    //    Version           : 1.0.0
    //    Date              : 4.06.2007
    //    Parms             : sort      / field by whith data will be sorted
    //                        display   / count of records for show
    //                        start     / first records for show
    //    Returns           : Error Indicator
    //
    //    Description       : Opens and selects a dabase
    // ================================================================================================
    function ArticleLayout($user_id = NULL, $module = NULL, $display = NULL, $sort = NULL, $start = NULL)
    {

        //Check if Constants are overrulled
        ($user_id != "" ? $this->user_id = $user_id : $this->user_id = NULL);
        ($module != "" ? $this->module = $module : $this->module = 32);
        ($display != "" ? $this->display = $display : $this->display = 10);
        ($sort != "" ? $this->sort = $sort : $this->sort = NULL);
        ($start != "" ? $this->start = $start : $this->start = 0);

        if (defined("_LANG_ID"))
            $this->lang_id = _LANG_ID;
        //if (empty($this->Msg)) $this->Msg = new ShowMsg();
        //$this->Msg->SetShowTable(TblModArticleSprTxt);
        if (empty($this->Spr))
            $this->Spr = & check_init('SysSpr', 'SysSpr');
        $this->Form = & check_init('FormArts', 'FrontForm', "'form_art'");
        if (empty($this->Crypt))
            $this->Crypt = & check_init('Crypt', 'Crypt');
        /* if (empty($this->Spr)) $this->Spr = new  SysSpr();
          if (empty($this->Form)) $this->Form = new FrontForm('form_art'); */
        //$this->db =  DBs::getInstance();
        if (empty($this->db))
            $this->db = DBs::getInstance();
        if (empty($this->Banner))
            $this->Banner = & check_init('Banner', 'Banner');

        (defined("USE_TAGS") ? $this->is_tags = USE_TAGS : $this->is_tags = 0);
        (defined("USE_COMMENTS") ? $this->is_comments = USE_COMMENTS : $this->is_comments = 0);

        if (empty($this->multi))
            $this->multi = & check_init_txt('TblFrontMulti', TblFrontMulti);
        $this->is_tags = 1;
    }

// End of ArticleLayout Constructor
    // ================================================================================================
    // Function : ShowArticleTask()
    // Version : 1.0.0
    // Date : 20.09.2009
    // Parms :
    // Returns :      true,false / Void
    // Description :  Show Article navigation by tasks
    // ================================================================================================
    // Programmer :  Ihor Trokhymchuk
    // Date : 20.09.2009
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowArticleTask($task = NULL)
    {
        ?>
    <div style="margin: 13px 0px 0px 0px; float:none; min-height:90px;">
        <div style="width:320px; float:left;">
            <?= $this->multi['TXT_ARTICLE_TASK']; ?>:
            <ul>
                <li>
                    <?
                    $this->fltr = " AND `status`='a'";
                    $rows = $this->GetArticlesRows('limit');
                    if ($rows > 0) {
                        ?><a href="<?= _LINK; ?>articles/last/"
                             class="t_link"><?= $this->multi['TXT_FRONT_TITLE_LATEST']; ?>&nbsp;→</a>&nbsp;<span
                                class="inacive_txt"><?= $rows; ?></span><?
                    } else {
                        ?><span class="inacive_txt"><?= $this->multi['TXT_FRONT_TITLE_LATEST']; ?></span><?
                    }
                    ?>
                </li>
                <?
                $this->fltr = " AND `status`='e'";
                $rows = $this->GetArticlesRows('limit');
                ?>
                <li>
                    <? if ($rows > 0) { ?><a href="<?= _LINK; ?>articles/arch/"
                                             class="t_link"><?= $this->multi['TXT_FRONT_TITLE_ARCH']; ?>&nbsp;→</a>
                    &nbsp;<span class="inacive_txt"><?= $rows; ?></span><?
                } else {
                    ?><span class="inacive_txt"><?= $this->multi['TXT_FRONT_TITLE_ARCH']; ?></span><?
                }
                    ?>
                </li>
                <?
                $this->fltr = " AND `status`!='i'";
                $rows = $this->GetArticlesRows('limit');
                ?>
                <li>
                    <? if ($rows > 0) { ?><a href="<?= _LINK; ?>articles/all/"
                                             class="t_link"><?= $this->multi['TXT_ALL_ARTICLES']; ?>&nbsp;→</a>&nbsp;
                    <span class="inacive_txt"><?= $rows; ?></span><?
                } else {
                    ?><span class="inacive_txt"><?= $this->multi['TXT_ALL_ARTICLES']; ?></span><?
                }
                    ?>
                </li>
            </ul>
        </div>
        <?
        $this->ShowArticleCat();
        ?>
    </div>
    <?
    }

    // ================================================================================================
    // Function : ShowArticleCat()
    // Version : 1.0.0
    // Date : 27.01.2006
    // Parms :
    // Returns :      true,false / Void
    // Description :  Show Article Category
    // ================================================================================================
    // Programmer :  Ihor Trokhymchuk
    // Date : 27.01.2006
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowArticleCat($cat = NULL)
    {
        $settings = $this->GetSettings();

        $q = "SELECT `" . TblModArticleCat . "`.*
              FROM `" . TblModArticleCat . "`
              WHERE 1 AND `lang_id`='" . $this->lang_id . "'
              AND `name`!=''
              ORDER BY `move` ASC ";
        $res = $this->db->db_Query($q);
        $rows = $this->db->db_GetNumRows();
        for ($i = 0; $i < $rows; $i++) {
            $arr_res[] = $this->db->db_FetchAssoc();
        }

        if ($rows > 0) {
            ?>
        <div style="width:300px;"><?= $this->multi["TXT_ARTICLE_CAT"]; ?>:
            <ul><?
                for ($i = 0; $i < $rows; $i++) {
                    $row = $arr_res[$i];
                    $name = $row['name'];
                    $q1 = "select * from " . TblModArticle . " where category='" . $row['cod'] . "' and status!='i' ";
                    $res1 = $this->db->db_Query($q1);
                    $rows1 = $this->db->db_GetNumRows();

                    if ($rows1) {
                        $link = $this->Link($row['cod'], NULL);
                        ?>
                        <li>
                            <a href="<?= $link; ?>"><?= $name; ?></a>
                        </li>
                        <?
                    } // end if
                } // end for
                ?>
            </ul>
        </div>
        <?
        }
    }

//end of function ShowArticleCat()
    // ================================================================================================
    // Function : ShowArticlesByPages()
    // Version : 1.0.0
    // Parms :
    // Returns :      true,false / Void
    // Description :  Show Short News width img by pages
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 01.09.2009
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowArticlesByPages()
    {
        $this->PageUser->h1 = $this->multi['TXT_ARTICLE_TITLE'];
        $this->PageUser->breadcrumb = '';

        $articles = $this->GetArticlesRows('limit');

        $pages = '';
        if (count($articles) > 0) {
            $n_rows = $this->GetArticlesRows('nolimit');
            $link = $this->Link($this->category, NULL);
            $pages = $this->Form->WriteLinkPagesStatic($link, $n_rows, $this->display, $this->start, $this->sort, $this->page);
        }

        echo View::factory('/modules/mod_article/templates/tpl_article_by_pages.php')
            ->bind('articles', $articles)
            ->bind('multi', $this->multi)
            ->bind('pages', $pages);
    }

// end of functtion ShowArticlesByPages
    // ================================================================================================
    // Function : GetArticlesRows()
    // Version : 1.0.0
    // Date : 20.09.2009
    // Parms :        $limit / limit or nolimit rows from table
    // Returns :      true,false / Void
    // Description :  Get articles
    // ================================================================================================
    // Programmer :  Ihor Trokhymchuk
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function GetArticlesRows($limit = 'limit')
    {
        if ($limit == 'nolimit') {
            $q = "
	             SELECT count(`" . TblModArticle . "`.id) AS `count`
              ";
        } else {
            $q = "
                  SELECT
                 `" . TblModArticle . "`.id,
                 `" . TblModArticle . "`.dttm,
                 `" . TblModArticle . "`.category as id_category,
                 `" . TblModArticleTxt . "`.name,
                 `" . TblModArticleTxt . "`.short,
                 `" . TblModArticleCat . "`.name as cat,
                 `" . TblModArticleCat . "`.translit as cat_translit,
                 `" . TblModArticle . "`.position,
                 `" . TblModArticleLinks . "`.link as art_link
            ";
        }

        $q .= "
            FROM `" . TblModArticle . "`, `" . TblModArticleTxt . "`, `" . TblModArticleCat . "`,`" . TblModArticleLinks . "`
            WHERE  `" . TblModArticle . "`.category = `" . TblModArticleCat . "`.cod
                  AND `" . TblModArticle . "`.id = `" . TblModArticleTxt . "`.cod
                  AND `" . TblModArticleTxt . "`.lang_id='" . $this->lang_id . "'
                  AND `" . TblModArticleCat . "`.lang_id='" . $this->lang_id . "'
                  AND `" . TblModArticleTxt . "`.name!=''
                  AND `" . TblModArticle . "`.id = `" . TblModArticleLinks . "`.cod
              ";
        if ($this->fltr != '')
            $q = $q . $this->fltr;

        $q = $q . " ORDER BY `" . TblModArticle . "`.position DESC";
        if ($limit == 'limit')
            $q = $q . " LIMIT " . $this->start . "," . $this->display . "";

        $res = $this->db->db_Query($q);

        if (!$res OR !$this->db->result) return false;
        $rows = $this->db->db_GetNumRows();


        $array = array();
        if ($limit == 'limit') {

            for ($i = 0; $i < $rows; $i++) {
                $row = $this->db->db_FetchAssoc();
                $array[$row['id']] = $row;
            }
            foreach ($array as $row) {

                $main_img_data = $this->GetMainImageData($row['id'], 'front');
                if (isset($main_img_data['path']) AND !empty($main_img_data['path'])) {
                    $row['image'] = ArticleImg_Path . '/' . $row['id'] . '/' . $main_img_data['path'];
                    $row['image'] = ImageK::getResizedImg($row['image'], 'size_width=132', 85);
                } else
                    $row['image'] = '/images/design/no-image.jpg';
                $row['dttm'] = $this->ConvertDate($row['dttm']);
                $row['name'] = strip_tags(stripslashes($row['name']));
                $row['short'] = strip_tags(stripslashes($this->Crypt->TruncateStr($row['short'], 400)));
                $row['link'] = _LINK . 'articles/' . $row['cat_translit'] . '/' . $row['art_link'] . '.html';
                $row['linkCat'] = _LINK . 'articles/' . $row['cat_translit'] . '/';


                $array[$row['id']] = $row;
            }
        } else {
            $row = $this->db->db_FetchAssoc();
            return $row['count'];
        }
        //echo "<br>q=".$q." res=".$res." rows=".$rows;
        return $array;
    }

// end of  GetArticlesRows()
    // ================================================================================================
    // Function : ShowArticleFull()
    // Date : 20.09.2009
    // Returns :      true,false / Void
    // Description :  Show Article   Full
    // Programmer :  Ihor Trokhymchuk
    // ================================================================================================
    function ShowArticleFull($art = NULL)
    {
        $this->PageUser->h1 = $this->multi['TXT_ARTICLE_TITLE'];
        $this->PageUser->title = '';
        $this->PageUser->breadcrumb = '';

        $rows = $this->GetArticleData($this->id);
        if ($rows == 0)
            return false;
        $full = $this->db->db_FetchAssoc();

        $settings = $this->GetSettings();

        if (isset($settings['img']) AND $settings['img'] == '1') {
            $rows = $this->GetMainImageDataAll($full['id'], 'front');
            if($rows>0){
                $cnt = 0;
                for($i=0;$i<$rows;$i++){
                    $row = $this->db->db_FetchAssoc();
                    if (isset($row['path']) AND !empty($row['path'])) {
                        $arr_img[$cnt]['image_origin'] = ArticleImg_Path . '/' . $full['id'] . '/' . $row['path'];
                        $arr_img[$cnt]['image_small'] = ImageK::getResizedImg($arr_img[$cnt]['image_origin'], 'size_auto=90', 85);
                        $arr_img[$cnt]['image_big'] = ImageK::getResizedImg($arr_img[$cnt]['image_origin'], 'size_auto=300', 85);
                        $cnt++;
                    }                    
                }
            }else $arr_img = array();
        } else $arr_img = array();

        $full['name'] = stripslashes($full['sbj']);
        $full['linkCat'] = $this->Link($full['category']);
        $full['catName'] = stripslashes($full['cat_name']);
        $full['full_news'] = stripslashes($full['full_art']);
        $full['id_department'] = stripslashes($full['id_department']);
        $full['dttm']=$this->ConvertDate($full['dttm']);

        echo View::factory('/modules/mod_article/templates/tpl_article_full.php')
                ->bind('multi',$this->multi)
                ->bind('arr_img',$arr_img)
                ->bind('full',$full);

        if (empty($this->Department))
            $this->Department = & check_init('DepartmentLayout', 'DepartmentLayout');
        $this->Department->ShowDepartmentLinkForArticles($full['id_department']);


        if ($this->is_comments == 1) {
            $this->Comments = new CommentsLayout($this->module, $this->id);
            $this->Comments->ShowComments();
        }

        if ($this->is_tags == 1) {
            $Tags = & check_init('FrontTags', 'FrontTags');
            if (count($Tags->GetSimilarItems($this->module, $this->id)) > 0) {
                ?>
            <div>
                <?
                $Tags->ShowSimilarItems($this->module, $this->id);
                ?>
            </div>
            <?
            }
        }
    }

// end of function ShowArticleFull()

    /**
     * ArticleLayout::GetMap()
     * Show map of articles
     * @author Yaroslav
     * @return void
     */
    function GetMap()
    {
        $q = "SELECT * FROM `" . TblModArticleCat . "` WHERE `lang_id`='" . _LANG_ID . "'ORDER BY `cod` ASC ";
        $res = $this->db->db_Query($q);
        $rows = $this->db->db_GetNumRows();
        $arr = array();
        for ($i = 0; $i < $rows; $i++)
            $arr[] = $this->db->db_FetchAssoc();
        ?>
    <ul><?
        for ($i = 0; $i < $rows; $i++) {
            $row = $arr[$i];
            $name = $row['name'];
            $linkCat = $this->Link($row['cod']);
            ?>
            <li><a href="<?= $linkCat; ?>"><?= $name; ?></a></li><?
            $q1 = "SELECT
            `" . TblModArticle . "`.id,
            `" . TblModArticle . "`.category,
            `" . TblModArticleTxt . "`.name,
            `" . TblModArticleLinks . "`.link
          FROM `" . TblModArticle . "` ,`" . TblModArticleTxt . "`, `" . TblModArticleLinks . "`
          WHERE
            `" . TblModArticle . "`.category ='" . $row['cod'] . "'
          AND
            `" . TblModArticle . "`.id = `" . TblModArticleTxt . "`.cod
          AND
            `" . TblModArticleTxt . "`.lang_id = '" . $this->lang_id . "'
          AND
           `" . TblModArticle . "`.status='a'
           AND
           `" . TblModArticleTxt . "`.name !=''
          AND
            `" . TblModArticle . "`.id = `" . TblModArticleLinks . "`.cod
          ORDER BY
            `" . TblModArticle . "`.dttm DESC
   ";

            $res1 = $this->db->db_Query($q1);
            //echo '<br/>'.$q1;
            $rows1 = $this->db->db_GetNumRows();
            if ($rows1) {
                ?>
                <ul><?
                    for ($j = 0; $j < $rows1; $j++) {
                        $row1 = $this->db->db_FetchAssoc();
                        $name1 = stripslashes($row1['name']);
                        $link = $linkCat . $row1['link'] . '.html';
                        //$this->Link($row1['category'], $row1['id']);
                        ?>
                        <li><a href="<?= $link; ?>"><?= $name1; ?></a></li><?
                    }
                    ?></ul><?
            }
        }
        ?></ul><?
    }

    /**
     * ArticleLayout::ShowArticleLast()
     *
     * @param integer $cnt
     * @return void
     */
    function ShowArticleLast($cnt = 5)
    {
        $q = "select distinct
        `" . TblModArticle . "`.*,
        `" . TblModArticleTxt . "`.name,
        `" . TblModArticleTxt . "`.short,
        `" . TblModArticleCat . "`.name as cat,
        `" . TblModArticleImg . "`.path,
        `" . TblModArticleImgSpr . "`.name as img_name,
        `" . TblModArticleImgSpr . "`.name as img_descr,
        `" . TblModArticleLinks . "`.link as art_link,
        `" . TblModArticleCat . "`.translit as cat_translit
        from    `" . TblModArticle . "`  LEFT JOIN `" . TblModArticleImg . "`
                ON ( `" . TblModArticle . "`.id = `" . TblModArticleImg . "`.id_art AND `" . TblModArticleImg . "`.show = '1') LEFT JOIN  `" . TblModArticleImgSpr . "`
                ON ( `" . TblModArticleImg . "`.id = `" . TblModArticleImgSpr . "`.cod AND `" . TblModArticleImgSpr . "`.lang_id = '" . $this->lang_id . "'),
                `" . TblModArticleTxt . "`,`" . TblModArticleCat . "`,`" . TblModArticleLinks . "`
       where `" . TblModArticle . "`.status='a'
       AND `" . TblModArticle . "`.id = `" . TblModArticleTxt . "`.cod
       AND `" . TblModArticleTxt . "`.lang_id='" . $this->lang_id . "'
       AND `" . TblModArticle . "`.category = `" . TblModArticleCat . "`.cod
       AND `" . TblModArticleCat . "`.lang_id='" . $this->lang_id . "'
       AND `" . TblModArticle . "`.id = `" . TblModArticleLinks . "`.cod
       order by position desc limit " . $cnt;

        /* $q = "SELECT
          `".TblModArticle."`.id,
          `".TblModArticle."`.dttm,
          `".TblModArticle."`.category,
          `".TblModArticleTxt."`.name,
          `".TblModArticleTxt."`.short,
          `".TblModArticleLinks."`.link,
          `".TblModArticleImg."`.path
          FROM  `".TblModArticleTxt."`, `".TblModArticleLinks."` , `".TblModArticle."`
          LEFT JOIN `".TblModArticleImg."` ON
          (
          `".TblModArticleImg."`.id_art = `".TblModArticle."`.id
          AND
          `".TblModArticleImg."`.show = 1
          )
          WHERE `".TblModArticle."`.status='a'
          AND `".TblModArticle."`.id = `".TblModArticleTxt."`.cod
          AND `".TblModArticle."`.id = `".TblModArticleLinks."`.cod
          AND `".TblModArticleTxt."`.lang_id='".$this->lang_id."'
          ORDER BY
          position DESC limit ".$cnt; */

        $res = $this->db->db_Query($q);
        $rows = $this->db->db_GetNumRows();
        //echo '<br>'.$q.'<br/> res='.$res;
        ?>
    <div id="lastArticles">
        <div class="captionChapter"><span><?= $this->multi['TXT_FRONT_TITLE_LATEST']; ?></span><span
                class="icoArticles">&nbsp;</span></div>
        <?
        if ($rows == 0) {
            ?>
            <div class="err"><?= $this->multi['MSG_NO_ARTICLES']; ?></div><?
        } else {
            $arr = array();
            for ($i = 0; $i < $rows; $i++)
                $arr[] = $this->db->db_FetchAssoc($res);
            for ($i = 0; $i < $rows; $i++) {
                $row = $arr[$i];
                $name = strip_tags(stripslashes($row['name']));
                $short = strip_tags(stripslashes($this->Crypt->TruncateStr($row['short'], 460)));
                $link = '/articles/' . $row['cat_translit'] . '/' . $row['art_link'] . '.html';
                $linkCat = '/articles/' . $row['cat_translit'] . '/';
                $imagePath = $row['path'];

                /* if(!empty($row['img_name']))
                  $img_tit = $row['img_name'];
                  else
                  $img_tit = $name; */
                //$main_img_data =  $this->GetMainImageData($row['id'], 'front');
                ///echo '$main_img_data=';print_r($main_img_data);
                ?>
                <div class="item">
                    <div class="image">
                        <?
                        if (isset($imagePath) AND !empty($imagePath)) {
                            $imagePath = ArticleImg_Path . '/' . $row['id'] . '/' . $row['path'];
                            ?>
                            <a href="<?= $link; ?>"><?= $this->ShowImage($imagePath, $row['id'], 'size_width=314', 85, NULL, " alt='" . $name . "' title='" . $name . "'  "); ?></a><?
                        } else {
                            ?><img src="/images/design/no-image.jpg" width="150" alt="" align="left"/><?
                        }
                        ?>
                    </div>
                    <div class="dateArticles"><?= $this->ConvertDate($row['dttm']); ?> - <a
                            href="<?= $linkCat; ?>"><?= $row['cat']; ?></a></div>
                    <a class="name" href="<?= $link; ?>"><?= $name; ?></a>

                    <div class="short"><?= $short; ?></div>
                    <a class="detail" href="<?= $link; ?>"><?= $this->multi['TXT_DETAILS']; ?>→</a>
                </div>
                <?
            }
        }
        ?></div><?
    }

//

    /**
     * ArticleLayout::ShowErr()
     *
     * @return void
     */
    function ShowErr()
    {
        if ($this->Err) {
            ?>
        <div class="err" align="center">
            <h3><?= $this->multi['MSG_ERR']; ?></h3>
        </div>
        <?
        }
    }

//end of fuinction ShowErr()
    // ================================================================================================
    // Function : ShowTextMessages
    // Version : 1.0.0
    // Date : 19.01.2006
    //
    // Parms :
    // Returns : true,false / Void
    // Description : Show the text messages
    // ================================================================================================
    // Programmer : Igor Trokhymchuk
    // Date : 19.01.2006
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowTextMessages($text = NULL)
    {
        echo "<H3 align=center class='msg'>$text</H3>";
    }

//end of function ShowTextMessages()
    // ================================================================================================
    // Function : ShowNavigation
    // Version : 1.0.0
    // Date : 19.01.2006
    //
    // Parms :
    // Returns : true,false / Void
    // Description : Show navigation of news
    // ================================================================================================
    // Programmer : Ihor Trokhymchuk
    // Date : 19.01.2006
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================
    function ShowNavigation()
    {
        ?>
    <div class="navigation">
        <a href="/"><?= SITE_NAME ?></a> »
        <? if (empty($this->id) and empty($this->category)) { ?>
        Статьи
        <? } else { ?>
        <a href="/article/">Статьи</a>
        <? } ?>
        <? if (!empty($this->category) and empty($this->id)) { ?>
        » <?=
        $this->Spr->GetNameByCod(TblModArticleCat, $this->category)
        ; ?>
        <?
    } else {
        if (!empty($this->category)) {
            ?>
            »
            <a href="<?= $this->Link($this->category); ?>"><?= $this->Spr->GetNameByCod(TblModArticleCat, $this->category); ?></a>
            <? } ?>
        <? } ?>
        <? if (!empty($this->id)) { ?>
        » <?=
        strip_tags($this->Spr->GetNameByCod(TblModArticleTxt, $this->id))
        ; ?>
        <? } ?>
    </div>
    <?
    }

    // ================================================================================================
    // Function : ShowSearchResult()
    // Version : 1.0.0
    // Date :    25.09.2006
    // Parms :   $id - poll id
    // Returns : true/false
    // Description : Show Saerch form for search in the news
    // ================================================================================================
    // Programmer : Dmitriy Kerest
    // Date : 25.09.2006
    // Reason for change : Creation
    // Change Request Nbr:
    // ================================================================================================

    function ShowSearchResult($rows)
    {
        if ($rows > 0) {
            ?>

        <ul>
            <?
            for ($i = 0; $i < $rows; $i++) {
                $row = $this->db->db_FetchAssoc();
                ?>
                <li><a href="<?= $this->Link($row['category'], $row['id']) ?>"><?= stripslashes($row['name']); ?></a>
                </li>
                <?
            }
            ?>

        </ul>
        <?
        } else {
            echo $this->multi['SEARCH_NO_RES'];
        }
    }

// end of function ShowSearchForm

    /**
     * ArticleLayout::ShowArticlesLinksForDepartment()
     * Show Articles links for department
     * @param mixed $id_department
     * @return
     */
    function ShowArticlesLinksForDepartment($id_department = null)
    {
        if (!$id_department)
            return false;

        $q = "SELECT
             `" . TblModArticle . "`.id,
             `" . TblModArticleTxt . "`.name,
             `" . TblModArticleCat . "`.name as cat,
             `" . TblModArticleCat . "`.translit as cat_translit,
             `" . TblModArticle . "`.position,
             `" . TblModArticleLinks . "`.link as art_link
        FROM `" . TblModArticle . "`, `" . TblModArticleTxt . "`, `" . TblModArticleCat . "`,`" . TblModArticleLinks . "`
        WHERE  `" . TblModArticle . "`.category = `" . TblModArticleCat . "`.cod
              AND `" . TblModArticle . "`.id = `" . TblModArticleTxt . "`.cod
              AND `" . TblModArticleTxt . "`.lang_id='" . $this->lang_id . "'
              AND `" . TblModArticleCat . "`.lang_id='" . $this->lang_id . "'
              AND `" . TblModArticleTxt . "`.name!=''
              AND `" . TblModArticle . "`.id = `" . TblModArticleLinks . "`.cod
              AND `" . TblModArticle . "`.id_department = '" . $id_department . "'
              ";

        $res = $this->db->db_Query($q);
        //echo '<br>'.$q.' $res='.$res.' $this->db->result='.$this->db->result;
        if (!$res OR !$this->db->result)
            return false;
        $rows = $this->db->db_GetNumRows();
        if ($rows > 0) {
            ?>
        <div class="videoNewTitle">Статьи по теме:</div><?
            $arr = array();
            for ($i = 0; $i < $rows; $i++)
                $arr[] = $this->db->db_FetchAssoc();
            ?>
        <ul><?
            for ($i = 0; $i < $rows; $i++) {
                $row = $arr[$i];
                //$catName = stripslashes($row['cat']);
                $name = strip_tags(stripslashes($row['name']));
                $link = '/articles/' . $row['cat_translit'] . '/' . $row['art_link'] . '.html';
                //$linkCat = '/articles/'.$row['cat_translit'].'/';
                ?>
                <li><a href="<?= $link ?>"><?= $name; ?></a></li><?
            }
            ?></ul><?
        }
        return true;
    }

}

//end of class articleLayout
?>