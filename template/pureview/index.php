<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_s['enc']; ?>" />
<link rel="stylesheet" href="<?php echo $_s['url'].$_s['tpd']; ?>/style.css" type="text/css" />
<link rel="alternate" type="application/rss+xml" title="<?php echo $_s['bname']; ?>" href="<?php echo $_lk['rsslink']; ?>" />
<title><?php echo $_s['title']; ?></title>
<?php /*<!--system.meta-block-tpl-%content%/-system.meta-block-tpl-->*/ ?>
<?php blocks('system.meta'); ?>
<?php echo $_intpl['inheader']; ?>
</head>
<body>
<div id="main">

<div id="content" class="right">
	<!-- google_ad_section_start -->
	<?php blog(); ?>
	<!-- google_ad_section_end -->
	<p class="docdate"><em><?php echo $doc['date']; ?></em></p>
	<div class="paging"><?php echo $_s['pglk']; ?></div>
</div>

<div id="header">
<?php /*
<!--header-block-tpl-
<span class="block">%edit%%content%</span>
/-header-block-tpl-->
*/ ?>
<?php blocks('header'); ?>
</div>

<div id="column" class="left">
<?php /*
<!--sidebar-block-tpl-
<div class="block">
	<h2>%title%</h2>
	<p>%edit%%content%</p>
</div>
/-sidebar-block-tpl--> */ ?>
<?php blocks('sidebar'); ?>
</div>

<div style="clear: both;"></div>

<div id="footer">
<?php /*
<!--footer-block-tpl-
<span class="block">%edit%%content%</span>
/-footer-block-tpl-->
*/ ?>
<?php blocks('footer'); ?>
</div>

</div>

<!-- js скрипты в конце, оптимизация загрузки -->
<script type="text/javascript" src="<?php echo $_s['url']; ?>javascript/microjs.js"></script>

<?php echo $_intpl['infooter']; ?>
</body>
</html>
