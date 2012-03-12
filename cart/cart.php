<?php

$nanoconf = @str_replace(array('www.','-'), array('','_'), strtolower($_SERVER['HTTP_HOST']));
$_glob = unserialize(file_get_contents('../data/'.$nanoconf.'/.settings.global'));
$_loc = unserialize(file_get_contents('../data/'.$nanoconf.'/.settings.local'));
foreach($_glob as $k=>$v) if(substr($k,0,2) =='_s') $_s[substr($k,2)] = $v;
$url = $_s['url'];
$valut = ' '.$_s['shopvalut'];
unset($nanoshop);
if(!isset($_s['shopvalut'])) $_s['shopvalut'] = "р.";
function plk($p) {
	global $_s, $_loc;
		$x = is_file('../.htaccess') ? '' : '?/';
		if(isset($_loc['customurl'][$p])) { $l = $_loc['customurl'][$p]; }
		else if(isset($_loc['alias'][$p])) $l = date('Y/m/',$p).$_loc['alias'][$p];
		else $l = date('Y/m/d/H:i:s',$p).'';
	return $_s['url'].$x.$l;
}
function unscart($t) {
	$t = str_replace("|+|", '"', $t);
	$t = unserialize($t);
	return $t;
}
function scart($t) {
	$t = serialize($t);
	$t = str_replace('"', "|+|", $t);
	return $t;
}
function setcart($t) {
	echo "<script>	$.Storage.set('cart', '".$t."');</script>";
}

if(!empty($_POST['oldcart']) &&  $_POST['oldcart'] != "oldcart") { // если было чего уже в корзине
	$cart = $_POST['oldcart'];
	$cart = unscart($cart);
}

if(!empty($_POST['newcart'])) { // если редактировали корзину
	foreach ($_POST['newcart'] as $v) {
		$cart[] = array($v['name'],$v['count'],$v['id']);
	}
}

if(isset($_POST['addnew'])) { // если добавляли товар
	$name = $_POST['name'];
	$number = $_POST['number'];
	$id = $_POST['id'];
	$link = plk($id);
	$price = $_loc['input_price'][$id];
	if(is_numeric($number) && $number > 0) {
		$number = (int)$number;
		$cart[] =  array($name,$number,$id,$link);
		$cart = scart($cart);
		echo '<div id="added"><h2>В корзину добавлен товар</h2>
		<div class="aboutadd"><a href="'.$link.'">'.$name.'</a><br> кол-во: '.$number.' цена: '.$price.$valut.' всего на сумму: '.$number*$price.$valut.'</div><br><br><br><br></div>';
		setcart($cart);	
		$cart = unscart($cart);
	} else {
		echo '<div id="added"><h2>Товар не добавлен</h2><div class="aboutadd">Неверное значение количества</div><br><br><br><br></div>';
	}
}

