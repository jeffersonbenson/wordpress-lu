(function() {
	tinymce.PluginManager.add( 'vocab_tooltip', function( editor, url ) {
		
		// Add Button to Visual Editor Toolbar
		editor.addButton('vocab_tooltip', {
			title: 'Insert/edit vocab tooltip',
			image: url + '/icon.png',
			onclick: function(a) {
				var text = editor.selection.getContent();
				console.log('selection content: ' + text);

				//give error if nothing selected
				if ( text.length === 0 ) {
					alert( 'Please select some text.' );
					return;
				}

				//create a searchable DOM element for the selected text
				var fakediv = document.createElement('div');
				fakediv.innerHTML = text;
				var selectedBlock = fakediv;
				console.log('new selectedBlock is');
				console.log(selectedBlock);
				console.log(selectedBlock.html);


				//we need to get the word (always) and the def(when editing an existing tip)

				//def is text in .tooltip after .tipheader and .def are removed from it
				var vocabword;
				var definition;

				//check if were creating or editing
				var tooltip = selectedBlock.querySelector('.tooltip');
				if(tooltip != null){
					//were editing an existing tip

					//remove junk from the definition
					var foundtipheader = tooltip.querySelector('.tipheader');
					if(foundtipheader != null){
						(foundtipheader.parentNode.removeChild(foundtipheader));
					}
					var foundvocabword = tooltip.querySelector('.vocabword');
					if(foundvocabword != null){
						(foundvocabword.parentNode.removeChild(foundvocabword));
					}

					definition = tooltip.innerText;
				
					//get the word itself
					var vocabBlock = selectedBlock
					var foundinput = vocabBlock.querySelector('input');
					foundinput.parentNode.removeChild(foundinput);
					var foundspan = vocabBlock.querySelector('span');
					foundspan.parentNode.removeChild(foundspan);
					vocabword= vocabBlock.innerText;

					console.log('definition is ' + definition + '. vocabword is ' +vocabword);
				}else{
					vocabword = text;
					definition = '';
					console.log('vocabword is ' + vocabword);
				}
				


				// Open window
				editor.windowManager.open({
					title: 'Vocab Tooltip',
					body: [
						{type: 'textbox', name: 'definition', label: 'Definition', value: definition}
					],
					// Insert content when the window form is submitted
					onsubmit: function(e) {
						console.log('saving tooltip. text is: ' + vocabword)
						//returns 0 or more spaces, followed by 0 or more commas, followed by 0 or more spaces, as long as the whole group is the end of our text
						//short version: grabs any spaces or commas from the end
						var trailingCharRegex = new RegExp('\\s*,*\\s*$');
						//grab spaces and commas at the end
						var trailingchars = text.match(trailingCharRegex);
						// console.log('trailing characters (will be moved outside of tooltip): ' + trailingchars);
						//removes spaces and commas at the end
						vocabword = vocabword.replace(trailingCharRegex, '');
						console.log('modified text is: ' + vocabword)

						if(e.data.definition != ''){
							console.log('definition is: ' + e.data.definition);
							content = '<label class="opentooltip"><input type="radio" name="tooltips"/>' + vocabword + '<span class="tooltip"><span class="tipheader">Definition<span class="closebtn" onclick="closeTooltips(event, this)"></span></span><span class="vocabword">' + vocabword + ': </span>' + e.data.definition + '</span></label>' + trailingchars;
							console.log('content to insert:')
							console.log(content);
						} else {
							console.log('No definition!')
							content = text;
						}
						console.log('\n');
						editor.insertContent(content);
					}
				});
			}
		});

	});
})();
