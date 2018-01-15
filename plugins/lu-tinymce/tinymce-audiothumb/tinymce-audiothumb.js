(function() {
	tinymce.PluginManager.add( 'audio_thumb', function( editor, url ) {
		// Add Button to Visual Editor Toolbar
		editor.addButton('audio_thumb', {
			title: 'Insert/replace audio thumbnail',
			cmd: 'audio_thumb',
			image: url + '/audio_thumb.png',
		});

		// Add Command when Button Clicked
		editor.addCommand('audio_thumb', function() {
			// Check we have selected some text selected
			var text = editor.selection.getContent({
				'format': 'html'
			});

			//note: any selected text will be replaced with audio, otherwise will output audio wherever cursor is

			var file = wp.media({ 
				title: 'Select/Upload Image',
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
				var thumbhtml = '<div class="audiothumb"><img class="" src="' + file_url + '" width="100%" height="auto"></img><svg class="playbutton" viewBox="0 0 24 24"><use xlink:href="#speakericon"/></svg></div>'

				// Insert selected text back into editor, wrapping it in an anchor tag
				editor.execCommand('mceReplaceContent', false, thumbhtml);
			});
		});
	});
})();