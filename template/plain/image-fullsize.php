<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_s['enc']; ?>" />
<title><?php echo $_s['title']; ?></title>
<style>
body { background: #e8e8df; }
.fullimage img { padding: 15px; background: #fff; }
</style>
<?php echo $_intpl['inheader']; ?>
</head>
<body>

<center>

<table border="0" class="fullimage">
<tr><td></td><td></td><td></td></tr>
<tr><td></td><td><img src="<?php echo $_v['fullsize']; ?>" border="0" alt="<?php echo $_s['title']; ?>" /></td><td></td></tr>
<tr><td></td><td></td><td></td></tr>
</table>

</center>

<!-- js скрипты в конце, оптимизация загрузки -->
<script type="text/javascript" src="<?php echo $_s['url']; ?>javascript/microjs.js"></script>

<?php echo $_intpl['infooter']; ?>
</body>
</html>