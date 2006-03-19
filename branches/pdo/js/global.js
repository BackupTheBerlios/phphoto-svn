


function get_by_id(id) {
	itm = null;
	if (document.getElementById)
		itm = document.getElementById(id);
	else if (document.all)
		itm = document.all[id];
	else if (document.layers)
		itm = document.layers[id];
	
	return itm;
}

function show_hide(id1, id2) {
	if (id1 != '')
		toggle_view(id1);
	if (id2 != '')
		toggle_view(id2);
}

function toggle_view(id) {

	if (!id)
		return;

	if (itm = get_by_id(id)) {
		if (itm.style.display == "none") {
			show_item(itm);
		} else {
			hide_item(itm);
		}
	}
}

function hide_item(itm) {
	if (!itm)
		return;
	itm.style.display = "none";
}

function show_item(itm) {
	if (!itm)
		return;
	itm.style.display = "";
}

function block_toggle(id) {
	show_hide("b_" + id + "_hdr_on", "b_" + id + "_hdr_off");
	toggle_view("b_" + id + "_content");
}

function change_class(id, cl) {
	itm = get_by_id(id);
	if (itm)
		itm.className = cl;
}

