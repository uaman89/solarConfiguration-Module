<?php

class userBlogLayout extends User{
    
       var $db=NULL;
       var $Msg=NULL;
       var $logon=NULL;
       var $Spr=NULL;
       var $Form = NULL;
       
       var $whattodo = NULL;
       var $referer_page = NULL;
       var $TextMessages = NULL;
       var $Catalog = NULL;
       
       
    function __construct($session_id=NULL, $user_id=NULL){
        ( $session_id   !="" ? $this->session_id  = $session_id   : $this->session_id  = NULL );
                ( $user_id      !="" ? $this->user_id     = $user_id      : $this->user_id     = NULL );

                if (empty($this->db))  $this->db = Singleton::getInstance('DB');
                if (empty($this->Msg)) $this->Msg = new ShowMsg();
                $this->Msg->SetShowTable(TblModUserSprTxt);
                if (empty($this->Logon)) $this->Logon = new  UserAuthorize();
                if (empty($this->Spr)) $this->Spr = new  SysSpr();
                if (empty($this->Form)) $this->Form = new FrontForm('form_mod_user');
                $this->multiUser = $this->Msg->GetMultiTxtInArr(TblModUserSprTxt);
                if(empty($this->Catalog)) $this->Catalog = Singleton::getInstance('Catalog'); 
    }           
    
    function show_TinyMCE(){
        ?>
        <script type="text/javascript" src="/include/js/tinymce/tiny_mce.js"></script>
        <script type="text/javascript">
            function tinyMceInit(){
                tinyMCE.init({
                        // General options
                        mode : "textareas",
                        theme : "advanced",
                        editor_selector : "tiny",
                        plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,images,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

                        // Theme options
                        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,link,pasteword,pastetext,table",
                        theme_advanced_buttons2 : "emotions,|,search,replace,|,hr,removeformat,visualaid,|,media,fullscreen,tcut,|,bullist,numlist,|,undo,redo,|,code,fullscreen,|,forecolor,backcolor,pagebreak,images",
                        theme_advanced_buttons3 : "",
                        theme_advanced_toolbar_location : "top",
                        theme_advanced_toolbar_align : "left",
                        theme_advanced_statusbar_location : "bottom",
                        theme_advanced_resizing : true,

                        // Example content CSS (should be your site CSS)
                       // content_css : "/default.css",
                       skin : "o2k7",
                       skin_variant : "silver",
                        //Path
                        relative_urls : false,
                        remove_script_host : true,

                        extended_valid_elements : "tcut",

                        language : "ru"
                });
            }
            tinyMceInit();
        </script>
        
        <?php
    }
    
    function show_JS(){
        ?>
         <script type="text/javascript">
         function verify() {
                var themessage = "<div style='text-align: left;'>Перевірте правильність заповнення полів реестрації.<br/> Зверніть увагу на слідуючі помилки:<br/><br/><span style='color:red;'>";
                document.getElementById('tiny').value = tinyMCE.get('tiny').getContent();
                if (document.forms.newBlog.headerBlog.value=="" ) {
                    themessage = themessage + " - Ви не ввели заголовок публікації!<br/>";
                }
                
                if (document.forms.newBlog.headerBlog.value=="" ) {
                    themessage = themessage + " - Введіть саму публікацію!<br/>";
                }
                
                if (themessage == "<div style='text-align: left;'>Перевірте правильність заповнення полів реестрації.<br/> Зверніть увагу на слідуючі помилки:<br/><br/><span style='color:red;'>")
                {
                    
                    SaveForm();
                    return true;
                }
                else 
                   $.fancybox(themessage+"</span></div>");
                return false;
            }
         function SaveForm(){
              $.ajax({
                   type: "POST",
                   data: $("#newBlog").serialize() ,
                   url: "<?php  echo _LINK;?>saveNewBlogRecord",
                   beforeSend : function(){ 
                       $("#CatformAjaxLoader").width($("#catalogBody").width()).height($("#catalogBody").height()+20).fadeTo("fast", 0.4);
                        //$(Did).show("fast");
                    },
                   success: function(html){
                       $("#CatformAjaxLoader").fadeOut("fast",function(){
                           //$("#contentBox").html(html);
                           $(".headerBlogAddNew").html("Редагувати запис:"+html)
                       });
                       

                   }
              });
         }
         </script>
         <?php
    }
    
