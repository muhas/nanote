<?php
//date_default_timezone_set('Europe/Moscow');

// определим домен
$domain = @str_replace(array('www.','-'), array('','_'), strtolower($_SERVER['HTTP_HOST']));
$_POST['datadir'] = 'data/'.$domain;

// установка домена
if(!is_dir($_POST['datadir']))
{
	mkdir($_POST['datadir']);
	chmod($_POST['datadir'], 0777);
	$_install = array('.panel', '.pages', '.login', '.blocks', '.cats', '.error404', '1254942727');

	foreach($_install as $_c)
	{
		copy('data/'.$_c, $_POST['datadir'].'/'.$_c);
		chmod($_POST['datadir'].'/'.$_c, 0777);
	}
}

// загрузка глобальных настроек
$_glob = unserialize(file_get_contents('data/.settings.blank.global'));

if(file_exists($_POST['datadir'].'/.settings.global'))
{
	$_glob = unserialize(file_get_contents($_POST['datadir'].'/.settings.global')) + $_glob;
}
// определится автоматически
$_s['url'] = '';

// формируем системные массивы
foreach($_glob as $k=>$v)
{
	$j = substr($k,0,2);
	if($j=='_l') $_l[substr($k,2)] = $v;
	if($j=='_s') $_s[substr($k,2)] = $v;
	if($j=='_d') $_d[substr($k,2)] = $v;
}

// запускаем сессию
session_start();

// если сохранение настроек
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['sett']) && (isset($_SESSION['adm']) || !trim($_s['pass']))) {

	// в поисках лучшего решения
	unset($_s['curl']);
	unset($_d['curl']);
	unset($_s['d']);
	unset($_s['pglk']);
	unset($_s['slf']);
	unset($_s['title']);
	unset($_s['plname']);
	unset($_s['lang']);
	unset($_s['sav_dir']);
	unset($_s['ver']);

	if(trim($_POST['_spass'])) $_POST['_spass'] = md5($_POST['_spass']);
	else $_POST['_spass'] = $_glob['_spass'];

	foreach($_POST as $k=>$v)
	{
		// плагинсы
		if(stristr($k, 'plugins_'))
		{
			$_glob['plugins'][str_replace('plugins_', '', $k)] = $v;
		}
		else $_glob[$k] = stripslashes(str_replace(array("\r", "\n"), array('',"\n"), $v));
	}

//$_loc['catid'][727] = array('goodcat', 'Хорошая категория', 'template', '1-отображать в списке категорий', '1-отображать в посте', '1-в таймлайне', '1-в rss', 'пароль');
//$_loc[cat][id] = array(727, 353);

	// статус плагинов
	if(isset($_glob['plugins']))
	{
		foreach($_glob['plugins'] as $pk=>$pv)
		{
			$_glob['plugins'][$pk] = isset($_POST['plugins_'.$pk]) ? 1 : 0;
		}
	}

	fsave($_POST['datadir'].'/.settings.global', 'w+', serialize($_glob));

	// копируем локальные настройки
	if(!file_exists($_POST['datadir'].'/.settings.local'))
	{
		copy('data/.settings.blank.local', $_POST['datadir'].'/.settings.local');
		chmod($_POST['datadir'].'/.settings.local', 0777);
	}

	// установка завершена
	header('Location: '.$_SERVER['HTTP_REFERER']);
}

// загрузка локальных настроек

// загрузка глобальных настроек
$lfile = file_exists($_POST['datadir'].'/.settings.local') ? $_POST['datadir'].'/.settings.local' : 'data/.settings.blank.local';
$_loc = unserialize(file_get_contents($lfile));

$_v = $_GET + $_POST;
$_c = $_COOKIE;

//
$_v['ver'] = 0.389;

// загрузка файлов
if(isset($_FILES['nefis']['name']))
{
	for ($i=0; $i<sizeof($_FILES['nefis']['name']); $i++)
	{
		if(trim($_FILES['nefis']['tmp_name'][$i]))
		{
			$name = strtolower(basename($_FILES['nefis']['name'][$i]));
			@copy($_FILES['nefis']['tmp_name'][$i], 'files/'.$name);
			$_upfiles[] = $name;
		}
	}
}

// null
$_v['pg'] = $_s['d'] = $pst['title'] = $_s['pglk'] = $_intpl['inheader']= $_intpl['infooter'] = '';

// избавляемся от magic quotes по-старинке
if(get_magic_quotes_gpc())
{
	foreach($_v as $k=>$v)
	{
		@$_v[$k] = stripslashes($v);
	}
}

// сообщения ошибок (todo: нужно вынести)
$_error[1] = 'Вы не можете использовать имя автора.';
$_error[2] = 'Неверный проверочный код.';
$_error[3] = 'Введите проверочный код.';
$_error[4] = 'В сообщении недопустимые слова.';
$_error[5] = 'Отправить пустое сообщение? :)';
$_error[6] = 'Похоже на дубль сообщения :(';
$_error[7] = 'Название должно быть больше 3 символов и состоять не только из цифер!';

$_success[1] = 'Комментарий опубликован!';
$_success[2] = 'Первая установка Nanote.';
$_success[3] = 'Новые настройки успешно записаны.';

$_title[1] = $_s['bname'].' - архив';
$_title[2] = $_s['bname'].' - страница';

// адреса для сабмита sitemap
$_urls['sitemap'] = explode("\n", $_s['urlsitemaptx']);

// адреса пинга
$_urls['ping'] = explode("\n", $_s['urlpingtx']);

// наличие .htaccess в директории - ЧПУ включены
if(is_file('.htaccess')) $_s['curl'] = 1;

// установка глобальных настроек
$_s['slf'] = $_SERVER['PHP_SELF'];
$_lk['rsslink'] = rsslink();

// попытка определить base url
if(!trim($_s['url'])) $_s['url'] = 'http://'.$_SERVER['HTTP_HOST'] . str_replace('index.php','',$_s['slf']);

$s = $_SERVER['QUERY_STRING'];
$e = explode('/', $s);

if(isset($e[1]) && is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$e[1]))
{
	$s = str_replace($e[1].'/','',$s);
}

if(substr($s, (strlen($s)-1), 1)=='/') $s = substr($s, 0, (strlen($s)-1));
$rq = explode($_s['px'], substr($s,1));

// вах, прямой алиас, дорогу! :)
if(isset($rq) && isset($_loc['alias'][$rq[0]]))
{
	$rq[1] = $_loc['alias'][$rq[0]];
	$rq[0] = 'p';
	$direct = 1;
}

// это не просто прямой алиас, а свой кастомная ссылка
if(isset($_loc['customurl'][$_SERVER['QUERY_STRING']]) || isset($_loc['customurl'][substr($_SERVER['QUERY_STRING'],1)]))
{
	$rq[0] = 'p';
	$rq[1] = isset($_loc['customurl'][$_SERVER['QUERY_STRING']]) ? $_loc['customurl'][$_SERVER['QUERY_STRING']] : $_loc['customurl'][substr($_SERVER['QUERY_STRING'],1)];
	$direct = 1;
}

// парсим запрос (кашмар на улице вязов)
if(isset($rq) && !isset($direct))
{
	if(isset($rq[3]) && is_numeric($rq[2]) && is_numeric($rq[1]) && is_numeric($rq[0]))
	{
		$e = explode(':',$rq[3]);
		$rq[1] = mktime($e[0],$e[1],$e[2],$rq[1],$rq[2],$rq[0]);
		$rq[0] = 'p';
	}
	else if(isset($rq[2]) && isset($_loc['alias'][$rq[2]]))
	{
		$rq[0] = 'p'; $rq[1] = $_loc['alias'][$rq[2]];
	}
	else if((isset($rq[1]) && is_numeric($rq[1]) && strlen($rq[1]) == 2 && $rq[0] != 's') || strlen($rq[0]) == 4)
	{
		if(isset($rq[2]) && !is_numeric($rq[2]) && !isset($_loc['alias'][$rq[2]]))
		{
			$_v['pg'] = '.error404';
		}
		else
		{
			$y=(isset($rq[0]) ? $rq[0] : date('Y'));
			$m=(isset($rq[1]) ? $rq[1] : 12);
			$dim=days_in_month($m, $y);
			$mon = (isset($rq[1]) ? $rq[1] : 1);
			$day1 = (isset($rq[2]) ? $rq[2] : 1);

			// меняем заголовок архива и прочее сео
			$_metatitle = $_title[1].' '.implode('-',$rq);
			$_intpl['inheader'] .= '<meta name="robots" content="noindex,follow,noodp,noydir" />'."\n";

			$_s['t_start'] = mktime(0, 0, 1, $mon, $day1, (int) $y) + ($_s['tmset'] * 3600);
			$day2 = (isset($rq[2]) ? $rq[2] : $dim);
			$_s['t_end'] = mktime(23, 59, 59, $m, $day2, (int) $y) + ($_s['tmset'] * 3600);
		}
		unset($rq);
	}
}

// первая установка?
if(!trim($_s['pass']))
{
	$rq[0] = 'pg';
	$rq[1] = '.panel';

	// javascript-уведомление
	@$_s['pglk'] .=
	'<script>
		var notify_msg = "' . $_success[2] . '";
	</script>';
}

// парсим шаблоны
$et = explode('-tpl-', $main_template = file_get_contents($_s['tpd'].'/index.php'));
$se = sizeof($et);
for($i=0;$i<$se;$i++)
{
	if($i%2)
	{
		$bn = explode('/-',$et[$i]);
		$_tplin[substr($bn[1],0,strlen($bn[1]))] = $bn[0];
	}
}

