<?php
// ================================================================================================
// System : CMS
// Module : userShow.class.php
// Date : 22.02.2011
// Licensed To: Yaroslav Gyryn 
// Purpose : Class definition For display interface of External users
// ================================================================================================

include_once( SITE_PATH.'/modules/mod_user/user.defines.php' );

/**
* Class User
* Class definition for all Pages - user actions
* @author Igor Trokhymchuk  <ihor@seotm.com>
* @version 1.1, 22.02.2011
* @property ShareLayout $Share
* @property FrontendPages $FrontendPages
* @property UserAuthorize $Logon
* @property UserShow $UserShow
* @property OrderLayout $Order
* @property FrontSpr $Spr
* @property FrontForm $Form 
* @property db $db     
* @property TblFrontMulti $multi
* @property CatalogLayout $Catalog
*/  
 class UserShow extends User {
       var $db=NULL;
       var $Msg=NULL;
       var $logon=NULL;
       var $Spr=NULL;
       var $Form = NULL;
       
       var $whattodo = NULL;
       var $referer_page = NULL;
       var $TextMessages = NULL;

       // ================================================================================================
       //    Function          : UserShow (Constructor)
       //    Date              : 22.02.2011
       //    Parms             : session_id / id of the ssesion
       //                          user_id    / User ID
       //    Returns           : Error Indicator
       //    Description       : Init variables
       // ================================================================================================
        function UserShow( $session_id=NULL, $user_id=NULL) {
                ( $session_id   !="" ? $this->session_id  = $session_id   : $this->session_id  = NULL );
                ( $user_id      !="" ? $this->user_id     = $user_id      : $this->user_id     = NULL );

                if (empty($this->db)) $this->db = DBs::getInstance();
                if (empty($this->Msg)) $this->Msg = &check_init('ShowMsg', 'ShowMsg');
                if (empty($this->Logon)) $this->Logon = &check_init('UserAuthorize', 'UserAuthorize');
                if (empty($this->Spr)) $this->Spr = &check_init('FrontSpr', 'FrontSpr');
                if (empty($this->Form)) $this->Form = &check_init('FrontForm', 'FrontForm');
                $this->multiUser = &check_init_txt('TblFrontMulti',TblFrontMulti); //$this->Msg->GetMultiTxtInArr(TblModUserSprTxt);
                if(empty($this->Catalog)) $this->Catalog = Singleton::getInstance('Catalog'); 
                
       } // End of UserShow Constructor

    
    // ================================================================================================
    // Function : LoginPage
    // Date : 22.02.2011
    // Returns : true,false / Void
    // Description : Show form for logon of the user on the front-end
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function LoginPage()
    {

        if( !$this->Logon->user_id ){

     if (empty($this->whattodo)) $this->whattodo=2;
     $this->Form->WriteFrontHeader( 'Login', _LINK.'login.html', NULL, NULL );
     //echo '<br>$this->referer_page='.$this->referer_page;
     if ( !isset($this->referer_page) OR empty($this->referer_page) ) {
        if ( isset($_SERVER['HTTP_REFERER']) ) {
            $this->referer_page = str_replace('&','AND',$_SERVER['REQUEST_URI']);
            $title = $this->multiUser['TXT_FRONT_PLEASE_LOGIN'];
        }
        else {
            $this->referer_page='/login.php?task=makelogon';
            $title = $this->multiUser['TXT_TITLE_LOGIN_PAGE'];
        }

     }
     else{
         $title = $this->multiUser['TXT_TITLE_LOGIN_PAGE'];
     }
     $this->Form->Hidden('referer_page', "/myaccount/");
     $this->Form->Hidden('whattodo', $this->whattodo);
     
                  
                  ?>
<div id="catalogBox">
            <span class="MainHeaderText">Авторизація</span>
            
            <div id="catalogBody">
                <?php if(!empty($this->Err) || !empty($this->TextMessages)){?>
                <div class="err" style="margin-top: 25px;">
                <?php
                $this->ShowErr();
                  $this->ShowTextMessages(); 
                ?>
               
                    </div>
                 <?php }?>
                  <table border="0" cellspacing="8" cellpadding="0" class="tblRegister" style="margin-top: 20px;margin-bottom: 20px;">
                   <tr align="right">
                        <td><span style="color:#515151;font-weight: bold;">Ім'я користувача</span><span class="redStar">* </span></td>
                        <td align="left"><?php  echo $this->Form->TextBox( 'login', $this->login);?></td>
                   </tr>
                   <tr align="right">
                        <td><span style="color:#515151;font-weight: bold;"><?php  echo $this->multiUser['FLD_PASSWORD']?></span><span class="redStar">* </span></td>
                        <td align="left"><?php  echo $this->Form->Password( 'pass', '', 20 );?></td>
                   </tr>
                   <tr>
                       <td></td>
                       <td><a href="<?php  echo _LINK;?>forgotpass.html" class="a02"><?php  echo $this->multiUser['TXT_FORGOT_PASS'];?></a> <a style="float: right;margin-right: 20px;" href="/registration/" title="Реєстрація">Реєстрація</a></td>
                   </tr>
                  </table> 
                  <div class="submit">
                    <?php $btnSubmit = $this->multiUser['BTN_SUBMIT']; ?>
                    <input class="btnCatalogImgUpload" style="margin-top: 0px;" type="submit" value="<?php  echo $btnSubmit;?>"/>
                  </div>
                  
                
            </div></div>
                  <?php
     
     
      
    $this->Form->WriteFrontFooter();
        }else{

            echo "<script type='text/javascript'>location.href='/'</script>";
        }
    } //end of function LoginPage()
           
   
    // ================================================================================================
    // Function : LoginPageOrder
    // Date : 22.02.2011
    // Returns : true,false / Void
    // Description : Show form for logon of the user on the front-end
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function LoginPageOrder( $referer_page )
    {
        if(!empty($referer_page)) $this->referer_page = $referer_page;
        if (empty($this->whattodo)) $this->whattodo=2;
    ?> 
      <h1><?php  echo $this->multi['TXT_AUTHORIZATION'];?></h1>
      <div class="body">
       <div class="orderFirstStepTxt">
        <?php  echo $this->multi['TXT_SECOND_STEP'];?>
       </div>  

       <div class="rightHeader">
        <div class="orderStep">
         <?php  echo $this->multi['TXT_STEP_2'];?>
        </div>
        <div class="orderStepImage">
         <img src="/images/design/step2.gif">
        </div>
       </div>

          <?php
         $this->Form->WriteFrontHeader( 'Login', _LINK.'login.html', NULL, NULL );
         //echo '<br>$this->referer_page='.$this->referer_page;
         if ( !isset($this->referer_page) OR empty($this->referer_page) ) {
            if ( isset($_SERVER['HTTP_REFERER']) ) 
                $this->referer_page = str_replace('&','AND',$_SERVER['REQUEST_URI']);
            else 
                $this->referer_page='/login.php?task=makelogon';
         }
         //echo '<br>$this->referer_page='.$this->referer_page;
         $this->Form->Hidden('referer_page', $this->referer_page);
         $this->Form->Hidden('whattodo', $this->whattodo);      
               
           if( !$this->Logon->user_id ){
               echo $this->ShowErr();
               echo $this->ShowTextMessages(); 

           ?>
          <div class="orderHelpText">
           <?php  echo $this->multi['TXT_HELP_NEW_USER'];?>
          </div>
          
          <div class="registerLinks">
              <a href="<?php  echo _LINK;?>registration/" class="registerLink"><?php  echo $this->multiUser['IMG_FRONT_SIGN_UP'];?></a>
          </div>
          
          <div class="orderHelpText">
           <?php  echo $this->multiUser['TXT_FRONT_RETURNING_USER_DESCRIPTION'];?>
          </div>
                             
           <div id="content2Box">
               <div class="subBody" align="left" style="padding-top:15px;">
                  <table border="0" cellspacing="2" cellpadding="0" class="regTable" width="100%">

                   <tr>
                     <td width="200">
                        <?php  echo $this->multiUser['FLD_LOGIN'];?>
                        &nbsp;
                        <?php  echo $this->Form->TextBox( 'login', $this->login, 'size="10"' );?>
                     </td>
                     <td width="170">
                        <?php  echo $this->multiUser['FLD_PASSWORD'];?>
                        <?php  echo $this->Form->Password( 'pass', '', 10 );?>
                     </td>
                     <td>
                        <?php $btnSubmit = $this->multiUser['BTN_SUBMIT']; ?>
                        <input type="image" src="/images/design/submit.png" alt="<?php  echo $btnSubmit;?>" title="<?php  echo $btnSubmit;?>"/>
                     </td>
                   </tr>
                  </table>
                  <div style="float:right; margin: 0px 20px 10px 0px;"><a href="<?php  echo _LINK;?>forgotpass.html" class="registerLink"><?php  echo $this->multiUser['TXT_FORGOT_PASS'];?></a></div>
                  
                </div>
           </div>
                <?php
           }
          /* else{
                $title = 'Зайти в мой профайл';
           ?>
                <div class="categoryTxt"><?php  echo $title;?></div>
           </div>
           <div id="content2Box">
               <div class="subBody">
                    Для Вашего компьютера уже создана сессия с логином <?php  echo $this->Logon->login;?>. Вы можете <a href="<?php  echo _LINK;?>myaccount/" title="перейти в профайл">перейти в свой профайл</a> или <a href="<?php  echo _LINK;?>logout.html" title="завершить сеанс">завершить сеанс</a>.
                </div>
           </div>
           <?php
              }*/
           ?>
     
       <div class="orderHelpInfo" align="left">
          <?php  echo $this->multi['TXT_HELP_INFO'];?>:
          <div class="orderHelpText">
             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php  echo $this->multi['TXT_HELP_FORGET_PSW'];?>
          </div>  
          
          <div class="orderHelpText">
             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php  echo $this->multi['TXT_HELP_SECURITY'];?>
          </div>
       </div>
       <?php $this->Form->WriteFrontFooter(); ?>
      </div>   
    <?php
    } //end of function LoginPageOrder()


    // ================================================================================================
    // Function : ShowRegForm
    // Date : 22.02.2011
    // Parms : $new_stat_id - id of the new created records of user stat.
    // Returns : true,false / Void
    // Description : Show the second step of regidstration. This is the personal and contact information.
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function checkAjaxFields(){
        if(empty ($this->val)){ echo 3;return false;}
        switch ($this->wichField) {
            case "login":
                
                $q="SELECT `login` FROM sys_user WHERE `login`='".$this->val."'";
                $this->db->db_Query($q);
                if($this->db->db_GetNumRows()>0) echo 1; else echo 0;
                break;
            case "email":
                $q="SELECT `email` FROM mod_user WHERE `email`='".$this->val."'";
                $this->db->db_Query($q);
                if($this->db->db_GetNumRows()>0) echo 1; else echo 0;
                break;

            default:
                break;
        }
    }
    
    
    
    function ShowRegForm()
    {
       ?>
        <div id="catalogBox">
            <span class="MainHeaderText">Реєстрація</span>
            <div id="CatformAjaxLoader"></div>
            <div id="catalogBody">
        
        <div class="registerBoxDiv">
            
         <div align="center"><?php $this->ShowErr();?></div>
 <?php
         $this->Form->WriteFrontHeader(NULL, "#", 'save_reg_data');
         //$this->Form->Hidden( 'save_reg_data', 'save_reg_data' );
         $this->Form->Hidden( 'subscr', $this->subscr );
         $this->Form->Hidden( 'referer_page', $this->referer_page );
         ?>
         <input type="hidden" id="UserImageFilePath" value="" name="userImage"/>
                <ul class="CatFormUl">
                  <li>Нікнейм:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="login" onkeyup="chekFildsTimer('login',this.value,'resultofChek1')" onchange="chekFildsTimer('login',this.value,'resultofChek1')" onblur="chekFildsTimer('login',this.value,'resultofChek1')"  value="<?php  echo $this->login?>"/>
                      <div id="resultofChek1"></div>
                  </li>
                  <li>Пароль:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="password" class="CatinputFromForm" onblur="passChek();" name="password" value=""/>
                  </li>
                  <li>Повторіть пароль:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="password" onblur="passChek();" class="CatinputFromForm" name="password2" value=""/>
                      <div id="passchek" class="redStar"></div>
                  </li>
                  <li>Прізвище:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="country" value="<?php  echo $this->country?>"/>
                  </li>
                  <li>Ім'я:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="name" value="<?php  echo $this->name?>"/>
                  </li>
                  <li>
                      Стать:<br/>
                      <select class="CatSelectFromForm" name="state">
                          <option <?php if($this->state=="m" || $this->state==NULL) echo "selected";?> selected  value="m">Чоловіча</option>
                          <option <?php if($this->state=="w") echo "selected";?> value="w">Жіноча</option>
                      </select>
                  </li>
                  <li>
                      День:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,1)"  type="text" class="dataInput" name="day" value="<?php  echo $this->day?>"/>
                      Місяць:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,2)" type="text" class="dataInput" name="month" value="<?php  echo $this->month?>"/>
                      Рік:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,3)" type="text" class="dataInput" style="width: 30px;" name="year" value="<?php  echo $this->year?>"/>
                       <div id="dateChek" class="redStar"></div>
                  </li>
                  <li>
                      Email:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="email" value="<?php  echo $this->email?>" onkeyup="chekFildsTimer('email',this.value,'resultofChek2')" onchange="chekFildsTimer('email',this.value,'resultofChek2')" onblur="chekFildsTimer('email',this.value,'resultofChek2')" />
                      <div id="resultofChek2"></div>
                  </li>
                  <li>Сайт:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="www" value="<?php  echo $this->www?>"/>
                  </li>
                  <li>Телефон:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone" value="<?php  echo $this->phone?>"/>
                  </li>
                  <li>Facebook:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone_mob" value="<?php  echo $this->phone_mob?>"/>
                  </li>
                  <li>Вконтакті:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="fax" value="<?php  echo $this->fax?>"/>
                  </li>
                  <li>Twitter:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="bonuses" value="<?php  echo $this->bonuses?>"/>
                  </li>
                  
                 </ul>
         <input type="hidden" name="user_status" value="3"/>
         <span style="font-weight: bold;"> Коротко про себе:<br/></span>
                       <textarea name="aboutMe" class="aboutMeText tinyProfile"><?php  echo $this->aboutMe?></textarea>
         <?php $this->Form->WriteFrontFooter(); ?>
                 <ul class="CatFormUl" id="imgLoaderConteiner">
                            <li id="imgLoaderConteiner" style="height: auto;">
                                <div id="catImgAjaxLoader"></div>
                        <div id="CatImageUploadBox">
                            <form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe">
                                         <input type="hidden" value="addImage" name="task"/>
                                         <input type="hidden" value="true" name="ajax"/>
                                      Виберіть зображення для аватари:<br/>
                                      <input id="catUserFileUploader" type="file" name="image" size="80"/>
                                      <input class="btnCatalogImgUpload" type="button" onclick="loadImage();" value="Завантажити"/>
                            </form>
                        </div>
                            <iframe id="hiddenframe" name="hiddenframe" style="width:0px; height:0px; border:0px"></iframe>
                            </li>
                            <li>
                              
                            </li>
                        </ul>
         <br/><input type="button" style="float: right" class="btnCatalogImgUpload" onclick="verify()" name="save_reg_data" class="submitBtn<?php  echo _LANG_ID?>" value="Реєстрація" />
        </div>
         <div class="needFields">
          <span class="redStar"> *</span> - поля з зірочкою обов'язкові для заповнення при реєстрації на порталі.
          <br/><br/>
         </div>

        </div>
        </div>
                
       <?php
       
    } //end of function ShowRegForm()
    
   function showRegJS(){
       ?>
       <script type="text/javascript" src="/include/js/tinymce/tiny_mce.js"></script>
        <script language="JavaScript"> 
            
            
            
            var tinyMCE;
                    function tinyMceInit(){
                        tinyMCE.init({
                                // General options
                                mode : "textareas",
                theme : "advanced",
                theme_advanced_buttons1 : "mymenubutton,bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink",
                theme_advanced_buttons2 : "",
                theme_advanced_buttons3 : "",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                               skin : "o2k7",
                               skin_variant : "silver",
                                //Path
                                relative_urls : false,
                                remove_script_host : true,

                                //extended_valid_elements : "tcut",

                                language : "ru"
                        });
                    }
                    tinyMceInit();
            
            var unikLogin=false;
            var unikEmail=false;
            var chekTimer;
            var wichFiledG,valG,resultBoxG;
            
            function chekFildsTimer(wichFiled,val,resultBox){
                window.clearInterval(chekTimer);
                wichFiledG=wichFiled;
                valG=val;
                resultBoxG=resultBox;
                chekTimer=window.setTimeout("chekFields();", 1000);
            }
            function chekFields(){
                wichFiled=wichFiledG;
                val=valG;
                resultBox=resultBoxG;
                $.ajax({
                   type: "GET",
                   url: "<?php  echo _LINK;?>checkReg?wichField="+wichFiled+"&val="+val,
                   beforeSend : function(){ 
                       $("#"+resultBox).html("");
                       $("#"+resultBox).css("background","url('/images/design/reg/ajax-loader.gif')no-repeat");
                       
                    },
                   success: function(html){
                       result=parseInt(html);
                       $("#"+resultBox).css("background","none");
                       if(result==1){
                           if(wichFiled=="login") $("#"+resultBox).html("<span class='redStar'>Такий нікнейм вже існує!</span>");
                           else $("#"+resultBox).html("<span class='redStar'>Такий E-mail вже зареэстрований!</span>");
                       } 
                       if(result==3){
                            $("#"+resultBox).html("<span class='redStar'>Це поле потрібно заповнити!</span>");
                       } 
                       if(result==0){ 
                           $("#"+resultBox).html("");
                           if(wichFiled=="login") unikLogin=true;
                           if(wichFiled=="email") unikEmail=true;
                       }
                   }
                });
            }
            function emailCheck (emailStr) {
                if (emailStr=="") return true;
                var emailPat=/^(.+)@(.+)$/;
                var matchArray=emailStr.match(emailPat);
                if (matchArray==null) 
                {
                    return false;
                }
                return true;
            }
            function passChek(){
                if (document.forms.form_mod_user.password.value!=document.forms.form_mod_user.password2.value) {
                    $("#passchek").html("Введені паролі не співпадають!");
                }else $("#passchek").html("");
            }
            function check(input,elem,wich) {     //метод, проверяющий значение поля input
               var resultint="";   //здесь сохранит итоговый результат
               var accept = "1234567890";   //допустимые символы, в данном случае числа

               for (var i = 0; i < input.length; i++) {   //проходим циклом по введенному в поле значению

               var symbol=""; //текущий символ
                  for (var j = 0; j < accept.length; j++){   //вложенный цикл, проверяем каждый символ поля на допустимость
                     if(input.charAt(i)==accept.charAt(j)) {    //если символ разрешен
                        symbol=input.charAt(i);
                        resultint+=symbol;   //добавляем его к resultint, таким образом, формируя его
                     }
                  }
               }
               if(wich==1) if(resultint>31) resultint="";
               if(wich==2) if(resultint>12) resultint="";
               if(wich==3) if(resultint<1900 || resultint>2020) resultint="";
               if(resultint=="") $("#dateChek").html("Введіть корректну дату народження!"); else $("#dateChek").html("")
               elem.value=resultint;
            }
            function verify() {
                var themessage = "<div style='text-align: left;'>Перевірте правильність заповнення полів реестрації.<br/> Зверніть увагу на слідуючі помилки:<br/><br/><span style='color:red;'>";
                if (document.forms.form_mod_user.login.value=="") {
                    themessage = themessage + " - Ви не ввели обов'язкове поле логіну!<br/>";
                }
                if ((!emailCheck(document.forms.form_mod_user.email.value))||(document.forms.form_mod_user.email.value=='')) {
                    themessage = themessage + " - Введіть будь ласка ваш E-mail!<br/>";
                }
                if (document.forms.form_mod_user.day.value=="" || document.forms.form_mod_user.month.value=="" || document.forms.form_mod_user.year.value=="") {
                    themessage = themessage + " - Ви не ввели свою дату народження!<br/>";
                }
                
                if (document.forms.form_mod_user.password.value!=document.forms.form_mod_user.password2.value || document.forms.form_mod_user.password.value=="") {
                    themessage = themessage + " - Введені паролі не співпадають або пусті!<br/>";
                }
                
                if(!unikLogin){
                    themessage = themessage + " - Ви ввели не унікальний нікнейм. Такий нікнейм вже існує!<br/>";
                }
                if(!unikEmail){
                    themessage = themessage + " - Ви ввели не унікальний E-mail. Такий E-mail вже існує!<br/>";
                }
                if (themessage == "<div style='text-align: left;'>Перевірте правильність заповнення полів реестрації.<br/> Зверніть увагу на слідуючі помилки:<br/><br/><span style='color:red;'>")
                {
                    $("#aboutMe").val(tinyMCE.get('aboutMe').getContent());
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
                   data: $("#form_mod_user").serialize() ,
                   url: "<?php  echo _LINK;?>registration/result.html",
                   beforeSend : function(){ 
                       $("#CatformAjaxLoader").width($("#catalogBody").width()+64).height($("#catalogBody").height()+20).fadeTo("fast", 0.4);
                        //$(Did).show("fast");
                        if (tinyMCE) {
                              for (n in tinyMCE.instances) {
                                inst = tinyMCE.instances[n];
                                if (tinyMCE.isInstance(inst)) {
                                  tinyMCE.execCommand('mceRemoveControl', false, inst.editorId);
                                }
                                }
                        }
                    },
                   success: function(html){
                       $("#CatformAjaxLoader").fadeOut("fast",function(){
                           $("#contentBox").html(html);
                           tinyMceInit();
                       });
                   }
              });
         }
         function loadImage(){
                if($('#catUserFileUploader').val()!=""){
                    loader=$("#imgLoaderConteiner");
                    $("#catImgAjaxLoader").width(loader.width()+10).height(loader.height()+30).fadeTo("fast", 0.4);
                    $('#catLoadImageForm').submit();
                }else $.fancybox('Виберіть зображення для завантаження');
            }
            function del(){
                $("#catImgAjaxLoader").fadeOut("fast", function(){
                    $('#CatImageUploadBox').html('<form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe">                               <input type="hidden" value="addImage" name="task"/><input type="hidden" value="true" name="ajax"/>                              Виберіть зображення:<br/>                              <input id="catUserFileUploader" type="file" name="image" size="80"/>                              <input class="btnCatalogImgUpload" type="button" onclick="loadImage();" value="Завантажити"/>                    </form>');
                });
            }
            function response(err,filePath,file){
              $("#catImgAjaxLoader").fadeOut("fast", function(){
                if(err==''){
                    $("#UserImageFilePath").val(file);
                    $('#CatImageUploadBox').html('<img class="avatarImage" width="120" src="'+filePath+'"/><form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe"><input type="hidden" value="deleteImage" name="task"/><input type="hidden" value="true" name="ajax"/><input type="hidden" value="'+filePath+'" name="fileDel"/><input type="button" class="btnCatalogFormDel" onclick="loadImage();" value="Видалити"/></form>');
                }else{
                    $.fancybox(err);
                }
              });
            }
        </script> 
      <?php
   }
    // ================================================================================================
    // Function : ShowRegFinish
    // Date : 22.02.2001
    // Returns : true,false / Void
    // Description : Show finish of registraion
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowRegFinish($res=NULL)
    {
       ?><div><?php
       if($res) $this->ShowTextMessages($this->Msg->show_text('MSG_PROFILE_SENT_OK'));
       else $this->ShowTextMessages($this->Msg->show_text('MSG_PROFILE_NOT_SENT'));
       ?></div><?php
    } //end of function ShowRegFinish()
    

    // ================================================================================================
    // Function : CheckFields()
    // Date : 22.02.2011
    // Parms :        $id - id of the record in the table
    // Returns :      true,false / Void
    // Description :  Checking all fields for filling and validation
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function CheckFields($id = NULL)
    {
        $this->Err=NULL;
        //echo '$this->email ='.$this->email;
       $q="SELECT `login` FROM sys_user WHERE `login`='".$this->login."'";
                $this->db->db_Query($q);
                if($this->db->db_GetNumRows()>0) $this->Err.="Користувач з таким нікнеймом вже існує<br/>";
                $q="SELECT `email` FROM mod_user WHERE `email`='".$this->email."'";
                $this->db->db_Query($q);
                if($this->db->db_GetNumRows()>0) $this->Err.="Користувач з такою електронною поштою вже зареестрованый<br/>";
        //echo '<br>$this->Err='.$this->Err.' $this->Msg->table='.$this->Msg->table;
        return $this->Err;
    } //end of function CheckFields()       

    
    // ================================================================================================
    // Function : EditProfile
    // Date : 22.02.2001
    // Returns : true,false / Void
    // Description : Show the form for editig data of profile of the user on front-end.
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
   function Show_JS(){
       ?>
        <script type="text/javascript" src="/include/js/tinymce/tiny_mce.js"></script>
        <script language="JavaScript"> 
            var unikEmail=false;
            var tinyMCE;
                    function tinyMceInit(){
                        tinyMCE.init({
                                // General options
                                mode : "textareas",
                theme : "advanced",
                theme_advanced_buttons1 : "mymenubutton,bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink",
                theme_advanced_buttons2 : "",
                theme_advanced_buttons3 : "",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                               skin : "o2k7",
                               skin_variant : "silver",
                                //Path
                                relative_urls : false,
                                remove_script_host : true,

                                //extended_valid_elements : "tcut",

                                language : "ru"
                        });
                    }
                    tinyMceInit();
                
            function chekFields(wichFiled,val,resultBox){
                $.ajax({
                   type: "GET",
                   url: "<?php  echo _LINK;?>checkReg?wichField="+wichFiled+"&val="+val,
                   beforeSend : function(){ 
                       $("#"+resultBox).html("");
                       $("#"+resultBox).css("background","url('/images/design/reg/ajax-loader.gif')no-repeat");
                    },
                   success: function(html){
                       result=parseInt(html);
                       $("#"+resultBox).css("background","none");
                       if(result==1){
                           if(wichFiled=="login") $("#"+resultBox).html("<span class='redStar'>Такий нікнейм вже існує!</span>");
                           else $("#"+resultBox).html("<span class='redStar'>Такий E-mail вже зареэстрований!</span>");
                       } 
                       if(result==3){
                            $("#"+resultBox).html("<span class='redStar'>Це поле потрібно заповнити!</span>");
                       } 
                       if(result==0){ 
                           $("#"+resultBox).html("");
                           if(wichFiled=="login") unikLogin=true;
                           if(wichFiled=="email") unikEmail=true;
                       }
                   }
                });
            }
            function emailCheck (emailStr) {
                if (emailStr=="") return true;
                var emailPat=/^(.+)@(.+)$/;
                var matchArray=emailStr.match(emailPat);
                if (matchArray==null) 
                {
                    return false;
                }
                return true;
            }
            function verify() {
                var themessage = "<div style='text-align: left;'>Перевірте правильність заповнення полів реестрації.<br/> Зверніть увагу на слідуючі помилки:<br/><br/><span style='color:red;'>";
                
//                if ((!emailCheck(document.forms.profile.email.value))||(document.forms.profile.email.value=='')) {
//                    themessage = themessage + " - Введіть будь ласка ваш E-mail!<br/>";
//                }
                if (document.forms.profile.day.value=="" || document.forms.profile.month.value=="" || document.forms.profile.year.value=="") {
                    themessage = themessage + " - Ви не ввели свою дату народження!<br/>";
                }
                
//                if(!unikEmail){
//                    themessage = themessage + " - Ви ввели не унікальний E-mail. Такий E-mail вже існує!<br/>";
//                }
                if (themessage == "<div style='text-align: left;'>Перевірте правильність заповнення полів реестрації.<br/> Зверніть увагу на слідуючі помилки:<br/><br/><span style='color:red;'>")
                {
                    $("#aboutMe").val(tinyMCE.get('aboutMe').getContent());
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
                   data: $("#profile").serialize() ,
                   url: "<?php  echo _LINK;?>myaccount/update/",
                   beforeSend : function(){ 
                       $("#CatformAjaxLoader").width($("#catalogBody").width()).height($("#catalogBody").height()+20).fadeTo("fast", 0.4);
                        //$(Did).show("fast");
                        if (tinyMCE) {
                              for (n in tinyMCE.instances) {
                                inst = tinyMCE.instances[n];
                                if (tinyMCE.isInstance(inst)) {
                                  tinyMCE.execCommand('mceRemoveControl', false, inst.editorId);
                                }
                                }
                        }
                    },
                   success: function(html){
                       $("#CatformAjaxLoader").fadeOut("fast",function(){
                           $("#catalogBox").html($("#catalogBox",html).html());
                            tinyMceInit();
                       });
                   }
              });
         }
         function check(input,elem,wich) {     //метод, проверяющий значение поля input
               var resultint="";   //здесь сохранит итоговый результат
               var accept = "1234567890";   //допустимые символы, в данном случае числа

               for (var i = 0; i < input.length; i++) {   //проходим циклом по введенному в поле значению

               var symbol=""; //текущий символ
                  for (var j = 0; j < accept.length; j++){   //вложенный цикл, проверяем каждый символ поля на допустимость
                     if(input.charAt(i)==accept.charAt(j)) {    //если символ разрешен
                        symbol=input.charAt(i);
                        resultint+=symbol;   //добавляем его к resultint, таким образом, формируя его
                     }
                  }
               }
               if(wich==1) if(resultint>31) resultint="";
               if(wich==2) if(resultint>12) resultint="";
               if(wich==3) if(resultint<1900 || resultint>2020) resultint="";
               if(resultint=="") $("#dateChek").html("Введіть корректну дату народження!"); else $("#dateChek").html("")
               elem.value=resultint;
            }
         function loadImage(){
                if($('#catUserFileUploader').val()!=""){
                    loader=$("#imgLoaderConteiner");
                    $("#catImgAjaxLoader").width(loader.width()+10).height(loader.height()).fadeTo("fast", 0.4);
                    $('#catLoadImageForm').submit();
                }else $.fancybox('Виберіть зображення для завантаження');
            }
            function del(){
                $("#catImgAjaxLoader").fadeOut("fast", function(){
                    $('#CatImageUploadBox').html('<form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe">                               <input type="hidden" value="addImage" name="task"/><input type="hidden" value="true" name="ajax"/>                              Виберіть зображення:<br/>                              <input id="catUserFileUploader" type="file" name="image" size="15"/>                              <input class="btnCatalogImgUpload" type="button" onclick="loadImage();" value="Завантажити"/>                    </form>');
                });
            }
            function response(err,filePath,file){
              $("#catImgAjaxLoader").fadeOut("fast", function(){
                if(err==''){
                    $("#UserImageFilePath").val(file);
                    $('#CatImageUploadBox').html('Аватар:<br/><img class="avatarImage" style="border:white solid 3px;" width="120" height="120" src="'+filePath+'"/><form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe"><input type="hidden" value="deleteImage" name="task"/><input type="hidden" value="true" name="ajax"/><input type="hidden" value="'+filePath+'" name="fileDel"/><input type="button" class="btnCatalogFormDel" onclick="loadImage();" value="Видалити"/></form>');
                }else{
                    $.fancybox(err);
                }
              });
            }
        </script>
       <?php
   }
   
   function EditProfileOld()
    {
     
     $SysGroup = new SysUser();
     $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->Logon->user_id." AND `".TblSysUser."`.id=".$this->Logon->user_id."";
     $res = $this->db->db_Query($q);
     if ( !$res OR !$this->db->result ) return false;
     $mas = $this->db->db_FetchAssoc();

     ?>
        <div>
       <div id="catalogBox">
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
                    <a class="blogBtnUserProfile" href="/myaccount/blog/">Блог</a>
                    <a class="editProfile selectedPunktClass" href="/myaccount/">Редагувати Профіль</a>
                    <a class="commentsProfile" href="/myaccount/comments/">Коментарі</a>
                </div>
                <div id="rightProfileMenuPart"></div>
            </div>


            <div id="catalogBody" style="background: #fafafa">
                <div id="CatformAjaxLoader"></div>
        <div class="registerBoxDiv">
            <?php
            $this->Form->WriteFrontHeader('profile', '#', 'update');
     $this->Form->Hidden( 'user_status', $mas['user_status'] );
     $this->Form->Hidden( 'email', $mas['email'] );
     ?>
           <input type="hidden" id="UserImageFilePath" value="" name="userImage"/>
                <ul class="CatFormUl">
                  <li>Прізвище:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="country" value="<?php  echo $mas['country']?>"/>
                  </li>
                  <li>Ім'я:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="name" value="<?php  echo $mas['name']?>"/>
                  </li>
                  <li>
                      Стать:<br/>
                      <select class="CatSelectFromForm" name="state">
                          <option <?php if($mas['state']=='m') echo "selected";?>  value="m">Чоловіча</option>
                          <option <?php if($mas['state']=='w') echo "selected";?> value="w">Жіноча</option>
                      </select>
                  </li>
                  <li>
                      День:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,1)"  type="text" class="dataInput" name="day" value="<?php  echo $mas['city'][5].$mas['city'][6]?>"/>
                      Місяць:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,2)" type="text" class="dataInput" name="month" value="<?php  echo $mas['city'][8].$mas['city'][9]?>"/>
                      Рік:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,3)" type="text" class="dataInput" style="width: 30px;" name="year" value="<?php  echo $mas['city'][0].$mas['city'][1].$mas['city'][2].$mas['city'][3]?>"/>
                       <div id="dateChek" class="redStar"></div>
                  </li>
                  <li>Сайт:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="www" value="<?php  echo $mas['www']?>"/>
                  </li>
                  <li>Телефон:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone" value="<?php  echo $mas['phone']?>"/>
                  </li>
                  <li>Facebook:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone_mob" value="<?php  echo $mas['phone_mob']?>"/>
                  </li>
                  <li>Вконтакті:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="fax" value="<?php  echo $mas['fax']?>"/>
                  </li>
                  <li>Twitter:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="bonuses" value="<?php  echo $mas['bonuses']?>"/>
                  </li>
                </ul>

           <span style="font-weight: bold;"> Коротко про себе:<br/></span>
                       <textarea id="aboutMe" name="aboutMe" class="aboutMeText tinyProfile"><?php  echo $mas['aboutMe']?></textarea>
         <?php $this->Form->WriteFrontFooter(); ?>

         <br/><input type="button" style="float: right" class="btnCatalogImgUpload" onclick="verify()" name="save_reg_data" class="submitBtn<?php  echo _LANG_ID?>" value="Зберегти" />
        </div>
                 <div style="float: left;display: block;margin-left: 35px;">
                 <ul class="CatFormUl" id="imgLoaderConteiner">
                            <li id="imgLoaderConteiner" style="height: auto;">
                                <div id="catImgAjaxLoader"></div>
                        <div id="CatImageUploadBox">
                           <?php  if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->Logon->user_id."/".$mas['discount'])){?>
                            Аватар:<br/><img class="avatarImage" width="120" height="120" src="<?php  echo "/images/mod_blog/".$this->Logon->user_id."/".$mas['discount']?>"/><form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe"><input type="hidden" value="deleteImage" name="task"/><input type="hidden" value="true" name="ajax"/><input type="hidden" value="/images/mod_blog/<?php  echo $this->user_id."/".$mas['discount']?>" name="fileDel"/><input type="button" class="btnCatalogFormDel" onclick="loadImage();" value="Видалити"/></form>
                           <?php }else{?>
                            <form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe">
                                         <input type="hidden" value="addImage" name="task"/>
                                         <input type="hidden" value="true" name="ajax"/>
                                      Виберіть зображення для аватари:<br/>
                                      <input id="catUserFileUploader" type="file" name="image" size="15"/>
                                      <input class="btnCatalogImgUpload" type="button" onclick="loadImage();" value="Завантажити"/>
                            </form>
                           <?php }?>
                        </div>
                            <iframe id="hiddenframe" name="hiddenframe" style="width:0px; height:0px; border:0px"></iframe>
                            </li>
                            <li>

                            </li>
                        </ul>
           </div>
        </div>
        </div>
                  </div>
       <?php
    } //end of function EditProfile()

    
    
    // ================================================================================================
    // Function : ShowCommentsBlock
    // Date : 22.02.2001
    // Returns : true,false / Void
    // Description : Show the form for editig data of profile of the user on front-end.
    // Programmer : Yaroslav Gyryn
    // ================================================================================================
    function ShowCommentsBlock()   
    {
     $SysGroup = new SysUser();
    // echo 'this->login = '.$this->login ;
     //echo '<br/>this->Logon->login = '.$this->Logon->login ;
     $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->Logon->user_id." AND `".TblSysUser."`.id=".$this->Logon->user_id."";
     $res = $this->db->db_Query($q);
     //echo '<br>'.$q.' $res='.$res.' $this->db->result='.$this->db->result;
     if ( !$res OR !$this->db->result ) return false;
     $mas = $this->db->db_FetchAssoc();

     ?>
        <div>
       <div id="catalogBox">
            <span class="MainHeaderText">Редагування профілю</span>
            
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
                    <a class="blogBtnUserProfile" href="/myaccount/blog/">Блог</a>
                    <a class="editProfile" href="/myaccount/">Редагувати Профіль</a>
                    <a class="commentsProfile selectedPunktClass" href="#">Коментарі</a>
                </div>
                <div id="rightProfileMenuPart"></div>
            </div>
           
            <div id="catalogBody" style="background: #fafafa">
            <?php
           if(!isset($this->Comments))
                $this->Comments = new FrontComments();
           $this->Comments->GetUserCommentsTree(10,$this->Logon->user_id);
           ?>
                <?php /*<div id="CatformAjaxLoader"></div>
                <div class="registerBoxDiv">
            <?php
            $this->Form->WriteFrontHeader('profile', '#', 'update');
     //$this->Form->Hidden( 'user_id', $mas['sys_user_id'] );
     $this->Form->Hidden( 'user_status', $mas['user_status'] );
     $this->Form->Hidden( 'email', $mas['email'] );
     ?>
           <input type="hidden" id="UserImageFilePath" value="" name="userImage"/>
                <ul class="CatFormUl">
                  <li>Прізвище:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="country" value="<?php  echo $mas['country']?>"/>
                  </li>
                  <li>Ім'я:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="name" value="<?php  echo $mas['name']?>"/>
                  </li>
                  <li>
                      Стать:<br/>
                      <select class="CatSelectFromForm" name="state">
                          <option <?php if($mas['state']=='m') echo "selected";?>  value="m">Чоловіча</option>
                          <option <?php if($mas['state']=='w') echo "selected";?> value="w">Жіноча</option>
                      </select>
                  </li>
                  <li>
                      День:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,1)"  type="text" class="dataInput" name="day" value="<?php  echo $mas['city'][5].$mas['city'][6]?>"/>
                      Місяць:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,2)" type="text" class="dataInput" name="month" value="<?php  echo $mas['city'][8].$mas['city'][9]?>"/>
                      Рік:<span class="redStar"> *</span><input id="nameOfPred" onchange="check(this.value,this,3)" type="text" class="dataInput" style="width: 30px;" name="year" value="<?php  echo $mas['city'][0].$mas['city'][1].$mas['city'][2].$mas['city'][3]?>"/>
                       <div id="dateChek" class="redStar"></div>
                  </li>
<!--                  <li>
                      Email:<span class="redStar"> *</span><br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="email" value="<?php  echo $mas['email']?>" onchange="chekFields('email',this.value,'resultofChek2')" onblur="chekFields('email',this.value,'resultofChek2')"/>
                      <div id="resultofChek2"></div>
                  </li>-->
                  <li>Сайт:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="www" value="<?php  echo $mas['www']?>"/>
                  </li>
                  <li>Телефон:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone" value="<?php  echo $mas['phone']?>"/>
                  </li>
                  <li>Facebook:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="phone_mob" value="<?php  echo $mas['phone_mob']?>"/>
                  </li>
                  <li>Вконтакті:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="fax" value="<?php  echo $mas['fax']?>"/>
                  </li>
                  <li>Twitter:<br/>
                      <input id="nameOfPred" type="text" class="CatinputFromForm" name="bonuses" value="<?php  echo $mas['bonuses']?>"/>
                  </li>
                </ul>
            <input type="hidden" name="user_status" value="3"/>
           <span style="font-weight: bold;"> Коротко про себе:<br/></span>
                       <textarea id="aboutMe" name="aboutMe" class="aboutMeText tinyProfile"><?php  echo $mas['aboutMe']?></textarea>
         <?php $this->Form->WriteFrontFooter(); ?>
           
         <br/><input type="button" style="float: right" class="btnCatalogImgUpload" onclick="verify()" name="save_reg_data" class="submitBtn<?php  echo _LANG_ID?>" value="Зберегти" />
        </div>
                 <div style="float: left;display: block;margin-left: 35px;">
                 <ul class="CatFormUl" id="imgLoaderConteiner">
                            <li id="imgLoaderConteiner" style="height: auto;">
                                <div id="catImgAjaxLoader"></div>
                        <div id="CatImageUploadBox">
                           <?php  if(is_file($_SERVER['DOCUMENT_ROOT']."/images/mod_blog/".$this->Logon->user_id."/".$mas['discount'])){?>
                            Аватар:<br/><img class="avatarImage" width="120" height="120" src="<?php  echo "/images/mod_blog/".$this->Logon->user_id."/".$mas['discount']?>"/><form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe"><input type="hidden" value="deleteImage" name="task"/><input type="hidden" value="true" name="ajax"/><input type="hidden" value="/images/mod_blog/<?php  echo $this->user_id."/".$mas['discount']?>" name="fileDel"/><input type="button" class="btnCatalogFormDel" onclick="loadImage();" value="Видалити"/></form>
                           <?php }else{?>
                            <form id="catLoadImageForm" name="catLoadImageForm" action="/login.html" enctype="multipart/form-data" method="post" target="hiddenframe">
                                         <input type="hidden" value="addImage" name="task"/>
                                         <input type="hidden" value="true" name="ajax"/>
                                      Виберіть зображення для аватари:<br/>
                                      <input id="catUserFileUploader" type="file" name="image" size="80"/>
                                      <input class="btnCatalogImgUpload" type="button" onclick="loadImage();" value="Завантажити"/>
                            </form>
                           <?php }?>
                        </div>
                            <iframe id="hiddenframe" name="hiddenframe" style="width:0px; height:0px; border:0px"></iframe>
                            </li>
                            <li>
                              
                            </li>
                        </ul>
           </div> */?>
        </div>
            </div>
        </div>
       <?php
    } //end of function EditProfile()
        
    // ================================================================================================
    // Function : ShowChangeEmailPass()
    // Date : 22.02.2001
    // Returns :      true,false / Void
    // Description :  Show form for change password to the new one.
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ShowChangeEmailPass()
    {                  
        $this->Form->WriteFrontHeader( 'ChangeEmailPass', _LINK.'myaccount/changepassword/', 'set_new_email_pass', NULL );
        ?>
        <h1><?php  echo $this->multiUser['TXT_FRONT_EDIT_EMAIL'];?></h1>
         <div class="body">
          <div class="needFields"><?php  echo $this->multiUser['TXT_CHANGE_PASS2'];?></div>
          <div align="center"><?php  echo $this->ShowErr()?></div>
          <table border="0" cellpadding="0" cellspacing="2" class="regTable">           
             <tr>
              <td>
                <?php  echo $this->multiUser['FLD_OLD_PASSWORD'];?>
                <span class="red_point">*</span>
              </td>
              <td><?php $this->Form->Password( 'oldpass', stripslashes($this->oldpass), '40' )?></td>
             </tr>
             
             <tr>
              <?php if(empty($this->email))
                $this->email = $this->Logon->login;?>
              <td>
                <?php  echo $this->multiUser['FLD_NEW_LOGIN'];?>
                <span class="red_point">*</span>
              </td>
              <td><?php $this->Form->TextBox( 'email', stripslashes($this->email), 'size="40"' )?></td>
             </tr>
             
             <tr>
              <?php if(empty($this->email2))
                $this->email2 = $this->Logon->login;?>
              <td>
                <?php  echo $this->multiUser['FLD_CONFIRM_NEW_LOGIN'];?>
                <span class="red_point">*</span>
              </td>
              <td>
                <?php $this->Form->TextBox( 'email2', stripslashes($this->email2), 'size="40"' )?>
              </td>
             </tr>
             
             <tr>
              <td>
                <?php  echo $this->multiUser['FLD_NEW_PASSWORD'];?>
                <span class="red_point">*</span>
              </td>
              <td><?php $this->Form->Password( 'password', $this->password, 40 )?></td>
             </tr>
             
             <tr>
              <td>
                <?php  echo $this->multiUser['FLD_CONFIRM_PASSWORD'];?>
                <span class="red_point">*</span>
              </td>
              <td>
                <?php $this->Form->Password( 'password2', $this->password2, 40 )?>
              </td>
             </tr>
             
          </table>        

          <div class="submit" align="center">
           <?php $imgFrontSubmit =  $this->Msg->show_text('IMG_FRONT_SUBMIT', TblSysTxt);?>
           <input type="submit"  class="submitBtn<?php  echo _LANG_ID?>"  value="<?php  echo $imgFrontSubmit?>"/>
           <input type="button" name="cancel" value="<?php  echo $this->Msg->show_text('_BUTTON_CANCEL', TblSysTxt);?>" class="cancelBtn<?php  echo _LANG_ID?>" onClick="javascript:window.location.href='<?php  echo _LINK;?>myaccount/';"/>
          </div>
         </div>
        <?php
        $this->Form->WriteFrontFooter();
        return true;
    } //end of function ShowChangeEmailPass()
              
       
    // ================================================================================================
    // Function : ForgotPass()
    // Date : 22.02.2001
    // Returns :      true,false / Void
    // Description :  Show fomr for sending nw passord to the user, who are forgot it.
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ForgotPass()
    {
        $this->Form->WriteFrontHeader( 'forgot_pass', _LINK.'forgotpass.html', 'send_pass', NULL );
        ?>
        <div id="catalogBox">
            <span class="MainHeaderText">Забули пароль?</span>
            <div id="catalogBody" style="background: #fafafa; padding-top: 35px;height: 250px">
               <b><?php  echo $this->multiUser['TXT_FORGOT_PASS2'];?></b>
               <br/>
               <?php  echo $this->ShowErr()?>
               <table border="0" cellspacing="2" cellpadding="0" class="regTable" style="width: 300px;">
                   
                <tr>
                 <td style="height: 100px;"  align="right"><?php  echo $this->multiUser['FLD_EMAIL'];?>:</td>
                 <td style="height: 100px;"><?php $this->Form->TextBox( 'email', stripslashes($this->email), '$size=30' )?></td>
                </tr>
                <tr>
                    <td style="width: 250px;" ></td>
                    <td>
                        <div class="submit">
                            <?php $imgFrontSubmit = $this->Msg->show_text('IMG_FRONT_SUBMIT', TblSysTxt);?>
                            <input type="submit" value="<?php  echo $imgFrontSubmit;?>"/>
                        </div>
                    </td>
                </tr>
               </table>
               
               
               <?php //src="<?php  echo $this->Spr->GetImageByCodOnLang(TblSysTxt, 'IMG_FRONT_SUBMIT', $this->lang_id)?>
           </div>        
        </div>
        <?php
        $this->Form->WriteFrontFooter();
        return true;
    } //end of function ForgotPass()
           


    // ================================================================================================
    // Function : ChangeLogin()
    // Date : 22.02.2001
    // Parms :   $old_login  / old login of the user
    //           $new_login  / new login of the user
    // Returns :      true,false
    // Description :  Change login for External user in the table sys_user
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ChangeLogin( $old_login = NULL, $new_login = NULL)
    {
       $q = "UPDATE `".TblSysUser."` set `login`='$new_login' WHERE `login`='$old_login'";
       $res = $this->db->db_Query($q);
       //echo '<br>$q='.$q.' $res='.$res.' $this->db->result='.$this->db->result;
       if ( !$res OR !$this->db->result) 
            return false;
       
       $q = "UPDATE `".TblModUser."` set `email`='$new_login' WHERE `email`='$old_login'";
       $res = $this->db->db_Query($q);
       if ( !$res OR !$this->db->result) 
            return false;
       
       return true;
    } //end of function ChangeLogin()         

      
    // ================================================================================================
    // Function : ShowErr()
    // Date : 22.02.2011
    // Returns :      void
    // Description :  Show errors
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ShowErr()
    {
        $this->Form->ShowErr($this->Err);
    } //end of function ShowErr()


    // ================================================================================================
    // Function : ShowTextMessages()
    // Date : 22.02.2001
    // Returns :      void
    // Description :  Show text messages
    // Programmer :  Yaroslav Gyryn
    // ================================================================================================
    function ShowTextMessages($txt=NULL)
    {
        if( !empty($txt) ) $this->TextMessages = $txt; 
        if ($this->TextMessages){
            $this->Form->ShowTextMessages($this->TextMessages);
        }
    } //end of function ShowTextMessages()

   // ================================================================================================
   // Function : CheckEmailFields()
   // Date : 22.02.2001
   // Returns :      $this->Err
   // Description :  Check fields of email for validation
   // Programmer :  Yaroslav Gyryn
   // ================================================================================================
   function CheckEmailFields( $source=NULL )
   {
     $this->Err=NULL;
     if (empty( $this->email )) 
        $this->Err = $this->Err.$this->multiUser['MSG_FLD_EMAIL_EMPTY'].'<br>';
//     else{
//         if ($source=='forgotpass'){
//             
//         }
//        if ( $this->email!=$this->email2 ) 
//            $this->Err = $this->Err.$this->multiUser['MSG_NOT_MATCH_REENTER_EMAIL'].'<br>';
//        /*if (!ereg("^[a-zA-Z0-9_.\-]+@[a-zA-Z0-9.\-].[a-zA-Z0-9.\-]+$", $this->email)) 
//            $this->Err = $this->Err.$this->Msg->show_text('MSG_NOT_VALID_EMAIL').'<br>';*/
//        if ($source=='forgotpass') return $this->Err;
//        
//        if ( $this->email!=$this->Logon->login AND !$this->unique_login($this->email) ) {
//           //$this->Err=$this->Err.$this->Msg->show_text('MSG_NOT_UNIQUE_LOGIN_1')." ".stripslashes($this->email)." ".$this->Msg->show_text('MSG_NOT_UNIQUE_LOGIN_2').'<br>';
//           $this->Err=$this->Err.$this->multiUser['MSG_NOT_UNIQUE_LOGIN'].'<br>';        
//        }            
//     }         
     return $this->Err; 
   } //end of function CheckEmailFields()    

   
   // ================================================================================================
   // Function : ChangePass()
   // Date : 22.02.2001
   // Returns :      true,false / Void
   // Description :  Show form for change password to the new one.
   // Programmer :  Yaroslav Gyryn
   // ================================================================================================
   /*function ChangePass()
   {
    ?>
    <div align=center><h1>Изменение пароля</h1></div>
   <form action="<?php  echo $_SERVER['PHP_SELF']?>" method=post>
      <table border=0 cellspacing=1 cellpadding=3>
       <tr><td colspan=2 align=center><H3><?php  echo $this->Msg->show_text('TXT_CHANGE_PASS2');?></H3>
       <tr><td colspan=2 align=center class="UserErr"><?php  echo $this->ShowErr()?>
       <tr><td>
       <tr>
        <td><?php  echo $this->Msg->show_text('FLD_OLD_PASSWORD');?>:
        <td><?php $this->Form->Password( 'oldpass', stripslashes($this->oldpass), $size=30 )?>
       <tr>
        <td><?php  echo $this->Msg->show_text('FLD_NEW_PASSWORD');?>:
        <td><?php $this->Form->Password( 'password', stripslashes($this->password), $size=30 )?>
       <tr>
        <td><?php  echo $this->Msg->show_text('FLD_CONFIRM_PASSWORD');?>:
        <td><?php $this->Form->Password( 'password2', stripslashes($this->password2), $size=30 )?>
       <tr>
        <td colspan=2 align=center>
         <INPUT TYPE="image" src="images/design/button_save.gif">
         <input type=hidden name=set_new_pass value=set_new_pass>
       <tr><td colspan=2 align=center>
      </table>
   </form> 
    <?php
    return true;
   } //end of function ChangePass()       */



     function ShowProfile()
     {

         $SysGroup = new SysUser();
         $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->Logon->user_id." AND `".TblSysUser."`.id=".$this->Logon->user_id."";
         $res = $this->db->db_Query($q);
         if ( !$res OR !$this->db->result ) return false;
         $mas = $this->db->db_FetchAssoc();
         ?>
         <div class="clear"></div>
         <div>
             <div id="catalogBox">
                 <ul class="prof-menu">
                     <li><a href="/data/">Отслеживание данных</a></li>
                     <li><a href="/myaccount/" class="current-prof">Личные данные</a></li>
                     <li><a href="/myaccount/message/">Обратная связь</a></li>
                 </ul>
                <div class="clear"></div>
                <table class="profile-info">
                    <tr class="tr-white"><td>ID: <?php  echo $mas['login']?></td></tr>
                    <tr class="tr-grey"><td><?php  echo $mas['surname']?></td></tr>
                    <tr class="tr-white"><td><?php  echo $mas['name']?></td></tr>
                    <tr class="tr-grey"><td><?php  echo $mas['secondname']?></td></tr>
                </table>
                <a href="/myaccount/edit/" class="edit-profile">Редактировать</a>
             </div>
         </div>
     <?php
     } //end of function ShowProfile()

     function ShowMap()
     {
         $id_map = $this->map_id;
         $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->Logon->user_id." AND `".TblSysUser."`.id=".$this->Logon->user_id."";
         $res = $this->db->db_Query($q);
         if ( !$res OR !$this->db->result ) return false;
         $mas = $this->db->db_FetchAssoc();
//        var_dump($mas);
         ?>
         <div class="id">
             <span class="spanid">ID:<?php  echo $mas['login']?></span>
             <?php  echo $mas['surname']?> <?php  echo $mas['name']?> <?php  echo $mas['secondname']?>

         </div>
         <?php

         $q="SELECT * FROM `mod_user_stantion` WHERE `idc`!=".$this->Logon->login;
         $res = $this->db->db_Query($q);
         if ( !$res OR !$this->db->result ) return false;
         $rows = $this->db->db_GetNumRows($res);
         $strAnother = '[';
         for ($i = 0; $i < $rows; $i++) {
             $new[$i] = $this->db->db_FetchAssoc();
             $arrAnother[$i] = "['".$new[$i]['ids']."', ".$new[$i]['lat']." , ".$new[$i]['long']." ,1]";
         }

         for ($i = 0; $i < $rows; $i++) {
             $strAnother .=$arrAnother[$i].',';
         }
         $strAnother .= ']';


         $q="SELECT * FROM `mod_user_stantion` WHERE `idc`=".$this->Logon->login;
         $res = $this->db->db_Query($q);
         if ( !$res OR !$this->db->result ) return false;
         $rows = $this->db->db_GetNumRows($res);
         for ($i = 0; $i < $rows; $i++) {
             $mas[$i] = $this->db->db_FetchAssoc();
         }
         for ($i = 0; $i < $rows; $i++) {
//             $mas[$i] = $this->db->db_FetchAssoc();
             $warning=0;
             for ($j = 1; $j < $mas[$i]['num']+1; $j++) {
                 $q = "SELECT * FROM `mod_user_tracker` WHERE `idc`=" . $this->Logon->login . " AND  `ids`=" . $mas[$i]['ids'] . " AND `idt`=" . $j;
                 $res = $this->db->db_Query($q);
                 if ($res && $this->db->result)
                     $track = $this->db->db_FetchAssoc();

                 if($track['statTilt']!=0 || $track['statAzim']!=0){
                     $warning=1;break;
                 }
             }


             $mas[$i]['geo']=$mas[$i]['lat'].','.$mas[$i]['long'];

             if($warning==0) {
                 if ($mas[$i]['stat'] == 0 || $mas[$i]['stat'] == 4) {
                     $arr[$i] = "['" . $mas[$i]['ids'] . "', " . $mas[$i]['lat'] . " , " . $mas[$i]['long'] . " ,1, 0]";
                 } else {
                     $arr[$i] = "['" . $mas[$i]['ids'] . "', " . $mas[$i]['lat'] . " , " . $mas[$i]['long'] . " ,1, 1]";
                 }
             }else{
                 $arr[$i] = "['" . $mas[$i]['ids'] . "', " . $mas[$i]['lat'] . " , " . $mas[$i]['long'] . " ,1, 1]";
             }

             if($mas[$i]['ids']==$id_map){$long=$mas[$i]['lat'];$lat=$mas[$i]['long'];$new_id=$i;}
         }
            $str = '[';
            for ($i = 0; $i < $rows; $i++) {
                $str .=$arr[$i].',';
            }
            $str .= ']';
echo '$str='.$str;
//         $str1 = '[';
//         for ($i = 0; $i < $rows; $i++) {
//             $str1 .=$mas[$i]['ids'].',';
//         }
//         $str1 .= ']';




         ?>
         <div class="clear"></div>
         <div>

             <div id="catalogBox">
                 <ul class="prof-menu">
                     <li><a href="/data/" class="current-prof">Отслеживание данных</a></li>
                     <li><a href="/myaccount/">Личные данные</a></li>
                     <li><a href="/myaccount/message/">Обратная связь</a></li>
                 </ul>
                 <div class="clear"></div>
                 <div style="width: 693px;border-top: 1px solid #949494;">
                     <div class="id-stantion">Подстанции</div>
                     <div class="map-button">
                         <a href="/data/">Вернуться к деталям</a>
                     </div>
                 </div>

                 <div class="clear"></div>

             </div>
         </div>


         <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>
         <script>
             // The following example creates complex markers to indicate beaches near
             // Sydney, NSW, Australia. Note that the anchor is set to
             // (0,32) to correspond to the base of the flagpole.

             function initialize() {

                 var mapOptions = {
                     zoom: 10,
                     mapTypeId: google.maps.MapTypeId.SATELLITE,
                     center: new google.maps.LatLng(<?php  echo $long?>, <?php  echo $lat?>)
                 }
                 var map = new google.maps.Map(document.getElementById('map-canvas'),
                     mapOptions);

                 setMarkers(map, beaches);
                 setMarkersAnother(map, beachesAnother);

             }

             /**
              * Data for the markers consisting of a name, a LatLng and a zIndex for
              * the order in which these markers should display on top of each
              * other.
              */
             var arr = new Array();
//             var arrr = new Array();
             var arrAnother = new Array();
                 arr = <?php  echo $str?>;


//             arrr = <?php // echo $str1?>//;

             arrAnother = <?php  echo $strAnother?>;


             var beaches = arr;
             var beachesAnother = arrAnother;

             function setMarkersAnother(map, locations) {
                 var image = {
                     url: '/images/design/greenformap.png',
                     size: new google.maps.Size(15, 15),
                     origin: new google.maps.Point(0,0),
                     anchor: new google.maps.Point(0, 32)
                 };
                 var shape = {
                     coords: [1, 1, 1, 20, 18, 20, 18 , 1],
                     type: 'poly'
                 };
                 for (var i = 0; i < locations.length; i++) {
                     var beach = locations[i];
                     var myLatLng = new google.maps.LatLng(beach[1], beach[2]);
                     var marker = new google.maps.Marker({
                         position: myLatLng,
                         map: map,
                         icon: image,
                         shape: shape,
                         title: beach[0],
                         zIndex: beach[3]
                     });

                 }

             }


             function setMarkers(map, locations) {
                 // Add markers to the map

                 // Marker sizes are expressed as a Size of X,Y
                 // where the origin of the image (0,0) is located
                 // in the top left of the image.

                 // Origins, anchor positions and coordinates of the marker
                 // increase in the X direction to the right and in
                 // the Y direction down.
                 var image = {
                     url: '/images/design/mapmarker.png',
                     // This marker is 20 pixels wide by 32 pixels tall.
                     size: new google.maps.Size(17, 17),
                     // The origin for this image is 0,0.
                     origin: new google.maps.Point(0,0),
                     // The anchor for this image is the base of the flagpole at 0,32.
                     anchor: new google.maps.Point(0, 32)
                 };

                 var imageForRed = {
                     url: '/images/design/redformap.png',
                     // This marker is 20 pixels wide by 32 pixels tall.
                     size: new google.maps.Size(17, 17),
                     // The origin for this image is 0,0.
                     origin: new google.maps.Point(0,0),
                     // The anchor for this image is the base of the flagpole at 0,32.
                     anchor: new google.maps.Point(0, 32)
                 };
                 // Shapes define the clickable region of the icon.
                 // The type defines an HTML &lt;area&gt; element 'poly' which
                 // traces out a polygon as a series of X,Y points. The final
                 // coordinate closes the poly by connecting to the first
                 // coordinate.
                 var shape = {
                     coords: [1, 1, 1, 20, 18, 20, 18 , 1],
                     type: 'poly'
                 };
                 for (var i = 0; i < locations.length; i++) {

                     var beach = locations[i];
                     var myLatLng = new google.maps.LatLng(beach[1], beach[2]);

                    if(beach[4]==0) {
                        var marker = new google.maps.Marker({
                            position: myLatLng,
                            map: map,
                            icon: image,
                            shape: shape,
                            title: beach[0],
                            zIndex: beach[3]
                        });
//                     var contentString = '<span class="id-st">'+arrr[i]+'</span>';
                        var contentString = '<div class="map-img-hover"><img src="/images/design/idformaps.png" style="position: relative;left: 27px;"><span class="id-st">' + beach[0] + '</span></div>';
                        var infowindow = new google.maps.InfoWindow({
                            content: contentString
                        });
                    }else{
                        var marker = new google.maps.Marker({
                            position: myLatLng,
                            map: map,
                            icon: imageForRed,
                            shape: shape,
                            title: beach[0],
                            zIndex: beach[3]
                        });
//                     var contentString = '<span class="id-st">'+arrr[i]+'</span>';
                        var contentString = '<div class="map-img-hover"><img src="/images/design/idformapsred.png" style="position: relative;left: 27px;"><span class="id-st">' + beach[0] + '</span></div>';
                        var infowindow = new google.maps.InfoWindow({
                            content: contentString
                        });
                    }

                     google.maps.event.addListener(marker, 'mouseover', getInfoCallback(map, contentString));

                 }

             }




             function getInfoCallback(map, content) {
                 var infowindow = new google.maps.InfoWindow({content: content});
                 google.maps.event.addListener(infowindow, 'domready', function() {
                     var iwOuter = $('.gm-style-iw');
                     var iwBackground = iwOuter.prev();
                     iwBackground.css({'display' : 'none'});
                     var iwParent = iwOuter.parent();

                     var x = iwParent.find('div:last-child');
                     x.css({'top' : '90px'});
                     x.css({'right' : '30px'});

                 });
                 return function() {

                     infowindow.setContent(content);
                     infowindow.open(map, this);
                 };
             }


             google.maps.event.addDomListener(window, 'load', initialize);





             //             $('.map-button').css({'display' : 'none'});


         </script>

<?php //var_dump($mas[0]);?>
         <div>
             <div class="map-header">
                 <img src="/images/design/greenformap.png" style="  margin: 5px 5px 0 15px;">
                 <span class="map-id">ID: <?php  echo $mas[$new_id]['ids']?>         </span>
                  <img src="/images/design/25.png" style="width: 30px;position: relative;top: 10px;"> Количество трекеров: <span class="map-value"><?php  echo $mas[$new_id]['num']?></span>
                 <img src="/images/design/13.png" style="width: 30px;position: relative;top: 10px;"> GPS координаты подстанции: <span class="map-value"><?php  echo $mas[$new_id]['long']?>°,<?php  echo $mas[$new_id]['lat']?>°</span>
             </div>
                 <div id="map-canvas"></div>



</div>
         <?php
     }


     function ShowData()
     {
         $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->Logon->user_id." AND `".TblSysUser."`.id=".$this->Logon->user_id."";
         $res = $this->db->db_Query($q);
         if ( !$res OR !$this->db->result ) return false;
         $mas = $this->db->db_FetchAssoc();
?>
         <script type='text/javascript'>

             $(document).ready(function() {
                 $("A.trigger").toggle(function() {
                         var text = document.getElementById($(this).attr('id'));
                         var a = 'DIV#box' + $(this).attr('data');
                         $(a).fadeIn();
                         $(this).addClass("trigger-hover");
                         text.innerHTML='Скрыть состояние трекеров';
                         return false;
                     },
                     function() {
                         var text = document.getElementById($(this).attr('id'));
                         var a = 'DIV#box' + $(this).attr('data');
                         $(a).fadeOut();
                         $(this).removeClass("trigger-hover");
                         text.innerHTML='Показать состояние трекеров';
                         return false;
                     });
             });
         </script>

         <script type="text/javascript">

             function openbox(id){
                 display = document.getElementById(id).style.display;

                 if(display=='none'){
                     document.getElementById(id).style.display='block';
                 }else{
                     document.getElementById(id).style.display='none';
                 }
             }
         </script>
         <div class="id">
             <span class="spanid">ID:<?php  echo $mas['login']?></span>
             <?php  echo $mas['surname']?> <?php  echo $mas['name']?> <?php  echo $mas['secondname']?>

         </div>
         <div class="clear"></div>
         <div>
         <div id="catalogBox">
         <ul class="prof-menu">
             <li><a href="/data/" class="current-prof">Отслеживание данных</a></li>
             <li><a href="/myaccount/">Личные данные</a></li>
             <li><a href="/myaccount/message/">Обратная связь</a></li>
         </ul>
<?php
         $SysGroup = new SysUser();
         $q="SELECT * FROM `mod_user_stantion` WHERE `idc`=".$this->Logon->login;
         $res = $this->db->db_Query($q);
         if ( !$res OR !$this->db->result ) return false;
         $rows = $this->db->db_GetNumRows($res);

         for ($i = 0; $i < $rows; $i++) {
             $mas[$i] = $this->db->db_FetchAssoc();
         }

         for ($i = 0; $i < $rows; $i++) {

         ?>

                 <div class="clear"></div>
                 <div style="width: 693px;border-top: 1px solid #949494;margin-top: 60px;">
                     <div class="id-stantion">Подстанция   ID:<?php  echo $mas[$i]['ids']?><img src="/images/design/warning.png" id="warning<?php  echo $mas[$i]['ids']?>" class="warn" style="display: none;"></div>
                     <div class="map-button">
                         <a href="/map/<?php echo $mas[$i]['ids'];?>">Показать на карте</a>
                     </div>
                 </div>

                 <div class="clear"></div>
                 <table class="data">
                    <tr class="gray">
                        <td>Статус датчика солнца</td>
                        <td>Ориентация трекеров</td>
                        <td>GPRS Координаты подстанции</td>
                        <td>Температура окружающей среды</td>
                        <td>Датчик ветра</td>
                    </tr>
                     <tr class="gray-1">
                         <td>
                             <img src="/images/design/11.png"><div class="clear"></div>
<!--                             --><?php //=$mas['stat']?>
                             <?php  switch($mas[$i]['stat']){
                                 case "0":
                                     ?>Ok<?php
                                     break;
                                 case "1":
                                     ?>Ошибка сканирования<?php
                                     break;
                                 case "2":
                                     ?>Нет связи с трекером<?php
                                     break;
                                 case "3":
                                     ?>Нет связи с датчиком ветра и снега<?php
                                     break;
                                 case "4":
                                     ?>Ночь<?php
                                     break;
                             }?>
                         </td>
                         <td>
                             <img src="/images/design/12.png"><div class="clear"></div>
                             Азимут: <?php  echo $mas[$i]['azim']?>°<br>
                             Угол возвышения: <?php  echo $mas[$i]['tilt']?>°
                         </td>
                         <td>
                             <img src="/images/design/13.png"><div class="clear"></div>
                             Долгота: <?php  echo $mas[$i]['long']?><br>
                             Широта: <?php  echo $mas[$i]['lat']?>
                         </td>
                         <td><img src="/images/design/14.png"><div class="clear"></div><?php  echo $mas[$i]['temp']?>°</td>
                         <td><img src="/images/design/15.png"><div class="clear"></div><?php  echo $mas[$i]['wind_speed']?> м/с<br>
                             Защита:
                             <?php  switch($mas[$i]['wind_protection']) {
                                 case "0":
                                     ?>Off<?php
                                     break;
                                 case "1":
                                     ?>On<?php
                                     break;
                             }?>
                         </td>
                     </tr>
                     <tr class="gray">
                         <td>Датчик снега</td>
                         <td>Облученность поверхности трекеров</td>
                         <td>Дата и время</td>
                         <td>Дата установки</td>
                         <td>Количество трекеров</td>
                     </tr>
                     <tr class="gray-1">
                         <td><img src="/images/design/21.png"><div class="clear"></div>
                             <?php  switch($mas[$i]['snow_protection']) {
                                 case "0":
                                     ?>Off<?php
                                     break;
                                 case "1":
                                     ?>On<?php
                                     break;
                             }?>
                         </td>
<!--                         --><?php //var_dump($mas[$i]);?>
                         <td><img src="/images/design/22.png"><div class="clear"></div><?php  echo $mas[$i]['irrd']?></td>
                         <td><img src="/images/design/23.png"><div class="clear"></div><?php  echo $mas[$i]['time']?></td>
                         <td><img src="/images/design/24.png"><div class="clear"></div><?php  echo substr($mas[$i]['inst'], 0, 2)?>.<?php  echo substr($mas[$i]['inst'], 2, 2)?>.<?php  echo substr($mas[$i]['inst'], 4, 2)?></td>
                         <td><img src="/images/design/25.png"><div class="clear"></div><?php  echo $mas[$i]['num']?></td>
                     </tr>
                 </table>
                 <div class="clear"></div>



                 <?php
                 if($mas[$i]['stat']!=0){?>
                    <?if($mas[$i]['stat']!=4){?>
                     <script type="text/javascript">
                         document.getElementById('warning<?php echo $mas[$i]['ids']?>').style.display='inline-block';
                     </script>
                     <?}?>
                <?}
                     $q="SELECT * FROM `mod_user_tracker` WHERE `idc`=".$this->Logon->login." AND  `ids`=".$mas[$i]['ids'];
                     $res = $this->db->db_Query($q);
                     if ( !$res OR !$this->db->result ) return false;
                     $trakRows = $this->db->db_GetNumRows($res);
                     $allTrecksWithoutInfo = $mas[$i]['num']-$trakRows;?>

             <div class="open-button">
                 <a href="#"  id="trigger<?=$mas[$i]['ids']?>"  data='<?=$mas[$i]['ids']?>' class="trigger">Показать состояние трекеров</a>
             </div>

             <div id="box<?=$mas[$i]['ids']?>" style="display: none;">
                 <?$warning=0;?>
            <?for ($j = 1; $j < $mas[$i]['num']+1; $j++) {?>
                 <?
                 $q="SELECT * FROM `mod_user_tracker` WHERE `idc`=".$this->Logon->login." AND  `ids`=".$mas[$i]['ids']." AND `idt`=".$j;
                 $res = $this->db->db_Query($q);
                 if ( $res && $this->db->result )
                    $track = $this->db->db_FetchAssoc();

                 ?>

             <div class="one">
                 <div class="line-wr">
                     <div class="line"></div>

                         <?php if($track){if($track['statAzim']!=0 || $track['statTilt']!=0){?><img src="/images/design/warning.png" class="warning-img"><?php }}?>

                 </div>
                 <div class="tracker">
                     <table>
                         <tr>
                             <td colspan="3" class="trecker-head">
                                 <?if($track){?><?php if($track['statAzim']==0 && $track['statTilt']==0){?><img src="/images/design/green.png" class="tracker-img"><?php }else{?><img src="/images/design/red.png" class="tracker-img"><?php }?>
                                 <?}else{?><img src="/images/design/green.png" class="tracker-img"><?}?>
                                 <span class="tracker-span">Tracker #<?php  echo $j?></span> </td>
                         </tr>
                         <tr>
                             <td style="border-right: 1px solid #dfdfdf;padding: 10px 0;">Статус привода азимута</td>
                             <td style="border-right: 1px solid #dfdfdf;padding: 10px 0;">Статус привода угла возвышения</td>
                             <td>Дата установки трекера</td>
                         </tr>
                         <tr>
                             <td style="border-right: 1px solid #dfdfdf;">
                                 <?if(!$track){?>

                                         ok
                                <?}else { ?>
                                     <?php switch ($track['statAzim']) {
                                         case "0":
                                             ?>ok<?php
                                             break;
                                         case "1":
                                             ?>Неисправность датчика положения<?php
                                             break;
                                         case "2":
                                             ?>Защита по току<?php
                                             break;
                                         case "3":
                                             ?>Обрыв силовой линии<?php
                                             break;
                                         case "4":
                                             ?>Нет связи с датчиком солнца<?php
                                             break;
                                         case "5":
                                             ?>Перезагрузка трекера<?php
                                             break;
                                     }
                                 }?>
                             </td>
                             <td style="border-right: 1px solid #dfdfdf;">
                                 <?if(!$track){?>
                                     ok
                                 <?}else { ?>
                                     <?php switch ($track['statTilt']) {
                                         case "0":
                                             ?>ok<?php
                                             break;
                                         case "1":
                                             ?>Неисправность датчика положения<?php
                                             break;
                                         case "2":
                                             ?>Защита по току<?php
                                             break;
                                         case "3":
                                             ?>Обрыв силовой линии<?php
                                             break;
                                         case "4":
                                             ?>Нет связи с датчиком солнца<?php
                                             break;
                                         case "5":
                                             ?>Перезагрузка трекера<?php
                                             break;
                                     }
                                 }?>
                             </td>
                             <td>
                            <?if(!$track){?>
                                 <?php  echo substr($mas[$i]['inst'], 0, 2)?>.<?php  echo substr($mas[$i]['inst'], 2, 2)?>.<?php  echo substr($mas[$i]['inst'], 4, 2)?>
                            <?}else { ?>
                                <?php  echo substr($track['inst'], 0, 2)?>.<?php  echo substr($track['inst'], 2, 2)?>.<?php  echo substr($track['inst'], 4, 2)?>
                            <?}?>
                             </td>
                         </tr>
                     </table>
                 </div>
             </div>

                <?

                    if($track['statTilt']!=0 || $track['statAzim']!=0){?>
                    <script type="text/javascript">
                        document.getElementById('warning<?php echo $mas[$i]['ids']?>').style.display='inline-block';
                    </script>
                    <?}
                ?>
         <?php }?>
            </div>


<?php }?>
             </div>
         </div>
         <div style="margin: 60px;"></div>
         <?php
     } //end of function ShowProfile()

     function EditProfile()
     {

         $SysGroup = new SysUser();
         $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->Logon->user_id." AND `".TblSysUser."`.id=".$this->Logon->user_id."";
         $res = $this->db->db_Query($q);
         if ( !$res OR !$this->db->result ) return false;
         $mas = $this->db->db_FetchAssoc();

         ?>
         <div class="clear"></div>
         <div>
             <div id="catalogBox">
                 <ul class="prof-menu">
                     <li><a href="/data/">Отслеживание данных</a></li>
                     <li><a href="/myaccount/" class="current-prof">Личные данные</a></li>
                     <li><a href="/myaccount/message/">Обратная связь</a></li>
                 </ul>
<!--                 --><?php //var_dump($mas)?>
                 <div class="clear"></div>
                 <form action="/myaccount/edit/save/" method="POST" name="save">
                 <table class="profile-info CatFormUl">
                     <tr class="tr-white"><td>ID: <?php  echo $mas['login']?></td></tr>
                     <tr class="tr-white1"><td>Фамилия*</td></tr>
                     <tr class="tr-grey"><td><input type="text" class="CatinputFromForm" name="surname" value="<?php  echo $mas['surname']?>"/></td></tr>
                     <tr class="tr-white1"><td>Имя*</td></tr>
                     <tr class="tr-grey"><td><input type="text" class="CatinputFromForm" name="name" value="<?php  echo $mas['name']?>"/></td></tr>
                     <tr class="tr-white1"><td>Отчество*</td></tr>
                     <tr class="tr-grey"><td><input type="text" class="CatinputFromForm" name="secondname" value="<?php  echo $mas['secondname']?>"/></td></tr>
                     <tr class="tr-white1"><td></td></tr>
                     <tr class="tr-white1"><td>Текущий пароль*</td></tr>
                     <tr class="tr-grey"><td><input type="text" disabled="disabled" class="CatinputFromForm" name="pass" value="<?php  echo $mas['pass']?>"/></td></tr>
                     <tr class="tr-white1"><td>Новый пароль*</td></tr>
                     <tr class="tr-grey"><td><input type="text" class="CatinputFromForm" name="newpass1" value=""/></td></tr>
                     <tr class="tr-white1"><td>Подтвердите пароль*</td></tr>
                     <tr class="tr-grey"><td><input type="text" class="CatinputFromForm" name="newpass2" value=""/></td></tr>
                     <input type="hidden" name="oldpass" value="<?php  echo $mas['pass']?>">
                 </table>



                 <input type="submit" class="edit-profile-button0" name="save" value="<?php  echo $this->multiUser['TXT_SAVE'];?>" />
                 <a href="/myaccount/" class="edit-profile-button1">Отменить</a>
                 </form>
             </div>
         </div>
     <?php
     } //end of function EditProfile()


    function ShowFeedMessage(){
        $SysGroup = new SysUser();
        $q="SELECT * FROM `".TblModUser."`,`".TblSysUser."` WHERE `".TblModUser."`.`sys_user_id`=".$this->Logon->user_id." AND `".TblSysUser."`.id=".$this->Logon->user_id."";
        $res = $this->db->db_Query($q);
        if ( !$res OR !$this->db->result ) return false;
        $mas = $this->db->db_FetchAssoc();
//        var_dump($mas);
        ?>
        <div class="id">
            <span class="spanid">ID:<?php  echo $mas['login']?></span>
            <?php  echo $mas['surname']?> <?php  echo $mas['name']?> <?php  echo $mas['secondname']?>

        </div>
        <div class="clear"></div>
        <div>
             <div id="catalogBox">
                 <ul class="prof-menu">
                     <li><a href="/data/">Отслеживание данных</a></li>
                     <li><a href="/myaccount/">Личные данные</a></li>
                     <li><a href="/myaccount/message/" class="current-prof">Обратная связь</a></li>
                 </ul>
                 <div class="clear"></div>

                 <?php if($this->send_result != '' ){
                    echo $this->send_result;
                }else{?>
                 <form action="/myaccount/message/send/" method="POST" name="save">
                     <input type="hidden" name="name" value="<?php  echo $mas['name']?>"/>
                     <input type="hidden" name="surname" value="<?php  echo $mas['surname']?>"/>
                     <input type="hidden" name="secondname" value="<?php  echo $mas['secondname']?>"/>
                     <div class="some-text">Eсли у вас возникли вопросы или предложения, Сообщите:</div>
                     <br>
                     <div class="mes-bg">
                         <div style="margin: 5px 0 0 40px;font-size: 15px;font-family: 'ArianAMURegular';">Сообщение</div>
                         <textarea name="message" class="prof-mess"></textarea><br>
                     </div>


                    <input type="submit" class="edit-profile-button2" name="save" value="<?php  echo $this->multiUser['_TXT_SEND'];?>" />
                </form>
            <?php }?>
            </div>
        </div>
    <?php
    }


     function SendMail(){
         if( is_array($this->cookie_serfing) ) {
             $keys = array_keys($this->cookie_serfing);
             $rows = count($keys);


             for($i=0;$i<$rows;$i++){
                 $this->serfing[$i]['tstart'] = $keys[$i];
                 $this->serfing[$i]['tstart_dt'] = strftime("%Y-%m-%d %H:%M:%S", $this->serfing[$i]['tstart']);
                 //all records exepts first item
                 if($i>0){
                     //for all items exepts last
                     $this->serfing[$i-1]['tstay'] = $keys[$i]-$this->serfing[$i-1]['tstart'];
                     $this->serfing[$i-1]['tstay_dt'] = Date_Calc::DateDiffInTime($this->serfing[$i-1]['tstart'], $keys[$i]);
                     if($i==($rows-1)) {
                         $this->serfing[$i]['tstay'] = time()-$this->serfing[$i-1]['tstart'];
                         $this->serfing[$i]['tstay_dt'] = Date_Calc::DateDiffInTime($this->serfing[$i]['tstart'], time());
                     }
                 }
                 else{
                     $this->serfing[$i]['tstay'] = '';
                     $this->serfing[$i]['tstay_dt'] = '';
                 }
                 $this->serfing[$i]['uri']=$this->cookie_serfing[$keys[$i]];
             }
         }//end if
         //echo '<br />$serfing=';print_r($serfing);

         if($this->quick_form==1) $subject = $this->multi['QUICK_FEEDBACK'].' :: '.$_SERVER['SERVER_NAME'].', '.$this->multi['_TXT_NAME'].': '.$this->name;
         else $subject = $this->multi['_TXT_FORM_NAME'].' :: '.$_SERVER['SERVER_NAME'].', '.$this->multi['_TXT_NAME'].': '.$this->name;

         $question = str_replace("\n", "<br/>", stripslashes($this->message));
         $body = '
        <style>
         td{ font-family:Arial,Verdana,sans-serif; font-size:11px;}
        </style>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr><td width="100">ФИО:</td><td>'.stripslashes($this->surname).' '.stripslashes($this->name).' '.stripslashes($this->secondname).'</td></tr>
        <tr><td colspan="2" align="left">Сообщение:</td></tr>
        <tr><td colspan="2">'.$question.'</td></tr>';
         $body .= '</table>';

         //save contact to database
//         $res = $this->SaveContact();
         //================ send by class Mail START =========================
         $massage = $body;
         $mail = new Mail($this->lang_id_for_send_emails);

         $SysSet = new SysSettings();
         $sett = $SysSet->GetGlobalSettings();
         if( !empty($sett['mail_auto_emails'])){
             $hosts = explode(";", $sett['mail_auto_emails']);
             for($i=0;$i<count($hosts);$i++){
                 //$arr_emails[$i]=$hosts[$i];
                 $mail->AddAddress($hosts[$i]);
             }//end for
         }
         if( !empty($this->fpath) ){
             $fpath = $this->uploaddir.$this->fpath;
             $mail->AddAttachment($fpath);
         }
         $mail->Subject = $subject;
         $mail->Body = $massage;
         //$mail->From = stripslashes($this->e_mail);
         //$mail->FromName = stripslashes($this->name);
         if( !$mail->SendMail() ) return false;
         //================ send by class Mail END =========================
         return true;
     } //end of function send_form()





 } //end of class UserShow
?>