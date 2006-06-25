/* $Id$ */

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
