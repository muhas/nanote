<div  class="micropost"><?php echo $pst['ed']; ?>
		<?php echo $pst['text']; ?>
	<div class="pcat">
		<?php echo catslist($pst['cats']); ?>
	</div>
</div>



<?php if(isset($_v['p'])) echo '<div id="navi"><a class="next" href="'.$nav['n']['lnk'].'">&laquo; '.$nav['n']['sub'].'</a> <a class="prev" href="'.$nav['p']['lnk'].'">'.$nav['p']['sub'].' &raquo;</a></div>'; ?>
