<h2><?php echo $pst['title']; ?> <?php echo $pst['ed']; ?></h2>
<div class="pdate">
	<?php echo $date = isset($pst['datelink']) ? $pst['datelink'] : $pst['date']; ?>
	<div class="pcat">
		<?php echo catslist($pst['cats']); ?>
	</div>
</div>
<div class="ptxt">
	<?php echo $pst['text']; ?>
</div>


<div class="pcomm">
	<?php if($_s['cmton'] && $pst['comtn']>1) { ?><?php echo $_l['cmtlink']; ?> <?php if((@$pst['comtn']-2)>0){ echo @$pst['comtn']-2;};?> <?php } ?>, <?php echo $_s['aname']; ?> <a href="<?php echo $pst['link']; ?>">#</a>
</div>


