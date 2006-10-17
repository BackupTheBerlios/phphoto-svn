/* $Id$ */

function addPreviewInfo(tbody, title, text)
{
	domEl('tr', [
		domEl('td', title, { 'class': 'preview-info-title' }),
		domEl('td', text, '')
	], { 'class': 'preview-info' }, tbody);
}

function photoPreviewBack()
{
	$('preview').style.display = '';
	$('photo-preview').style.display = 'none';
}

function approvePhotoResponse(t)
{
	var photo_id = 0;
	var xml = t.responseXML;
	var query = xml.getElementsByTagName('query')[0];
	photo_id = query.getElementsByTagName('photo-id')[0].firstChild.nodeValue;
	displayPhotoPreview(photo_id);
}

function approvePhoto(photo_id)
{
	ajaxIndicator($('photo-preview'), '/images/indicators/indicator_medium.gif');
	var serv = _ajax_service("approve-photo", { 'pid': String(photo_id) });
	serv.onSuccess = approvePhotoResponse;
	advAJAX.get(serv);
}

function cancelPhoto(photo_id)
{
	ajaxIndicator($('photo-preview'), '/images/indicators/indicator_medium.gif');
	var serv = _ajax_service("reject-photo", { 'pid': String(photo_id) });
	serv.onSuccess = approvePhotoResponse;
	advAJAX.get(serv);
}

function displayPhotoPreviewResponse(t)
{
	var photo_id = 0;
	var xml = t.responseXML;
	var service = xml.getElementsByTagName('service')[0];
	var query = xml.getElementsByTagName('query')[0];
	var response = xml.getElementsByTagName('response')[0];
	var author = response.getElementsByTagName('author')[0];
	photo_id = query.getElementsByTagName('photo-id')[0].firstChild.nodeValue;

	var file = getElementValue(response, 'file', '');
	var title = getElementValue(response, 'photo-title', '');
	var description = getElementValue(response, 'photo-description', '');
	var date = getElementValue(response, 'photo-added', '');
	var alogin = getElementValue(author, 'user-login', '');
	var approved = getElementValue(response, 'photo-approved', '-');

	var parent = $('photo-preview');

	domEl('h1', title, '', parent, true);
	domEl('img', '', {
		'alt': title,
		'src': file,
		'class': 'photo'
	}, parent);
	domEl('p', description, '', parent);
	domEl('h2', 'Informacje', '', parent);
	var tbody = domEl('tbody', '', '');
	addPreviewInfo(tbody, 'Autor:', alogin);
	addPreviewInfo(tbody, 'Dodane:', date);
	addPreviewInfo(tbody, 'Zaakceptowane:', approved);
	addPreviewInfo(tbody, 'Kategorie:', '');
	domEl('table', tbody, '', parent);
	domEl('h2', 'Statystyki', '', parent);
	domEl('h2', 'Akcje', '', parent);
	var btnsdiv = domEl('div', '', { 'class': 'buttons' });
	domEl('a',
		domEl('img', '', {
			'src': 'images/icons/arrow_undo.png',
			'alt': '',
			'width': '16',
			'height': '16',
			'onclick': 'photoPreviewBack();'
		}),
		{ 'class': 'button' }, btnsdiv);
	if (approved == '-') {
		domEl('a',
			domEl('img', '', {
				'src': 'images/icons/accept.png',
				'alt': '',
				'width': '16',
				'height': '16',
				'onclick': 'approvePhoto(' + photo_id + ');'
			}),
			{ 'class': 'button' }, btnsdiv);
	} else {
		domEl('a',
			domEl('img', '', {
				'src': 'images/icons/cancel.png',
				'alt': '',
				'width': '16',
				'height': '16',
				'onclick': 'cancelPhoto(' + photo_id + ');'
			}),
			{ 'class': 'button' }, btnsdiv);
	}
	parent.appendChild(btnsdiv);

	//domEl('span', title, '', $('preview-photo-' + photo_id));
}

