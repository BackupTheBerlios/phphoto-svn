
function _ajax_service(meth) {
	var obj = new Object;
	obj.url = _ajax_service_base_url;// + "&method=" + encodeURIComponent(meth);
	obj.parameters = new Object;
	obj.parameters["method"] = meth;
	if (arguments[1]) {
		var opt = arguments[1];
		for (var i in opt) {
			//url += "&" + encodeURIComponent(i) + "=" + encodeURIComponent(opt[i]);
			obj.parameters[i] = opt[i];
		}
	}
	return obj;
}

