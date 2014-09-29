<?php
	
require_once 'libraries/markdown/Markdown.inc.php';
	
function render_static_page($pagename) {
	
	$filename = BOOTSITE_CONTENT_DIR . '/' . $pagename . '.md';
	if (file_exists($filename)) {
    	echo Michelf\Markdown::defaultTransform(file_get_contents($filename));
	} else {
	    echo "The file $pagename does not exist";
	}
}

render_static_page($page);

?>