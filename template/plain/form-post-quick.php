<style>.quick_post div { background: #fff; } </style>
<script type="text/javascript" src="http://js.nicedit.com/nicEdit-latest.js"></script>
<script type="text/javascript" src="<?php echo $_s['url'].$_s['tpd']; ?>/javascript/form.js"></script>


<div class="shadow" style="background: #ffffcc; padding: 10px;font-family: Verdana;">
<form name="postfo" action="" method="POST" id="quick-post" onkeypress="ctrlEnter(event, this);">
<table width="100%" border="0">
	<tr>
	<td width="100%" style="padding-right:30px;">
<input type="hidden" name="act" value="ed">
<input type="hidden" name="p" value="">
<input type="hidden" name="template" value="">
<input type="hidden" name="comm" value="">
<input type="text" name="title" value="" style="width: 100%;"><br />
<textarea name="text" id="tx" style="width: 100%;height:200px;"></textarea><br />
	</td>
	<td>
<small style="color: #777;">
	<a href="<?php echo $_s['url']; ?>?/ed">запись</a>
	<a href="<?php echo $_s['url']; ?>?/ep">страница</a>
	<a href="<?php echo $_s['url']; ?>?/pg/.cats">категории</a>
	<a href="<?php echo $_s['url']; ?>?/pg/.blocks">блоки</a>
	<a href="<?php echo $_s['url']; ?>?/pg/.panel">установки</a>
	<a href="<?php echo $_s['url']; ?>?/lgout">выйти</a>
</small>
<input type="button" id="btn" onclick="ned();" value="NicEdit (+/-)"><br />
<input name="comments" id="chk" type="checkbox" value="1" checked="checked"> <?php echo $_l['commts']; ?><br />
<input name="draft" id="chk" type="checkbox" value="1"> черновик<br />
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
</select><select name="category">
<option value="">категория</option>
<?php
	if(isset($_loc['catid']))
	{
		foreach ($_loc['catid'] as $k=>$v)
		{
			$chk = in_array($k, $_loc['cat'][$_v['p']][0]) ? 'selected' : '';
			echo '<option value="'.$k.'" '.$chk.'>'.$v[1].'</option>';
		}
	}
?>
</select>
</div>
<input type="submit" value="Ctrl+Enter" style="width: 150px;"> 
	</td>
	</tr>
</table>

</form>
</div>

<div style="clear:both;">&nbsp;</div>