if(isset($_v['ppp'])) $_s['ppp']=$_v['ppp'];
else if(@trim($rq[0]))
{
	if($rq[0]=='s')
	{
		$_v['sp'] = $rq[1];

		if(isset($rq[2]))
		{
		  if ($rq[2] == "t" ) {//nanote-git
		    $_v['act']='t';//nanote-git
		    $rq[1]=$rq[3];//nanote-git
		  } elseif ($rq[2] == "sw") {//nanote-git
		  	$_v['sw'] = urldecode($rq[2]);
			$_v['act']='sw';
		  }//nanote-git
		}

		// меняем заголовок и прочее сео
		$_metatitle = $_title[2].' '.ceil($_v['sp']/$_s['ppp']+1).' '.@$rq[2];
		$_intpl['inheader'] .= '<meta name="robots" content="noindex,follow,noodp,noydir" />'."\n";
	}
	else if($rq[0]=='ep') { $_v['act']=$rq[0]; $_v['pg']=@$rq[1]; }
	else if($rq[0]=='bk') { $_v['act']=$rq[0]; $_v['p']=@$rq[1]; }
	else if($rq[0]=='p') $_v['p']=$rq[1];
	else if($rq[0]=='sw') { $_v['sw']=urldecode($rq[1]); $_v['act']='sw'; }
	else if($rq[0]=='pg') $_v['pg']=str_replace('-','_',$rq[1]);
	else if($rq[0]=='unban') $_v['act']='unban';
	else if(isset($rq[1]) && trim($rq[1])) { $_v['act']=$rq[0]; $_v['p']=$rq[1]; }
	else $_v['act']=$rq[0];
}

	function fsave($f,$m,$t) {
		$fh = fopen($f, $m);
		flock($fh, LOCK_EX);
		fwrite($fh, $t);
		flock($fh, LOCK_UN);
		fclose($fh);
		@chmod($f, 0777);
	}

	function _get($s, $a, $b) {
		$z = strpos ($s, $a);
		if ($z !== false) {
			$z += strlen ($a);
			$y = strpos ($s, $b);
			if ($y !== false) return substr ($s, $z, $y - $z);
		}
		return false;
	}

	function getposts() {
	global $_loc, $_s;

		$d = dir($_POST['datadir']);

		while (false !== ($en = $d->read()))
		{
			// циферное имя файла, это запись
			if(is_numeric($en))
			{
				// проверяем не скрыта ли (черновик) и не отложенная ли публикация
				$allow = ((!@$_loc['draft'][$en] && $en < time()) || isset($_SESSION['adm'])) ? 1 : 0;

				if($allow)
				{
					// если фильтруем по времени
					if(isset($_s['t_start']) && isset($_s['t_end']))
					{
						if(($en >= $_s['t_start']) && ($en <= $_s['t_end'])) $psts[] = $en;
					}
					else $psts[] = $en;
				}
			}
		}
		$d->close();

		if(isset($psts)) return $psts;
		else return false;
	}


	function posttemplates() {
	global $_loc, $_s;

		$d = dir($_s['tpd']);
		while (false !== ($et = $d->read()))
		{
			if (stristr($et, 'post-') && (!stristr($et, 'default') && !stristr($et, 'full')))
			{
				$posttpl[$et] = str_replace('.php', '', $et);
			}
		}

		return $posttpl;
	}

	// элементарная генерация ключей
	function wordsrate($text, $size=6)
	{
		$text = str_replace(array('.',"\n"), ' ', $text);
		$text = preg_replace('| +|', ' ', clean(strip_tags($text)));
		$words = explode(' ', $text);

		$num = 0;
		$cword = array();

		foreach($words as $word)
		{
			$len = function_exists('mb_strlen') ? mb_strlen($word, 'UTF-8') : strlen($word);

			if($len > 4)
			{
				$num++;
				$cword[] = function_exists('mb_strtolower') ? mb_strtolower($word, 'UTF-8') : strtolower($word);
				if($num>$size) break;
			}
		}

		return $cword;
	}

/**
 * Description of Image_Processor
 *
 * @author mrak, http://pnk.pp.ua
 */
class Image_Processor {

	protected $contentType = null;
	protected $img = null;
	protected $tmpname = null;

	protected function getMime($fileName) {

	return mime_content_type($fileName);
	}

	public function __construct($fileName) {
	// если файл у нас загружен
	if (file_exists('files/'.$fileName))
	{
		$fileName = 'files/'.$fileName;
	}
	else
	{
		// временно копируем удаленное изображение
		$tmpexp = explode('/', $fileName);
		$this->tmpname = 'files/'.$tmpexp[sizeof($tmpexp)-1];
		copy($fileName, $this->tmpname);
		$fileName = $this->tmpname;
	}

	if (is_readable($fileName)) {

		if(stristr($fileName,'png')) $this->contentType = 'image/png';
		if(stristr($fileName,'jpg') || stristr($fileName,'jpeg')) $this->contentType = 'image/jpeg';
		if(stristr($fileName,'gif')) $this->contentType = 'image/gif';

		switch ($this->contentType) {
		case 'image/png':
			$img = imagecreatefrompng($fileName);
			break;
		case 'image/jpeg':
			$img = imagecreatefromjpeg($fileName);
			break;
		case 'image/gif':
			$img = imagecreatefromgif($fileName);
			break;
		default:
			throw new Exception('Wrong content type "' . $this->contentType . '"');
			break;
		}
		if (!is_resource($img)) {
		throw new Exception('Could not read image');
		}
		$this->img = $img;
	} else {
		throw new Exception('Cannot read file "' . $fileName . '"');
	}
	}

	public function resample($newwidth, $newheight, $newFileName) {
		global $_s, $_upfiles;

		// имя превьюхи если файл удаленный
		if (!file_exists('files/'.$newFileName))
		{
			$tmpexp = explode('/', $newFileName);
			$newFileName = $tmpexp[sizeof($tmpexp)-1];
		}

		if($this->tmpname != null && !$_s['grubimg'])
		{
			unlink($this->tmpname);
		}
		else
		{
			$_upfiles[] = str_replace(array('/','files'), '', $this->tmpname);
		}

		$height = imagesy($this->img);
		$width = imagesx($this->img);

		$k = min($newheight / $height, $newwidth / $width);
		if ($k >= 1) {
			$k = 1;
		}
		$newwidth = $k * $width;
		$newheight = $k * $height;

		$thumb = imagecreatetruecolor($newwidth, $newheight);

		imagecopyresampled($thumb, $this->img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

		// название файла миниатюры
		$rname = 'files/thumb_'.$newFileName;

		switch ($this->contentType) {
			case 'image/png':
			imagepng($thumb, $rname);
			break;
			case 'image/jpeg':
			imagepng($thumb, $rname);
			break;
			case 'image/gif':
			imagepng($thumb, $rname);
			break;
			default:
			throw new Exception('DEATH');
			break;
		}
	}
}

	// импорт rss (возможен из локального файла)
	function imp_rss($r)
	{
	if(@$f = file_get_contents($r))
	{
		$ex=explode('<item>',$f);

		for($i=1; $i<sizeof($ex); $i++)
		{
				$_rss['title'] = _get($ex[$i],'<title>','</title>');
				$_rss['text'] = _get($ex[$i],'<description>','</description>');
				$_rss['time'] = strtotime(_get($ex[$i],'<pubDate>','</pubDate>'));

				foreach($_rss as $k=>$v) $_rss[$k] = str_replace(array('<![CDATA[',']]>'),'',$v);

				$_rss['text'] = strtr($_rss['text'], array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
				fsave($_POST['datadir'].'/'.$_rss['time'],'w+',$_rss['title']."\n".str_replace(array("\r","\n"),'',$_rss['text'])."\n[comments]\n");
		}

		return 'Всего импортировано ['.(sizeof($ex)-1).'] записей.';
	} else return 'Канал недоступен';
	}

	function _curl($url, $time, $nobody=true, $ref=false) {

		if(function_exists('curl_init') && $ch = curl_init())
		{
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
			if($nobody) curl_setopt($ch, CURLOPT_HEADER, false);
			if($nobody) curl_setopt($ch, CURLOPT_NOBODY, true);

			if($ref) curl_setopt($ch, CURLOPT_REFERER, $ref);
			curl_setopt($ch, CURLOPT_TIMEOUT, $time);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);

			curl_exec($ch);
			curl_close($ch);

			return true;
		}
		else return false;
	}

	function ping($url, $blogname, $blogurl) {
		global $_s;

		$blogname = $blogname ? $blogname : $_s['bname'];

		$tb_send='<?xml version="1.0"?>
		<methodCall>
				<methodName>weblogUpdates.ping</methodName>
				<params>
					<param>
						<value>'.$blogname.'</value>
					</param>
					<param>
						<value>'.$blogurl.'</value>
					</param>
				</params>
		</methodCall>';

		@$host = explode('/', str_replace('http://', '', $url), 2);

		$tb_sock = fsockopen($host[0], 80);
		fputs($tb_sock, "POST /" . @$host[1] . " HTTP/1.1\r\n");
		fputs($tb_sock, "User-Agent: Nanote\r\n");
		fputs($tb_sock, "Host: " . $host[0] . "\r\n");
		fputs($tb_sock, "Content-Type: text/xml\r\n");
		fputs($tb_sock, "Content-length: " . strlen($tb_send) . "\r\n");
		fputs($tb_sock, "Connection: close\r\n\r\n");
		fputs($tb_sock, $tb_send);

		while (!feof($tb_sock))
		{
			@$response .= fgets($tb_sock, 128);
		}

		fclose($tb_sock);
		strpos($response, '<error>0</error>') ? $return = true : $return = $response;
		return $return;
	}

	function days_in_month($a_month, $a_year) {
		return date('t', strtotime($a_year . '-' . $a_month . '-01'));
	}

	function rus2lat($str) {
		// стандарт транслитерации взят из плагина для WP RusToLat, автор Andrey Serebryakov
		$iso = array(
			"Є"=>"YE","І"=>"I","Ѓ"=>"G","і"=>"i","№"=>"","є"=>"ye","ѓ"=>"g",
			"А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
			"Е"=>"E","Ё"=>"YO","Ж"=>"ZH","Ї"=>"I",
			"З"=>"Z","И"=>"I","Й"=>"J","К"=>"K","Л"=>"L",
			"М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R",
			"С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"X",
			"Ц"=>"C","Ч"=>"CH","Ш"=>"SH","Щ"=>"SHH","Ъ"=>"'",
			"Ы"=>"Y","Ь"=>"","Э"=>"E","Ю"=>"YU","Я"=>"YA",
			"а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
			"е"=>"e","ё"=>"yo","ж"=>"zh","ї"=>"i",
			"з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"x",
			"ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"shh","ъ"=>"",
			"ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
			"«"=>"","»"=>"","—"=>"",' '=>'_','"'=>'','@'=>'',
			'^'=>'','|'=>'','.'=>'','<'=>'','>'=>'','…'=>''
		);
		return strtolower(strtr($str,$iso));
	}

