<?php
// ================================================================================================
// System : SEOCMS
// Module : asked_frontend.class.php
// Version : 1.0.0
// Date : 27.05.2009
//
// Purpose    : Class definition for layout of Asked
//
// ================================================================================================

class AskedLayout extends Asked {

    var $fltr = NULL;
    var $category = null;

    // ================================================================================================
    //    Function          : AskedLayout (Constructor)
    //    Version           : 1.0.0
    //    Date              : 27.05.2009
    //    Returns           : Error Indicator
    //    Description       : Set the variabels
    // ================================================================================================
     function AskedLayout($user_id = NULL, $module = NULL)
     {
         //Check if Constants are overrulled
        ( $user_id != "" ? $this->user_id = $user_id : $this->user_id = NULL );
        ( $module != "" ? $this->module = $module : $this->module = 87 );

         $this->db =  DBs::getInstance();
         $this->Form = &check_init('FormAsked', 'FrontForm', "'mod_asked'");
         if(empty($this->multi)) $this->multi = &check_init_txt('TblFrontMulti', TblFrontMulti);
         if(defined("_LANG_ID")) $this->lang_id = _LANG_ID;
         if (empty($this->Crypt)) $this->Crypt = &check_init('Crypt', 'Crypt');
         if(empty($this->Spr)) $this->Spr = &check_init('SysSpr', 'SysSpr');
     }


    /**
     * AskedLayout::GetNRows()
     * @author Yaroslav
     * @param bool $limit
     * @return
     */
    function GetNRows( $limit = false )
    {
        $q = "SELECT * FROM ".TblModAsked." WHERE `visible` = '1' ";
        if( $this->fltr!='' ) $q = $q.$this->fltr;
        $q .=" ORDER BY `id` DESC";
        if($limit) $q .= " limit ".$this->start.",".$this->display."";
        $res = $this->db->db_Query( $q );
        //echo "<br>q=".$q." res=".$res;
        if( !$res OR !$this->db->result) return false;
        $rows = $this->db->db_GetNumRows();
        //echo "<br>rows=".$rows;
        $array = array();
        for( $i = 0; $i <$rows; $i++ ){
            $array[] = $this->db->db_FetchAssoc();
        }
        return $array;
    } // end of GetNRows



    /**
     * AskedLayout::ShowAnswersByPages()
     *
     * @author Yaroslav
     * @param mixed $page
     * @return void
     */
    function ShowAnswersByPages()
    {
        ?>
        <script type="text/javascript">
            function showDetails(elem)
            {
                var el=$(elem).parent().eq(0).parent().eq(0);
                el.find('.dot').toggle(500);
                el.find('.hide').toggle(300);
                return false;
            }
        </script>

        <a class="btnAsk" href="<?=_LINK?>ask/show_form/"><?=$this->multi['TXT_ADD_QUESTION'];?>&nbsp;→</a>
        <?
        $array = $this->GetNRows(true);
        $rows = count($array);
        if($rows==0){
             ?><div class="err"><?=$this->multi['MSG_NO_ASKED'];?></div><?
             return;
         }
        $counttextshow=320;
        for($i = 0; $i < $rows; $i++) {
            $row = $array[$i];
            $fullAnswer = stripslashes($row['answer']);
            $shortAnswer = $this->Crypt->TruncateStr(strip_tags(stripslashes($fullAnswer), '<a>'),$counttextshow);
            //$date = $this->ConvertDate($row['dttm']);
            ?>
          <div class="questionContent">
                <div class="dictionaryItem">Вопрос:</div>
                <div class="clearing"></div>
                <div class="title"><strong>Автор:</strong> <?=stripslashes($row['author']);?></div>
                <div class="title"><?=stripslashes($row['question']);?></div>
            </div>

            <div class="answerContent">
            <?if(!empty($fullAnswer)){
                ?><div class="answerCaption">Ответ:</div>

                <?if(strlen($fullAnswer)>$counttextshow){?>
                    <div class="dot">
                        <?=$shortAnswer;?>
                        <br/><a class="detail" href="#" onclick="showDetails(this); return false;" ><?=$this->multi['TXT_DETAILS'];?>→</a>
                    </div>
                    <div class="hide">
                        <?=$fullAnswer;?>
                        <br/>
                        <a class="detail" href="#" onclick="showDetails(this); return false;" >←&nbsp;<?=$this->multi['TXT_ROLL_BACK']?></a>
                    </div>
                    <?
                }
                else{?>
                    <div class="dot">
                        <?=$shortAnswer;?>
                    </div>
                <?}
            }?>
            </div>
            <?
        }

        ?><div class="pageNaviClass"><?
           $array = $this->GetNRows();
           $rows1 = count($array);
           $link = _LINK."ask/";
           $this->Form->WriteLinkPagesStatic( $link, $rows1, $this->display, $this->start, $this->sort, $this->page );
       ?></div><?
    }


