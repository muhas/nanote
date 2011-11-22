<style>
	.quick_post div { background: #fff; }
	.quick_panel small { display: block; color: #777; padding: 5px; }
	.qp { float: left; background: #ffffcc; padding: 10px; position: relative; padding: 10px; width: 568px; }
	.qp h3 { position: absolute; top: -20px; right: 0; color: #ccc; }
</style>
<script type="text/javascript" src="http://js.nicedit.com/nicEdit-latest.js"></script>
<script type="text/javascript" src="<?php echo $_s['url'].$_s['tpd']; ?>/javascript/form.js"></script>

<form name="postfo" action="" method="POST" id="quick-post" onkeypress="ctrlEnter(event, this);">

<div class="qp shadow">

<div class="quick_post">
<input type="hidden" name="act" value="ed">
<input type="hidden" name="p" value="">
<input type="hidden" name="template" value="">
<input type="hidden" name="comm" value="">
<input type="text" name="title" value=""><br />
<textarea name="text" id="tx" rows="10" cols="65"></textarea><br />
</div>

<div class="quick_panel">
<small>Режим быстрой записи, для создания записи с расширинными настройками, нажмите <a href="?/ed">сюда</a>.</small>
<span style="float: left; width: 49%">

<span style="padding: 5px;">
<select name="category">
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
</span><br />

	<input name="comments" id="chk" type="checkbox" value="1" checked="checked"> <?php echo $_l['commts']; ?><br />
	<input name="draft" id="chk" type="checkbox" value="1"> черновик<br />
</span>
<span style="float: left; width: 49%">
	<input type="button" id="btn" onclick="ned();" value="NicEdit (+/-)"><br />
	<input type="submit" value="Ctrl+Enter">
</span>
</div>

</div>

</form>

<div style="clear: both;"></div>