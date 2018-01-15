(function() {
	tinymce.PluginManager.add( 'lightbox', function( editor, url ) {
		// Add Button to Visual Editor Toolbar
		editor.addButton('lightbox', {
			title: 'Insert lightbox',
			cmd: 'lightbox',
			image: url + '/lightbox_icon.png',
		});

		// Add Command when Button Clicked
		editor.addCommand('lightbox', function() {
			// Check we have selected some text selected
			var text = editor.selection.getContent({
				'format': 'html'
			});

			var lightboxhtml = '<div class="lightboxcontentwrapper"><div class="openinlightbox">replace me!<div class="addtolightbox">replace me!</div></div></div>';

			editor.execCommand('mceReplaceContent', false, lightboxhtml);
		});
	});
})();