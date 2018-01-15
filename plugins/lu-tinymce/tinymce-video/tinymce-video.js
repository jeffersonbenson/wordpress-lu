(function() {
	tinymce.PluginManager.add( 'simple_video', function( editor, url ) {
		// Add Button to Visual Editor Toolbar
		editor.addButton('simple_video', {
			title: 'Insert/replace video',
			cmd: 'simple_video',
			image: url + '/play_icon.png',
		});

		// Add Command when Button Clicked
		editor.addCommand('simple_video', function() {
			// Check we have selected some text selected
			var text = editor.selection.getContent({
				'format': 'html'
			});

			//note: any selected text will be replaced with video, otherwise will output video wherever cursor is

			var file = wp.media({ 
				title: 'Upload Video',
				multiple: false
			}).open();
			file.on('select', function(e){
				// This will return the selected file from the Media Uploader, the result is an object
				var uploaded_file = file.state().get('selection').first();
				// We convert uploaded_file to a JSON object to make accessing it easier
				// Output to the console uploaded_file
				console.log(uploaded_file);
				var file_url = uploaded_file.toJSON().url;
				// Let's assign the url value to the input field
				// $('#file_url').val(file_url);
				console.log(file_url);

				//generate the html for our video
				var vidhtml = '<video controls src="' + file_url + '" width="100%" height="auto" preload="auto" poster="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"></video>'

				// Insert selected text back into editor, wrapping it in an anchor tag
				editor.execCommand('mceReplaceContent', false, vidhtml);
			});
		});
	});
})();