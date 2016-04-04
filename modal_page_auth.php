<script language="JavaScript">


</script>
<h1 class="modal-h1">Авторизироваться</h1>
<form method="post" action="/login.php">
    <input type="hidden" name="referer_page" id="referer_page" value="<?=$_SERVER['REQUEST_URI']?>"/>
    <input type="hidden" name="whattodo" id="whattodo" value="2"/>
    <label for="login" class="modal-label">Логин(Имя)</label><br>
    <input type="text" id="login" name="login" size="40" value="" ><br>
    <label for="password" class="modal-label">Пароль </label><br>
    <input type="text" id="pass" name="pass" size="40" value="" ><br>
    <input type="submit" class="modal-button" style="text-align: middle;font: 18px solid #fff;" value="ВОЙТИ" />
</form>
