//<![CDATA[
var at;
function ned() {
if(!at) {
at = new nicEditor({fullPanel : true}).panelInstance('tx',{hasPanel : true});
} else {
at.removeInstance('tx');
at = null;
}
}
//]]>

function pf(va){
	ffl = document.forms.postfo.text;
	va=va.replace(/^([^\\\/]*(\\|\/))*/,"").toLowerCase();
	if(va.indexOf('jpg')!=-1||va.indexOf('gif')!=-1||va.indexOf('png')!=-1||va.indexOf('jpeg')!=-1) ffl.value += ' <img src="/files/'+va+'" border="0">';
	else if(va!="") ffl.value += ' <a href="/files/'+va+'">'+va+'</a>';
	ffl.focus();
}
function nefl(){
	div = document.createElement("div");
	div.innerHTML = '<input name="nefis[]" onchange="pf(this.value);" type="file"> <a onClick="rm(this)" style="cursor: pointer;">[-]</a>';
	document.getElementById("nefls").appendChild(div);
}
function rm(a) {
	var cD = a.parentNode;
	cD.parentNode.removeChild(cD);
}
function toggle(id) {
	obj = document.getElementById(id);
	if(obj.style.display == "none") obj.style.display = "block";
	else obj.style.display = "none";
}