function displayPhotoTTResponse(t)
{
	var photo_id = 0;
	var xml = t.responseXML;
	var service = xml.getElementsByTagName('service')[0];
	var query = xml.getElementsByTagName('query')[0];
	var response = xml.getElementsByTagName('response')[0];
	var author = response.getElementsByTagName('author')[0];
	photo_id = query.getElementsByTagName('photo-id')[0].firstChild.nodeValue;

	var file = getElementValue(response, 'file', '');
	var title = getElementValue(response, 'photo-title', '');
	var description = getElementValue(response, 'photo-description', '');
	var alogin = getElementValue(author, 'user-login', '');

	var parent = $('preview-photo-tt-' + photo_id);

	domEl('h1', title, '', parent, true);
	domEl('h2', alogin, '', parent);
	domEl('img', '', {
		'alt': title,
		'src': file
	}, parent);
	domEl('p', description, '', parent);
	domTT_updatePosition('preview-photo-domTT-' + photo_id);
	//domEl('span', title, '', $('preview-photo-' + photo_id));
}

function displayPhotoPreview(pid) {
	$('preview').style.display = 'none';
	$('photo-preview').style.display = '';
	ajaxIndicator($('photo-preview'), '/images/indicators/indicator_medium.gif');
	var serv = _ajax_service("get-photo", { 'pid': String(pid), 'w': '200', 'h': '200' });
	serv.onSuccess = displayPhotoPreviewResponse;
	advAJAX.get(serv);
}

function displayPhotoTT(t, e, pid) {
	domTT_activate(t, e, 'content', domEl('div', '', { 'id': 'preview-photo-tt-' + pid, 'class': 'preview-photo-tt' }), 'delay', 500, 'id', 'preview-photo-domTT-' + pid);
	domTT_updatePosition('preview-photo-domTT-' + pid);
	ajaxIndicator($('preview-photo-tt-' + pid), '/images/indicators/indicator_medium.gif');
	var serv = _ajax_service("get-photo", { 'pid': String(pid), 'w': '200', 'h': '200' });
	serv.onSuccess = displayPhotoTTResponse;
	advAJAX.get(serv);
}

function getPhotoResponse(t)
{
	var photo_id = 0;
	var xml = t.responseXML;
	var service = xml.getElementsByTagName('service')[0];
	var query = xml.getElementsByTagName('query')[0];
	var response = xml.getElementsByTagName('response')[0];
	photo_id = query.getElementsByTagName('photo-id')[0].firstChild.nodeValue;

	var file = '';
	if (response.getElementsByTagName('file')[0].firstChild)
		file = response.getElementsByTagName('file')[0].firstChild.nodeValue;
	var title = '';
	if (response.getElementsByTagName('photo-title')[0].firstChild)
		title = response.getElementsByTagName('photo-title')[0].firstChild.nodeValue;

	domEl('a',
		domEl('img', '', {
			'alt': title,
			'src': file
		}),
		{
			'onclick': 'displayPhotoPreview(' + photo_id + '); return false;', 'href': '#',
			'onmouseover': 'displayPhotoTT(this, event, ' + photo_id + ');'
		},
		$('preview-photo-' + photo_id), true);
	//domEl('span', title, '', $('preview-photo-' + photo_id));
}

function getPhoto(pid)
{
	domEl('div', '', { 'class': 'preview-photo', 'id': 'preview-photo-' + pid }, $('preview-photos'));
	ajaxIndicator($('preview-photo-' + pid));
	var serv = _ajax_service("get-photo", { 'pid': String(pid), 'w': '75', 'h': '75' });
	serv.onSuccess = getPhotoResponse;
	advAJAX.get(serv);
}

