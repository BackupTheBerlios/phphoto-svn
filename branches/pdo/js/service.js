/* $Id$ */

function registerCheckLogin(t) {
	warn = document.getElementById("loginexistswarn");
	if (warn) {
		warn_p = warn.parentNode;
		if (warn_p)
			warn_p.removeChild(warn);
	}
	
	xml = t.responseXML;
	if (xml.getElementsByTagName("exists").length > 0) {
		warn = document.createElement("span");
		warn.setAttribute("id", "loginexistswarn");
		warn.setAttribute("class", "warning");
		warn_text = document.createTextNode("Login jest już zajęty");
		warn.appendChild(warn_text);
		field = document.getElementById("user_login");
		warn_p = field.parentNode;
		if (field && warn_p)
			warn_p.insertBefore(warn, field.nextSibling);
	}
}

var Rules = {
	'#register_form #user_login': function(element) {
		element.onblur = function() {
			el = document.getElementById("user_login");
			if (el) {
				var serv = _ajax_service("checkloginexists", { login: el.value });
				serv.onSuccess = registerCheckLogin;
				advAJAX.get(serv);
			}
		}
	}
}

Behaviour.register(Rules);
