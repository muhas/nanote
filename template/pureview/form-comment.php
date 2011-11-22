<p>
<form id="cmtform" method="post" action="#form">
<h3><a name="form" href="#form">Оставить комментарий к &laquo;<?php echo $pst['title']; ?>&raquo;</a></h3>
<input type="hidden" name="act" value="comm" />
<input type="hidden" name="p" value="<?php echo $_v['p']; ?>" />
<?php if(!isset($_SESSION['adm'])) { ?>
<label title="Укажите здесь ник, email (подписка), сайт или оставьте пустым.">
<u>Свободное поле</u>
<input type="text" name="nick" maxlength="28" onclick="this.value=''" value="<?php echo (@$_c['nnk']); ?>" />
</label>
<br />
<?php } ?>
<label>
<u>Текст комментария</u> *
<textarea class="cmtext" id="cmtxt" name="text" rows="15" cols="40"><?php echo @htmlspecialchars($_v['text']); ?></textarea><br />
</label>
<?php
if(!isset($_SESSION['adm']) && $_s['cmtspam']!=0)
{
	if($_s['cmtspam'] == 1)
	{
?>
<table align="center">
<tr>
  <td>
<img src="http://captchator.com/captcha/image/<?php echo session_id(); ?>" />
  </td>
  <td align="center">
  <p>Введите проверочный код</p>
<input type="text" id="captcha_val" name="answer" />
  </td>
</tr>
</table>
<?php
	}
}
?>
<input type="text" name="text<?php echo $_v['p']; ?>" value="" style="position:absolute;top:0px;left:-6550px;" />
<script>
	document.write('<i');
	document.write('nput type="text" id="<?php echo $_v['p']; ?>"');
	document.write(' name="<?php echo md5($_s['pass'] + $_v['p']); ?>" ');
	document.write('value="<?php echo time(); ?>">');
	document.getElementById('<?php echo $_v['p']; ?>').style.display = 'none';
</script>
<input type="submit" />
</form>
</p>