function previewCategoryResponse(t)
{
	var category_id = 0;
	var xml = t.responseXML;
	var service = xml.getElementsByTagName('service')[0];
	var query = xml.getElementsByTagName('query')[0];
	var response = xml.getElementsByTagName('response')[0];
	var creator = response.getElementsByTagName('creator')[0];
	category_id = query.getElementsByTagName('category-id')[0].firstChild.nodeValue;

	var name = getElementValue(response, 'category-full-name', '');
	var description = getElementValue(response, 'category-description', '');
	var date = getElementValue(response, 'category-created', '');
	var cname = getElementValue(creator, 'user-login', '');
	var pcnt = getElementValue(response, 'photos-count', '');
	var tpcnt = getElementValue(response, 'total-photos-count', '');
	var scnt = getElementValue(response, 'subcategories-count', '');

	domEl('h1', name, '', $('preview'), true);
	domEl('p', description, '', $('preview'));
	domEl('h2', 'Informacje', '', $('preview'));
	var tbody = domEl('tbody', '', '');
	addPreviewInfo(tbody, 'Data utworzenia:', date);
	addPreviewInfo(tbody, 'Utworzona przez:', cname);
	addPreviewInfo(tbody, 'Liczba podkategorii:', scnt);
	addPreviewInfo(tbody, 'Liczba zdjęć:', pcnt);
	addPreviewInfo(tbody, 'Liczba zdjęć wraz z podkat.:', tpcnt);
	domEl('table', tbody, '', $('preview'));
	domEl('h2', 'Najnowsze zdjęcia', '', $('preview'));
	domEl('div', '', { 'id': 'preview-photos' }, $('preview'));

	var photos = response.getElementsByTagName('photos')[0].getElementsByTagName('photo-id');
	for (var i = 0, ph; ph = photos[i]; i++) {
		if (i >= 4)
			break;
		getPhoto(ph.firstChild.nodeValue);
	}

	domEl('h2', 'Akcje', '', $('preview'));
	var btnsdiv = domEl('div', '', { 'class': 'buttons' });
	domEl('a',
		domEl('img', '', {
			'src': _base_url + '/images/icons/image.png',
			'alt': '',
			'width': '16',
			'height': '16'
		}),
		{ 'class': 'button', 'href': url('adm-photos', { 'cid': category_id }) }, btnsdiv);
	domEl('a',
		domEl('img', '', {
			'src': _base_url + '/images/icons/images.png',
			'alt': '',
			'width': '16',
			'height': '16'
		}),
		{ 'class': 'button', 'href': url('adm-photos', { 'cid': category_id, 'scid': 'on' }) }, btnsdiv);
	$('preview').appendChild(btnsdiv);
}

function previewCategory(cid)
{
	ajaxIndicator($('preview'), '/images/indicators/indicator_medium.gif');

	var serv = _ajax_service("get-category", { 'cid': String(cid) });
	serv.onSuccess = previewCategoryResponse;
	advAJAX.get(serv);
}

function selectCategory(cid)
{
	var selected = getElementsByClass('selected', $('category-0'), 'li');
	for (var i = 0, sel; sel = selected[i]; i++) {
		sel.removeAttribute('class');
	}
	$('category-li-' + cid).className = 'selected';

	$('preview').style.display = '';
	$('photo-preview').style.display = 'none';

	previewCategory(cid);
}

function showIcons(cid)
{
	$('category-icons-' + cid).style.display = '';
	$('category-icons-switch-' + cid).setAttribute('src', _base_url + '/images/icons/control_rewind-sm.png');
	$('category-icons-switch-' + cid).setAttribute('onclick', 'hideIcons(' + cid + '); return false;');
}

function hideIcons(cid)
{
	$('category-icons-' + cid).style.display = 'none';
	$('category-icons-switch-' + cid).setAttribute('src', _base_url + '/images/icons/control_fastforward-sm.png');
	$('category-icons-switch-' + cid).setAttribute('onclick', 'showIcons(' + cid + '); return false;');
}

function moved(t)
{
	var xml = t.responseXML;
	var service = xml.getElementsByTagName('service')[0];
	var query = xml.getElementsByTagName('query')[0];
	var response = xml.getElementsByTagName('response')[0];
	var category_id = query.getElementsByTagName('category-id')[0].firstChild.nodeValue;
	var direction = query.getElementsByTagName('direction')[0].firstChild.nodeValue;
	var category2_id = response.getElementsByTagName('category2-id')[0].firstChild.nodeValue;

	if (category2_id == 0)
		return;

	var li1 = $('category-li-' + category_id);
	var li2 = $('category-li-' + category2_id);
	var ul = li1.parentNode;
	if (direction == 1) {
		li1 = ul.replaceChild(li2, li1);
		ul.insertBefore(li1, li2);
		$('category-down-' + category_id).style.display = '';
		$('category-up-' + category2_id).style.display = '';
	} else {
		li2 = ul.replaceChild(li1, li2);
		ul.insertBefore(li2, li1);
		$('category-up-' + category_id).style.display = '';
		$('category-down-' + category2_id).style.display = '';
//		insertAfter(li1.parent, li2, li1);
	}

	var el = li1.nextSibling;
	var vis = false;
	while (el != null) {
		if (el.nodeType == Node.ELEMENT_NODE && el.nodeName == 'li') {
			vis = true;
			break;
		}
		el = el.nextSibling;
	}
	if (!vis)
		$('category-down-' + category_id).style.display = 'none';

	el = li1.previousSibling;
	vis = false;
	while (el != null) {
		if (el.nodeType == Node.ELEMENT_NODE && el.nodeName == 'li') {
			vis = true;
			break;
		}
		el = el.previousSibling;
	}
	if (!vis)
		$('category-up-' + category_id).style.display = 'none';

	var el = li2.nextSibling;
	var vis = false;
	while (el != null) {
		if (el.nodeType == Node.ELEMENT_NODE && el.nodeName == 'li') {
			vis = true;
			break;
		}
		el = el.nextSibling;
	}
	if (!vis)
		$('category-down-' + category2_id).style.display = 'none';

	el = li2.previousSibling;
	vis = false;
	while (el != null) {
		if (el.nodeType == Node.ELEMENT_NODE && el.nodeName == 'li') {
			vis = true;
			break;
		}
		el = el.previousSibling;
	}
	if (!vis)
		$('category-up-' + category2_id).style.display = 'none';
}

