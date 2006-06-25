/* $Id$ */

function ajaxIndicator(el) {
	return domEl('img', '', { 'src': _base_url + '/images/indicators/indicator.gif', 'alt': 'Loading...', 'title': 'Loading...' }, el, 1);
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

function updatedGroupMembersCount(t) {

	gid = 0;

	xml = t.responseXML;

	query = xml.getElementsByTagName('query')[0];
	gid = query.getElementsByTagName('group-id')[0].firstChild.nodeValue;

	service = xml.getElementsByTagName('service')[0];

	if (service.getAttribute('status') != 'success') {
		members = "?";
	} else {
		response = xml.getElementsByTagName('response')[0];
		members = response.getElementsByTagName('count')[0].firstChild.nodeValue;
	}

	domEl('', members, '', $('members-count-' + gid), 1);
}

function updateGroupMembersCount(gid) {
	//ajaxIndicator($('members-count-' + gid));

	var serv = _ajax_service("count-group-members", { 'gid': String(gid) });
	serv.onSuccess = updatedGroupMembersCount;
	advAJAX.get(serv);
}

function addGroupMemberRow(tbody, group_id, member)
{
	login = ' ';
	name = ' ';
	title = ' ';
	add_time = ' ';
	added_by = ' ';
	user_id = 0;
	allow_remove = 0;
	if (member.getElementsByTagName('user-login')[0].firstChild)
		login = member.getElementsByTagName('user-login')[0].firstChild.nodeValue;
	if (member.getElementsByTagName('user-name')[0].firstChild)
		name = member.getElementsByTagName('user-name')[0].firstChild.nodeValue;
	if (member.getElementsByTagName('user-title')[0].firstChild)
		title = member.getElementsByTagName('user-title')[0].firstChild.nodeValue;
	if (member.getElementsByTagName('add-time')[0].firstChild)
		add_time = member.getElementsByTagName('add-time')[0].firstChild.nodeValue;
	if (member.getElementsByTagName('addedby-login')[0].firstChild)
		added_by = member.getElementsByTagName('addedby-login')[0].firstChild.nodeValue;
	if (member.getElementsByTagName('user-id')[0].firstChild)
		user_id = member.getElementsByTagName('user-id')[0].firstChild.nodeValue;
	if (member.getElementsByTagName('allow-remove')[0].firstChild)
		allow_remove = member.getElementsByTagName('allow-remove')[0].firstChild.nodeValue;
	domEl('tr', [
		domEl('td', login),
		domEl('td', [
			domEl('', name, '')//,
			//domEl('div', title, { 'class': 'details' })
		]),
		domEl('td', [
			domEl('', add_time)//,
			//domEl('div', added_by, { 'class': 'details' })
		]),
		domEl('td',
			allow_remove == '1' ? domEl('a',
				domEl('img', '', { 'src': _base_url + '/images/icons/b_drop.png', 'alt': 'Usuń', 'title': 'Usuń', 'class': 'icon'}),
			{ 'href': '#', 'onclick': 'removeGroupMember(' + group_id + ', ' + user_id + ');return false;' }) : '',
		{ 'class': 'icon', 'id': 'del-' + group_id + '-' + user_id })
	], { 'id': 'member-' + group_id + '-' + user_id }, tbody);
}

function getGroupMembers(t) {

	group_id = 0;
	allow_add = 0;

	xml = t.responseXML;

	response = xml.getElementsByTagName('response')[0];
	query = xml.getElementsByTagName('query')[0];

	group_id = query.getElementsByTagName('group-id')[0].firstChild.nodeValue;
	element = document.getElementById(group_id);

	domEl('', '', '', $("members-table-" + group_id), 1);

	members = response.getElementsByTagName('member');

	tbody = domEl('tbody', '', { 'id': 'members-tbody-' + group_id });
	for (var i = 0, member; member = members[i]; i++) {
		addGroupMemberRow(tbody, group_id, member);
	}

	content =
		domEl('table', [
			domEl('thead', [
				domEl('tr', [
					domEl('th', 'Login'),
					domEl('th', [
						domEl('', 'Nazwa', '')//,
						//domEl('div', 'Tytuł', { 'class': 'details' })
					]),
					domEl('th', [
						domEl('', 'Dodany')//,
						//domEl('div', 'Przez', { 'class': 'details' })
					]),
					domEl('th', '', '')
				]),
			]),
			tbody
		]);

	if (response.getElementsByTagName('allow-add')[0].firstChild)
		allow_add = response.getElementsByTagName('allow-add')[0].firstChild.nodeValue;

	tab = domEl('tr', [
		domEl('td', '', { 'colspan': '2' }),
		domEl('td', [
			content,
			allow_add != '1' ? '' : domEl('form', [
				domEl('input', '', { 'type': 'text', 'id': 'member-login-' + group_id, 'name': 'member-login-' + group_id }),
				domEl('input', '', { 'type': 'submit', 'id': 'member-login-add-' + group_id, 'name': 'member-login-add-' + group_id, 'value': 'Dodaj' }),
			], { 'method': 'post', 'action': '#', 'onsubmit': 'addGroupMember(' + group_id + '); return false;',  }),
			domEl('div', '', { 'id': 'member-status-' + group_id, 'class': 'ajaxstatus' })
		], { 'colspan': '2' }),
		domEl('td', '', {'colspan': '6' })
		],
		{ 'id' : 'members-table-' + group_id }
	);


	insertAfter(element.parentNode, tab, element);
}

function removedGroupMember(t)
{
	gid = 0;
	uid = 0;

	xml = t.responseXML;

	query = xml.getElementsByTagName('query')[0];
	gid = query.getElementsByTagName('group-id')[0].firstChild.nodeValue;
	uid = query.getElementsByTagName('user-id')[0].firstChild.nodeValue;

	td = document.getElementById('del-' + gid + '-' + uid);

	service = xml.getElementsByTagName('service')[0];

	if (service.getAttribute('status') != 'success') {
		domEl('a',
			domEl('img', '', { 'src': _base_url + '/images/icons/b_drop.png', 'alt': 'Usuń', 'title': 'Usuń', 'class': 'icon'}),
			{ 'href': '#', 'onclick': 'removeGroupMember(' + gid + ', ' + uid + ');return false;' }, td, 1);
		handleAjaxError(t, $('member-status-' + gid));
	} else {
		domEl('', '', '', $("member-" + gid + '-' + uid), 1);
		updateGroupMembersCount(gid);
		setAjaxStatus('Użytkownik został usunięty.', $('member-status-' + gid), 0);
	}
}

function removeGroupMember(group_id, user_id)
{
	ajaxIndicator($('del-' + group_id + '-' + user_id));

	var serv = _ajax_service("remove-group-member", { 'gid': String(group_id), 'uid': String(user_id) });
	serv.onSuccess = removedGroupMember;
	advAJAX.get(serv);

	return false;
}

function addedGroupMember(t)
{
	group_id = 0;

	xml = t.responseXML;

	service = xml.getElementsByTagName('service')[0];
	query = xml.getElementsByTagName('query')[0];
	group_id = query.getElementsByTagName('group-id')[0].firstChild.nodeValue;

	if (service.getAttribute('status') == 'success') {
		response = xml.getElementsByTagName('response')[0];
		element = document.getElementById(group_id);

		member = response.getElementsByTagName('member')[0];

		tbody = document.getElementById('members-tbody-' + group_id);
		if (tbody)
			addGroupMemberRow(tbody, group_id, member);
		updateGroupMembersCount(group_id);

		setAjaxStatus('Użytkownik został dodany.', $('member-status-' + group_id), 0);
	} else {
		handleAjaxError(t, $('member-status-' + group_id));
	}
}

function addGroupMember(gid)
{
	ajaxIndicator($('member-status-' + group_id));

	el = document.getElementById('member-login-' + gid);

	var serv = _ajax_service("add-group-member", { 'gid': String(gid), 'user-login': String(el.value) });
	serv.onSuccess = addedGroupMember;
	advAJAX.get(serv);

	el.value = '';
}

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
	},
	'div.buttons a.button' : function(element) {
		element.onmouseover = function() {
			element.className = "button_hl";
		};
		element.onmouseout = function() {
			element.className = "button";
		};
	},
	'table#groups-table td a.members-icon': function(element) {
		element.onclick = function() {

			gid = element.parentNode.parentNode.getAttribute('id');

			el = document.getElementById("members-table-" + gid);

			if (el) {
				domEl('', '', '', el, 1);
				return false;
			}

			tab = domEl('tr', [
				domEl('td', '', { 'colspan': '2' } ),
				domEl('td',
					ajaxIndicator(''),
					{ 'colspan': '4' }),
				domEl('td', '', {'colspan': '4' })
				],
				{ 'id' : 'members-table-' + gid}
			);

			insertAfter(element.parentNode.parentNode.parentNode, tab, element.parentNode.parentNode);

			var serv = _ajax_service("get-group-members", { 'gid': gid });
			serv.onSuccess = getGroupMembers;
			advAJAX.get(serv);

			return false;
		};
	}
}

Behaviour.register(Rules);
