	function ActiveInPlaceEditors(options) {
		try {
		var url = options.url;
		var section = options.section;

		var editors = [];
		
		function createEditor(element) {
			try {
			editors.push(new Ajax.InPlaceEditor(
					element,
					url,
					{
						onComplete: function (){
							document.fire(section+":onAfterEdit");
						},
						onCreate: function() {
							document.fire(section+":onBeforeEdit");
						}
					}
			));
			}
			catch (e) {console.log(e);}
		}
		
		function removeEditors() {
			editors.each(function(editor) { editor.dispose(); });
		}
		
		function addEditors() {
			var pre = "#" + section + " .entry";
			$$(pre + " .heading,"+pre + " .data").each(function (element) {
				createEditor(element);
			});
		}
		
		function init() {
			console.log("test");
			addEditors();
			console.log(editors);
		}

		if (document.loaded) {
			init();
		} else {
			document.observe('dom:loaded', function () { 
				init();
			});
		}	
		
		function onBeforeUpdate() {
			if(options.onBeforeUpdate) {
				options.onBeforeUpdate();
			}
			document.stopObserving(section+':onBeforeUpdate', onBeforeUpdate);
			removeEditors();
		}

		function onAfterUpdate() {
			if(options.onAfterUpdate) {
				options.onAfterUpdate();
			}
			document.stopObserving(section+':onAfterUpdate', onAfterUpdate);
			addEditors();
		}

		document.observe(section+':onBeforeUpdate', onBeforeUpdate);
		document.observe(section+':onAfterUpdate', onAfterUpdate);
		
		}
		catch (e) {console.log(e);}
	}