function move(cid, dir)
{
	var serv = _ajax_service("move-category", { 'cid': String(cid), 'direction': String(dir) });
	serv.onSuccess = moved;
	advAJAX.get(serv);
}

function moveUp(cid)
{
	move(cid, 1);
}

function moveDown(cid)
{
	move(cid, -1);
}

CategoryTree.onSelect = function(cid) {
	$('preview').style.display = '';
	$('photo-preview').style.display = 'none';

	previewCategory(cid);
}

CategoryTree.onFill = function(cid, cat, cat_pos, cats_total) {

	var subs = 0;
	var ph = 0;
	var tph = 0;
	var substr = "";

	if (cat.getElementsByTagName('photos-count')[0].firstChild)
		ph = cat.getElementsByTagName('photos-count')[0].firstChild.nodeValue;
	if (cat.getElementsByTagName('total-photos-count')[0].firstChild)
		tph = cat.getElementsByTagName('total-photos-count')[0].firstChild.nodeValue;
	if (cat.getElementsByTagName('subcategories-count')[0].firstChild)
		subs = cat.getElementsByTagName('subcategories-count')[0].firstChild.nodeValue;
	substr = ' (' + subs + ') (' + ph + '/' + tph + ')';

	domEl('span', substr, { 'class': 'details' }, getElementsByClass('tree-item', $('category-li-' + cid), 'span'));

	domEl('span',
		domEl('a',
			domEl('img', '', { 'src': _base_url + '/images/icons/control_fastforward-sm.png', 'alt': '', 'class': 'icon', 'onclick': 'showIcons(' + cid + '); return false;', 'id': 'category-icons-switch-' + cid }),
		{ 'href': '#' })
	, { 'class': 'icons' }, $('category-li-' + cid));
	domEl('span', [
		domEl('a',
			domEl('img', '', { 'src': _base_url + '/images/icons/pencil-sm.png', 'alt': '', 'class': 'icon' }),
		{ 'href': url('adm-edit-category', { 'cid': cid, 'ref': _self_url }) }),
		domEl('a',
			domEl('img', '', { 'src': _base_url + '/images/icons/cross-sm.png', 'alt': '', 'class': 'icon' }),
		{ 'href': url('adm-remove-category', { 'cid': cid, 'ref': _self_url }) }),
		domEl('a',
			domEl('img', '', { 'src': _base_url + '/images/icons/arrow_up-sm.png', 'alt': '', 'class': 'icon' }),
		{ 'href': '#', 'id': 'category-up-' + cid, 'onclick': 'moveUp(' + cid + '); return false;' }),
		domEl('a',
			domEl('img', '', { 'src': _base_url + '/images/icons/arrow_down-sm.png', 'alt': '', 'class': 'icon' }),
		{ 'href': '#', 'id': 'category-down-' + cid, 'onclick': 'moveDown(' + cid + '); return false;' })
	], { 'class': 'icons', 'id': 'category-icons-' + cid, 'style': 'display: none' }, $('category-li-' + cid));

	if (cat_pos == 0) {
		$('category-up-' + cid).style.display = 'none';
	}
	if (cat_pos >= cats_total - 1) {
		$('category-down-' + cid).style.display = 'none';
	}
}

var Rules = {
	'#refresh-category1': function(element) {
		element.onclick = function() {
			CategoryTree.fill(0);
			return false;
		}
	},
	'#refresh-category2': function(element) {
		element.onclick = function() {
			CategoryTree.fill(0);
			return false;
		}
	}
}

Behaviour.register(Rules);

Behaviour.addLoadEvent(function() {
	CategoryTree.fill(0);
});

var domTT_styleClass = 'tooltip';
