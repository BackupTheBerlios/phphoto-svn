/* $Id$ */

var Rules = {
	'#submit-button': function(el) {
		el.onclick = function() {
			var cids = new Array();
			for (var i in _selected_categories) {
				if (_selected_categories[i] == 1)
					cids.push(i);
			}
			$('cid').setAttribute('value', cids.toString());
			return true;
		}
	}
}

Behaviour.register(Rules);

Behaviour.addLoadEvent(function() {
	domEl('input', '', { 'type': 'hidden', 'name': 'cid', 'id': 'cid' }, $('upload-form'));
});
