/* $Id$ */

function AutoCompleteInput(id, parent) {
	this._elem = id;
	this._open = false;
	this._items = new Array();	// All items
	this._f_items = new Array();	// filtered items
	this._input_val;
	this._selected = -1;
	this._parent = parent;
}

AutoCompleteInput.prototype.init = function() {
	var me = this;

	var elem = $(this._elem);

	elem.setAttribute("autocomplete","off");

	this.onkeydown = function(ev) {
		var key = getKeyCode(ev);
		switch (key) {
			case KEY_ESCAPE:
				me.close();
				break;
			case KEY_DOWN:
				me.open();
				if (me._selected < 0 || me._selected + 1 >= me._f_items.length)
					me.select(0);
				else
					me.select(me._selected + 1);
				break;
			case KEY_UP:
				me.open();
				if (me._selected <= 0)
					me.select(me._f_items.length - 1);
				else
					me.select(me._selected - 1);
				break;
			case KEY_ENTER:
			case KEY_TAB:
				if (me._selected >= 0 && me._open) {
					cancelEvent(ev);
				}
				break;
		}
	}

	this.onkeyup = function(ev) {
		var key = getKeyCode(ev);
		switch (key) {
			case KEY_TAB:
			case KEY_ENTER:
				if (me._selected >= 0 && me._open) {
					me.liClick(me._selected);
					cancelEvent(ev);
				}
				break;
			case KEY_ESCAPE:
				break;
			default:
				if (me._input_val != this.value) {
					me.open();
					me._input_val = this.value;
					me._f_items = me._items;
					me.fillList();
				}
				break;
		}
	}

	this.onfocus = function(ev) {
		me._input_val = this.value;
		if (me._input_val) {
			me.open();
		}
		if (me._open) {
			me._f_items = me._items;
			me.fillList();
		}
	}

	this.onblur = function(ev) {
		me.close();
	}

	addEvent(elem, 'keydown', this.onkeydown, false);
	addEvent(elem, 'keyup', this.onkeyup, false);
	//addEvent(elem, 'blur', this.onblur, false);
	addEvent(elem, 'focus', this.onfocus, false);

}

AutoCompleteInput.prototype.open = function(elem) {
	if (!this._open) {

		var elem = $(this._elem);
		var div = domEl('div', '', {'id': this._elem + '-ac-div', 'class': 'ac-list'});

		var y = 0;
		var x = 0;
		for (var p = elem; p; p = p.offsetParent) {
			y += p.offsetTop;
			x += p.offsetLeft;
		}
		y += elem.offsetHeight;
		div.style.top = y + 'px';
		div.style.left = x + 'px';


		this._parent.appendChild(div);
		this._open = true;
		this._f_items = this._items;
	}
}

AutoCompleteInput.prototype.close = function() {
	if (this._open) {
		var aclist = $(this._elem + '-ac-div');
		aclist.parentNode.removeChild(aclist);
		this._open = false;
		this._input_val = '';
	}
}

AutoCompleteInput.prototype.fillList = function() {
	var el = $(this._elem);

	//domEl('', '', '', ul, true);
	this._selected = -1;
	domEl('ul', '', { 'id': this._elem + '-ac-ul' }, $(this._elem + '-ac-div'), true);
	var ul = $(this._elem + '-ac-ul');

	var itms = new Array();
	var el_val = this._input_val;
	var n = 0;
	for (var i = 0; i < this._f_items.length; i++) {
		var s = this._f_items[i];
		if(s.toLowerCase().indexOf(el_val.toLowerCase()) == "0") {
			itms.push(s);
			var li = domEl('li', s, { 'onclick': "this._ac.liClick('" + n + "'); ", 'id': this._elem + '-ac-li-' + n });
			//var li = domEl('li', s, { 'onclick': "alert('" + s + "'); " });
			li._ac = this;
			ul.appendChild(li);
			n++;
		}
	}
	this._f_items = itms;
	if (this._f_items.length == 0)
		this.close();
}

AutoCompleteInput.prototype.liClick = function(i) {
	this.select(i);
	$(this._elem).value = this._f_items[i];
	this.close();
}

AutoCompleteInput.prototype.select = function(i) {
	if (this._selected >= 0) {
		$(this._elem + '-ac-li-' + this._selected).className = '';
	}
	if (i >= 0 && i < this._f_items.length) {
		this._selected = i;
		$(this._elem + '-ac-li-' + i).className = 'selected';
	} else {
		this._selected = -1;
	}
}
