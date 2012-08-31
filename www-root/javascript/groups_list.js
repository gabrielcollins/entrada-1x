var groups_sortable;
var groups;
var idx = 0;
function cleanupList() {
	ol = $('group_container');
	if(ol.immediateDescendants().length > 0) {
		ol.show();
		$('group_notice').hide();
	} else {
		ol.hide();
		$('group_notice').show();
	}
	groups_sortable = Sortable.create('group_container', {
		onUpdate: writeOrder
	});
	writeOrder(null);
}

function writeOrder(container) {
	$('group_order').value = Sortable.sequence('group_container').join(',');	
}

document.observe('click', function(e, el) {
  if (el = e.findElement('.remove')) {
    $(el).up().remove();
    cleanupList();
  }
});


document.observe("dom:loaded", function() {        

	$('group_ids').observe('change', function(event){
		select = $('group_ids');
		option = select.options[select.selectedIndex];
		li = new Element('li', {id: 'type_'+option.value, 'class': 'group'});
		li.insert(option.text+"  ");
		li.insert(new Element('span', {style: 'cursor:pointer;float:right;','class': 'remove'}).insert(new Element('img', {src: DELETE_IMAGE_URL})));
		$('group_container').insert(li);
		cleanupList();
		select.selectedIndex = 0;
		//fires the change event for the list holding the added elements
		if ("fireEvent" in $('group_order'))
			$('group_order').fireEvent("onchange");
		else
		{
			var evt = document.createEvent("HTMLEvents");
			evt.initEvent("change", false, true);
			$('group_order').dispatchEvent(evt);
		}
	});
	cleanupList();
});