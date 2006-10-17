/* $Id$ */

var _category_tree = new Array();
var _selected_categories = new Object;

function selectCategory(cid)
{
	var makesel = true;
//	if ($('category_parent').getAttribute('value') == cid)
//		makesel = false;
//	var selected = getElementsByClass('selected', $('category-0'), 'li');
//	for (var i = 0, sel; sel = selected[i]; i++) {
//		sel.removeAttribute('class');
//	}
	if ($('category-li-' + cid).className == 'selected') {
		$('category-li-' + cid).className = '';
		_selected_categories[String(cid)] = 0;
	} else {
		$('category-li-' + cid).className = 'selected';
		_selected_categories[String(cid)] = 1;
	}
}

function collapseCategory(cid)
{
	$('category-img-' + cid).setAttribute('src', _base_url + '/images/icons/tree-plus.png');
	$('category-img-' + cid).setAttribute('onclick', 'expandCategory(' + cid + ');');
	domEl('', '', '', $('category-' + cid), true);
}

function expandCategory(cid)
{
	$('category-img-' + cid).setAttribute('src', _base_url + '/images/icons/tree-minus.png');
	$('category-img-' + cid).setAttribute('onclick', 'collapseCategory(' + cid + ');');
	domEl('div', '', { 'id': 'category-' + cid }, $('category-li-' + cid), false);
	fillCategoryTree(cid);
}

function preSelectCategory()
{
	if (_category_tree.length == 0)
		return;
	var cid = _category_tree.shift();
	if (_category_tree.length == 0)
		selectCategory(cid);
	else
		expandCategory(cid);
}

function getParentsResponse(t)
{
	var xml = t.responseXML;
	var response = xml.getElementsByTagName('response')[0];

	var parents = response.getElementsByTagName('parent');
	for (var i = 0, p; p = parents[i]; i++) {
		_category_tree.push(p.firstChild.nodeValue);
	}
	_category_tree.push($('orig_category_parent').getAttribute('value'));
	preSelectCategory();
}

function fillCategoryTreeResponse(t)
{
	var category_id = 0;
	var xml = t.responseXML;
	var service = xml.getElementsByTagName('service')[0];
	var query = xml.getElementsByTagName('query')[0];
	var response = xml.getElementsByTagName('response')[0];
	category_id = query.getElementsByTagName('category-id')[0].firstChild.nodeValue;

	var categories = response.getElementsByTagName('category');

	domEl('ul', '', { 'class': 'tree', 'id': 'category-ul-' + category_id }, $('category-' + category_id), true);
	for (var i = 0, cat; cat = categories[i]; i++) {

		var name = ' ';
		var subs = 0;
		var cid = 0;

		if (cat.getElementsByTagName('category-name')[0].firstChild)
			name = cat.getElementsByTagName('category-name')[0].firstChild.nodeValue;
		if (cat.getElementsByTagName('category-id')[0].firstChild)
			cid = cat.getElementsByTagName('category-id')[0].firstChild.nodeValue;
		if (cat.getElementsByTagName('subcategories-count')[0].firstChild)
			subs = cat.getElementsByTagName('subcategories-count')[0].firstChild.nodeValue;

		var imgsrc, imgoncl;
		if (subs > 0) {
			imgsrc = _base_url + '/images/icons/tree-plus.png';
			imgoncl = 'expandCategory(' + cid + ');';
		} else {
			imgsrc = _base_url + '/images/icons/tree-empty.png';
			imgoncl = '';
		}

		domEl('li', [
			domEl('img', '', { 'src': imgsrc, 'alt': '', 'id': 'category-img-' + cid, 'onclick': imgoncl }),
			domEl('span', name, { 'onclick': 'selectCategory(' + cid + ')', 'class': 'tree-item' })],
			{ 'id': 'category-li-' + cid }, $('category-ul-' + category_id));

	}

/*	if (category_id == 0) {
		var selcid = $('orig_category_parent').getAttribute('value');
		var serv = _ajax_service("get-category-parents", { 'cid': String(selcid) });
		serv.onSuccess = getParentsResponse;
		advAJAX.get(serv);
	} else {
		preSelectCategory();
	}*/
}

function fillCategoryTree(cid)
{
	var target = 'category-' + cid;
	ajaxIndicator($(target));

	var serv = _ajax_service("get-sub-categories", { 'cid': String(cid) });
	serv.onSuccess = fillCategoryTreeResponse;
	advAJAX.get(serv);
}

var Rules = {
}

Behaviour.register(Rules);

Behaviour.addLoadEvent(function() {
	fillCategoryTree(0);
});