	function resizeimg($arNextMatch)
	{
	global $_s;

		$img = $arNextMatch[0];
		preg_match("/src=\"(.*)\/(.*?)\"/i", $img, $arMatch);
		$pefix = $arMatch[1];
		$src = $arMatch[2];

		// проверяем на удаленность
		if (!file_exists('files/'.$src))
		{
			$src = $pefix . '/' . $src;
		}

		$imgProc = new Image_Processor($src);
		$imgProc->resample($_s['thumbsize'], $_s['thumbsize'], $src);

		if($_s['grubimg'])
		{
			$tmpexp = explode('/', $src);
			$arNextMatch[0] = str_replace($src, '/files/'.$tmpexp[sizeof($tmpexp)-1], $arNextMatch[0]);
		}

		return $arNextMatch[0];
	}

	function replacethumbs($arNextMatch)
	{
	global $_s;

		$img = $arNextMatch[0];
		preg_match("/src=\"(.*)\/(.*?)\"/i", $img, $arMatch);
		preg_match("/src=\"(.*)\.(.*?)\"/i", $img, $flMatch);

		$pefix = $arMatch[1];
		$src = $arMatch[2];

		$tmpexp = explode('/', $src);
		$filename = $tmpexp[sizeof($tmpexp)-1];

		$img = str_replace($pefix.'/', '', $img);
		$img = str_replace($src,  $_s['url'].'files/thumb_'.$src, $img);

		if(!stristr($pefix,'http')) $link = $_s['imgtpl'] ? imglk($_s['url'].'files/'.$src) : $_s['url'].'files/'.$src;
		else $link = $_s['imgtpl'] ? imglk($flMatch[1].'.'.$flMatch[2]) : $flMatch[1].'.'.$flMatch[2];

		if(file_exists('files/'.$filename)) $link = $_s['imgtpl'] ? imglk($_s['url'].'files/'.$src) : $_s['url'].'files/'.$src;

		return '<a href="'.$link.'" rel="nofollow" class="fullsizeimg">'.$img.'</a>';
	}

	function plk($p) {
	global $_s, $_loc;

		$x = isset($_s['curl']) ? '' : '?/';
		if(isset($_loc['customurl'][$p])) { $l = $_loc['customurl'][$p]; }
		else if(isset($_loc['alias'][$p])) $l = date('Y/m/',$p).$_loc['alias'][$p];
		else $l = date('Y/m/d/H:i:s',$p).'';

	return $_s['url'].$x.$l;
	}

	function pgk($p) {
	global $_s;
		return isset($_s['curl']) ? $_s['url'].'pg/'.$p : $_s['url'].'?/pg/'.$p;
	}

	function alk($p,$a=false) {
	global $_s, $_v;
		$a = $a ? '/'.$a : '';
	return isset($_s['curl']) ? $_s['url'].$p.$a : $_s['url'].'?/'.$p.$a;
	}

	function slk($p, $sw=false) {
	global $_s, $_v;
	  global $rq; //muhas категории new
	  if (isset($rq[2]) && $rq[2] == "t") {
	    $a = '/t/'.$rq[1]; //muhas
	    return isset($_s['curl']) ? $_s['url'].'s/'.$p.$a : $_s['url'].'?/s/'.$p.$a;  //muhas
	  }
	  if (isset($rq[0]) &&  $rq[0] == "t") { //muhas
	    $a = '/t/'.$rq[1]; //muhas
	    return isset($_s['curl']) ? $_s['url'].'s/'.$p.$a : $_s['url'].'?/s/'.$p.$a;  //muhas
	  }//muhas

		$a = isset($_v['sw']) ? '/'.$_v['sw'] : '';
	return isset($_s['curl']) ? $_s['url'].'s/'.$p.$a : $_s['url'].'?/s/'.$p.$a;
	}

	function swlk($p) {
	global $_s;
		$a = isset($_v['s']) ? '/s/'.$_v['s'] : '';
	return isset($_s['curl']) ? $_s['url'].'sw/'.$p.$a : $_s['url'].'?/sw/'.$p.$a;
	}

	function dtlk($p) {
	global $_s;
	return isset($_s['curl']) ? $_s['url'].$p : $_s['url'].'?/'.$p;
	}

	function ctlk($p) {
	global $_s, $_loc;
	$alias = $_loc['catid'][$p][0];
	return isset($_s['curl']) ? $_s['url'].'t/'.$p : $_s['url'].'?/t/'.$p;
	}

	function uslk($p, $mail) {
	global $_s;
	return $_s['url'].'?unsubscribe='.$p.'&mail='.$mail;
	}

	function rsslink() {
	global $_s;
	return alk($_s['rsstpl']);
	}

	function imglk($img) {
	global $_s;
	return $_SERVER['PHP_SELF'].'?fullsize='.$img;
	}

	// генерация ссылки на архив
	function datelinks($time, $sufx = '-', $hours = false)
	{
		// дата = ссылка на архив
		$d = date('d', $time);
		$m = date('m', $time);
		$y = date('Y', $time);
		$Hi = date('H:i', $time);

		$_hi = $hours ? ' '.$Hi : '';

		// генерируем ссылки на архив
		return '<a href="' . dtlk($y.'/'.$m.'/'.$d) . '" rel="nofollow">' . $d .
		'</a>'.$sufx.'<a href="' . dtlk($y.'/'.$m) . '" rel="nofollow">' . $m .
		'</a>'.$sufx.'<a href="' . dtlk($y) . '" rel="nofollow">' . $y . '</a> '.$_hi;
	}

	function cd($f) {
		return 'onClick="return confirm(\'Подтвердите удаление ['.htmlspecialchars($f).']\')"';
	}

	function postdata($from, $move = false)
	{
		global $_loc;

		if(isset($_loc['alias'][$from]))
		{
			if($move) $_loc['alias'][$_loc['alias'][$from]] = $move;
			unset($_loc['alias'][$_loc['alias'][$from]]);
		}
		if(isset($_loc['alias'][$from]))
		{
			if($move) $_loc['alias'][$move] = $_loc['alias'][$from];
			unset($_loc['alias'][$from]);
		}
		if(isset($_loc['customurl'][$from]))
		{
			if($move)
			{
				$_loc['customurl'][$_loc['customurl'][$from]] = $move;
				$_loc['customurl'][$move] = $_loc['customurl'][$from];
			}
			unset($_loc['customurl'][$_loc['customurl'][$from]]);
			unset($_loc['customurl'][$from]);
		}
		if(isset($_loc['subs'][$from]))
		{
			if($move) $_loc['subs'][$move] = $_loc['subs'][$from];
			unset($_loc['subs'][$from]);
		}
		if(isset($_loc['seelog'][$from]))
		{
			if($move) $_loc['seelog'][$move] = $_loc['seelog'][$from];
			unset($_loc['seelog'][$from]);
		}
		if(isset($_loc['template'][$from]))
		{
			if($move) $_loc['template'][$move] = $_loc['template'][$from];
			unset($_loc['template'][$from]);
		}
		if(isset($_loc['draft'][$from]))
		{
			if($move) $_loc['draft'][$move] = $_loc['draft'][$from];
			unset($_loc['draft'][$from]);
		}
		if(isset($_loc['comments'][$from]))
		{
			if($move) $_loc['comments'][$move] = $_loc['comments'][$from];
			unset($_loc['comments'][$from]);
		}
		if(isset($_loc['cat'][$from]))
		{
			if($move) $_loc['cat'][$move] = $_loc['cat'][$from];
			unset($_loc['cat'][$from]);
		}
	}

