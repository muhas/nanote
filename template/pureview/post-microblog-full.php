<?php if(isset($_v['p'])) echo '<div id="navi"><a class="next" href="'.$nav['n']['lnk'].'">&laquo; '.$nav['n']['sub'].'</a> <a class="prev" href="'.$nav['p']['lnk'].'">'.$nav['p']['sub'].' &raquo;</a></div>'; ?>
<div class="post shadow">
<h2><a name="more" href="#more"><?php echo $pst['title']; ?></a> <?php echo $pst['ed']; ?></h2>
<div><?php echo $pst['text']; ?></div>
<div class="category"><?php echo catslist($pst['cats']); ?></div>
<div><?php if($_s['cmton'] && $pst['comtn']>1) { ?><?php echo $_l['cmtlink']; ?> <?php if((@$pst['comtn']-2)>0){ echo @$pst['comtn']-2;};?> <?php } ?> # <?php echo $date = isset($pst['datelink']) ? $pst['datelink'] : $pst['date']; ?>, <?php echo $_s['aname']; ?></div>
</div>