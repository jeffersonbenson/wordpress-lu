(function() {
	tinymce.PluginManager.add( 'insert_iframe', function( editor, url ) {
		
		// Add Button to Visual Editor Toolbar
		editor.addButton('insert_iframe', {
			title: 'Insert/edit Iframe',
			image: url + '/icon.svg',
			onclick: function(a) {
				var selectedBlock = jQuery(editor.selection.getNode()).find('iframe');
				var width = "";
				var height = "";
				var source = "";
				
				// select existing tooltip
				if(selectedBlock){
					console.log(selectedBlock);
					console.log(selectedBlock.attr('src'));
					source = selectedBlock.attr('src');
					height = selectedBlock.attr('height');
					width = selectedBlock.attr('width');
				}

				// Open window
				editor.windowManager.open({
					title: 'Insert Iframe',
					body: [
						{type: 'textbox', name: 'iframe_src', label: 'Source', value: source},
						{type: 'textbox', name: 'iframe_width', label: 'Width', value: width},
						{type: 'textbox', name: 'iframe_height', label: 'Height', value: height}
					],
					onsubmit: function(e) {
						// Insert content when the window form is submitted
						if(e.data.insert_iframe != ''){
							content = '<iframe src="' + e.data.iframe_src + '" width="'+e.data.iframe_width+'" height="'+e.data.iframe_height+'"></iframe>';
						}
						editor.insertContent(content);
					}
				});
			}
		});

	});
})();