	function savepost($title = false, $text = false)
	{
		global $_loc, $_s, $_v, $_l, $_lk, $plugins, $pst, $extra_options, $_urls, $_upfiles;

		// сохранение
		if($text)
		{
			$_v['title'] = $title;
			$_v['text'] = $text;
		}

		if (!isset($_v['title']) || !isset($_v['text']))
		{
			// экстра поля
			@$extra_options = $_loc['fields'][$_loc['template'][$_v['p']]];

			if (isset($_v['p']))
			{
				$pst = ptinfo($_v['p']);
				$pst['text'] = htmlspecialchars(str_replace(array("\r", "\n"), array('',"\n"), $pst['text']), ENT_QUOTES);

				// перехват плагинами
				if(isset($plugins['template.form-post']))
				{
					rsort($plugins['template.form-post']);
					foreach($plugins['template.form-post'] as $func)
					{
						if(function_exists($func)) $func();
					}
				}

				include $_s['tpd'].'/form-post.php';
				exit();
			}
			else
			{
				// перехват плагинами
				if(isset($plugins['template.form-post']))
				{
					rsort($plugins['template.form-post']);
					foreach($plugins['template.form-post'] as $func)
					{
						if(function_exists($func)) $func();
					}
				}

				include $_s['tpd'].'/form-post.php';
				exit();
			}
		}
		else
		{
			if(!@trim($_v['p']))
			{
				$_v['p'] = time();
				$_new = true;
			}

			@$newdate = strtotime($_v['dated']);

			if(!isset($_new) && $newdate && $newdate != $_v['p'])
			{
				postdata($_v['p']);
				@unlink($_POST['datadir'].'/'.$_v['p']);
				$_v['p'] = $newdate;
			}

			if(isset($_new) && $newdate && $newdate > time())
			{
				$_v['p'] = $newdate;
			}

			if(trim($_v['title']))
			{
				$lat = str_replace('_','-',rus2lat(clean($_v['title'])));

				// защита от повторяющихся url (проблема одинаковых заголовков)
				$num = 2;
				while(isset($_loc['alias'][$lat.'-'.$num]) && $_v['p'] != $_loc['alias'][$lat.'-'.$num]) $num++;
				$lat = (isset($_loc['alias'][$lat]) && $_v['p'] != $_loc['alias'][$lat]) ? $lat . '-' . $num : $lat;

				$_loc['alias'][$lat] = $_v['p'];
				$_loc['alias'][$_v['p']] = $lat;
			}

			// кстомные url
			if(@trim($_v['alias']))
			{
				if(isset($_loc['customurl'][$_v['p']]))
				{
					unset($_loc['customurl'][$_loc['customurl'][$_v['p']]]);
					unset($_loc['customurl'][$_v['p']]);
				}
				$_loc['customurl'][$_v['alias']] = $_v['p'];
				$_loc['customurl'][$_v['p']] = $_v['alias'];
			}

			// комментарии вкл/выкл
			if(isset($_v['comments'])) $_loc['comments'][$_v['p']] = 1;
			else $_loc['comments'][$_v['p']] = 0;

			// другой шаблон
			$_loc['template'][$_v['p']] = isset($_v['template']) ? $_v['template'] : '';

			// черновик
			$_loc['draft'][$_v['p']] = isset($_v['draft']) ? 1 : 0;

			// категория(и)
			$_loc['cat'][$_v['p']] = array(explode(',', $_v['category']));

			// сохраняем поля
			foreach($_v as $ke=>$va)
			{
				if(strstr($ke, 'input_'))
				{
					@$_loc[$ke][$_v['p']] = $va;
				}
			}

			// ресайз
			if($_s['thumbson'])
			{
				$_v['text'] = preg_replace_callback("/<img(.*?)>/i", "resizeimg", $_v['text']);
			}

			// перехват плагинами
			if(isset($plugins['post.save']))
			{
				rsort($plugins['post.save']);
				foreach($plugins['post.save'] as $func)
				{
					if(function_exists($func)) $func();
				}
			}

			// прикрепленные файлы
			if(isset($_upfiles))
			{
				foreach($_upfiles as $vf) $_atfiles[$_v['p']][] = $vf;
			}

			@fsave($_POST['datadir'].'/'.$_v['p'],'w+',$_v['title']."\n".trim($_v['text']).(trim($_v['comm']) ? "[comments]\n" : '[comments]').trim($_v['comm'])."\n");
			@fsave($_POST['datadir'].'/.settings.local', 'w+', serialize($_loc));

			// если есть новые прикрепленные файлы
			if(isset($_atfiles))
			{
				if(is_file($_POST['datadir'].'/.se.files'))
				{
					$allfiles = unserialize(file_get_contents($_POST['datadir'].'/.se.files'));
				}

				$newfiles = isset($allfiles) ? ($_atfiles + $allfiles) : $_atfiles;
				@fsave($_POST['datadir'].'/.se.files', 'w+', serialize($newfiles));
			}

			// новая запись, ping, сабмит sitemap
			if(isset($_new))
			{
				if(isset($_urls['sitemap']) && trim($_urls['sitemap'][0]))
				{
					foreach($_urls['sitemap'] as $sp)
					{
						_curl(trim($sp).urlencode(alk('sitemap.xml')), 10);
					}
				}

				if(isset($_urls['ping']) && trim($_urls['ping'][0]))
				{
					foreach($_urls['ping'] as $pn)
					{
						ping(trim($pn), $_v['title'], $_lk['rsslink']);
					}
				}
			}

			if(!$text)
			{
				header('Location: '.plk($_v['p']));
			}
			else
			{
				return $_v['p'];
			}
		}
	}

	function ptinfo($ide)
	{
	global $_s, $_v, $_l, $_glob, $plugins, $_loc, $anons;

		$pst['ed']='';

		// проверяем не скрыта ли (черновик) и не отложенная ли публикация
		$allow = ((!@$_loc['draft'][$ide] && $ide < time()) || isset($_SESSION['adm'])) ? 1 : 0;

		if(!isset($ide) || !trim($ide) || !file_exists($_POST['datadir'].'/'.$ide) || !$allow) return 0;

		// категории
		if(isset($_loc['cat'][$ide]))
		{
			$pst['cats'] = $_loc['cat'][$ide];
		} else $pst['cats'] = '';

		// черновик, только автору
		if(@$_loc['draft'][$ide] && !$_SESSION['adm']) return 0;

		$inon = explode('[comments]',$dt = file_get_contents($_POST['datadir'].'/'.$ide));

		@list($pst['title'], $pst['raw']) = explode("\n", $dt, 2);
		@list($pst['title'], $pst['text']) = explode("\n", $inon[0], 2);

		$pst['fulltext'] = $pst['text'];
		$pst['id'] = $ide;
		$pst['timestamp'] = $ide + $_s['tmset'] * 3600;

		$tx = explode("\n", $pst['text']);

		$pst['link'] = plk($ide);

		if(@$_v['act'] != 'ed')
		{
			// субатомарная типографика :j
			// $pst['text'] = '<p>'.str_replace("\r\n\r\n", '</p><p>', trim($pst['text'])).'</p>';
			// пустой заголовок
			$pst['title'] = trim($pst['title']) ? trim($pst['title']) : $_l['nosubj'];

			// замена на миниатюры
			if($_s['thumbson'])
			{
				$pst['text'] = preg_replace_callback("/<img(.*?)>/i", "replacethumbs", $pst['text']);
			}

			// автозамена в записи
			if(trim($_s['replacetx']))
			{
				$replace = explode("\n", $_s['replacetx']);
				foreach ($replace as $v)
				{
					if(trim($v))
					{
						$ex = explode('=>', trim($v));
						$from = trim($ex[0]);
						$to = trim($ex[1]);

						if(!is_array($pst))
						{
							foreach($pst as $ptkey => $ptval) $to = str_replace('%'.$ptkey.'%', $ptval, $to);
						}

						$pst['text'] = preg_replace('/'.$from.'/ui', $to, $pst['text']);
					}
				}
			}

		}

		if(!isset($_v['p']))
		{
			// нашли ручной подкат (cut), разбиваем
			if($s = strpos($pst['text'], $_s['cut']))
			{
				$srt = substr($pst['text'], 0, $s);
			}
			// автоподкат если больше двух разрывов строки
			else if((sizeof($tx)>2) && $_s['autocut'])
			{
				$srt = $tx[0];
				$srt = preg_replace_callback("/<img(.*?)>/i", "replacethumbs", $srt);
			}

			if(isset($srt))
			{
				$pst['text'] = $srt.str_replace(array('%link%','%title%'), array($pst['link'], $pst['title']), $_l['more']);
			}
		}

		$pst['date'] = date('d-m-Y, H:i', $ide + $_s['tmset'] * 3600);

		// генерируем ссылки на архив
		$pst['datelink'] = datelinks($ide + $_s['tmset'] * 3600, '-', true);

		@$pst['commts'] = explode("\n", $inon[1]);

		//пересчет комментариев, считаем только открытые
		$comment_num = $pst['comtnhd'] = 0;

		if(!isset($_SESSION['adm']))
		{
			foreach($pst['commts'] as $v)
			{
				if(!stristr($v, '@@'.$_s['aname'])) $comment_num++;
				else $pst['comtnhd']++;
			}
		}
		else
		{
			$comment_num = sizeof($pst['commts']);
		}

		@$pst['comtn'] = $comment_num;

		if(isset($_SESSION['adm']))
		{
			$pst['ed']='<div class="edpanel"><a id="ed" href='.alk('ed',$pst['id']).' title="Редактировать">E</a> <a id="de" title="Удалить" href='.alk('de',$pst['id']).' '.cd($pst['title']).'>X</a></div>';
		}

		$pst['template'] = @$_loc['template'][$pst['id']] ? $_loc['template'][$pst['id']] : $_s['tpp'];
		//nanote-git
		if (!empty($_loc['cat'][$pst['id']][0][0]) && empty($_loc['template'][$pst['id']])) {
		  foreach ($_loc['catid'] as $v => $k) {
		    if  ($k[0] == $_loc['cat'][$pst['id']][0][0]) {
		     $pst['template'] = $_loc['catid'][$v+1][2] ? $_loc['catid'][$v+1][2] : $_s['tpp'];
		    }
		  }
		} 
		//nanote-git
		$pst['template'] = isset($_s['post.tpl.hold']) ? $_s['post.tpl.hold'] : $pst['template'];

		// перехват плагинами
		if(isset($plugins['post.get.'.$pst['id']]))
		{
			rsort($plugins['post.get.'.$pst['id']]);
			foreach($plugins['post.get.'.$pst['id']] as $func)
			{
				if(function_exists($func)) $pst = $func($pst);
			}
		}

		// перехват плагинами
		if(isset($plugins['post.get']))
		{
			rsort($plugins['post.get']);
			foreach($plugins['post.get'] as $func)
			{
				if(function_exists($func)) $pst = $func($pst);
			}
		}

		if($_s['autometa'])
		{
			$anons['title'] = $pst['title'];
			$anons['text'] = $pst['text'];
		}

	return $pst;
	}

	function pginfo($f) {
	global $_s, $plugins;

		$d['ed'] = $d['comtn'] = '';

		$f = str_replace('-', '_', $f);
		$fname = str_replace($_POST['datadir'].'/', '', $f);

		if(strstr($f, '.se') || !$fc = @file_get_contents($f)) return false;

		$e1 = explode('</'.$_s['pgt'].'>',$fc);
		$e2 = explode('<'.$_s['pgt'].'>',$e1[0]);

		$d['title'] = isset($e2[1]) ? $e2[1] : $fname;
		$d['text'] = isset($e1[1]) ? $e1[1] : $fc;
		$d['link'] = pgk(str_replace('_', '-', $fname));
		$d['timestamp'] = filemtime($f) + $_s['tmset'] * 3600;
		$d['date'] = date('d-m-Y', $d['timestamp']);
		$d['datexml'] = date('Y-m-d', $d['timestamp']);

		$d['template'] = $_s['tpp'];

		// перехват плагинами
		if(isset($plugins['page.get']))
		{
			rsort($plugins['post.get']);
			foreach($plugins['page.get'] as $func)
			{
				if(function_exists($func)) $d = $func($d);
			}
		}

	return $d;
	}