if(isset($_POST['shopuser'])) { // если оформляли заказ
	$message = '<div style="width:500px;"><table  border="1" width="500px">';
	$message .= '<tr style="text-weight:bold;"><th>Наименование</th><th width="12px">Кол-во</th><th width="50px">Цена за еденицу</th><th width="50px">Общая цена</th></tr>';
	foreach ($cart as $k=>$v) {
		$name = $v[0];
		$number = $v[1];	
		$id = $v[2];
		$link = plk($id);
		$price = $_loc['input_price'][$id];
		$summ = $number*$price;
		$message .= '<tr>
		<td class="name"><a href="'.$link.'">'.$name.'</a></td>
		<td class="count">'.$number.'</td>
		<td class="price"><span>'.$price.'</span>'.$valut.'</td>
		<td class="summ"><span>'.$summ.'</span>'.$valut.'</td>
		</tr>
		';
		$total = $total + $summ; // подсчитали всё
	}
	$message .= '</table><div style="text-align:right;">Итого: <span>'.$total.'</span>'.$valut.'</div></div>';
	if($_POST['aboutcart'] != 'Примечание к заказу') $message .=  'Примечание к заказу: 
'.$_POST['aboutcart'];
	$message .=  "<br>Имя заказчка: ".$_POST['shopuser']."<br>Email заказчика: ".$_POST['shopmail']."<br>";
	$mail=str_replace(array('http://', '/'), array('zakaz@', ''), $url);
	if(mail($_s['email'], "Заказ с сайта".$_s['bname'], $message, "Content-Type: text/html; charset=utf-8\nFrom: ".$_s['bname']." <".$mail.">\r\n")) {
		echo "<h2>Ваш заказ отправлен менеджеру</h2>";
		$message = 'Ваш заказ<br>'.$message.'<br><b>Наш менеджер свяжется с вами.</b>';
		mail($_POST['shopmail'], 'Заказ с сайта "'.$_s['bname'].'"', $message, "Content-Type: text/html; charset=utf-8\nFrom: ".$_s['bname']." <".$mail.">\r\n");
		unset($cart);
		setcart('');
		
	} else {
		echo "<h2>Ошибка отправки заказа</h2>";
	}
}
if(!empty($cart)){ // если корзинка не пуста
	echo '<h2>Корзина товаров</h2>';

	echo '<form id="cartable" action="'.$url.'cart/cart.php"  method="post">
	<div class="table">
	
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<th class="name">Наименование</th><th class="count">Ко-во</th><th class="price">Цена за единицу</th><th class="summ">Общая цена</th><th class="delete"><span class="clearcart">Очистить корзину</span> </th>';
	// удаляем элементы с одинаковым названием и складываем их кол-во
	usort($cart,create_function('$a,$b','return $a[0]>$b[0];'));  //чудоколдунство сортировки массива по первому элементу вложенных массиов
	// а вот как это работает я сам не очень понимаю
	// сначала задаем имя последнего(предыдущего), сверяем текущее с ним
	// и если оно такое же, то плюсуем к последнему кол-во, а текущее херим
	// вся хитрость в определении того к чему плюсовать, из-за этого и появилась $i
	// методом квантовых случайностей aka научный тык было установлено что
	// в такой конструкции всё работает, так что трогать не рекомендуется)
	// убейте меня кто-нибудь
	$i=0;
	$lasid="буквы";
	foreach ($cart as $k => $v) {
		if ($v[2] == $lasid) {
			$num = (int) $cart[$lastcount][1];
			$inc = (int) $v[1];
			$cart[$lastcount][1] = $num + $inc;
			unset($cart[$k]);
			$i++;
			$lastcount = $k-$i;
		} else {
			$lastcount = $k;
			$i=0;
		}
		$lasid = $v[2];
	}
	// уф, всё-таки надо учиться программированию, алгоритмам в частности

	// ну а теперь выводим карзиночку и считаем общую сумму
	$total=0;
	foreach ($cart as $k=>$v) {
		$name = $v[0];
		$number = $v[1];	
		$id = $v[2];
		$link = plk($id);
		$price = $_loc['input_price'][$id];
		$summ = $number*$price;
		echo '<tr id="tr'.$k.'">';
		echo '<td class="name"><a href="'.$link.'">'.$name.'</a>
		<input type="hidden" name="newcart['.$k.'][name]" value="'.$name.'" />
		<input type="hidden" name="newcart['.$k.'][id]" value="'.$id.'" />		</td>
		<td class="count"><input class="newcart" name="newcart['.$k.'][count]" value="'.$number.'"></td>
		<td class="price"><span>'.$price.'</span>'.$valut.'</td>
		<td class="summ"><span>'.$summ.'</span>'.$valut.'</td>
		<td class="delete"><span class="del'.$k.'">удалить<span></td>
		<script>
			$(".del'.$k.'").click(function () {
				$("#tr'.$k.'").remove();
				writeTotal();
			});
			$("#tr'.$k.' .newcart").keyup(function () {
				$("#tr'.$k.' .summ span").text($(this).val()*$("#tr'.$k.' .price span").text());
				writeTotal();
			});
		</script>
		';
		$total = $total + $summ; // подсчитали всё
	}
	echo '</table></div>';
	echo '<div class="total">Итого: <span>'.$total.'</span>'.$valut.'</div>';
	$cart = scart($cart);
	setcart($cart);
	//echo '<input  type="hidden" value="'.$cart.'" name="oldcart">';
	echo '<div id="switchcart"> <span  href="" class="creatcart">Оформить заказ</span> || <span  href="" class="editcart">Сохранить изменения</span></div><div class="submit" style="display:none;"></div></form>';
} else {
	echo "Корзина пуста<br><br><br>";
}
?>
<script>
$(".delete  .clearcart").click(function () {
	$.Storage.set("cart", "");
	$("#addcart .cart").empty();
	$("#addcart .cart").html("Корзина пуста<br><br><br>");
	$("input.oldcart").val('');
	$.Storage.set('cartcount', '');
});

$("#switchcart .creatcart").click(function () {
	
	$("#cartable .submit").append('<h2>Оформление заказа</h2><input name="shopmail" title="Ваш e-mail"  value="" id="shopmail"><input name="shopuser" title="Ваше имя"  id="shopuser"><textarea name="aboutcart" id="aboutcart">Примечание к заказу</textarea><input type="submit" name="submit" value=" Сделать заказ "/>');
	$("#cartable .submit").slideDown();
	$("#switchcart").slideUp();
	//$("form#cartable").submit();
	$("input:text").each(function(){
		if(this.value == '')
			this.value = this.title;
	});
	$("input:text").focus(function(){
		if(this.value == this.title)
			this.value = '';
	});
	$("input:text").blur(function(){
		if(this.value == '')
			this.value = this.title;
	});
	$(function() {
		$("#aboutcart").focus(function(event) {
			$(this).text("");
			$(this).unbind(event);
		});
	});
	$("input:image, input:button, input:submit").click(function(){
		$(this.form.elements).each(function(){
			if(this.type =='text'){
				if(this.value == this.title && this.title != ''){
					this.value='';
				}
			}
		});
	});
});

$("#switchcart .editcart").click(function () {
	$("form#cartable").submit();
});

var cartable = { 
	target:        "#addcart .cart",    
	beforeSubmit:  cartableBefore
}; 	
$("#cartable").ajaxForm(cartable);

function cartableBefore()  {
	$("#addcart .cart").empty();
	$("#addcart .cart").html("<div class='cartwait'></div>");
}

function writeTotal() {
	var prodTotal = 0;
	$("td.summ span").each(function() {
		var valString = $(this).text() || 0;
		prodTotal += parseInt(valString);
	});
	$(".total span").text(prodTotal);
}
$("input.oldcart").val('<?php echo $cart;?>');
$("#added").slideUp(3000);

</script>
