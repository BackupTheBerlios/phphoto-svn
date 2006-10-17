
function _ajax_service(meth) {
	var obj = new Object;
	obj.url = _ajax_service_base_url;
	obj.method = _ajax_http_method;
	obj.parameters = new Object;
	obj.parameters["method"] = meth;
	if (arguments[1]) {
		var opt = arguments[1];
		for (var i in opt) {
			//url += "&" + encodeURIComponent(i) + "=" + encodeURIComponent(opt[i]);
			obj.parameters[i] = opt[i];
//			alert(i + ': ' + opt[i]);
		}
	}
	return obj;
}

function ajaxIndicator(el) {
	var img = '/images/indicators/indicator.gif';
	if (arguments[1])
		img = arguments[1];
	return domEl('img', '', { 'src': _base_url + img, 'alt': 'Loading...', 'title': 'Loading...' }, el, 1);
}

function setAjaxStatus(msg, target, error) {
	if (error)
		target.className = 'ajaxerror';
	else
		target.className = 'ajaxstatus';

	domEl('', msg, '', target, 1);
}

function handleAjaxError(t, target) {
	service = xml.getElementsByTagName('service')[0];
	error = service.getElementsByTagName('error')[0];
	if (error.getElementsByTagName('exception').length > 0) {
		setAjaxStatus(error.getElementsByTagName('exception')[0].firstChild.nodeValue, target, 1);
	}
}
