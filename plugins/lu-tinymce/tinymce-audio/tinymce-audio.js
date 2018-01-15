(function() {
	tinymce.PluginManager.add( 'simple_audio', function( editor, url ) {
		// Add Button to Visual Editor Toolbar
		editor.addButton('simple_audio', {
			title: 'Insert/replace audio',
			cmd: 'simple_audio',
			image: url + '/audio_icon.png',
		});

		// Add Command when Button Clicked
		editor.addCommand('simple_audio', function() {
			// Check we have selected some text selected
			var text = editor.selection.getContent({
				'format': 'html'
			});

			//note: any selected text will be replaced with audio, otherwise will output audio wherever cursor is

			var file = wp.media({ 
				title: 'Upload Audio File (Please use mp3 or AAC encoded mp4)',
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

				//generate the html for our audio
				var html = '<audio controls src="' + file_url + '"></audio>'

				// Insert selected text back into editor, wrapping it in an anchor tag
				editor.execCommand('mceReplaceContent', false, html);
			});
		});
	});
})();