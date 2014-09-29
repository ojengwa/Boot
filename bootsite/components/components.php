<?php

    include dirname(__FILE__) . "/media-files/urls.php";
    include_once(dirname(__FILE__) . "/auth/auth-controller.php");
    include_once(dirname(__FILE__) . "/email/email-controller.php");


    /**
     * Includes a component
     *
     * @return void
    */
    function import_component($component_name, $component_options=array()){
        if(strpos($component_name, '/login')){
            $twitter_auth_url = genTwitterUrl(BOOTSITE_TWITTER_KEY, BOOTSITE_TWITTER_SECRET);
            $fb_auth_url = genFbUrl(BOOTSITE_FACEBOOK_KEY, BOOTSITE_FACEBOOK_SECRET);
        }
        include(BOOTSITE_DIR . "/components/" . $component_name . ".php");
    }

    function process_post($component_name) {
        if ($component_name === "auth") {
            return authProcessPost();
        }
    }
?>