    function ShowForm($flag = 0)
    {
    ?>
    <h2><?=$this->multi['TXT_ADD_QUESTION']?></h2>
    <div id="asked">
        <?
        if ($flag == 1) {
           ?><b>Ваш отзыв получен. <br/>После прохождения модерации он будет опубликован на сайте.</b><?
           $this->author = '';
           $this->email = '';
           $this->question = '';
        }
        else {
            ?>
           <form method="post" action="<?=_LINK;?>ask/add/" name="form_mod_asked" id="form_mod_asked" enctype="multipart/form-data">
               <?//$this->ShowErr();?>
               <div class="floatContainer">
                   <div class="width25 floatToLeft"><?=$this->multi['_TXT_NAME'];?>: <span class="red">*</span></div>
                   <div class="width75 floatToRight">
                        <?$this->Form->TextBox('asked_author', stripslashes($this->author));?>
                        <span class="form-hint red"><?$this->Form->ShowMessage('asked_author', $this->Err);?></span>
                   </div>
               </div>
               <div class="floatContainer">
                   <div class="width25 floatToLeft"><?=$this->multi['_TXT_E_MAIL'];?>: <span class="red">*</span></div>
                   <div class="width75 floatToRight">
                        <?$this->Form->TextBox('asked_email', stripslashes($this->email));?>
                        <span class="form-hint red"><?$this->Form->ShowMessage('asked_email', $this->Err);?></span>
                   </div>
               </div>

               <div class="floatContainer">
                   <div class="width25 floatToLeft"><?=$this->multi['_FLD_CATEGORY'];?>:</div>
                   <div class="width75 floatToRight">
                        <?/*$this->Form->TextBox('asked_email', stripslashes($this->email));?>
                        <span class="form-hint red"><?$this->Form->ShowMessage('asked_category', $this->Err);?></span>
                         <?*/
                         $this->Spr->ShowInComboBox( TblModAskedCat, 'asked_category', $this->asked_category, 40, $this->multi['FLD_SELECT_CHAPTER'] );
                         ?>
                   </div>
               </div>

               <div class="floatContainer">
                   <div class="width25 floatToLeft"><?=$this->multi['_TXT_MESSAGE'];?>: <span class="red">*</span></div>
                   <div class="width75 floatToRight">
                        <?$this->Form->TextArea('question', stripslashes($this->question), 6, 38);?><br />
                        <span class="form-hint red"><?$this->Form->ShowMessage('asked_question', $this->Err);?></span>
                   </div>
               </div>

               <?/*include_once(SITE_PATH.'/include/kcaptcha/kcaptcha.php');?>
               <div class="floatContainer">
                    <div class="width25 floatToLeft"><img src="/include/kcaptcha/index.php?<?=session_name()?>=<?=session_id()?>" alt="" /></div>
                    <div class="width75 floatToRight">
                        <div style="font-size:10px;"><?=$this->multi['_TXT_CAPTCHA'];?></div>
                        <input type="text" name="captchacodestr" class="captchacode"/>
                    </div>
               </div>*/?>

               <div class="floatContainer">
                    <div class="width75 floatToRight">
                    <input type="submit" class="btnSubmit" name="submit" value="<?=$this->multi['_TXT_SEND']?>" class="btnSubmit" onclick="return verify();"/>
                    <?//$this->Form->Button('submit',$this->multi['_TXT_SEND'], 'onclick="return verify();"');?></div>
               </div>
           </form>
           <?
       }
       ?>
   </div>

<?/*
        <div class="form-label"><label for="asked_author">Имя</label></div>
        <div class="form-input">
            <?
            if(!empty($this->Logon->user_id) ) {
                $User = new UserShow();
                $userData = $User->GetUserDataByUserEmail($this->Logon->login);
                $this->author = stripslashes($userData['name']).' '.stripslashes($userData['phone']);
            }
            $this->Form->TextBox('asked_author', $this->author, $size, 'asked_author', $this->Err)
            ?>
            <span class="form-hint red"><?$this->Form->ShowMessage('asked_author', $this->Err);?></span>
        </div>
        <div class="form-label"><label for="asked_email">E-mail</label></div>
        <div class="form-input">
            <?
            if(!empty($this->Logon->user_id) ) $this->email = $this->Logon->login;
            $this->Form->TextBox('asked_email', $this->email, $size, 'asked_email', $this->Err)
            ?>
            <span class="form-hint red"><?$this->Form->ShowMessage('asked_email', $this->Err);?></span>
        </div>
        <div class="form-label"><label for="asked_question">Отзыв</label></div>
        <div class="form-input">
            <?$this->Form->TextArea('question', $this->question, 4, 80, 'style="width:600px; height: 100px; text-align:left;"', 'asked_question', $this->Err)?>
        </div>
        <div class="form-label"></div>
        <div class="form-input">
            <input type="image" name="submit" src="/images/design/send.png" />
        </div>
        */
    }