	function catslist($cats=false, $td='<a href="%link%" rel="nofollow">%name%</a>', $i=',') {
	global $_s, $_l, $_v, $_loc;

		if(is_array($cats))
		{
			foreach($cats as $v)
			{
				if(isset($_loc['catid'][$v[0]]))
				{
					$pst['cats_links'][] = str_replace(array('%link%', '%name%'), array(ctlk($v[0]), $_loc['catid'][$v[0]][1]), $td);
				}
			}
			if(isset($pst['cats_links'])) return implode($i, $pst['cats_links']);
		}

		return '';
	}

	function catlist($td='<a href="%link%" rel="nofollow">%name%</a>', $i='<br>') {
	global $_s, $_l, $_v, $_loc;

	if(isset($_loc['catid']))
	{
		foreach($_loc['catid'] as $k=>$v)
		{
			$_cats[] = str_replace(array('%link%', '%name%'), array(ctlk($k), $v[1]), $td);
		}
		if(isset($_cats)) return implode($i, $_cats);
	}

		return '';
	}

	function pglist($c=0, $td=0) {
	global $_s, $_l, $_v;

		$d = dir($_POST['datadir']);

		while (false !== ($et = $d->read()))
		{
			if (!is_numeric($et))
			{
				// если не системный и не директория, то обычная страница
				if(substr($et,0,1) != '.' && !is_dir($_POST['datadir'].'/'.$et) && $et != $_s['index']) $docs[filemtime($_POST['datadir'].'/'.$et)] = $et;
				// инклудим страницы-расширения
				else if(substr($et,0,2) == '._') include_once($_POST['datadir'].'/'.$et);
			}
		}

		$out=false;

		if(isset($docs))
		{
			krsort($docs);
			$dn=0;

			foreach($docs as $et)
			{
				if(!$c) $c = sizeof($docs);

				if($dn<$c)
				{
					$doc = pginfo($_POST['datadir'].'/'.$et);
					$dc = $td ? $td : $_l['pglist'];
					// оформляем шаблон
					if($doc)
					{
						foreach($doc as $k=>$v) $dc = str_replace('%'.$k.'%', $v, $dc);
						$dn++;
						$out .= $dc;
					}
				}
			}
		}

		$d->close();

	return $out;
	}

	// вывод блоков
	function blocks($place) {
	global $_s, $_l, $_lk, $_loc, $_tplin, $plugins;

		// если блок вообще существует
		if(isset($_loc['block'][$place]))
		{
			$blocks = $_loc['block'][$place];
			$template = $_tplin[$place . '-block'];

			ksort($blocks);

			foreach($blocks as $k=>$v)
			{

				$prepare = str_replace('%title%', $v[0], $template);
				$prepare = str_replace('%content%', $v[1], $prepare);

				// перехват плагинами
				if(isset($plugins['block.get']))
				{
					rsort($plugins['block.get']);
					foreach($plugins['block.get'] as $func)
					{
						if(function_exists($func)) $prepare = $func($prepare);
					}
				}

				// перехват плагинами
				if(isset($plugins['block.get.'.$place.'_'.$k]))
				{
					rsort($plugins['block.get.'.$place.'_'.$k]);
					foreach($plugins['block.get.'.$place.'_'.$k] as $func)
					{
						if(function_exists($func)) $prepare = $func($prepare);
					}
				}

				// не грузим тяжелые функции без надобности (на минутку: они парсят файлы)
				if(strstr($prepare, '%pglist%'))
				{
					$prepare = str_replace('%pglist%', pglist(8), $prepare);
				}

				if(strstr($prepare, '%cmtlist%'))
				{
					$prepare = str_replace('%cmtlist%', cmtlist(8), $prepare);
				}

				if(strstr($prepare, '%catlist%'))
				{
					$prepare = str_replace('%catlist%', catlist(), $prepare);
				}

				// массив системных переменных
				foreach($_s as $sk=>$sv) $prepare = str_replace('%'.$sk.'%', $sv, $prepare);
				// массив ссылок
				foreach($_lk as $lk=>$lv) $prepare = str_replace('%'.$lk.'%', $lv, $prepare);

				// скрытые секции
				if(isset($_SESSION['adm'])) $prepare = str_replace(array('<!--is_admin-', '-is_admin-->'), '', $prepare);

				// если админ: меню редактирования
				$prepare = isset($_SESSION['adm']) ? str_replace('%edit%', '<div class="edpanel"><a id="ed" href='.alk('bk', $place.'_'.$k).' title="Редактировать">E</a> <a id="de" title="Удалить" href='.alk('de', $place.'_'.$k).' '.cd($v[0]).'>X</a></div>', $prepare) : str_replace('%edit%', '', $prepare);

				// перехват плагинами
				if(isset($plugins['block.output']))
				{
					rsort($plugins['block.output']);
					foreach($plugins['block.output'] as $func)
					{
						if(function_exists($func)) $prepare = $func($prepare);
					}
				}

				echo $prepare;
			}
		}
	}

	// список последней активности (по обновлению записи, не комментариям!)
	function cmtlist($c=0, $td=0, $lm=0) {
	global $_s, $_l, $_loc;

		$d = dir($_POST['datadir']);
		while (false !== ($et = $d->read()))
		{
			if (substr($et,0,1) != '.' && is_numeric($et))
			{
				// проверяем не скрыта ли (черновик)
				$allow = (!@$_loc['draft'][$et] || isset($_SESSION['adm'])) ? 1 : 0;

				if($allow)
				{
					$cmts[filemtime($_POST['datadir'].'/'.$et)] = $et;
				}
			}
		}
		$d->close();

		if(isset($cmts))
		{
		krsort($cmts);
		$out=false;
		$dn=0;

		foreach($cmts as $et)
		{
			if(!$c) $c=10;
			$cmt = ptinfo($et);

			if(($cmt['comtn']-2)>0 && $dn<$c)
			{
				list($cmt['nick'], $cmt['ip'], $cmt['date'], $cmt['text']) = explode('»', $cmt['commts'][($cmt['comtn']-2)]);

				$cmt['cmtlink'] = $cmt['link'].'#cmt-'.($cmt['comtn']-2);
				$e = explode('<',$cmt['text']);
				$cmt['text'] = strip_tags($e[0]);
				$s = strpos($cmt['nick'], '@');

				if($s) $cmt['nick'] = substr($cmt['nick'],0,$s);

				$cmt['nick'] = (@trim($cmt['nick']) ? $cmt['nick'] : $_l['anonym']);
				$dc = $td ? $td : $_l['cmtlist'];

				foreach($cmt as $k=>$v) @$dc = str_replace('%'.$k.'%',$v,$dc);

				$dn++;
				$out .= $dc;
			}
		}

		}

	if(isset($out)) return $out;
	else return false;
	}

	// предыдущая/следующая запись
	function ptnext($p) {
	 global $_s, $_l;

		$allp = getposts();
		rsort($allp);

		$s = sizeof($allp);
		$nx['p']['sub'] = $nx['p']['lnk'] = $nx['n']['sub'] = $nx['n']['lnk'] = '';

		for($i=0;$i<$s;$i++)
		{
			if($allp[$i] == $p)
			{
				if(@$n = ptinfo($allp[$i+1]))
				{
					$nx['p']['sub'] = $n['title'];
					$nx['p']['lnk'] = $n['link'];
				}
				if(@$p = ptinfo($allp[$i-1]))
				{
					$nx['n']['sub'] = $p['title'];
					$nx['n']['lnk'] = $p['link'];
				}
			return @$nx;
			}
		}
	}

	function clean($t) {
		return str_replace(array('"','<','>','\"','$','(','(','*','+','%','$','#',':','[',']','&','!','?','~','=','+','№',':',';','{','}','`','/','\\',"\r","\n",',',')','\'','- ',' -'),'',$t);
	}

	// регулярка маркеров
	function retex($t) {
		return preg_replace('#\B(@[_а-яёА-ЯЁ0-9a-zA-Z]+)#u', '<a href="'.swlk('\\1').'" rel="nofollow">\\1</a>',$t);
	}

	// сохраняем введенный nick
	if(@trim($_v['nick']))
	{
		setcookie('nnk', $_v['nick'], time() + 604800);
	}

