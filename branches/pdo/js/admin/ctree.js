/* $Id$ */

function CategoryTree() {
}

CategoryTree.allowDeselect = false;
CategoryTree.selected = 0;
CategoryTree.preselected = 0;

CategoryTree.onCollapse = function(cid) {
}

CategoryTree.onExpand = function(cid) {
}

CategoryTree.onSelect = function(cid) {
}

CategoryTree.onDeselect = function(cid) {
}

CategoryTree.onBeforeItemFill = function(cid) {
	return true;
}

CategoryTree.onAfterFill = function(cid) {
	return true;
}

CategoryTree.onFill = function(cid, cat, cat_pos, cats_total) {
}

CategoryTree.collapse = function(cid) {
	$('category-img-' + cid).setAttribute('src', _base_url + '/images/icons/tree-plus.png');
	$('category-img-' + cid).setAttribute('onclick', 'CategoryTree.expand(' + cid + ');');
	domEl('', '', '', $('category-' + cid), true);
	CategoryTree.onCollapse(cid);
}

CategoryTree.expand = function(cid) {
	$('category-img-' + cid).setAttribute('src', _base_url + '/images/icons/tree-minus.png');
	$('category-img-' + cid).setAttribute('onclick', 'CategoryTree.collapse(' + cid + ');');
	domEl('div', '', { 'id': 'category-' + cid }, $('category-li-' + cid), false);
	CategoryTree.fill(cid);
	CategoryTree.onExpand(cid);
}

CategoryTree.select = function(cid)
{
	var makesel = true;
	if (CategoryTree.allowDeselect) {
		if (CategoryTree.selected == cid)
			makesel = false;
	}
	var selected = getElementsByClass('selected', $('category-0'), 'li');
	for (var i = 0, sel; sel = selected[i]; i++) {
		sel.removeAttribute('class');
	}
	if (makesel) {
		$('category-li-' + cid).className = 'selected';
		CategoryTree.onSelect(cid);
		CategoryTree.selected = cid;
	} else {
		CategoryTree.onDeselect(cid);
		CategoryTree.selected = '';
	}
}

CategoryTree._category_tree = new Array();

CategoryTree.preSelectCategory = function() {
	if (CategoryTree._category_tree.length == 0)
		return;
	var cid = CategoryTree._category_tree.shift();
	if (CategoryTree._category_tree.length == 0)
		CategoryTree.select(cid);
	else
		CategoryTree.expand(cid);
}

CategoryTree.getParentsResponse = function(t) {
	var xml = t.responseXML;
	var response = xml.getElementsByTagName('response')[0];

	var parents = response.getElementsByTagName('parent');
	for (var i = 0, p; p = parents[i]; i++) {
		CategoryTree._category_tree.push(p.firstChild.nodeValue);
	}
	CategoryTree._category_tree.push(CategoryTree.preselected);
	CategoryTree.preSelectCategory();
}

CategoryTree.fillResponse = function(t) {
	var category_id = 0;
	var xml = t.responseXML;
	var service = xml.getElementsByTagName('service')[0];
	var query = xml.getElementsByTagName('query')[0];
	var response = xml.getElementsByTagName('response')[0];
	category_id = query.getElementsByTagName('category-id')[0].firstChild.nodeValue;

	var categories = response.getElementsByTagName('category');

	domEl('ul', '', { 'class': 'tree', 'id': 'category-ul-' + category_id }, $('category-' + category_id), true);

	var cid = 0;
	var ncids = 0;

	for (var i = 0, cat; cat = categories[i]; i++) {

		if (cat.getElementsByTagName('category-id')[0].firstChild)
			cid = cat.getElementsByTagName('category-id')[0].firstChild.nodeValue;

		if (CategoryTree.onBeforeItemFill(cid)) {
			var name = ' ';
			var subs = 0;

			if (cat.getElementsByTagName('category-name')[0].firstChild)
				name = cat.getElementsByTagName('category-name')[0].firstChild.nodeValue;
			if (cat.getElementsByTagName('subcategories-count')[0].firstChild)
				subs = cat.getElementsByTagName('subcategories-count')[0].firstChild.nodeValue;

			var imgsrc, imgoncl;
			if (subs > 0) {
				imgsrc = _base_url + '/images/icons/tree-plus.png';
				imgoncl = 'CategoryTree.expand(' + cid + ');';
			} else {
				imgsrc = _base_url + '/images/icons/tree-empty.png';
				imgoncl = '';
			}

			domEl('li', [
					domEl('img', '', { 'src': imgsrc, 'alt': '', 'id': 'category-img-' + cid, 'onclick': imgoncl }),
					domEl('span', name, { 'onclick': 'CategoryTree.select(' + cid + ')', 'class': 'tree-item' })
				],
				{ 'id': 'category-li-' + cid }, $('category-ul-' + category_id));

			CategoryTree.onFill(cid, cat, i, categories.length);
		}
	}

	CategoryTree.onAfterFill(category_id);

	if (CategoryTree.preselected > 0) {
		if (category_id == 0) {
			var selcid = CategoryTree.preselected;
			var serv = _ajax_service("get-category-parents", { 'cid': String(selcid) });
			serv.onSuccess = CategoryTree.getParentsResponse;
			advAJAX.get(serv);
		} else {
			CategoryTree.preSelectCategory();
		}
	}
}

CategoryTree.fill = function(cid) {
	var target = 'category-' + cid;
	ajaxIndicator($(target));

	var serv = _ajax_service("get-sub-categories", { 'cid': String(cid) });
	serv.onSuccess = CategoryTree.fillResponse;
	advAJAX.get(serv);
}
