<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_s['enc']; ?>" />
<link rel="stylesheet" href="<?php echo $_s['url'].$_s['tpd']; ?>/style.css" type="text/css" />
<script type="text/javascript" src="http://js.nicedit.com/nicEdit-latest.js"></script>
<script type="text/javascript" src="<?php echo $_s['url']; ?>/javascript/microjs.js"></script>
<script type="text/javascript" src="<?php echo $_s['url'].$_s['tpd']; ?>/javascript/form.js"></script>
<style>
.title { background: #ddd; cursor: pointer; margin: 5px; border-radius: 4px; width: 97%; }
#options { width: 97%; margin: 5px; padding: 5px; border-left: 2px solid #ddd; }
input[type="file"] { width: 200px; }
</style>
<title><?php echo $_s['title']; ?></title>
<?php echo @$_intpl['inheader']; ?>
</head>
<body>
<div style="width: 650px; margin: 0 auto;">
<form name="postfo" method="post" enctype="multipart/form-data">
<input type="hidden" name="act" value="<?php echo $_v['act']; ?>">
<input type="hidden" name="p" value="<?php echo @$_v['p']; ?>">
<input type="text" name="title" value="<?php echo htmlspecialchars($pst['title']); ?>"><br />

<?php if($_v['act']=='bk'): ?>
<span style="padding: 5px;">
<select name="place">
<option value="<?php echo @$cplace; ?>">место</option>
<?php
		foreach ($place as $k=>$v)
		{
			$chk = (isset($cplace) && $cplace==$v) ? 'selected' : '';
			echo '<option value="'.$v.'" '.$chk.'>'.$v.'</option>';
		}
?>
</select>
<select name="sort">
<option value="">позиция</option>
<option value="up">↑ поднять</option>
<option value="down">↓ опустить</option>
</select>
</span>
<?php endif; ?>

<?php if($_v['act']=='ed'): ?>
<span style="padding: 5px;">
<select name="category[]">
<option value="">категория</option>
<?php
	if(isset($_loc['catid']))
	{
		foreach ($_loc['catid'] as $k=>$v)
		{
			$chk = (isset($_v['p']) && in_array($k, $_loc['cat'][$_v['p']][0])) ? 'selected' : '';
			echo '<option value="'.$k.'" '.$chk.'>'.$v[1].'</option>';
		}
	}
?>
</select>
</span>
<?php endif; ?>

<textarea name="text" id="tx" rows="15" cols="65"><?php echo @$pst['text']; ?></textarea><br />
<input type="button" id="btn" onclick="ned();" value="NicEdit (+/-)"><br />
<?php if($_v['act']=='ed'): ?>
<div class="option">
<select name="template">
<option value="">шаблон записи</option>
<?php
		// шаблоны
		$d = dir($_s['tpd']);

		while (false !== ($et = $d->read()))
		{
			if (stristr($et, 'post-') && (!stristr($et, 'default') && !stristr($et, 'full') && !stristr($et, 'form')))
			{
				$chk = (isset($_v['p']) && @$_loc['template'][$_v['p']]) ? 'selected' : '';
				echo '<option value="'.$et.'" '.$chk.'>'.str_replace('.php', '', $et).'</option>';
			}
		}
?>
</select>
</div>

<div class="option"><input name="comments" id="chk" type="checkbox" value="1" <?php if(@$_loc['comments'][$_v['p']] || !@trim($_v['p'])) echo 'checked'; ?>> <?php echo $_l['commts']; ?></div>

<div class="option"><input name="draft" id="chk" type="checkbox" value="1" <?php if(@$_loc['draft'][$_v['p']]) echo 'checked'; ?>> черновик</div>

<div class="option">
<span class="tip">Дата<sup>?</sup><span>Если укажете дату в будущем, то сделаете отложенную публикацию. Формат d-m-Y H:i:s</span></span><input name="dated" type="text" value="<?php echo isset($_v['p']) ? date('d-m-Y H:i:s', $pst['timestamp']) : date('d-m-Y H:i:s', time() + ($_s['tmset'] * 3600)); ?>" style="width: 100px;"></div>

<div style="clear: both;"></div>

<div class="title" align="center" onclick="toggle('comment')"> <?php echo $_l['commts']; ?> </div>
<div id="comment" style="display: none;">
<textarea name="comm" rows="7" cols="65"><?php echo @htmlspecialchars(implode("\n", $pst['commts'])); ?></textarea>
</div>

<?php endif; ?>

<div class="title" align="center" onclick="toggle('upload')"> загрузить </div>
<div id="upload" style="display: none;">
<div id='nefls'><input name="nefis[]" id="fl1" onchange="pf(this.value);" type="file"> <a onClick="nefl()" style="cursor: pointer;">[+]</a>  <a onClick="document.forms.postfo.fl1.value=''" style="cursor: pointer;">[-]</a></div>
</div>

<div class="title" align="center" onclick="toggle('options')"> дополнительно </div>
<div id="options" style="display: none;">
<?php if($_v['act']=='ed'): ?>
<span>
	Своя ссылка на запись<br>
	<input type="text" name="alias" value="<?php echo @$_loc['customurl'][$_v['p']]; ?>">
</span>
<?php endif; ?>
<?php
	if(isset($extra_options))
	{
		foreach($extra_options as $k=>$v)
		{
?>
<span>
	<?php echo $v['title']; ?><br>
	<input type="text" name="input_<?php echo $k; ?>" value="<?php echo @$_loc['input_'.$k][$_v['p']]; ?>">
</span>
<?php
		}
	}
?>
</div>

<input type="submit">
</form>
</div>
</body>