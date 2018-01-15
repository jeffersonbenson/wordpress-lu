<?php
// better way to do this? goal is to have all our plugins in this one repo, but still keep subfolders for organization

/**
 * Plugin Name: TinyMCE plugins
 * Version: 1.0
 * Author: Dan Crankshaw
 * Description: all our other TinyMCE Plugins
 */


// class TinyMCEMisc{
	
	include('tinymce-lightbox/tinymce-lightbox.php');
	include('tinymce-video/tinymce-video.php');
	include('tinymce-videothumb/tinymce-videothumb.php');
	include('tinymce-iframe/tinymce-iframe.php');
	include('tinymce-resettable-iframe/tinymce-iframe.php');
	include('tinymce-vocabtooltips/tinymce-vocabtooltips.php');
	include('tinymce-sourcetooltips/tinymce-sourcetooltips.php');
	include('tinymce-audio/tinymce-audio.php');
	include('tinymce-audiothumb/tinymce-audiothumb.php');
// }

// $tinymce_misc = new TinyMCEMisc;

?>