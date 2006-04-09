/* $Id$ */

var Rules = {
	'#menu li.menu ul li': function(element) {
		element.onmouseover = function() {
			element.className = "hover";
		};
		element.onmouseout = function() {
			element.className = "";
		};
	},
	'form fieldset': function(element) {
		element.onmouseover = function() {
			element.className = "hover";
		};
		element.onmouseout = function() {
			element.className = "";
		};
	},
	'tr.odd': function(element) {
		element.onmouseover = function() {
			element.className = "hilight";
		};
		element.onmouseout = function() {
			element.className = "odd";
		};
	},
	'tr.even': function(element) {
		element.onmouseover = function() {
			element.className = "hilight";
		};
		element.onmouseout = function() {
			element.className = "even";
		};
	}
}

Behaviour.register(Rules);