    function addNewRecordShowRedactor(){
        $this->show_TinyMCE();
        $this->show_JS();
        $SysGroup = new SysUser();
    // echo 'this->login = '.$this->login ;
     //echo '<br/>this->Logon->login = '.$this->Logon->login ;
     $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->Logon->user_id." AND `".TblSysUser."`.id=".$this->Logon->user_id."";
     $res = $this->db->db_Query($q);
     //echo '<br>'.$q.' $res='.$res.' $this->db->result='.$this->db->result;
     if ( !$res OR !$this->db->result ) return false;
     $mas = $this->db->db_FetchAssoc();
     $update=false;
     if($this->recordId!=NULL){
         $update=true;
         $q="SELECT * FROM ".TblModUserBlog." WHERE `id_user`='".$this->Logon->user_id."' AND `id`='".$this->recordId."'";
         $ress = $this->db->db_Query($q);
         $rowcount=$this->db->db_GetNumRows();
         $val = $this->db->db_FetchAssoc();
         if($rowcount==1){
             $this->headerBlog=$val['title'];
             $this->blogContent=$val['content'];
         }
     }
     $_SESSION['sys_user_id']=$this->Logon->user_id;
        ?>
      
        <div id="catalogBox">
            <?php if($this->recordId!=NULL){ ?>
            <span class="MainHeaderText">Редагувати запис</span>
            <?php }else{  ?>
            <span class="MainHeaderText">Додати запис</span>
            <?php }?>
            
            
            
            <div id="profileMenuHandler">
                <div id="leftProfileMenuPart">
                    <?php  if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->Logon->user_id."/".$mas['discount'])){?>
                    <br/>
                           <img class="avatarImage profileAvatar" src="<?php  echo $this->Catalog->ShowCurrentImageExSize("/images/mod_blog/".$this->Logon->user_id."/".$mas['discount'], 70, 70, 'center', 'center', '85', NULL, NULL, NULL, true);?>" alt="" />
                            <?php }else{?>
                      <br/><img class="avatarImage profileAvatar" width="70" height="70" src="/images/design/noAvatar.gif"/>     
                           <?php }?>
                    <?php if(empty($mas['name'])){?>
                    <span class="profileName"><?php  echo $mas['login']?></span>
                    <?php }else{?>
                    <span class="profileName"><?php  echo $mas['name']." ".$mas['country']?></span>
                    <?php }?>
                </div>
                <div id="centerProfileMenuPart">
                    <a class="blogBtnUserProfile selectedPunktClass" href="/myaccount/blog/">Блог</a>
                    <a class="editProfile" href="/myaccount/">Редагувати Профіль</a>
                    <a class="commentsProfile" href="/myaccount/comments/">Коментарі</a>
                </div>
                <div id="rightProfileMenuPart"></div>
            </div>
            
            
            <div id="catalogBody" style="background: #fafafa">
                <div id="CatformAjaxLoader"></div>
                <?php if($this->recordId!=NULL){ ?>
            <span  class="headerBlogAddNew">Редагувати запис:</span><br/>
            <input type="button" style="float: right" class="btnCatalogImgUpload" onclick="location.href='http://1ztua.seotm.biz/myaccount/blog/'" name="save_reg_data" value="Новий запис"/>
            <?php }else{?>
            <span  class="headerBlogAddNew">Додати запис:</span>
            <?php }?>
            <span class="zagolovok">Заголовок запису:</span>
            
            <form method="post" action="#" name="newBlog" id="newBlog">
                <?php if($this->recordId!=NULL){ ?>
                <input type="HIDDEN" name="recordId" value="<?php  echo $this->recordId?>"/>
                <?php }?>
                  <input class="headerOfSingleBlogInput" type="text" name="headerBlog" value="<?php  echo $this->headerBlog;?>"/><br style="clear: both;"/>
                  <textarea name="blogContent" style="width:100%" class="tiny" id="tiny"><?php  echo $this->blogContent?></textarea>
            </form>
            <?php if($this->recordId!=NULL){ ?>
            <input type="button" style="float: left" class="btnCatalogImgUpload" onclick="verify()" name="save_reg_data" class="submitBtn<?php  echo _LANG_ID?>" value="Зберігти" />
            <?php }else{?>
            <input type="button" style="float: left" class="btnCatalogImgUpload" onclick="verify()" name="save_reg_data" class="submitBtn<?php  echo _LANG_ID?>" value="Публікація" />
            <?php }?>
        </div>
        </div>
        <?php
    }
    
    function saveNewBlogRecord(){
        
        if($this->recordId!=NULL){
            $q="UPDATE `".TblModUserBlog."` SET
                `title`='".$this->headerBlog."',
                `content`='".$this->blogContent."',
                `dttm`='".date("Y-m-d")." ".date("G:i:s")."',
                `is_comment`=1,
                `visible`=1
                WHERE `id`='".$this->recordId."' AND `id_user`='".$this->Logon->user_id."'";
            $res = $this->db->db_Query($q);
            if($res)
            echo "<span style='float:right; color:green;margin-left:10px;'>Ваш запис успішно змінений</span>";
            else echo "<span style='float:right; color:red;'>Під час внесення змін виникла помилка</span>";
        }else{
            $q="INSERT INTO `".TblModUserBlog."` SET
                `id_user`='".$this->Logon->user_id."',
                `title`='".$this->headerBlog."',
                `content`='".$this->blogContent."',
                `dttm`='".date("Y-m-d")." ".date("G:i:s")."',
                `is_comment`=1,
                `visible`=1";
            $res = $this->db->db_Query($q);
            $blogId=$this->db->db_GetInsertID();
            if($res){?>
            <span style='float:right; color:green;'>Ваш запис успішно опублікований</span>
                <br/><script type="text/javascript">location.href='<?php  echo $this->link($this->Logon->user_id,$blogId)?>'</script>
            <?php }else echo "<span style='float:right; color:red;'>Під час публікації виникла помилка</span>";
        }
        
    }
    
    function showUserBlogRecords(){
        $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->userId." AND `".TblSysUser."`.id=".$this->userId."";
        $res = $this->db->db_Query($q);
        $mas = $this->db->db_FetchAssoc();//die(mysql_error());
        $userImage="";
        
        if(isset($mas['discount'])) $userImage=$mas['discount'];
        
        $userBlogArr=$this->getUsersBlogArr();//print_r($userBlogArr);
        $rowsCount=count($userBlogArr);
       // $mainContent=strpos($mas[''],"<!-- pagebreak -->");
         ?>
      
        <div id="catalogBox">
            <?php if($mas['group_id']!=7){?>
            <div id="profileMenuHandlerBlog" style="background: none; border: none;">
                <div id="leftProfileMenuPartBlog"  style="width: 409px;">
                    <?php  if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->userId."/".$userImage)){?>
                    <br/>
                    <img class="avatarImage profileAvatarBlog" src="<?php  echo $this->Catalog->ShowCurrentImageExSize("/images/mod_blog/".$this->userId."/".$userImage, 102, 102, 'center', 'center', '85', NULL, NULL, NULL, true);?>" alt="" />
                           <?php if(empty($mas['name'])){?>
                    <span class="profileNameBlog"><?php  echo $mas['login']?><p style="color: #585858; font-size: 14px;line-height: 0px;">блоги</p>
                           </span>
                    <?php }else{?>
                    <span class="profileNameBlog"><?php  echo $mas['name']." ".$mas['country']?><p style="color: #585858; font-size: 14px;line-height: 0px;">блоги</p>
                           </span>
                    <?php }?>
                            <?php }else{?>
                      <br/><img class="avatarImage profileAvatar" width="70" height="70" src="/images/design/noAvatar.gif"/>     
                           <?php }?>
                           
                </div>
                <div id="centerProfileMenuPartBlog" style="width: 188px;">
                    <a class="blogBtnUserProfileBlog selectedPunktClassBlog"  href="/blog/<?php  echo $this->userId?>/">Блог</a>
                    <a class="editProfileBlog" style="width: 112px;" href="/blog/user/<?php  echo $this->userId?>/about/">Про мене</a>
                </div>
                <div id="rightProfileMenuPartBlog" style="width: 89px;"></div>
            </div>
            <?php }else{//=============expert club begin?>
             <div id="profileMenuHandlerBlogExpert" style="<?php
             if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->userId."/".$mas['expertImg'])){ 
                 echo "background:url('/images/mod_blog/".$this->userId."/".$mas['expertImg']."') no-repeat bottom left;"; 
             }else{
                 echo "background: none;";
                 }
             ?>border: none;">
              <span class="profileNameBlogExpert" style="position: absolute;margin-left: 321px; margin-top: 67px;"><?php  echo $mas['name']." ".$mas['country']?>
                           <p style="color: #585858;text-align: right; font-size: 14px;line-height: 0px; font-style: italic;font-weight: normal">експертний клуб</p>
                           </span>
                <div id="leftProfileMenuPartBlogExpert"  style="width: 409px;">
                          
                </div>
                <div id="centerProfileMenuPartBlogExpert" style="width: 188px;">
                    <a class="blogBtnUserProfileBlogExpert selectedPunktClassBlogExpert" style="width: 76px;"  href="/blog/<?php  echo $this->userId?>/">Блог</a>
                    <a class="editProfileBlogExpert" style="width: 112px;" href="/blog/user/<?php  echo $this->userId?>/about/">Про мене</a>
                </div>
                <div id="rightProfileMenuPartBlogExpert" style="width: 89px;"></div>
            </div>
            <?php }//===========expert club end?>
            
            <div id="catalogBody" style="padding-left: 0px; padding-right: 0px; background: none;width: 100%;">