	// пароль правельный, открываем сессию
	if (isset($_v['passwd']) && md5($_v['passwd']) == $_s['pass'])
	{
		$_SESSION['adm'] = 1;
	}

// подложка, полноразмерное изображение
if(isset($_v['fullsize']))
{
	if(is_file($_POST['datadir'].'/.se.files'))
	{
		$allfiles = unserialize(file_get_contents($_POST['datadir'].'/.se.files'));
	}

	$_intpl['inheader'] .= '<meta name="robots" content="noindex,follow,noodp,noydir" />'."\n";

	$tmpexp = explode('/', $_v['fullsize']);
	$filename = $tmpexp[sizeof($tmpexp)-1];

	$_s['title'] = @$pst['title'] ? $pst['title'] : $filename;

	include $_s['tpd'].'/image-fullsize.php';
	exit();
}

// псевдоаякс для панели управления
if(isset($_v['majax']))
{
	header("Content-type: text/javascript");

	if($_v['majax'] == 'makethumbs')
	{
		$psts = getposts();
		$total = 0;

		if($psts && $pz = sizeof($psts))
		{
			rsort($psts);

			for ($i=0; $i<sizeof($psts); $i++)
			{
				$pst = ptinfo($psts[$i]);

				$images = explode('src', $pst['text']);

				// ресайз
				if($_s['thumbson'])
				{
					$pst['raw'] = preg_replace_callback("/<img(.*?)>/i", "resizeimg", $pst['raw']);
				}

				$total += sizeof($images)-1;
			}
		}

		$_msg = 'Пересоздано '.$total.' миниатюр.';
	}

	if($_v['majax'] == 'checkup')
	{

		if(!@$f = file_get_contents('http://code.google.com/feeds/p/nanote/downloads/basic'))
		{
			$_msg = 'Не удается соедениться.';
		}
		else
		{
			$ex = explode('<entry>', $f);

			foreach($ex as $entry)
			{
				$id = explode('<id>', $entry);

				if(strstr($id[1], 'nanote-v-'))
				{
					$vers = _get($id[1], 'nanote-v-', '.zip');
					$desc = trim(_get($id[1], '<content type="html">', '</content>'));
					$dex = explode("\n\n", str_replace('&lt;pre&gt;','',$desc));
					$info_link = trim(_get($id[1], '<link rel="alternate" type="text/html" href="', '" />'));
					$download_link = 'http://nanote.googlecode.com/files/nanote-v-'.$vers.'.zip';

					break;
				}
			}

			$_msg = 'Вы пользуетесь текущей стабильной версией <b>' . $_v['ver'] . '</b>';

			if($_v['ver']<$vers)
			{
				@$_msg = 'Доступна версия <b>' . $vers . '</b> : <a href="'.$link.'">скачать</a><hr>' . trim($dex[0]);
			}
			if($_v['ver']>$vers)
			{
				$_msg = 'Вы пользуетесь экспериментальной версией <b><a href="http://nanote.googlecode.com/svn/trunk/">' . $_v['ver'] . '</a></b> (стабильная - <a href="'.$info_link.'">'.$vers.'</a>)';
			}
		}
	}

	if($_v['majax'] == 'doping')
	{
		$pingsuccess = 0;

		if(isset($_urls['ping']))
		{
			foreach($_urls['ping'] as $pn)
			{
				if(ping(trim($pn), $_s['bname'], $_lk['rsslink']))
				{
					$pingsuccess++;
				}
			}
		}

		$_msg = 'Успешно пропинговано '.$pingsuccess.' сервисов.';
	}

	if($_v['majax'] == 'addsitemap')
	{
		$sitemapsuccess = 0;

		if(isset($_urls['sitemap']))
		{
			foreach($_urls['sitemap'] as $sp)
			{
				$sp = str_replace('%url%', urlencode(alk('sitemap.xml')), trim($sp));

				if(_curl($sp, 10))
				{
					$sitemapsuccess++;
				}
			}
		}

		$_msg = 'Отправлено '.$sitemapsuccess.' уведомлений о sitemap.';
	}

	exit('document.getElementById(\''.$_v['majax'].'\').innerHTML = \''.$_msg.'\'; ');
}

// отписка
if(isset($_v['unsubscribe']))
{
	if(in_array($_v['mail'], $_loc['subs'][$_v['unsubscribe']]))
	{
		$key = array_search($_v['mail'], $_loc['subs'][$_v['unsubscribe']]);
		unset($_loc['subs'][$_v['unsubscribe']][$key]);
		if(@fsave($_POST['datadir'].'/.settings.local', 'w+', serialize($_loc)))
		{
			header('Location: '.$_s['url']);
		}
	}
}

// заголовок по-умолчанию
$_s['title'] = $_s['bname'];

if(isset($_glob['plugins']))
{
	foreach($_glob['plugins'] as $plugin => $status)
	{
		if($status) include('plugins/'.$plugin);
	}
}

// перехват плагинами
if(isset($plugins['main']))
{
	rsort($plugins['main']);
	foreach($plugins['main'] as $func)
	{
		if(function_exists($func)) $func();
	}
}

// actions
if(isset($_v['act']))
{

	if(isset($plugins['action.'.$_v['act']]))
	{
		foreach($plugins['action.'.$_v['act']] as $func)
		{
			if(function_exists($func)) $func();
		}
	}


// универсальный rss
if(strstr($_v['act'], 'rss'))
{
	// пример добавления фидов. пока такой костыль :(
	//if($_v['act'] == "rss-feed")  header('Location:http://feeds.feedburner.com/plain-man/xumt');
	//if($_v['act'] == "rss-full")  header('Location:http://feeds.feedburner.com/plain-man/full');
	header('Content-type: application/xml');

	echo "<?xml version=\"1.0\" encoding=\"".$_s['enc']."\"?>\n<rss version=\"2.0\">\n<channel>\n<title>".$_s['bname']."</title>\n<link>".$_s['url']."</link>\n<description></description>\n<language>ru</language>\n";

	$_s['post.tpl.hold'] = $_v['act'];

	unset($_v['act']);
	blog();

	echo "\n</channel>\n</rss>";
	exit();
}

switch($_v['act']) {

case 't':
		$_v['act'] = $_v['pg'] = $_v['p'] = null;
		$tags = explode(',', $rq[1]);

		$_v['sw'] = $_v['act'] = null;

		// узнаем имена тегов/категорий
		for ($i=0; $i<sizeof($tags); $i++) $tnames[] = $_loc['catid'][$tags[$i]][1];

		foreach ($_loc['cat'] as $k=>$v)
		{
			foreach($v[0] as $incat)
			{	
				if(in_array($incat, $tags))
				{
					// проверяем не скрыта ли (черновик)
					$allow = ((!@$_loc['draft'][$k] && $k < time()) || isset($_SESSION['adm'])) ? 1 : 0;
					$psts[] = $k;
				}
			}
		}
		
		// javascript уведомление и подсветка найденного
		@$_s['pglk'] .=
			'<script>
				var search = \''.$_v['sw'].'\';
				var searchtpl = \'' . $_l['search'] . '\';
				var notify_msg = "Найдено ' . sizeof($psts) . ' записей.";
			</script>';

	// меняем заголовок и прочее сео
	$_s['title'] = $_s['bname'].' - '.implode(', ', $tnames);
	$_intpl['inheader'] .= '<meta name="robots" content="noindex,follow,noodp,noydir" />'."\n";

	include $_s['tpd'].'/index.php';
	exit();
break;

case 'ban':
	$ip = trim($_v['p']);

	if(strstr($_s['stop_word_ip'], $ip))
	{
		$_glob['_sstop_word_ip'] = str_replace(array(','.$ip, $ip), '', $_s['stop_word_ip']);
	}
	else
	{
		$_glob['_sstop_word_ip'] = $_s['stop_word_ip'].','.$ip;
	}

	fsave($_POST['datadir'].'/.settings.global','w+',serialize($_glob));

	header('Location: '.$_SERVER['HTTP_REFERER']);
break;

case 'lgout':
	if ($_SESSION['adm'])
	{
		unset($_SESSION['adm']);
		header('Location: '.$_s['url']);
	}
break;

case 'comm':
if(isset($_v['p']) && is_numeric($_v['p']) && file_exists($_POST['datadir'].'/'.$_v['p']))
{
	if ($_s['cmton'] || isset($_SESSION['adm']))
	{
		$postinfo = ptinfo($_v['p']);

		//
		for ($i=1; $i<($postinfo['comtn']-1)+$postinfo['comtnhd']; $i++)
		{
			list($cmt['nick'], $cmt['ip'], $cmt['date'], $cmt['text']) = explode('»', $postinfo['commts'][$i]);
			$md5[] = md5(trim($cmt['text']));
		}
		//

		if (@trim($_v['text']))
		{
			$exw = explode(',', $_s['stop_word_ip']);

			$txt = substr(trim($_v['text']), 0, 4096);
			@$nick = substr(clean(strip_tags($_v['nick'])), 0, 69);
			$_v['ip'] = getenv('REMOTE_ADDR');

			if(isset($_SESSION['adm']))
			{
				$nick = $_s['aname'];
			}
			else if(stristr($nick, $_s['aname']))
			{
				$_s['err'] = 1;
			}

			$txt = str_replace(array("\r", "\n",'»'), array("", "\r", '&raquo;'), $txt);

			// capcha
			if(!isset($_SESSION['adm']) && $_s['cmtspam']!=0)
			{
				// выбрана невидимка
				if($_s['cmtspam'] == 2)
				{
					$uniq = md5($_s['pass'] + $_v['p']);

					if (!trim($_v[$uniq]) || trim($_v['text'.$_v['p']]))
					{
						$_s['err'] = 2;
					}

				}

				// выбран капчатор
				if($_s['cmtspam'] == 1)
				{
					if (isset($_v['answer']) && $_v['answer'])
					{
						$answer = preg_replace('/[^a-z0-9]+/i', '', $_v['answer']);

						if (implode(file('http://captchator.com/captcha/check_answer/'.session_id().'/'.$answer)) != '1')
						{
							$_s['err'] = 2;
						}
					} else $_s['err'] = 3;
				}
			}

			if(!isset($_SESSION['adm']))
			{
				$txt = str_replace(array('<', '>'), array('&lt;', '&gt;'), $txt);
				$txt = str_replace('"', '&quot;', $txt);
			}

			$txt = str_replace("\r", "\n", $txt);
			$txt = str_replace("\n", ' <br /> ', $txt);

			// дубликат
			if(isset($md5) && in_array(md5(trim($txt)), $md5))
			{
				$_s['err'] = 6;
			}

			// проверка на стоп-слова
			foreach($exw as $v)
			{
				if($v = trim($v) && (stristr($txt.' '.$nick,$v) || strstr($_v['ip'],$v)))
				{
					$_s['err'] = 4;
				}
			}

			$mfrom = strstr($nick, '@') ? $nick : "noreply-$_s[email]";

			if((!isset($_s['err']) && $_loc['comments'][$_v['p']]) || isset($_SESSION['adm']))
			{

				// перехват плагинами
				if(isset($plugins['comment.save']))
				{
					rsort($plugins['comment.save']);
					foreach($plugins['comment.save'] as $func)
					{
						if(function_exists($func)) $func();
					}
				}

				fsave($_POST['datadir'].'/'.$_v['p'], 'a+',$nick.'»'.$_v['ip'].'»'.date('d-m-y, H:i', time() + $_s['tmset'] * 3600).'»'.$txt."\n");

				// email-уведомление автору
				if($_s['email'] && !isset($_SESSION['adm']))
				{
					mail($_s['email'], 'comment '.$nick,
					$nick." (".$_v['ip'].")\n---\n".$_v['text']."\n---\n".plk($_v['p']),
					'Content-Type: text/plain; charset='.$_s['enc']."\nFrom: ".$nick."<$mfrom>\r\n"
					);
				}

				// email-уведомления подписавшимся
				if(isset($_loc['subs'][$_v['p']]) && !isset($_SESSION['adm']))
				{
					@$partnick = explode('@', $nick);
					foreach($_loc['subs'][$_v['p']] as $mail)
					{
						if($mail != $nick)
						{
							mail($mail, 'comment '.$partnick[0],
							$partnick[0]."\n---\n".$_v['text']."\n---\nСсылка: ".plk($_v['p'])."\nОтписаться: ".uslk($_v['p'], $mail),
							'Content-Type: text/plain; charset='.$_s['enc']."\nFrom: ".$partnick[0]."<noreply-".$_s['email'].">\r\n"
						);
						}
					}
				}

				$_s['succ'] = 1;
				$_v['text'] = '';
			}
		} else $_s['err'] = 5;

		// уведомления
		$notify_msg = isset($_s['err']) ? $_error[$_s['err']] : $_success[$_s['succ']];

		// подписка на email-уведомления
		if((isset($nick) && strstr($nick, '@')) && !isset($_SESSION['adm']) && @!in_array($nick, $_loc['subs'][$_v['p']]))
		{
			$_loc['subs'][$_v['p']][] = $nick;
			@fsave($_POST['datadir'].'/.settings.local', 'w+', serialize($_loc));
		}

		// javascript-уведомление
		@$_s['pglk'] .=
		'<script>
			var notify_msg = "' . $notify_msg . '";
		</script>';
	}
}
break;

case 'de':
	if(isset($_SESSION['adm']))
	{
		// детект блока: left_1 : left - place, 1 - sort
		if(strstr($_v['p'], '_')) $xpos = explode('_', $_v['p']);

		// удаление блока
		if(isset($xpos) && isset($_loc['block'][$xpos[0]]))
		{
			array_splice($_loc['block'][$xpos[0]], $xpos[1], 1);
			fsave($_POST['datadir'].'/.settings.local', 'w+', serialize($_loc));
		// удаление записи/страницы
		}
		else if($xpos[0] == 'cat' && isset($_loc['catid'][$xpos[1]]))
		{
			unset($_loc['catid'][$xpos[1]]);
			fsave($_POST['datadir'].'/.settings.local', 'w+', serialize($_loc));
			header('Location: '.$_SERVER['HTTP_REFERER']);
		}
		else
		{
			//exit();
			@unlink($_POST['datadir'].'/'.$_v['p']);
			postdata($_v['p']);
			fsave($_POST['datadir'].'/.settings.local', 'w+', serialize($_loc));
		}

		header('Location: '.$_s['url']);
	}
break;

case 'ep':
if(isset($_SESSION['adm']))
{
if (@trim($n = $_v['title']) && @trim($t = $_v['text']))
{
		if($_v['p'])
		{
			$n = $_v['p'];
			$t = '<'.$_s['pgt'].'>'.$_v['title'].'</'.$_s['pgt'].'>'.$t;
		}
		else
		{
			$t = '<'.$_s['pgt'].'>'.$n.'</'.$_s['pgt'].'>'.$t;
			$n = rus2lat($n);//date('dMY_hmi');
		}

		// перехват плагинами
		if(isset($plugins['page.save']))
		{
			rsort($plugins['page.save']);
			foreach($plugins['page.save'] as $func)
			{
				if(function_exists($func)) $func();
			}
		}

		@fsave($_POST['datadir'].'/'.$n, 'w+', $t);
		header('Location: '.pgk($n));
}
else
{
	if(isset($_v['pg']) && trim($_v['pg']))
	{
		@$po = str_replace(array("\r", "\n"), array("", "\r"), htmlspecialchars(file_get_contents($_POST['datadir'].'/'.@$_v['pg']), ENT_QUOTES));
		@$pst['text'] = str_replace("\r", "\n", $po);
		$px = 1;
	}

	@$pst['title'] = $_v['p'] = $_v['pg'];

	if(isset($px))
	{
		$pst = pginfo($_POST['datadir'].'/'.$_v['p']);
	}

	// перехват плагинами
	if(isset($plugins['template.form-post']))
	{
		rsort($plugins['template.form-post']);
		foreach($plugins['template.form-post'] as $func)
		{
			if(function_exists($func)) $func();
		}
	}

	include $_s['tpd'].'/form-post.php';
	exit();
}
}
break;

case 'bk':
if(isset($_SESSION['adm']))
{
	// это редактирование
	if(isset($_v['p']))
	{
		// left_1 : left - place, 1 - sort
		$xpos = explode('_', $_v['p']);
		$cplace = $xpos[0];
		$csort = $xpos[1];

		// чтение блока
		if(isset($_loc['block'][$cplace][$csort][0]))
		{
			$pst['title'] = $_loc['block'][$cplace][$csort][0];
			$pst['text'] = $_loc['block'][$cplace][$csort][1];
		}
	}

	// сохранение
	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		@$blocksize = sizeof($_loc['block'][$_v['place']]) ? sizeof($_loc['block'][$_v['place']]) : 0;

		$csort = isset($csort) ? $csort : $blocksize;

		// перемещение блока в другую секцию, удаление старого
		if(isset($_v['p']) && isset($_v['place']) && $_v['place']!=$cplace)
		{
			$csort = $blocksize;
			$fsize = sizeof($_loc['block'][$cplace]);
			array_splice($_loc['block'][$cplace], $xpos[1], 1);
		}

		$_v['text'] = str_replace(array("\r", "\n"), array('',"\n"), $_v['text']);

		$_loc['block'][$_v['place']][$csort][0] = $_v['title'];
		$_loc['block'][$_v['place']][$csort][1] = $_v['text'];

		if(isset($cplace)) $asize = sizeof($_loc['block'][$cplace]);

		if(trim($_v['sort']) && isset($_v['p']) && $asize)
		{
			// поднимаем
			if($_v['sort'] == 'up' && $csort!=0)
			{
				$tmp = $_loc['block'][$xpos[0]][$csort];
				$_loc['block'][$xpos[0]][$csort] = $_loc['block'][$xpos[0]][$csort-1];
				$_loc['block'][$xpos[0]][$csort-1] = $tmp;
			}
			// опускаем
			if($_v['sort'] == 'down' && $csort!=($asize-1))
			{
				$tmp = $_loc['block'][$xpos[0]][$csort];
				$_loc['block'][$xpos[0]][$csort] = $_loc['block'][$xpos[0]][$csort+1];
				$_loc['block'][$xpos[0]][$csort+1] = $tmp;
			}
		}

		// перехват плагинами
		if(isset($plugins['block.save']))
		{
			rsort($plugins['block.save']);
			foreach($plugins['block.save'] as $func)
			{
				if(function_exists($func)) $func();
			}
		}

		fsave($_POST['datadir'].'/.settings.local', 'w+', serialize($_loc));

		header('Location: '.$_s['url']);
	}

	// находим позиции для блоков
	foreach($_tplin as $k=>$v)
	{
		if(strstr($k, 'block'))
		{
			$place[] = str_replace('-block', '', $k);
		}
	}

	// перехват плагинами
	if(isset($plugins['template.form-post']))
	{
		foreach($plugins['template.form-post'] as $func)
		{
			if(function_exists($func)) $func();
		}
	}

	// перехват плагинами
	if(isset($plugins['template.form-post']))
	{
		rsort($plugins['template.form-post']);
		foreach($plugins['template.form-post'] as $func)
		{
			if(function_exists($func)) $func();
		}
	}

	include $_s['tpd'].'/form-post.php';
	exit();
}
break;

case 'ed':
	// nanote-git 
	if (function_exists(nuserole)) {
	  if(nuserole($rq[1])){ 
	    savepost();
	  }
	} else {
	  if(isset($_SESSION['adm'])) {
	    savepost();
	  }
	}
	// nanote-git
break;

case 'sitemap.xml':
	header('Content-type: application/xml');

	echo "<?xml version=\"1.0\" encoding=\"".$_s['enc']."\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

	$_s['post.tpl.hold'] = 'sitemap.xml';

	$_s['ppp'] = 5000;

	echo pglist(0, "<url>\n\t<loc>%link%</loc>\n\t<lastmod>%datexml%</lastmod>\n\t<priority>0.7</priority>\n</url>\n");

	unset($_v['act']);
	blog();

	echo "</urlset>";
	exit();
break;

case 'sw':
	if(@md5($_v['sw'])==$_s['pass'])
	{
		$_SESSION['adm'] = 1;
		header('Location: '.$_s['url']);
	}

	if((strstr($_v['sw'],'http://') || strstr($_v['sw'], '.xml')) && isset($_SESSION['adm']))
	{
		$re = imp_rss($_v['sw']);

		@$doc['date'] .=
			'<script>
				var notify_msg = "'.$re.'";
			</script>';
		$_v['sw'] = $_v['act'] = null;
	}
	else
	{
		$_v['act'] = $_v['pg'] = $_v['p'] = null;
		$_v['sw'] = clean($_v['sw']);

		$d = dir($_POST['datadir']);
		while (false !== ($et = $d->read()))
		{
			if(substr($et,0,1)!='.')
			{
				$contents = file_get_contents($_POST['datadir'].'/'.$et);
				$result = function_exists('mb_strtolower') ? @eregi(mb_strtolower(trim($_v['sw']), 'UTF-8'), mb_strtolower($contents, 'UTF-8')) : @eregi(trim($_v['sw']), $contents);

				// проверяем не скрыта ли (черновик)
				$allow = ((!@$_loc['draft'][$et] && $et < time()) || isset($_SESSION['adm'])) ? 1 : 0;

				if($allow && $result)
				{
					$psts[] = $et;
				}
			}
		}
		$d->close();

		// javascript уведомление и подсветка найденного
		@$_s['pglk'] .=
			'<script>
				var search = \''.$_v['sw'].'\';
				var searchtpl = \'' . $_l['search'] . '\';
				var notify_msg = "Найдено ' . sizeof($psts) . ' записей.";
			</script>';
	}

	// меняем заголовок и прочее сео
	$_s['title'] = $_s['bname'].' - '.$_v['sw'].' - '.@ceil($_v['sp']/$_s['ppp']+1);
	$_intpl['inheader'] .= '<meta name="robots" content="noindex,follow,noodp,noydir" />'."\n";

	include $_s['tpd'].'/index.php';
	exit();
break;

}

}
//

