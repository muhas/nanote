<?php
// Category list

if (isset($_SESSION['adm']))
{

echo '<center><h2>Управление категориями</h2></center>';

// шаблоны
$d = dir($_s['tpd']);
$t = time();
$catn = 0;
$tpls = '';

$posttpl = posttemplates();

// сохранение
if(isset($_POST['save']))
{
	unset($_loc['catid']);

	foreach($_POST['cat'] as $k=>$v)
	{
		if(trim($v)) $_loc['catid'][$k] = array($v, $_POST['name_'.$k], $_POST['template_'.$k], 1, 1, 1, 1, 1, 0);
	}
	fsave($_POST['datadir'].'/.settings.local', 'w+', serialize($_loc));
}

echo '<form action="" method="post"><table class="settings"><td colspan=4 class="title"><center>Редактирование категорий</center></td>';

if(isset($_loc['catid']))
{

foreach($_loc['catid'] as $key=>$value)
{
	$templates = $tpls = '';
	$catn++;

	foreach($posttpl as $k=>$v)
	{
		@$chk = (stristr($k, $_loc['catid'][$key][2])) ? 'selected' : '';
		$templates .= '<option value="'.$k.'" '.$chk.'>'.$v.'</option>';
	}

	echo '<tr>
		<td class="row"><input name="cat['.$key.']" value="'.$value[0].'"></td>
		<td class="row"><input name="name_'.$key.'" value="'.$value[1].'"></td>
		<td class="row">
			<select name="template_'.$key.'">
			<option value="">шаблон по-умолчанию</option>
			'.$templates.'
			</select>
		</td>
		<td class="row"><a href="'.alk('de','cat_'.$key).'" onClick="return confirm(\'Подтвердите удаление категории ['.$value[1].']\')">удалить</a></td>
	</tr>';
}

}

foreach($posttpl as $k=>$v) $tpls .= '<option value="'.$k.'">'.$v.'</option>';

echo '<tr><td colspan=4 class="title"><center>Создать новую категорию</center></td></tr>
<tr>
		<td class="row"><input name="cat['.$catn.']" value=""></td>
		<td class="row"><input name="name_'.$catn.'" value=""></td>
		<td class="row">
			<select name="template_'.$catn.'">
			<option value="">шаблон по-умолчанию</option>
			'.$tpls.'
			</select>
		</td>
		<td class="row"></td>
</tr><tr><td colspan=4><br /><input name="save" type="hidden" value="on" /><input type="submit" /></td></tr></table></form>';


} ?>
