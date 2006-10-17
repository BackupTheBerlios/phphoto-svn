/* $Id$ */

CategoryTree.onBeforeItemFill = function(cid) {
	return (cid != $('cid').getAttribute('value'));
}

CategoryTree.onSelect = function(cid) {
	$('category_parent').setAttribute('value', cid);
}

CategoryTree.onDeselect = function(cid) {
	$('category_parent').setAttribute('value', '');
}

var Rules = {
}

Behaviour.register(Rules);

Behaviour.addLoadEvent(function() {
	CategoryTree.allowDeselect = true;
	CategoryTree.preselected = $('orig_category_parent').getAttribute('value');
	CategoryTree.fill(0);
});

