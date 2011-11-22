/* MicroJS Core for Nanote, v. 0.12 */

// Декларируем переменные/массивы.
var tmin, tmout;
var buffer = [];

// ala prototype/jquerty
function gid(i) {
	return document.getElementById(i);
}

function jah(url,target) {
	if (window.XMLHttpRequest) {
		req = new XMLHttpRequest();
		req.onreadystatechange = function() {jahDone(target);};
		req.open("GET", url, true);
		req.send(null);
	} else if (window.ActiveXObject) {
		req = new ActiveXObject("Microsoft.XMLHTTP");
		if (req) {
			req.onreadystatechange = function() {jahDone(target);};
			req.open("GET", url, true);
			req.send();
		}
	}
}

function jahDone(target) {
	if (req.readyState == 4) {
		if (req.status == 200) {
			results = req.responseText;
			atrib = results.split('~@~');
			if(atrib[0]=='inner') {
				gid(atrib[1]).innerHTML = atrib[2];
			}
			if(atrib[0]=='notify') {
				send_notify(atrib[1]);
			}
			if(atrib[2]=='return') {
				gid(target).innerHTML = buffer[target];
			}
		} else {
			send_notify(req.statusText);
			buffer[elem.name] = gid(elem.name).innerHTML;
		}
	}
}

// Вспылающее уведомление.
function send_notify(t) {
	gid('notifywindow').style.posTop = 10;
	gid('notifywindow').style.posRight = 10;
	gid('notifywindow').style.display = "block";
	gid('notify').innerHTML = t;
	fadeIn('notifywindow',0);
	hidden = setTimeout('fadeOut("notifywindow",1);',2400);
}

// Исчезновение.
function fadeOut(obj,ot) {
	clearTimeout(tmin);
	if(ot > 0) {
		ot -= 0.05;
		gid(obj).style.opacity = ot;
		gid(obj).style.filter = 'alpha(opacity=' + ot*100 + ')';
		tmout=setTimeout('fadeOut("'+obj+'",'+ot+')',9);
	}
	if(ot <= 0.05) {
		clearTimeout(hidden);
		gid(obj).style.display = "none";
	}
}

// Появление.
function fadeIn(obj,ot) {
	clearTimeout(tmout);
	if(ot < 1) {
		ot += 0.05;
		gid(obj).style.opacity = ot;
		gid(obj).style.filter = 'alpha(opacity=' + ot*100 + ')';
		tmin=setTimeout('fadeIn("'+obj+'",'+ot+')',12);
	}
}

// Отправка по Ctrl+Enter.
function ctrlEnter(event, formElem)
{
	if((event.ctrlKey) && ((event.keyCode == 0xA) || (event.keyCode == 0xD)))
	{
			gid('quick-post').submit();
	}
}

// Вставка комментария.
function nick(n)
{
	gid('cmtxt').value = n + ', ' + gid('cmtxt').value;
	gid('cmtxt').focus();
}

// Выделение найденных слов.
function doHighlight(bodyText, searchTerm)
{
	tags = searchtpl.split("%word%");

	highlightStartTag = tags[0];
	highlightEndTag = tags[1];

	var newText = "";
	var i = -1;
	var lcSearchTerm = searchTerm.toLowerCase();
	var lcBodyText = bodyText.toLowerCase();

	while (bodyText.length > 0) {
		i = lcBodyText.indexOf(lcSearchTerm, i+1);
		if (i < 0) {
			newText += bodyText;
			bodyText = "";
		} else {
			if (bodyText.lastIndexOf(">", i) >= bodyText.lastIndexOf("<", i)) {
				if (lcBodyText.lastIndexOf("/script>", i) >= lcBodyText.lastIndexOf("<script", i)) {
					newText += bodyText.substring(0, i) + highlightStartTag + bodyText.substr(i, searchTerm.length) + highlightEndTag;
					bodyText = bodyText.substr(i + searchTerm.length);
					lcBodyText = bodyText.toLowerCase();
					i = -1;
				}
			}
		}
	}

	return newText;
}

function highlightSearchTerms(searchText, treatAsPhrase, warnOnFailure)
{
	if (treatAsPhrase) {
		searchArray = [searchText];
	} else {
		searchArray = searchText.split(" ");
	}

	if (!document.body || typeof(document.body.innerHTML) == "undefined") return false;

	var bodyText = gid('content').innerHTML;

	for (var i = 0; i < searchArray.length; i++) {
		bodyText = doHighlight(bodyText, searchArray[i]);
	}

	gid('content').innerHTML = bodyText;
	return true;
}

// Отложенная инициализация.
window.onload = function() {
	d = document;

	mObj = d.getElementsByTagName("body")[0].appendChild(d.createElement("div"));
	mObj.id = "notifywindow";
	mObj.style.visiblity = "none";
	mObj.style.display = "none";
	mObj.style.position = "fixed";

	alertObj = mObj.appendChild(d.createElement("div"));
	alertObj.id = "notify";

	alertObj.appendChild(d.createElement("text"));

	if(window.notify_msg) send_notify(notify_msg);
	if(window.search) highlightSearchTerms(search, true, true);
	if(window.relocation) setTimeout(function() { document.location.href = relocation; }, 200);


	if(document.postfo) document.postfo.text.focus();
}