<!--                <span class="pageHandler">-->
                <?php $rows_all = count($this->getUsersBlogArr('nolimit'));
                $link = $this->link($this->userId);
                ?>
                 <?php if($rowsCount>$this->display){?>
                <div class="PageNaviBack">  
                   
                <div class="pageNaviClass" style="padding-top: 15px;height: 40px;"><?php $this->Form->WriteLinkPagesStatic( $link, $rows_all, $this->display, $this->start, $this->sort, $this->page);?></div>
                </div>
<!--                </span>-->
                <?php }?>
                <?php
                if($rowsCount==0) echo "<h1 style='text-align: center;line-height: 200px;'>У даного користувача блоги відсутні</h1> ";
                $items=0;
                for( $i=0; $i<$rowsCount; $i++ )
                {
                  $row=$userBlogArr[$i];
                  if($i==0)
                    $items = $row['id'];
                  else
                    $items = $items.', '.$row['id'];
                }
               //echo $q,'<br/> res= '.$res;
       
                /*$ModulesPlug = new ModulesPlug();
                $id_module = $ModulesPlug->GetModuleIdByPath( '/modules/mod_article/article.backend.php' );*/
                $id_module = 87; 
                if(!isset($this->Comments))
                    $this->Comments = new FrontComments($id_module);
                // Масив кількості коментарів на блог
                $commentCount = $this->Comments->GetCommentsCount($items);
        
                for($i=0;$i<$rowsCount;$i++){
                    $row=$userBlogArr[$i];
                    $shortContentEnd=strpos($row['content'],"<!-- pagebreak -->");
                     if($shortContentEnd==0){
                          $mainContent=$row['content'];
                     }else $mainContent=substr($row['content'],0,$shortContentEnd);
                    $day=$row['dttm'][5].$row['dttm'][6];
                    $month=$row['dttm'][8].$row['dttm'][9];
                    $year=$row['dttm'][0].$row['dttm'][1].$row['dttm'][2].$row['dttm'][3];
                    $time=$row['dttm'][11].$row['dttm'][12].$row['dttm'][13].$row['dttm'][14].$row['dttm'][15];
                    ?>
                <div class="newsColumnLast" style="float: left; display: block;width: 100%;">
                    <span class="blogData"><?php  echo $day.".".$month.".".$year." - ".$time?></span>
                    <span class="blogSingleHeader"><?php  echo $row['title']?></span>
                    <div class="singleBlogContent"><?php  echo $mainContent?></div>
                    <div class="news_colum1_1_footer">
                        <div class="news_colum1_1_footer_text"><img src="/images/design/oblako.png" alt="" />
                        <?php $link = "/blog/".$this->userId."/entry/".$row['id'].'#commentsBlock';?>
                        <a href="<?php  echo $link;?>">Коментарів - <?php
                            if(isset($commentCount[$row['id']]))
                                echo $commentCount[$row['id']];
                            else
                                echo '0';
                                ?></a>
                                
                        <?php  if($this->userId==$this->Logon->user_id) echo "<a class='editLink' href='"."/myaccount/blog/edit/".$row['id']."/'>Редагувати запис</a><a style='margin-left:10px' href='/blog/".$row['id']."/user/".$this->Logon->user_id."/delete'>Видалити запис</a>";?></div>
                        <a class="news_colum1_1_footer_but" style="text-decoration: none" href="<?php  echo "/blog/".$this->userId."/entry/".$row['id']?>">Читати</a>
                    </div>
                </div>
                    <?php
                }
             
                $link = $this->link($this->userId);
                ?><div class="pageNaviClass" style="padding-top: 10px;"><?php $this->Form->WriteLinkPagesStatic( $link, $rows_all, $this->display, $this->start, $this->sort, $this->page);?></div>
            </div>
        </div>
         
         <?php
    }
    
    function getUsersBlogArr($limit='limit'){
        $q="SELECT * FROM ".TblModUserBlog." WHERE `id_user`='".$this->userId."' AND `visible`=1 ORDER BY `dttm` DESC";
        if($limit=='limit') $q = $q." LIMIT ".$this->start.", ".($this->display);
                $res = $this->db->db_Query($q);
        if( !$res or !$this->db->result) return false;
        $rows = $this->db->db_GetNumRows($res);
        //echo '<br>$rows='.$rows;
        $arr=array();
        for($i=0;$i<$rows;$i++){
            $row = $this->db->db_FetchAssoc();
            $arr[$i]=$row;
        }
        //print_r($arr);
        return $arr;
    }
    
    function ShowUserInfo(){
        $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->userId." AND `".TblSysUser."`.id=".$this->userId."";
        $res = $this->db->db_Query($q);
        $mas = $this->db->db_FetchAssoc();
        $userImage="";
        $day=$mas['city'][5].$mas['city'][6];
        $month=$mas['city'][8].$mas['city'][9];
        $year=$mas['city'][0].$mas['city'][1].$mas['city'][2].$mas['city'][3];
        if(isset($mas['discount'])) $userImage=$mas['discount'];
        ?>
         <div id="catalogBox">
        
        
             <?php //=$this->makeImageGrey($userImage,$_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->userId."/")?>
             
         <?php if($mas['group_id']!=7){?>
            <div id="profileMenuHandlerBlog" style="background: none; border: none;">
                <div id="leftProfileMenuPartBlog"  style="width: 409px;">
                    <?php  if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->userId."/".$userImage)){?>
                    <br/>
                    <img class="avatarImage profileAvatarBlog" src="<?php  echo $this->Catalog->ShowCurrentImageExSize("/images/mod_blog/".$this->userId."/".$userImage, 102, 102, 'center', 'center', '85', NULL, NULL, NULL, true);?>" alt="" />
                           <?php if(empty($mas['name'])){?>
                    <span class="profileNameBlog"><?php  echo $mas['login']?><p style="color: #585858; font-size: 14px;line-height: 0px;">блоги</p>
                           </span>
                    <?php }else{?>
                    <span class="profileNameBlog"><?php  echo $mas['name']." ".$mas['country']?><p style="color: #585858; font-size: 14px;line-height: 0px;">блоги</p>
                           </span>
                    <?php }?>
                           
                            <?php }else{?>
                      <br/><img class="avatarImage profileAvatar" width="70" height="70" src="/images/design/noAvatar.gif"/>     
                           <?php }?>
                          
                </div>
                <div id="centerProfileMenuPartBlog" style="width: 188px;">
                    <a class="blogBtnUserProfileBlog"  href="/blog/<?php  echo $this->userId?>/">Блог</a>
                    <a class="editProfileBlog selectedPunktClassBlog" style="width: 112px;" href="/blog/user/<?php  echo $this->userId?>/about/">Про мене</a>
                </div>
                <div id="rightProfileMenuPartBlog" style="width: 89px;"></div>
            </div>
            <?php }else{//=============expert club begin?>
             <div id="profileMenuHandlerBlogExpert" style="<?php
             if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->userId."/".$mas['expertImg'])){ 
                 echo "background:url('/images/mod_blog/".$this->userId."/".$mas['expertImg']."') no-repeat bottom left;"; 
             }else{
                 echo "background: none;";
                 }
             ?>border: none;">
              <span class="profileNameBlogExpert" style="position: absolute;margin-left: 321px; margin-top: 67px;"><?php  echo $mas['name']." ".$mas['country']?>
                           <br style="clear: both"/><p style="color: #585858;text-align: right; font-size: 14px;line-height: 0px; font-style: italic;font-weight: normal">експертний клуб</p>
                           </span>
                <div id="leftProfileMenuPartBlogExpert"  style="width: 409px;">
                          
                </div>
                <div id="centerProfileMenuPartBlogExpert" style="width: 188px;">
                    <a class="blogBtnUserProfileBlogExpert " style="width: 76px;"  href="/blog/<?php  echo $this->userId?>/">Блог</a>
                    <a class="editProfileBlogExpert selectedPunktClassBlogExpert" style="width: 112px;" href="/blog/user/<?php  echo $this->userId?>/about/">Про мене</a>
                </div>
                <div id="rightProfileMenuPartBlogExpert" style="width: 89px;"></div>
            </div>
            <?php }//===========expert club end?>
         <div id="catalogBody" style="padding-left: 0px; padding-right: 0px; background: none;width: 100%;">
             <ul class="CatFormUl" style="padding-left: 35px;width: 250px;float: left;">
                 <?php if(!empty($mas['country'])){?>
                 <li>Прізвище:
                      <span id="nameOfPred" class="aboutMeSpan"><?php  echo $mas['country']?> </span>
                  </li>
                  <?php }?>
                  <?php if(!empty($mas['name'])){?>
                  <li>Ім'я:
                      <span id="nameOfPred" class="aboutMeSpan"><?php  echo $mas['name']?></span>
                  </li>
                  <?php }?>
                  <li>
                      Стать:
                          <span id="nameOfPred" class="aboutMeSpan"><?php if($mas['state']=='m') echo "Чоловіча";else echo "Жіноча";?></span>
                  </li>
                  <?php if(!empty($day) && !empty($month) && !empty($year)){?>
                  <li>
                      Дата народження:<span id="nameOfPred" class="aboutMeSpan"><?php  echo $day.".".$month.".".$year?></span>
                  </li>
                  <?php }?>
                  <?php if(!empty($mas['www'])){?>
                  <li>Сайт:
                      <span id="nameOfPred" class="aboutMeSpan"><?php  echo $mas['www']?></span>
                  </li>
                  <?php }?>
                  <?php if(!empty($mas['phone'])){?>
                  <li>Телефон:
                      <span id="nameOfPred" class="aboutMeSpan"><?php  echo $mas['phone']?></span>
                  </li>
                  <?php }?>
                  <?php if(!empty($mas['phone_mob'])){?>
                  <li>Facebook:
                      <span id="nameOfPred" class="aboutMeSpan" ><?php  echo $mas['phone_mob']?></span>
                  </li>
                  <?php }?>
                  <?php if(!empty($mas['fax'])){?>
                  <li>Вконтакті:
                      <span id="nameOfPred" class="aboutMeSpan"><?php  echo $mas['fax']?></span>
                  </li>
                  <?php }?>
                  <?php if(!empty($mas['bonuses'])){?>
                  <li>Twitter:
                      <span id="nameOfPred" class="aboutMeSpan"><?php  echo $mas['bonuses']?></span>
                  </li>
                  <?php }?>
                </ul>                 
             <?php if(!empty($mas['aboutMe'])){?>
             <div class="aboutMeTexBox">
                 <span style="font-weight: bold;">Коротко про мене:</span><br/><br/>
                          <?php  echo $mas['aboutMe']?></div>
             <?php }?>
        </div>
         </div>
        <?php
    }
    
    function ShowCurrentArticle(){
        if(isset($this->recordId)){
            $q="SELECT * FROM ".TblModUserBlog." WHERE `id`='".$this->recordId."' AND `visible`=1";
            $res = $this->db->db_Query($q);
            $row = $this->db->db_FetchAssoc();
            
            $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->userId." AND `".TblSysUser."`.id=".$this->userId."";
             $res = $this->db->db_Query($q);
            $mas = $this->db->db_FetchAssoc();
            $userImage="";
        
        if(isset($mas['discount'])) $userImage=$mas['discount'];
        
        $userBlogArr=$this->getUsersBlogArr();//print_r($userBlogArr);
       // $mainContent=strpos($mas[''],"<!-- pagebreak -->");
         ?>
      
        <div id="catalogBox">
             <?php if($mas['group_id']!=7){?>
            <div id="profileMenuHandlerBlog" style="background: none; border: none;">
                <div id="leftProfileMenuPartBlog"  style="width: 409px;">
                    <?php  if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->userId."/".$userImage)){?>
                   <br/>
                    <img class="avatarImage profileAvatarBlog" src="<?php  echo $this->Catalog->ShowCurrentImageExSize("/images/mod_blog/".$this->userId."/".$userImage, 102, 102, 'center', 'center', '85', NULL, NULL, NULL, true);?>" alt="" />
                           <?php if(empty($mas['name'])){?>
                    <span class="profileNameBlog"><?php  echo $mas['login']?><p style="color: #585858; font-size: 14px;line-height: 0px;">блоги</p>
                           </span>
                    <?php }else{?>
                    <span class="profileNameBlog"><?php  echo $mas['name']." ".$mas['country']?><p style="color: #585858; font-size: 14px;line-height: 0px;">блоги</p>
                           </span>
                    <?php }?>
                            <?php }else{?>
                      <br/><img class="avatarImage profileAvatar" width="70" height="70" src="/images/design/noAvatar.gif"/>     
                           <?php }?>
                           
                </div>
                <div id="centerProfileMenuPartBlog" style="width: 188px;">
                    <a class="blogBtnUserProfileBlog selectedPunktClassBlog"  href="/blog/<?php  echo $this->userId?>/">Блог</a>
                    <a class="editProfileBlog" style="width: 112px;" href="/blog/user/<?php  echo $this->userId?>/about/">Про мене</a>
                </div>
                <div id="rightProfileMenuPartBlog" style="width: 89px;"></div>
            </div>
            <?php }else{//=============expert club begin?>
             <div id="profileMenuHandlerBlogExpert" style="<?php
             if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->userId."/".$mas['expertImg'])){ 
                 echo "background:url('/images/mod_blog/".$this->userId."/".$mas['expertImg']."') no-repeat bottom left;"; 
             }else{
                 echo "background: none;";
                 }
             ?>border: none;">
              <span class="profileNameBlogExpert" style="position: absolute;margin-left: 321px; margin-top: 67px;"><?php  echo $mas['name']." ".$mas['country']?>
                           <p style="color: #585858;text-align: right; font-size: 14px;line-height: 0px; font-style: italic;font-weight: normal">експертний клуб</p>
                           </span>
                <div id="leftProfileMenuPartBlogExpert"  style="width: 409px;">
                          
                </div>
                <div id="centerProfileMenuPartBlogExpert" style="width: 188px;">
                    <a class="blogBtnUserProfileBlogExpert selectedPunktClassBlogExpert" style="width: 76px;"  href="/blog/<?php  echo $this->userId?>/">Блог</a>
                    <a class="editProfileBlogExpert" style="width: 112px;" href="/blog/user/<?php  echo $this->userId?>/about/">Про мене</a>
                </div>
                <div id="rightProfileMenuPartBlogExpert" style="width: 89px;"></div>
            </div>
            <?php }//===========expert club end?>
            
            
            <div id="catalogBody" style="padding-left: 0px; padding-right: 0px; background: none;width: 100%;">
                <?php
                    
                    $day=$row['dttm'][5].$row['dttm'][6];
                    $month=$row['dttm'][8].$row['dttm'][9];
                    $year=$row['dttm'][0].$row['dttm'][1].$row['dttm'][2].$row['dttm'][3];
                    $time=$row['dttm'][11].$row['dttm'][12].$row['dttm'][13].$row['dttm'][14].$row['dttm'][15];
                    ?>
                <div class="newsColumnLast" style="float: left; display: block;width: 100%;">
                    <span class="blogData"><?php  echo $day.".".$month.".".$year." - ".$time?></span>
                    <span class="blogSingleHeader"><?php  echo $row['title']?></span>
                    <div class="singleBlogContent"><?php  echo $row['content']?></div>
                   
                </div>
                  <?php  if($this->userId==$this->Logon->user_id) echo "<a class='editLink' href='"."/myaccount/blog/edit/".$row['id']."/'>Редагувати запис</a><a class='editLink' style='margin-left:10px' href='/blog/".$row['id']."/user/".$this->Logon->user_id."/delete'>Видалити запис</a>";?>
            </div>
        </div>
            
            <?php


        //if( $Page->News->is_comments==1){
            if(!isset($this->Comments))
                $this->Comments = new FrontComments($this->module, $this->recordId);
             $this->Comments->ShowCommentsByModuleAndItem();
             ?> <div style="margin-left: 10px;"><?php
             $this->Comments->FacebookComments();
              ?></div> <?php
            
        }
    }
    function showLastBlogsUser($expert=false){
        $q="SELECT `".TblModUser."`.*,
            `".TblSysUser."`.`login`,
            `".TblModUserBlog."`.`dttm`,
            `".TblModUserBlog."`.`title`,
            `".TblModUserBlog."`.`id_user`,
            `".TblModUserBlog."`.`id` AS `ArticlId`
            FROM   `".TblModUserBlog."` INNER JOIN ( select max(id) as id from `".TblModUserBlog."` group by `id_user` ) dts ON dts.id=`".TblModUserBlog."`.id, 
              `".TblModUser."`,
              `".TblSysUser."`
            WHERE `".TblModUserBlog."`.`id_user`=`".TblSysUser."`.`id`";
        $q.=" AND `".TblModUser."`.`sys_user_id`=`".TblSysUser."`.`id`";
        if(!empty($this->letter))
        $q.=" AND (`".TblModUser."`.`country` LIKE '".$this->letter."%' OR `".TblSysUser."`.`login` LIKE '".$this->letter."%')";
        if($expert) $q.=" AND `".TblSysUser."`.`group_id`=7";
        else $q.=" AND `".TblSysUser."`.`group_id`=5";
        $q.=" 
                ORDER BY `".TblModUserBlog."`.`dttm` DESC";
        $q = $q." LIMIT 5";
        $res = $this->db->db_Query($q);
       // echo '<br>$q='.$q;
        $rows = $this->db->db_GetNumRows();
        if($rows>0){
            //echo $q;
            ?>
            <div id="best_blogs">
                <div id="best_blogs_title">Останні блоги</div>
            <?php
            for($i=0;$i<$rows;$i++){
                $row=$this->db->db_FetchAssoc();
                ?>
                <div class="blog_items">
                    <a href="<?php  echo $this->link($row['sys_user_id'])?>">
                    <img src="<?php  echo $this->Catalog->ShowCurrentImageExSize("/images/mod_blog/".$row['sys_user_id']."/".$row['discount'], 41, 41, 'center', 'center', '85', NULL, NULL, NULL, true);?>" alt="" />
                    </a>
                    <div class="blog_item_name"><?php  echo $row['name']." ".$row['country']?></div>
                    <div class="blog_item_date"><?php  echo $row['dttm']?></div>
                    <a style="text-align: left; margin-right: 20px;" href="<?php  echo $this->link($row['id_user'],$row['ArticlId'])?>" class="blog_item_thema"><?php  echo $row['title']?></a>
                </div>
                <?php
                if($i%2!=0){
                    ?>
                <div style="float: left;display: block;width: 280px;"></div>
                    <?php
                }
            } 
            ?><br style="clear: both;"/><a class="more_blog" style="text-align: right; margin-right: 20px;" href="/users/">Переглянути всіх<img src="/images/design/down.png" alt="" /></a>
            </div><?php
         }
    }
    function showBestBlogs($experts=false){
        // AND `visible`=1 LIMIT 5
        $module=87;
        
  
         $q="SELECT 
                `id_item`,
                count(id_item) as `count`,
                `".TblModUserBlog."`.*,
                `".TblModUser."`.*,
                `".TblSysUser."`.`group_id`,    
                `".TblModUserBlog."`.`id` as `ArticlId`    
                FROM `".TblSysModComments."`,`".TblModUserBlog."`,`".TblModUser."`,`".TblSysUser."` 
                WHERE `id_module`='".$module."'
                    AND `".TblModUserBlog."`.`id`=`id_item`
                    AND `".TblModUser."`.`sys_user_id`=`".TblModUserBlog."`.`id_user`
                    AND `".TblSysUser."`.`id`=`".TblModUserBlog."`.`id_user`";
         if($experts)      
         $q.=" AND `".TblSysUser."`.`group_id`=7 ";
         else $q.=" AND `".TblSysUser."`.`group_id`=5 ";
               $q.= " GROUP BY id_item 
                ORDER BY `count` DESC
                ";

            $res = $this->db->db_Query($q);
            $rows = $this->db->db_GetNumRows();
            if($rows>0){
            //echo $q;
            ?>
            <div id="best_blogs">
                <div id="best_blogs_title"><?php if($experts) echo "Експертний клуб";else echo "Найкращі блоги";?></div>
            <?php
            for($i=0;$i<$rows;$i++){
                $row=$this->db->db_FetchAssoc();
                ?>
                <div class="blog_items">
                    <a href="<?php  echo $this->link($row['sys_user_id'])?>">
                    <img src="<?php  echo $this->Catalog->ShowCurrentImageExSize("/images/mod_blog/".$row['sys_user_id']."/".$row['discount'], 41, 41, 'center', 'center', '85', NULL, NULL, NULL, true);?>" alt="" />
                    </a>
                    <div class="blog_item_name"><?php  echo $row['name']." ".$row['country']?></div>
                    <div class="blog_item_date"><?php  echo $row['dttm']?></div>
                    <a style="text-align: left; margin-right: 20px;" href="<?php  echo $this->link($row['id_user'],$row['ArticlId'])?>" class="blog_item_thema"><?php  echo $row['title']?></a>
                </div>
                <?php
                if($i%2!=0){
                    ?>
                <div style="float: left;display: block;width: 280px;"></div>
                    <?php
                }
            } 
            ?><br style="clear: both;"/><a class="more_blog" style="text-align: right; margin-right: 20px;" href="/users/"><?php if(!$experts) echo "Переглянути більше блогів";else echo "Переглянути всіх";?> <img src="/images/design/down.png" alt="" /></a>
            <?php if(!$experts){?>
            <div class="WhantBlog">
            <span class="WhantBlogText">Бажаєте вести блог?<a class="news_colum1_1_footer_but" style="text-decoration: none;margin-top: -2px;margin-left: 14px;" href="/registration/">Приєднатись</a></span>
            </div>
            <?php }?>
            </div>
               
            <?php
         }
    }
    
    function showBestExperts(){
        $module=87;
        if(empty($this->CatalogLayout)) $this->CatalogLayout = Singleton::getInstance('Catalog'); 
  
        $q="SELECT 
                `id_item`,
                count(id_item) as `count`,
                `".TblModUserBlog."`.*,
                `".TblModUser."`.*,
                `".TblSysUser."`.`group_id`,    
                `".TblModUserBlog."`.`id` as `ArticlId`    
                FROM `".TblSysModComments."`,`".TblModUserBlog."`,`".TblModUser."`,`".TblSysUser."` 
                WHERE `id_module`='".$module."'
                    AND `".TblModUserBlog."`.`id`=`id_item`
                    AND `".TblModUser."`.`sys_user_id`=`".TblModUserBlog."`.`id_user`
                    AND `".TblSysUser."`.`id`=`".TblModUserBlog."`.`id_user`
                    AND `".TblSysUser."`.`group_id`=7
                GROUP BY id_item 
                ORDER BY `count` DESC
                ";
            $res = $this->db->db_Query($q);
            $rows = $this->db->db_GetNumRows();
            if($rows>0){
            ?>
            <div id="expert_club">
                <div id="expert_club_title">Експертний клуб</div>
            <?php
            for($i=0;$i<$rows;$i++){
                $row=$this->db->db_FetchAssoc();
                ?>
                <div class="expert_club_items">
                    <a href="<?php  echo $this->link($row['sys_user_id'])?>">
                    <img src="<?php  echo $this->CatalogLayout->ShowCurrentImageExSize("/images/mod_blog/".$row['sys_user_id']."/".$row['discount'], 41, 41, 'center', 'center', '85', NULL, NULL, NULL, true);?>" alt="" />
                    </a>
                    <div class="expert_club_item_name"><?php  echo $row['name']." ".$row['country']?></div>
                    <div class="expert_club_item_thema"><?php  echo $row['dttm']?></div>
                    <a href="<?php  echo $this->link($row['id_user'],$row['ArticlId'])?>" class="blog_item_thema"><?php  echo $row['title']?></a>
                </div>
                <?php
                if($i%2!=0){
                    ?>
                <div style="float: left;display: block;width: 280px;"></div>
                    <?php
                }
            } 
            ?><br style="clear: both;"/><a class="more_blog" style="text-align: right; margin-right: 20px;" href="/experts/">Переглянути всіх<img src="/images/design/down.png" alt="" /></a>
            </div><?php
            }
    }
    function countOfAllUsers($expert=NULL){
        $q="SELECT count(*) as count
            FROM `".TblModUser."`,`".TblSysUser."`
            WHERE ";
        $q.=" `".TblModUser."`.`sys_user_id`=`".TblSysUser."`.`id`";
        if($expert) $q.=" AND `".TblSysUser."`.`group_id`=7";
        else $q.=" AND `".TblSysUser."`.`group_id`=5";
        $q.=" ORDER BY `".TblModUser."`.`country` ASC";
        $res = $this->db->db_Query($q);
        $row=$this->db->db_FetchAssoc();
        return $row['count'];
    }
    function showAllUsers($expert=NULL){
         $rows_all=$this->countOfAllUsers($expert);
         $this->display=40;
        $q="SELECT `".TblModUser."`.*,`".TblSysUser."`.`login`,`".TblModUserBlog."`.`dttm`
            FROM `".TblModUserBlog."` INNER JOIN ( select max(id) as id from `".TblModUserBlog."` group by `id_user` ) dts ON dts.id=`".TblModUserBlog."`.id, 
              `".TblModUser."`,
              `".TblSysUser."`
            WHERE `".TblModUserBlog."`.`id_user`=`".TblSysUser."`.`id`";
        $q.=" AND `".TblModUser."`.`sys_user_id`=`".TblSysUser."`.`id`";
        if(!empty($this->letter))
        $q.=" AND (`".TblModUser."`.`country` LIKE '".$this->letter."%' OR `".TblSysUser."`.`login` LIKE '".$this->letter."%')";
        if($expert) $q.=" AND `".TblSysUser."`.`group_id`=7";
        else $q.=" AND `".TblSysUser."`.`group_id`=5";
        $q.=" GROUP BY `".TblModUser."`.`sys_user_id`
                ORDER BY `".TblModUserBlog."`.`dttm` DESC";
        if(empty($this->letter)) $q = $q." LIMIT ".$this->start.", ".($this->display);
        $res = $this->db->db_Query($q);
        //echo '<br>$q='.$q;
        $rows = $this->db->db_GetNumRows();
        //echo $rows;
       
        if(isset($mas['discount'])) $userImage=$mas['discount'];
        ?>
         <div id="catalogBox">
            <span class="MainHeaderText" style="width: 100%"><?php if(!$expert) echo "Користувачі порталу <a href='/experts/' style='float: right;margin-right: 90px;color:#0950A5'>Експерти</a>"; else echo "Експерти порталу <a href='/users/' style='float: right;margin-right: 90px;color:#0950A5'>Користувачі</a>";?></span>
            
            <div class="alphavitBox">
                <span class="alafaBoxes" style="margin-left: 5px;">
                    <?php if($expert){?>
                    <a <?php if(empty($this->letter))  echo "class='letterSelected'";?> style="margin-left: 5px;margin-right: 5px;" href="/experts/">Всі</a>
                    <?php }else{?>
             <a <?php if(empty($this->letter))  echo "class='letterSelected'";?> style="margin-left: 5px;margin-right: 5px;" href="/users/">Всі</a>
             <?php }?>
             <a <?php if($this->letter=="А") echo "class='letterSelected'";?> href="?letter=А">А</a>
             <a <?php if($this->letter=="Б") echo "class='letterSelected'";?> href="?letter=Б">Б</a>
             <a <?php if($this->letter=="В") echo "class='letterSelected'";?> href="?letter=В">В</a>
             <a <?php if($this->letter=="Г") echo "class='letterSelected'";?> href="?letter=Г">Г</a>
             <a <?php if($this->letter=="Д") echo "class='letterSelected'";?> href="?letter=Д">Д</a>
             <a <?php if($this->letter=="Е") echo "class='letterSelected'";?> href="?letter=Е">Е</a>
             <a <?php if($this->letter=="Ж") echo "class='letterSelected'";?> href="?letter=Ж">Ж</a>
             <a <?php if($this->letter=="З") echo "class='letterSelected'";?> href="?letter=З">З</a>
             <a <?php if($this->letter=="И") echo "class='letterSelected'";?> href="?letter=И">И</a>
             <a <?php if($this->letter=="Й") echo "class='letterSelected'";?> href="?letter=Й">Й</a>
             <a <?php if($this->letter=="К") echo "class='letterSelected'";?> href="?letter=К">К</a>
             <a <?php if($this->letter=="Л") echo "class='letterSelected'";?> href="?letter=Л">Л</a>
             <a <?php if($this->letter=="М") echo "class='letterSelected'";?> href="?letter=М">М</a>
             <a <?php if($this->letter=="Н") echo "class='letterSelected'";?> href="?letter=Н">Н</a>
             <a <?php if($this->letter=="О") echo "class='letterSelected'";?> href="?letter=О">О</a>
             <a <?php if($this->letter=="П") echo "class='letterSelected'";?> href="?letter=П">П</a>
             <a <?php if($this->letter=="Р") echo "class='letterSelected'";?> href="?letter=Р">Р</a>
             <a <?php if($this->letter=="С") echo "class='letterSelected'";?> href="?letter=С">С</a>
             <a <?php if($this->letter=="Т") echo "class='letterSelected'";?> href="?letter=Т">Т</a>
             <a <?php if($this->letter=="У") echo "class='letterSelected'";?> href="?letter=У">У</a>
             <a <?php if($this->letter=="Ф") echo "class='letterSelected'";?> href="?letter=Ф">Ф</a>
             <a <?php if($this->letter=="Х") echo "class='letterSelected'";?> href="?letter=Х">Х</a>
             <a <?php if($this->letter=="Ц") echo "class='letterSelected'";?> href="?letter=Ц">Ц</a>
             <a <?php if($this->letter=="Ч") echo "class='letterSelected'";?> href="?letter=Ч">Ч</a>
             <a <?php if($this->letter=="Ш") echo "class='letterSelected'";?> href="?letter=Ш">Ш</a>
             <a <?php if($this->letter=="Щ") echo "class='letterSelected'";?> href="?letter=Щ">Щ</a>
             <a <?php if($this->letter=="Э") echo "class='letterSelected'";?> href="?letter=Э">Э</a>
             <a <?php if($this->letter=="Ю") echo "class='letterSelected'";?> href="?letter=Ю">Ю</a>
             <a <?php if($this->letter=="Я") echo "class='letterSelected'";?> href="?letter=Я">Я</a>
                </span><span class="alafaBoxes">
             <a <?php if($this->letter=="A") echo "class='letterSelected'";?> href="?letter=A">A</a>
             <a <?php if($this->letter=="B") echo "class='letterSelected'";?> href="?letter=B">B</a>
             <a <?php if($this->letter=="C") echo "class='letterSelected'";?> href="?letter=C">C</a>
             <a <?php if($this->letter=="D") echo "class='letterSelected'";?> href="?letter=D">D</a>
             <a <?php if($this->letter=="E") echo "class='letterSelected'";?> href="?letter=E">E</a>
             <a <?php if($this->letter=="F") echo "class='letterSelected'";?> href="?letter=F">F</a>
             <a <?php if($this->letter=="G") echo "class='letterSelected'";?> href="?letter=G">G</a>
             <a <?php if($this->letter=="H") echo "class='letterSelected'";?> href="?letter=H">H</a>
             <a <?php if($this->letter=="I") echo "class='letterSelected'";?> href="?letter=I">I</a>
             <a <?php if($this->letter=="J") echo "class='letterSelected'";?> href="?letter=J">J</a>
             <a <?php if($this->letter=="K") echo "class='letterSelected'";?> href="?letter=K">K</a>
             <a <?php if($this->letter=="L") echo "class='letterSelected'";?> href="?letter=L">L</a>
             <a <?php if($this->letter=="M") echo "class='letterSelected'";?> href="?letter=M">M</a>
             <a <?php if($this->letter=="N") echo "class='letterSelected'";?> href="?letter=N">N</a>
             <a <?php if($this->letter=="O") echo "class='letterSelected'";?> href="?letter=O">O</a>
             <a <?php if($this->letter=="P") echo "class='letterSelected'";?> href="?letter=P">P</a>
             <a <?php if($this->letter=="Q") echo "class='letterSelected'";?> href="?letter=Q">Q</a>
             <a <?php if($this->letter=="R") echo "class='letterSelected'";?> href="?letter=R">R</a>
             <a <?php if($this->letter=="S") echo "class='letterSelected'";?> href="?letter=S">S</a>
             <a <?php if($this->letter=="T") echo "class='letterSelected'";?> href="?letter=T">T</a>
             <a <?php if($this->letter=="U") echo "class='letterSelected'";?> href="?letter=U">U</a>
             <a <?php if($this->letter=="V") echo "class='letterSelected'";?> href="?letter=V">V</a>
             <a <?php if($this->letter=="W") echo "class='letterSelected'";?> href="?letter=W">W</a>
             <a <?php if($this->letter=="X") echo "class='letterSelected'";?> href="?letter=X">X</a>
             <a <?php if($this->letter=="Y") echo "class='letterSelected'";?> href="?letter=Y">Y</a>
             <a <?php if($this->letter=="Z") echo "class='letterSelected'";?> href="?letter=Z">Z</a>
                </span>
         </div>
            
            <div id="catalogBody" style="padding-left: 0px;width: 671px;padding-right: 15px;border-left: 1px solid #AFAFAF;
    border-right: 1px solid #AFAFAF;border-bottom: 1px solid #AFAFAF;">
                <div class="letterUser letterUserie">
                    <span class="letterUser" style="margin-top: -31px;margin-left: -15px;"><?php  echo $this->letter?></span>
                </div>
                <div class="usersBox">
                    
                <?php $cheker=0;
                if($rows==0) echo "<h1 style='text-align: center;margin-top: 200px;margin-bottom: 200px;'>Вибачте. На літеру '".$this->letter."' немає жодного користувача!</h1> ";
                for($i=0;$i<$rows;$i++){
                $row=$this->db->db_FetchAssoc();
                 $flag=false;
                if(!empty($this->letter)){
                $familia=ucfirst($row['country']);
                
                 if(strlen($this->letter)==2){
                     if($familia[1]!=$this->letter[1]) $flag=true;
                 }else if($familia[0]!=$this->letter) $flag=true;
                }
                if(isset($row['country']) && !empty($row['country']) && $flag && !empty($this->letter)){
                    $cheker++;
                    if($cheker==$rows && $rows!=1) echo "<h1 style='text-align: center;margin-top: 200px;margin-bottom: 200px;'>Вибачте. На літеру '".$this->letter."' немає жодного користувача!</h1> ";
                    if($rows==1){
                        echo "<h1 style='text-align: center;margin-top: 200px;margin-bottom: 200px;'>Вибачте. На літеру '".$this->letter."' немає жодного користувача!</h1> ";
                        continue;
                    }
                    else
                    continue;
                }
                    ?>
                
                <div class="userBoxSingleItem">
                    <a class="" style="text-decoration: none;font-size: 12px;" href="<?php  echo $this->link($row['sys_user_id'])?>">
                        <?php if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$row['sys_user_id']."/".$row['discount'])){?>
                        <img class="avatarImage profileAvatar" src="<?php  echo $this->Catalog->ShowCurrentImageExSize("/images/mod_blog/".$row['sys_user_id']."/".$row['discount'], 70, 70, 'center', 'center', '85', NULL, NULL, NULL, true);?>" alt="" />
                        <?php }else{?>
                        <img class="avatarImage profileAvatar" width="70" height="70" src="/images/design/noAvatar.gif"/> 
                        <?php }?>
                    </a>
                    <div class="userProfileLink">
                        <?php if($row['name']=="" || $row['country']==""){ ?>
                          <a class="" style="text-decoration: none;font-size: 12px;" href="<?php  echo $this->link($row['sys_user_id'])?>"><?php  echo $row['login']?></a>
                          
                        <?php }else{?>
                          <a class="" style="text-decoration: none;font-size: 12px;" href="<?php  echo $this->link($row['sys_user_id'])?>"><?php  echo $row['name']."<br/>".$row['country']?></a>
                        <?php }
                        ?>
                          
                    </div>
                </div>
                    
                    <?php
                }?>
                </div>
                <div class="pageNaviClass" style="padding-top: 15px;height: 40px;"><?php $this->Form->WriteLinkPagesStatic( "/users/", $rows_all, $this->display, $this->start, $this->sort, $this->page);?></div>
            </div>
        </div>
        
         
         <?php
    }
    
    function link($userId,$IdOfrecord=NULL){
        if($IdOfrecord==NULL){
            return "/blog/".$userId."/";
        }else{
            return "/blog/".$userId."/entry/".$IdOfrecord."/";
        }
    }
    
    function showExpertsUsersInHeaderRandom(){
    
        $q="SELECT * FROM  `".TblModUser."`,`".TblSysUser."`,`".TblModUserBlog."`
                WHERE `".TblModUser."`.`sys_user_id`=`".TblSysUser."`.`id`
                    AND  `".TblModUser."`.`ShowInTop`='1'
                    AND `".TblModUser."`.`sys_user_id`=`".TblSysUser."`.`id`
                    AND `".TblModUserBlog."`.`id_user`=`".TblSysUser."`.`id`
                        GROUP BY `".TblModUser."`.`sys_user_id`"; 
        
        $res = $this->db->db_Query($q);
        $rowsUser=$this->db->db_GetNumRows();
        $genUserArr=array();
        if($rowsUser>0){
        for ($i = 0; $i < $rowsUser; $i++) {
            $rowUser=$this->db->db_FetchAssoc();
            $genUserArr[$i]=$rowUser;
        }
        $generatedRow=mt_rand(0,$rowsUser-1);
        if($rowsUser>0){
        $q="SELECT `".TblModUser."`.`name`,
                   `".TblModUserBlog."`.`id` AS `RecordId`,
                   `".TblModUser."`.`country`,
                   `".TblModUser."`.`expertImgHeader`,
                   `".TblModUser."`.`sys_user_id`,    
                   `".TblModUserBlog."`.`title`,
                   `".TblModUser."`.`expertTitle`    
            FROM  `".TblModUser."`,`".TblSysUser."`,`".TblModUserBlog."`
                WHERE `".TblModUser."`.`sys_user_id`=`".TblSysUser."`.`id`
                    AND  `".TblSysUser."`.`id`=".$genUserArr[$generatedRow]['sys_user_id']."
                    AND `".TblModUser."`.`sys_user_id`=`".TblSysUser."`.`id`
                    AND `".TblModUserBlog."`.`id_user`=`".TblSysUser."`.`id`
                        ORDER BY `".TblModUserBlog."`.`dttm` DESC
                   "; 
        
        $res = $this->db->db_Query($q);
        $rowArt=$this->db->db_FetchAssoc();
        $rows=$this->db->db_GetNumRows();
        
        //echo $generatedRow;
        if($rows>0){
            if(!isset($this->Crypt))
            $this->Crypt=new Crypt();
        ?>
        <div id="header_part2">
                    <div id="headerUserExpertFoto">
                        <?php if(isset ($rowArt['expertImgHeader']) && !empty($rowArt['expertImgHeader'])){
                            ?>
                        <a class="expertTopHeaderLinkImg" href="<?php  echo $this->link($rowArt['sys_user_id'])?>">
                            <img width="166" height="150" onmouseover="$(this).attr('src','<?php  echo "/images/mod_blog/".$rowArt['sys_user_id']."/".$rowArt['expertImgHeader']?>')" onmouseout="$(this).attr('src','<?php  echo $this->makeImageGrey($rowArt['expertImgHeader'], "/images/mod_blog/".$rowArt['sys_user_id']."/")?>')" src="<?php  echo $this->makeImageGrey($rowArt['expertImgHeader'], "/images/mod_blog/".$rowArt['sys_user_id']."/")?>"/>
                        </a>
                        <?php }?>
                    </div>
                      <div id="say">
                        <div id="say1">
                            <?php  echo $rowArt['name']." ".$rowArt['country'].":"?><br/>
                            <span style="font-size: 10px; color:#0f7cc4;height: 11px;display: block;width: 100%"><?php  echo $rowArt['expertTitle']?></span>
                            <span style="font-weight: normal;font-style: normal;margin-top: 4px;display: block;width:160px;height: 28px; font-size: 11px;">
                        <?php  echo $this->Crypt->TruncateStr(strip_tags(stripslashes($rowArt['title'])),90);?>
                            </span>
                            <a class="expertTopHeaderLink" href="<?php  echo $this->link($rowArt['sys_user_id'], $rowArt['RecordId'])?>">Обговорення</a>
                            </div>
                </div></div>
        
        <?php
        }
        }
        }
    }
    
    function deleteBlog(){
        $q="SELECT `id_user` FROM `".TblModUserBlog."` WHERE `id`=".$this->idOfDelete."";
        $res = $this->db->db_Query($q);
        $row=$this->db->db_FetchAssoc();
        $rows=$this->db->db_GetNumRows();
        $userFromTqable=$row['id_user'];
        if($this->userId==$this->Logon->user_id && $userFromTqable==$this->Logon->user_id && $rows>0){
         $q="DELETE FROM `".TblModUserBlog."`
            WHERE `id`=".$this->idOfDelete." 
            ";
         $res = $this->db->db_Query($q);
         $row=$this->db->db_FetchAssoc();
         $rows=$this->db->db_GetNumRows();
         if($res){
             ?><h1 style="color: green;text-align: center;margin-top: 50px;">Ваш запис успішно видалений</h1><?php
         }else{
             ?><h1 style="color: #ff0000;text-align: center;margin-top: 50px;">Під час видалення запису виникла помилка. Якщо помилка повториться зверніться до Адміністрації.</h1><?php
         }
        }
        ?><script type="text/javascript">
            timer=setTimeout(function(){
                location.href="<?php  echo $this->link($this->userId);?>";
            },5000);
                    </script><?php
    }


    function makeImageGrey($filenameFull,$path){
    //Получаем размеры изображения
        $linkPath=$path;
        $path=SITE_PATH.$path;
       $ext=$this->getExtension($filenameFull);
       $nameFullLen=strlen($filenameFull);
       $extLen=strlen($ext);
       $filename=substr($filenameFull,0,$nameFullLen-$extLen-1);
       $filenameFullPath=$path.$filenameFull;
       if(!is_file($path.$filename."_grey.".$ext)){
          $img_size = GetImageSize($filenameFullPath);
          $width = $img_size[0];
          $height = $img_size[1];
          //Создаем новое изображение с такмими же размерами
          $img = imageCreate($width,$height);
          //Задаем новому изображению палитру "оттенки серого" (grayscale)
          for ($c = 0; $c < 256; $c++) {
            ImageColorAllocate($img, $c,$c,$c);
          }
          //Содаем изображение из файла Jpeg
          $img2 = ImageCreateFromJpeg($filenameFullPath);
          //Объединяем два изображения
          ImageCopyMerge($img,$img2,0,0,0,0, $width, $height, 100);
          //Сохраняем полученное изображение
          imagejpeg($img, $path.$filename."_grey.".$ext);
         //Освобождаем память, занятую изображением
          imagedestroy($img);
           
       }
       return $linkPath.$filename."_grey.".$ext;
}
}
?>