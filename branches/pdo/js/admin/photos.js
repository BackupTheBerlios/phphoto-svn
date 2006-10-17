/* $Id$ */

var ts;
var ac;

CategoryTree.onFill = function(cid, cat, cat_pos, cats_total) {

	var ph = 0;
	var tph = 0;
	var substr = "";

	if (cat.getElementsByTagName('photos-count')[0].firstChild)
		ph = cat.getElementsByTagName('photos-count')[0].firstChild.nodeValue;
	if (cat.getElementsByTagName('total-photos-count')[0].firstChild)
		tph = cat.getElementsByTagName('total-photos-count')[0].firstChild.nodeValue;
	substr = ' (' + ph + '/' + tph + ')';

	domEl('span', substr, { 'class': 'details' }, getElementsByClass('tree-item', $('category-li-' + cid), 'span'));
}

CategoryTree.onSelect = function(cid) {
	$('category_id').setAttribute('value', cid);
}

CategoryTree.onDeselect = function(cid) {
	$('category_id').setAttribute('value', '');
}


function getLoginsResponse(t) {
	var group_id = 0;
	var allow_add = 0;

	var xml = t.responseXML;

	var response = xml.getElementsByTagName('response')[0];
	var logins = response.getElementsByTagName('login');

	ac._items = new Array();
	for (var i = 0, login; login = logins[i]; i++) {
		ac._items.push(login.firstChild.nodeValue);
	}
}

var Rules = {};
Behaviour.register(Rules);

Behaviour.addLoadEvent(function() {
	ts = new TabSheet('right-pane');
	ts.addPage('filter', 'Filtr');
	ts.addPage('preview', 'Podgląd');
	ts.addPage('stats', 'Statystyki');
	ts.build();
	ts.showPage(0);
	CategoryTree.allowDeselect = true;
	CategoryTree.preselected = $('category_id').getAttribute('value');
	CategoryTree.fill(0);

	ac = new AutoCompleteInput('user_login', $('filter'));
	ac.init();

	var serv = _ajax_service("get-logins");
	serv.onSuccess = getLoginsResponse;
	advAJAX.get(serv);
});
