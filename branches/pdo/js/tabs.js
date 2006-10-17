/* $Id$ */

function TabSheet(id) {
	this._container = id;
	this._pages = new Array();
	this._active_page = -1;
}

TabSheet.prototype.addPage = function(id, title) {
	var page = new Object();
	page['title'] = title;
	page['id'] = id;
	this._pages.push(page);
}

TabSheet.prototype.build = function() {
	var tabs = domEl('div', '', { 'class': 'tabs-header' });
	for (var i = 0, page; page = this._pages[i]; i++) {
		var hdr = domEl(
				'span',
				page['title'],
				{ 
					'onclick': 'this._tabsheet.showPage(' + i + ')',
					'id': 'ts-hdr-' + page['id']
				});
		hdr._tabsheet = this;
		tabs.appendChild(hdr);
		$(page['id']).style.display = 'none';
	}
	insertFirst($(this._container), tabs);
}

TabSheet.prototype.showPage = function(i) {
	if (i == this._active_page)
		return;
	if (this._active_page >= 0) {
		$(this._pages[this._active_page]['id']).style.display = 'none';
		$('ts-hdr-' + this._pages[this._active_page]['id']).className = '';
	}
	this._active_page = i;
	$(this._pages[this._active_page]['id']).style.display = '';
	$('ts-hdr-' + this._pages[this._active_page]['id']).className = 'selected';
}

