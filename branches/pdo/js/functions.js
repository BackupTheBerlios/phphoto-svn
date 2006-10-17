/* $Id$ */

var KEY_TAB = 9;
var KEY_ENTER = 13;
var KEY_ESCAPE = 27;
var KEY_UP = 38;
var KEY_DOWN = 40;

function url(action) {
	var s = _base_url + '/index.php?action=' + encodeURIComponent(action);
	if (arguments[1]) {
		var opt = arguments[1];
		for (var i in opt) {
			s += "&" + encodeURIComponent(String(i)) + "=" + encodeURIComponent(String(opt[i]));
		}
	}
	return s;
}


function $() {
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')
			element = document.getElementById(element);
		if (arguments.length == 1)
			return element;
		elements.push(element);
	}
	return elements;
}

/*
	domEl() function - painless DOM manipulation
	written by Pawel Knapik  //  pawel.saikko.com
*/

var domEl = function(e,c,a,p,x) {
	if(e||c) {
		c=(typeof c=='string'||(typeof c=='object'&&!c.length))?[c]:c;
		e=(!e&&c.length==1)?document.createTextNode(c[0]):e;
		var n = (typeof e=='string')?document.createElement(e) :
		!(e&&e===c[0])?e.cloneNode(false):e.cloneNode(true);
		if(e.nodeType!=3) {
			c[0]===e?c[0]='':'';
			for(var i=0,j=c.length;i<j;i++) typeof c[i]=='string'?
			n.appendChild(document.createTextNode(c[i])):
			n.appendChild(c[i].cloneNode(true));
			if(a){for (var i in a) i=='class'?n.className=a[i]:n.setAttribute(i,a[i]);}
		}
	}
	if(!p)return n;
	p=(typeof p=='object'&&!p.length)?[p]:p;
	for(var i=(p.length-1);i>=0;i--) {
		if(x){while(p[i].firstChild)p[i].removeChild(p[i].firstChild);
		if(!e&&!c&&p[i].parentNode)p[i].parentNode.removeChild(p[i]);}
		if(n) p[i].appendChild(n.cloneNode(true));
	}
}

function insertAfter(parent, node, referenceNode) {
	parent.insertBefore(node, referenceNode.nextSibling);
}

function toggle(obj) {
	var el = $(obj);
	if ( el.style.display != 'none' ) {
		el.style.display = 'none';
	} else {
		el.style.display = '';
	}
}

function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp('(^|\\s)'+searchClass+'(\\s|$)');
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

function getElementValue(el, tag) {
	var val = null;
	if (arguments.length == 3)
		val = arguments[2];

	var ell = el.getElementsByTagName(tag);

	if (ell.length > 0 && ell[0].firstChild && ell[0].firstChild.nodeType == Node.TEXT_NODE)
		val = ell[0].firstChild.nodeValue;

	return val;
}

function insertFirst(parent, node) {
	parent.insertBefore(node, parent.firstChild);
}

function addEvent(elm, evType, fn, useCapture) {
	if (elm.addEventListener) {
		elm.addEventListener(evType, fn, useCapture);
		return true;
	} else if (elm.attachEvent) {
		var r = elm.attachEvent('on' + evType, fn);
		return r;
	} else {
		elm['on' + evType] = fn;
	}
}

function getKeyCode(ev) {
	if (ev) {
		return ev.keyCode;
	} else if (window.event) {
		return window.event.keyCode;
	}
}

function cancelEvent(e) {
	if (window.event) window.event.returnValue = false
	else e.preventDefault()
}
