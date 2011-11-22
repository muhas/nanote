<dl class="cmt"><a name="cmt-<?php echo $i; ?>"></a>
<dt><strong onclick="nick('<?php echo $cmt['nick']; ?>')"><?php echo $cmt['nick'].$cmt['ip']; ?></strong> <em><a href="<?php echo plk($pst['id']).'#cmt-'.$i; ?>"><?php echo $cmt['date']; ?></a></em></dt>
<dd><p><?php if($s && $_s['avatar']) echo '<img src="http://gravatar.com/avatar/'.md5($cmt['mail']).'?s=35" id=ava>'; ?><?php echo $cmt['text']; ?></p></dd>
</dl>