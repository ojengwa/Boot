<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(-1);

    include_once dirname(__FILE__) . '/../../' . 'translations/en.php';
    include_once dirname(__FILE__) . '/../../' . 'models/Database.class.php';
    include_once dirname(__FILE__) . '/../../' . 'models/User.class.php';
    include_once dirname(__FILE__) . '/../../' . 'libraries/passwordhash/password.php';
    include_once dirname(__FILE__) . '/../../' . 'components/email/email-controller.php';
    //Twitter and facebok Auths
    include_once dirname(__FILE__) . '/../../' . 'libraries/twitter/twitter.php';
    include_once dirname(__FILE__) . '/../../' . 'libraries/facebook/facebook.php';


    /**
     * Looks if we are to register or login a user
     */
    function authProcessPost() {

        $result = array("successful" => true);

        if (isset($_POST["login"])) {
            $remember_me = null;

            // die($_POST['redirect_url']);

            if (isset($_POST['user_rememberme'])) {
                $remember_me = $_POST['user_rememberme'];
            }

            $result = loginUser($_POST['user_email'], $_POST['user_password'], $remember_me);
            if ($result['successful'] === true && key_exists("redirect_url", $_POST)) {
                @header("Location: " . $_POST['redirect_url']);
            }
        }

        if (isset($_POST["register"])) {

            $username = $_POST['user_email'];

            if (isset($_POST["user_name"]))
                $username = $_POST['user_name'];

            $result = registerNewUser($username, $_POST['user_email'], $_POST['user_password_new'], $_POST['user_password_repeat'], $_SERVER['REMOTE_ADDR']);
        }

        if (isset($_POST["recover"])) {
            $user_email = $_POST['user_email'];
            $result = recoveruserpassword($user_email);
        }



        if (isset($_REQUEST["logout"])) {

            $user = User::currentUser();
            if ($user) {
                $user->logout();
            }
        }

        // print_r($result);
        return $result;
    }

    function logoutUser() {
        $user = User::currentUser();
        $user->logout();
    }

    function recoveruserpassword($user_email) {
        $array = User::resetpasswordrequest($user_email);
        if ($array['status']) {

            sendpasswordrecoveryemail($user_email, $array['token']);
        }
    }

    function sendpasswordrecoveryemail($user_email, $passwordresettoken) {
        $from = "admin@bootsite.com";
        $to = $user_email;
        $subject = "Password Recovery";
        $text_message = BOOTSITE_BASE_URL."/passwordrecovery?t=".$passwordresettoken;
        sendmail($from, $to, $subject, $text_message);
    }

    /**
     * handles the entire registration process. checks all error possibilities, and creates a new user in the database if
     * everything is fine
     */
    function registerNewUser($user_name, $user_email, $user_password, $user_password_repeat, $user_ip) {
        $db = Database::connect();

        // we just remove extra space on username and email
        $user_name  = trim($user_name);
        $user_email = trim($user_email);
        $errors = array();
        $messages = array();
        $registration_successful = false;

        // check provided data validity
        if (empty($user_name)) {
            $errors[] = MESSAGE_USERNAME_EMPTY;
        } elseif (empty($user_password) || empty($user_password_repeat)) {
            $errors[] = MESSAGE_PASSWORD_EMPTY;
        } elseif ($user_password !== $user_password_repeat) {
            $errors[] = MESSAGE_PASSWORD_BAD_CONFIRM;
        } elseif (strlen($user_password) < 6) {
            $errors[] = MESSAGE_PASSWORD_TOO_SHORT;
        } elseif (strlen($user_name) > 64 || strlen($user_name) < 2) {
            $errors[] = MESSAGE_USERNAME_BAD_LENGTH;
      //  } elseif (!preg_match('/^[a-z\d]{2,64}$/i', $user_name)) {
      //      $errors[] = MESSAGE_USERNAME_INVALID;
        } elseif (empty($user_email)) {
            $errors[] = MESSAGE_EMAIL_EMPTY;
        } elseif (strlen($user_email) > 64) {
            $errors[] = MESSAGE_EMAIL_TOO_LONG;
        } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = MESSAGE_EMAIL_INVALID;

        // finally if all the above checks are ok
        } else if ($db) {

            // if username or/and email find in the database
            if (User::emailInUse($user_email)) {
                $errors[] = MESSAGE_EMAIL_ALREADY_EXISTS;
            } else if (User::usernameInUse($user_name)) {
                $errors[] = MESSAGE_USERNAME_EXISTS;
            } else {

                $result = User::createUserAccount($user_name, $user_password, $user_email, $user_ip);

                if ($result['successful'] == true) {
                	$new_user = $result['user'];

                    // send a verification email
                    if ($new_user->sendWelcomeEmail("")) {
                        // when mail has been send successfully
                        $messages[] = MESSAGE_VERIFICATION_MAIL_SENT;
                        $registration_successful = true;
                    } else {
                        User::deleteUserAccount($user_email);
                        $errors[] = MESSAGE_VERIFICATION_MAIL_ERROR;
                    }
                } else {
                    $errors[] = MESSAGE_REGISTRATION_FAILED . ": " . implode((array)$result['errors']);
                }
            }

        }

        return array(
            "successful" => $registration_successful,
            "errors" => $errors,
            "messages" => $messages
        );
    }



  /**
     * Logs in with the data provided in $_POST, coming from the login form
     * @param $user_name
     * @param $user_password
     * @param $user_rememberme
     */
    function loginUser($user_email, $user_password, $user_rememberme) {
        return User::loginUser($user_email, $user_password, $user_rememberme, true);
    }
 	/**
     * Logs in with an email and a secret token
     * @param $user_name
     * @param $user_password
     * @param $user_rememberme
     */

    function autoLoginUser($user_email) {

    }
    /**
     * Generates a unique auth URL for each session
     * @param $key - FACEBOOK APP KEY
     * @param $secret - FACEBOOK APP SECRET
     */
    function genFbUrl($key, $secret){
        $fb = new Facebook(array(
            'appId' => $key,
            'secret' => $secret
            )
        );
        return $fb->getLoginUrl(array('redirect_uri'=> 'http://' . $_SERVER['HTTP_HOST'] . BOOTSITE_BASE_URL . "facebook"));
    }

    /**
     * Generates a unique auth URL for each session
     * @param $key - TWITTER APP KEY
     * @param $secret - TWITTER APP SECRET
     */

    function genTwitterUrl($key, $secret){

        //Check to ensure if session was set
        if(session_status() === PHP_SESSION_NONE || session_id() === ''){
            session_start();
        }
        /* Build TwitterOAuth object with client credentials. */
        $con = new TwitterOAuth($key, $secret);

        // Get temporary credentials.
        $request_token = $con->getRequestToken(@$_REQUEST['oauth_verifier']);
        // Save temporary credentials to file. NOT THE BEST IMPLEMENTATION BUT SESSION IS NOT PERSISTING SOMEHOW.
        $token = $request_token['oauth_token'];
        $token_secret = $request_token['oauth_token_secret'];
        $f = fopen('f.txt', 'w');
        fwrite($f, $token . ',' . $token_secret);
        fclose($f);
        // Build authorize URL to Twitter.
        $url = $con->getAuthorizeURL($token);
        return $url;
    }

    /**
     * Authenticates a user's token
     * @param $key - TWITTER APP KEY
     * @param $secret - TWITTER APP SECRET
     * @param $verifier - Oauth token verifier from Twitter
     */
    function twitterAuth($key, $secret, $verifier){

        //Retrieve auths detials from file
        $f = fopen('f.txt', 'r');
        $str = fread($f, filesize('f.txt'));
        fclose($f);
        $oauth = explode(',', $str);
        // Create TwitteroAuth object with app key/secret and token key/secret that was generated above
        $connection = new TwitterOAuth($key, $secret, $oauth[0],$oauth[1]);
        // Request access tokens from twitter
        $access_token = $connection->getAccessToken($verifier);

        // Save the access tokens.
        $_SESSION['access_token'] = $access_token;
        $content = $connection->get('account/verify_credentials');
        return $content;
    }