// главная страница
if($_s['index'] && !trim($_SERVER['QUERY_STRING']))
{
	$_v['pg'] = $_s['index'];
}

// получаем информацию о записи (посте)
if(isset($_v['p']))
{
	$pst = ptinfo($_v['p']);
}

// заголовок страницы
if(isset($_v['pg']) && !isset($doc))
{
	$doc = pginfo($_POST['datadir'].'/'.$_v['pg']);

	if($doc)
	{
		$anons['title'] = $doc['title'];
		$anons['text'] = $doc['text'];
	}
}

$_s['title'] = isset($doc['title']) ? $doc['title'] : $_s['bname'];
$_s['title'] = isset($_metatitle) ? $_metatitle : $_s['title'];

function blog($mode=false) {
	global $_v, $_s, $_c, $cmt, $pst, $plugins, $psts, $_l, $doc, $_glob, $_loc, $_d, $_tplin, $anons;

	// перехват плагинами
	if(isset($plugins['blog.start']))
	{
		rsort($plugins['blog.start']);
		foreach($plugins['blog.start'] as $func)
		{
			if(function_exists($func)) $func();
		}
	}

if((!isset($_v['act']) && preg_match("/^([a-zA-Z0-9_.]{3,60})$/",$_v['pg'],$m)) && $mode!='list')
{
	if($doc)
	{
		if(isset($_SESSION['adm']) && substr($_v['pg'],0,1)!='.')
		{
			print '<div class="edpanel"><a id="ed" title="Редактировать" href='.alk('ep',$_v['pg']).'>E</a> <a id="de" title="Удалить" href='.alk('de',$_v['pg']).' '.cd($doc['title']).'>X</a></div>';
		}

		// перехват плагинами
		if(isset($plugins['template.page']))
		{
			rsort($plugins['template.page']);
			foreach($plugins['template.page'] as $func)
			{
				if(function_exists($func)) $func();
			}
		}

		include $_POST['datadir'].'/'.$_v['pg'];
	} else {
		$_v['pg'] = '.error404';
		include $_POST['datadir'].'/'.$_v['pg'];
	}
}
else if(!isset($_v['p']) || $mode=='list')
{
	// массив записей пустой, читаем их из директории
	if(!isset($psts) && !isset($_v['sw']))
	{
		$psts = getposts();
	}

	// быстрая запись
	if(isset($_SESSION['adm']) && $_SERVER['QUERY_STRING']=='')
	{
		include($_s['tpd'].'/form-post-quick.php');
	}

	// есть записи - выводим
	if($psts && $pz = sizeof($psts))
	{
		rsort($psts);

		@$pgs =  $pz / $_s['ppp'];
		$sp = (isset($_v['sp']) && is_numeric($_v['sp']) ? $_v['sp'] : 0);
		if ($pz > $_s['ppp']) $psts = array_slice($psts, $sp, $_s['ppp']);

		// перехват плагинами
		if(isset($plugins['blog.timeline']))
		{
			rsort($plugins['blog.timeline']);
			foreach($plugins['blog.timeline'] as $func)
			{
				if(function_exists($func)) $func();
			}
		}

		for ($i=0; $i<sizeof($psts); $i++)
		{
			// если не цифровой, значит страница
			if(!is_numeric($psts[$i]))
			{
				$pst = pginfo($_POST['datadir'].'/'.$psts[$i]);
			}
			else
			{
				$pst = ptinfo($psts[$i]);
				$pst['text'] = retex($pst['text']);
				if ($_s['nbr']) $pst['text'] = nl2br($pst['text']);
			}

			// меняем надпись комментарии, если отключены
			if(is_numeric($psts[$i]))
			{
				$_l['cmtlink'] = $_loc['comments'][$psts[$i]] ? '<a name="cmt" href="'.$pst['link'].'#cmt">'.$_l['commts'].'</a>' : $_l['freeze'];
			}

			// перехват плагинами
			if(isset($plugins['template.post.list']))
			{
				rsort($plugins['template.post.list']);
				foreach($plugins['template.post.list'] as $func)
				{
					if(function_exists($func)) $func();
				}
			}

			// перехват плагинами
			if(isset($plugins['template.'.$pst['template']]))
			{
				rsort($plugins['template.'.$pst['template']]);
				foreach($plugins['template.'.$pst['template']] as $func)
				{
					if(function_exists($func)) $func();
				}
			}

			include $_s['tpd'].'/'.$pst['template'];
		}

		// постраничная навигация (пейджинг)
		if ($pgs > 1)
		{
			//$j=1;
			$j=0;
			while($j < $pgs)
			{
				$pn = $j + 1;
				if ($j*$_s['ppp']==$sp)
				{
					$_s['pglk'] .= '<a href='.slk($j*$_s['ppp']).'><strong>'.$pn.'</strong></a> ';
				}
				else
				{	$rel='';
					if ($j*$_s['ppp'] == $sp + $_s['ppp']) $rel = ' rel="next"';
					if ($j*$_s['ppp'] == $sp - $_s['ppp']) $rel = ' rel="previous"';
					$_s['pglk'] .= '<a href='.slk($j*$_s['ppp']).$rel.'>'.$pn.'</a> ';
				}
				$j++;
			}
			//$_s['pglk'] = '<a href="'.slk(0).'">1</a> '.$_s['pglk'];
		}

	} else echo $_l['empty'];

}
else if(isset($_v['p']) && is_numeric($_v['p']) && $pst)
{
	// обрабатываем разрывы строк
	if ($_s['nbr']) $pst['text'] = nl2br($pst['text']);

	// навигация, пред-след пост
	$nav = ptnext($_v['p']);

	// засчитываем просмотр
	//$_glob['seelog'][$_v['p']] += 1;

	// меняем надпись комментарии, если отключены
	$_l['cmtlink'] = (isset($_loc['comments'][$_v['p']]) && $_loc['comments'][$_v['p']]) ? '<a name="cmt" href="'.$pst['link'].'#cmt">'.$_l['commts'].'</a>' : $_l['freeze'];

	$pst['text'] = retex($pst['text']);

	// шаблон полной версии
	$pst['template'] = str_replace('.php', '', $pst['template']);
	$pst['template'] = $pst['template'].'-full.php';

	// перехват плагинами
	if(isset($plugins['template.post.single']))
	{
		rsort($plugins['template.post.single']);
		foreach($plugins['template.post.single'] as $func)
		{
			if(function_exists($func)) $func();
		}
	}

	// перехват плагинами
	if(isset($plugins['template.'.$pst['template']]))
	{
		rsort($plugins['template.'.$pst['template']]);
		foreach($plugins['template.'.$pst['template']] as $func)
		{
			if(function_exists($func)) $func();
		}
	}

	include $_s['tpd'].'/'.$pst['template'];

	// обрабатываем комментарии
	for ($i=1; $i<($pst['comtn']-1)+$pst['comtnhd']; $i++)
	{
		list($cmt['nick'], $cmt['ip'], $cmt['date'], $cmt['text']) = explode('»', $pst['commts'][$i]);
		$s = strpos($cmt['nick'], '@');

		@$ip = $cmt['ip'];

		if($s)
		{
			$cmt['mail'] = $cmt['nick'];

			if(!isset($_SESSION['adm']))
			{
				$cmt['nick'] = substr($cmt['nick'], 0, $s);
			}
		}

		if(isset($_SESSION['adm']) && $cmt['nick']!=$_s['aname'])
		{
			if(strstr($_s['stop_word_ip'], $cmt['ip'])) $_ipban = 'un-ban';
			else $_ipban = 'ban';

			$cmt['ip']=' <a href="https://www.nic.ru/whois/?query='.$cmt['ip'].'">'.$cmt['ip'].'</a> [<a href='.alk('ban',$cmt['ip']).'>'.$_ipban.'</a>]';
		}
		else $cmt['ip']='';

		if($cmt['nick'] == $_s['aname']) $s = $cmt['mail'] = $_s['email'];
		$cmt['nick'] = (@trim($cmt['nick']) ? $cmt['nick'] : $_l['anonym']);

		// разные шаблоны комментария для автора и гостя
		$comment_template = ($cmt['nick']!=$_s['aname']) ? 'comment.php' : 'comment-author.php';

		// перехват плагинами
		if(isset($plugins['template.comment']))
		{
			rsort($plugins['template.comment']);
			foreach($plugins['template.comment'] as $func)
			{
				if(function_exists($func)) $func();
			}
		}

		// если личный или собственный комментарий
		if(isset($_SESSION['adm']) || getenv('REMOTE_ADDR') == $ip)
		{
			include $_s['tpd'].'/'.$comment_template;
		}
		else if(!strstr($cmt['text'],'@@'.$_s['aname']))
		{
			include $_s['tpd'].'/'.$comment_template;
		}

	}

	// перехват плагинами
	if(isset($plugins['template.form-comment']))
	{
		rsort($plugins['template.form-comment']);
		foreach($plugins['template.form-comment'] as $func)
		{
			if(function_exists($func)) $func();
		}
	}

	// если комменты разрешены или админ - даем форму
	if (($_s['cmton'] && (isset($_loc['comments'][$_v['p']]) && $_loc['comments'][$_v['p']])) || isset($_SESSION['adm']))
	{
		include $_s['tpd'].'/form-comment.php';
	}

} else include $_POST['datadir'].'/.error404';

}

if(isset($_v['p']))
{
	$_s['title'] = $pst['title'] ? $pst['title'] : $_s['bname'];
}

// 404 header
if($_v['pg'] == '.error404') header('HTTP/1.0 404 Not Found');

// перехват плагинами
if(isset($plugins['template.index']))
{
	rsort($plugins['template.index']);
	foreach($plugins['template.index'] as $func)
	{
		if(function_exists($func)) $func();
	}
}

// атоматические мета-теги (не рекомендуются)
if(isset($anons) && $_s['autometa'])
{
	$_intpl['inheader'] = '<meta name="description" content="'.$anons['title'].'" />'."\n".'<meta name="keywords" content="'.implode(', ',wordsrate($anons['text'])).'" />'."\n".$_intpl['inheader'];
}

include $_s['tpd'].'/index.php';

?>
