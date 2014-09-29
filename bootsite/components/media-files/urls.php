<?php

namespace bootsite_media;

function handle_request($uri) {
	
	// die($uri);
	if (0 === strpos($uri, BOOTSITE_BASE_URL . "/media")) {
		require( 'media.php' );
		return true;
	}
	
	return false;
}

?>