<?php

/* Serves media content. For quickstart, obviously, media content should be served from a CDN */

function render_media_file($uri) {
	
	// TODO: Fix security issue here with loading other file
	
	$media_name = $uri;
	if (0 === strpos($uri, BOOTSITE_BASE_URL))
	{
		// if it starts with a path, remove it to get the name
		$media_name = substr($uri, strlen(BOOTSITE_BASE_URL));
	}
	
	$media_dir = __DIR__ . "/../../sample-website";
	
	if (pathinfo($media_name, PATHINFO_EXTENSION) === "css") {
		
		$css_path = realpath($media_dir . $media_name);
		if ($css_path === "")
			return false;
		
		header('Content-Type: text/css');
		include($css_path);
	}	
	else if (pathinfo($media_name, PATHINFO_EXTENSION) === "png") {
		
		$image_path = realpath($media_dir . $media_name);
		if ($image_path === "")
			return false;
		
		header('Content-Type: image/png');
		readfile($image_path);
	}
	else {
		echo "Media file not found";
	}
}

render_media_file($uri);

?>