    /**
     * AskedLayout::CheckFields()
     *
     * @author Yaroslav
     * @return
     */
    function CheckFields()
    {
        if (empty($this->author)) $this->Err['asked_author'] = $this->multi['MSG_EMPTY_NAME'];

        if (empty($this->email)) {
            $this->Err['asked_email'] = $this->multi['MSG_EMPTY_EMAIL'];
        } else if (!preg_match("/^[a-zA-Z0-9_.\-]+@[a-zA-Z0-9.\-].[a-zA-Z0-9.\-]+$/", $this->email)) {
            $this->Err['asked_email'] = 'Введите правильный E-mail';
        }

        if (empty($this->question)) $this->Err['asked_question'] = $this->multi['MSG_EMPTY_QUESTION'];

        return $this->Err;
    }


    /**
     * AskedLayout::Category()
     * Show List Of Categories for Left Menu
     * @author Yaroslav
     * @return void
     */
    function Category()
    {
        $q = "SELECT `".TblModAskedCat."`.*
              FROM `".TblModAskedCat."`
              WHERE `lang_id`='"._LANG_ID."'
              ORDER BY `move` ASC ";
        $res = $this->db->db_Query( $q );
        $rows = $this->db->db_GetNumRows();

        if ($rows>0){
            //$this->Form->Title($this->multi["FLD_CHAPTERS"]);
            ?><div class="verticalMenu">
                <ul><?
                $arr = array();
                for( $i = 0; $i < $rows; $i++ )
                    $arr[] = $this->db->db_FetchAssoc();

                for( $i = 0; $i < $rows; $i++ ){
                    $row = $arr[$i];
                    $name = $row['name'];
                    $q1 = "select * from ".TblModAsked." where category='".$row['cod']."' and visible = '1' ";
                    $res1 = $this->db->db_Query( $q1 );
                    //echo $q1.'<br/> res1 ='.$res1;
                    $rows1 = $this->db->db_GetNumRows();

                    if( $rows1 ) {
                        $class='';
                        if($this->category== $row['cod'])
                            $class='Active';
                        $link =  $this->Link($row['cod']);
                        ?><li><a class="<?=$class;?>" href="<?=$link;?>"><?=$name;?></a></li><?
                    } // end if
                } // end for
                ?></ul>
            </div><?
        }
    } //end of function Category
}
?>