<?php
    include_once 'Database.class.php';
    include_once dirname(dirname(__FILE__)) . '/libraries/passwordhash/password.php';
// echo dirname(dirname(__FILE__));die;
if(session_id() == '') {
    session_start();
}

class User {

    private static $table_name = 'users';
    private static $field_names = array(
             'user_id' => 'user_id',
             'user_email' => 'email',
             'user_name' => 'username',
             'user_password_hash' => 'password_hash',
             'activation_hash' => 'activation_hash',
             'registration_ip' => 'registration_ip',
             'registration_time' => 'registration_time',
             'last_login' => 'last_login',
             'failed_logins' => 'user_failed_logins',
             'user_last_failed_login' => 'user_last_failed_login',
             'last_failed_login' => 'user_last_failed_login',
             'user_active_field' => 'user_active'
    );

    private static $currentUser = null;
    private $user_id = 0;
    private $username = null;
    private $email = null;
    private $user_is_logged_in = false;
    private $user_gravatar_image_url = "";
    private $user_gravatar_image_tag = "";
    private $user_firstname = "";
    private $user_lastname = "";


    static function configureFields($config) {
        User::$table_name = $config['table_name'];
        User::$field_names = $config['field_names'];
    }

    function User($user_id) {
        $this->user_id = $user_id;
    }

    /**
     * Checks if a user account with the same email already exists.
     *
     * @return bool  False if the user account does not exist, True if it does.
     */
    public static function emailInUse($user_email) {

        $query_check_user_name = Database::$pdo->prepare('SELECT ' . User::$field_names['user_email'] . ' FROM ' . User::$table_name . ' WHERE ' . User::$field_names['user_email'] . '=:user_email');
        $query_check_user_name->bindValue(':user_email', $user_email, PDO::PARAM_STR);
        $query_check_user_name->execute();
        $result = $query_check_user_name->fetchAll();

        if (count($result) > 0) {
           return true;
        }

        return false;
    }

    /**
     * Checks if a user account with the same username already exists.
     *
     * @return bool  False if the user account does not exist, True if it does.
     */
    public static function usernameInUse($user_name) {

        $query_check_user_name = Database::$pdo->prepare('SELECT ' . User::$field_names['user_name'] . ' FROM ' . User::$table_name . ' WHERE ' . User::$field_names['user_name'] . '=:user_name');
        $query_check_user_name->bindValue(':user_name', $user_name, PDO::PARAM_STR);
        $query_check_user_name->execute();
        $result = $query_check_user_name->fetchAll();

        if (count($result) > 0) {
           return true;
        }

        return false;
    }


   /*
     * sends an email to the provided email address
     * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
     */
    function sendWelcomeEmail($user_activation_hash = "")
    {
    	return true;
    }



    public static function deleteUserAccount($user_email) {
        // delete this users account immediately, as we could not send a verification email
        $query_delete_user = Database::$pdo->prepare('DELETE FROM users WHERE ' . User::$field_names['user_email'] . '=:user_email');
        $query_delete_user->bindValue(':user_email', $user_email, PDO::PARAM_INT);
        $query_delete_user->execute();
    }

    /**
     * Creates a new user account.
     *
     * @todo  Add form validation.
     *        Display a nice message telling the user they have successfully created an account and need to confirm to activate.
     *        Send an email to the user to confirm registration.
     * @return void
     */
    public static function createUserAccount($user_name, $user_password, $user_email, $user_registration_ip) {

        // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
        // the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
        // compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
        // want the parameter: as an array with, currently only used with 'cost' => XX.
        $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);
        // generate random hash for email verification (40 char string)
        $user_activation_hash = sha1(uniqid(mt_rand(), true));
        $table_name = User::$table_name;

        $id_field = User::$field_names['user_id'];
        $username_field = User::$field_names['user_name'];
        $user_email_field = User::$field_names['user_email'];
        $user_password_hash_field = User::$field_names['user_password_hash'];
        $activation_hash_field = User::$field_names['activation_hash'];
        $registration_ip_field = User::$field_names['registration_ip'];
        $registration_time_field = User::$field_names['registration_time'];
        $last_login_field = User::$field_names['last_login'];
        $user_active_field = User::$field_names['user_active_field'];

        $query_user = Database::$pdo->prepare("SELECT * FROM {$table_name} WHERE {$user_email_field} = :user_email");
        $query_user->bindValue(':user_email', trim($user_email), PDO::PARAM_STR);
        $query_user->execute();

        // get result row (as an object)
        $result_row = $query_user->fetch();

            // if this user not exists
    	if (isset($result_row[$id_field])) {
            $errors[] = "Email already in use";
            return array("successful" => false, "errors" => $errors, "user" => null);
        }

            // write new users data into database
        $query_new_user_insert = Database::$pdo->prepare("INSERT INTO {$table_name} ({$username_field}, {$user_password_hash_field}, {$user_email_field}, {$activation_hash_field}, {$user_active_field}, {$registration_ip_field}, {$registration_time_field}) VALUES(:username, :password_hash, :email, :activation_hash, :user_active, :registration_ip, now())");
        $query_new_user_insert->bindValue(':username', $user_name, PDO::PARAM_STR);
        $query_new_user_insert->bindValue(':password_hash', $user_password_hash, PDO::PARAM_STR);
        $query_new_user_insert->bindValue(':email', $user_email, PDO::PARAM_STR);
        $query_new_user_insert->bindValue(':activation_hash', $user_activation_hash, PDO::PARAM_STR);
        $query_new_user_insert->bindValue(':user_active', 1, PDO::PARAM_INT);
        $query_new_user_insert->bindValue(':registration_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);

        if (!$query_new_user_insert->execute()) {

            if (defined(BOOTSITE_DEBUG_MODE))
                echo "<h1>Database failure: " . print_r(Database::$pdo->errorInfo()) . "</h1>\n";

            return array("successful" => false, "errors" => "Could not insert user into database", "user" => null);
        }

        // id of new user
        $user_id = Database::$pdo->lastInsertId();
        $user = new User($user_id);
        
        autoLoginUser($user_email);
        return array("successful" => true, "errors" => null, "user" => $user);

    }

     /**
     * Automatically logs in the user with this email. Will create an account if does not exist
     *
     * @return array
     */
    public static function autoLoginUser($user_email) {
    	
		$table_name = User::$table_name;
		$user_email_field = User::$field_names['user_email'];
		$id_field = User::$field_names['user_id'];
	
		$user_email = trim($user_email);
		
        // If user does not exist, then create the user
        $query_user = Database::$pdo->prepare("SELECT * FROM {$table_name} WHERE {$user_email_field} = :user_email");
        $query_user->bindValue(':user_email', trim($user_email), PDO::PARAM_STR);
        $query_user->execute();
    
        $result_row = $query_user->fetch();

        // if this user not exists
    	if (!isset($result_row[$id_field])) {
    		
            $result = User::createUserAccount($user_email, getGuid(), $user_email, $_SERVER['REMOTE_ADDR']);
			
			if ($result['successful'] == false) {
				return $result;
			}
        }
		
    	return User::loginUser($user_email, "", true, false);
    }
	
	
    public static function loginUser($user_email, $user_password, $user_rememberme, $validate_pw) 
    {
	
		// die("Entered");
		
        $errors = array();
        $messages = array();
        $registration_successful = false;

         if (empty($user_email)) {
            $errors[] = MESSAGE_EMAIL_EMPTY;
            return array("successful" => false, "errors" => $errors);
        }

        if ($validate_pw === true && empty($user_password)) {
     
            $errors[] = MESSAGE_PASSWORD_EMPTY;
            return array("successful" => false, "errors" => $errors);
        }

        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        	// This is the case where the user may try to login with username
        	// We should try to log him in using username. For now tho, failure
            $errors[] = MESSAGE_LOGIN_FAILED;
            return array("successful" => false, "errors" => $errors);
        }

        if (!$db = Database::connect())
            return array("successful" => false, "errors" => $errors);

        $table_name = User::$table_name;
        $id_field = User::$field_names['user_id'];
        $username_field = User::$field_names['user_name'];
        $user_email_field = User::$field_names['user_email'];
        $user_password_hash_field = User::$field_names['user_password_hash'];
        $activation_hash_field = User::$field_names['activation_hash'];
        $registration_ip_field = User::$field_names['registration_ip'];
        $registration_time_field = User::$field_names['registration_time'];
        $last_login_field = User::$field_names['last_login'];
        $failed_logins_field = User::$field_names['failed_logins'];
        $last_failed_login_field = User::$field_names['last_failed_login'];
        $user_active_field = User::$field_names['user_active_field'];

        // database query, getting all the info of the selected user
        $query_user = Database::$pdo->prepare("SELECT * FROM {$table_name} WHERE {$user_email_field} = :user_email");
        $query_user->bindValue(':user_email', trim($user_email), PDO::PARAM_STR);
        $query_user->execute();
        // get result row (as an object)
        $result_row = $query_user->fetch();

            // if this user not exists
    	if (!isset($result_row[$id_field])) {
            $errors[] = "Not found" . MESSAGE_LOGIN_FAILED;
            return array("successful" => false, "errors" => $errors);
        }

        if (($result_row[$failed_logins_field] >= 20) && ($result_row[$last_failed_login_field] > (time() - 30))) {
        	$errors[] = MESSAGE_PASSWORD_WRONG_3_TIMES;
           	return array("successful" => false, "errors" => $errors);
        }

        if ($validate_pw && !password_verify($user_password, $result_row[$user_password_hash_field])) {
            // increment the failed login counter for that user
            $sth = Database::$pdo->prepare("UPDATE {$table_name} SET {$failed_logins_field} = {$failed_logins_field}+1, {$last_failed_login_field} = :user_last_failed_login WHERE {$user_email_field} = :user_email");

            $sth->execute(array(':user_email' => $user_email, ':user_last_failed_login' => time()));

            $errors[] = MESSAGE_PASSWORD_WRONG;
            return array("successful" => false, "errors" => $errors);
        }

            // has the user activated their account with the verification email
        if ($result_row[$user_active_field] != 1) {
            $errors[] = MESSAGE_ACCOUNT_NOT_ACTIVATED;
            return array("successful" => false, "errors" => $errors);
        }

        User::$currentUser = new User($result_row[$id_field]);
        User::$currentUser->user_id = $result_row[$id_field];
        User::$currentUser->username = $result_row[$username_field];
        User::$currentUser->email = $result_row[$user_email_field];
        User::$currentUser->user_is_logged_in = true;

        if (isset($user_rememberme)) {
            User::$currentUser->writeLoginSession($user_rememberme);
        }

        User::$currentUser->loadUserData();

        // reset the failed login counter for that user
        $sth = Database::$pdo->prepare("UPDATE {$table_name} SET {$failed_logins_field} = 0, {$last_failed_login_field} = NULL WHERE {$user_email_field} = :user_email AND {$failed_logins_field} != 0");
        $sth->execute(array(':user_email' => User::$currentUser->email));

  		return array("successful" => true, "errors" => $errors);
    }

    public function loadUserData() {

        if (!empty($this->email)) {
            return true;
        }

        if (Database::connect()) {

            $table_name = User::$table_name;
            $user_email_field = User::$field_names['user_email'];

             // database query, getting all the info of the selected user
            $query_user = Database::$pdo->prepare("SELECT * FROM {$table_name} WHERE {$user_email_field} = :user_email");
            $query_user->bindValue(':user_email', $_SESSION['user_email'], PDO::PARAM_STR);
            $query_user->execute();
            // get result row (as an object)
            $result_row = $query_user->fetch();

            if (!isset($result_row[User::$field_names['user_id']])) {
            	echo("Session user not found");
            	return;
            }

            $this->user_id = $result_row[User::$field_names['user_id']];
            $this->username = $result_row[User::$field_names['user_name']];
            $this->email = $result_row[User::$field_names['user_email']];
            $this->user_is_logged_in = true;
        }

    }

    public function getEmail() {
        return $this->email;
    }

    public function getCustomTag($tag_name) {
        if ($tag_name == "affiliate") {
            return "BSAFFILIATE";
        }
    }

    public function setCustomTag($tag_name) {

    }

    public static function currentUser() {

        if (User::$currentUser)
            return User::$currentUser;

        if (!array_key_exists("user_id", $_SESSION))
            return false;

        $user = new User($_SESSION['user_id']);
        $user->loadUserData();

        if ($user->isUserLoggedIn()) {
            User::$currentUser = $user;
            return $user;
        }

        return null;
    }
        public static function resetpasswordrequest($user_email) {
        if (emailInUse($user_email)) {
            if (Database::connect()) {

                $passwordresettoken = hash('sha256', mt_rand());

                $query_user = Database::$pdo -> prepare("UPDATE users SET resetpasswordtoken = {$passwordresettoken}, resetpasswordrequest = 1 WHERE " . User::$field_names['user_email'] . " = :user_email");
                $query_user -> bindValue(':user_email', $user_email, PDO::PARAM_STR);
                $query_user -> execute();
                // get result row (as an object)
                //$result_row = $query_user -> fetchObject();
                $array =  array();
                $array['status'] = true;
                $array['token'] = $passwordresettoken;
                return $array;
            }
        }

    }


    public static function resetpassword($user_email, $user_password) {
        if (emailInUse($user_email)) {
            if (Database::connect()) {
                $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);

                $user_password_hash_field = User::$field_names['user_password_hash'];
                $query_user = Database::$pdo -> prepare("UPDATE users SET {$user_password_hash_field} = {$user_password_hash}  WHERE " . User::$field_names['user_email'] . " = :user_email");
                $query_user -> bindValue(':user_email', $user_email, PDO::PARAM_STR);
                $query_user -> execute();
                // get result row (as an object)
                $result_row = $query_user -> fetchObject();

            }

        }
    }
    private function writeLoginSession($remember_me) {

        // write user data into PHP SESSION [a file on your server]
        $_SESSION['user_id'] = $this->user_id;
        $_SESSION['user_name'] = $this->username;
        $_SESSION['user_email'] = $this->email;
        $_SESSION['user_logged_in'] = 1;

          // if user has check the "remember me" checkbox, then generate token and write cookie
        if (isset($user_rememberme)) {
            $this->newRememberMeCookie();
        } else {
            // Reset remember-me token
            $this->deleteRememberMeCookie();
        }

    }

    /**
     * Create all data needed for remember me cookie connection on client and server side
     */
    private function newRememberMeCookie()
    {
    	return false;

        // if database connection opened
        if (Database::connect()) {
            // generate 64 char random string and store it in current user data
            $random_token_string = hash('sha256', mt_rand());
            $sth = Database::$pdo->prepare("UPDATE users SET user_rememberme_token = :user_rememberme_token WHERE user_id = :user_id");
            $sth->execute(array(':user_rememberme_token' => $random_token_string, ':user_id' => $_SESSION['user_id']));

            // generate cookie string that consists of userid, randomstring and combined hash of both
            $cookie_string_first_part = $_SESSION['user_id'] . ':' . $random_token_string;
            $cookie_string_hash = hash('sha256', $cookie_string_first_part . COOKIE_SECRET_KEY);
            $cookie_string = $cookie_string_first_part . ':' . $cookie_string_hash;

            // set cookie
            setcookie('rememberme', $cookie_string, time() + COOKIE_RUNTIME, "/", COOKIE_DOMAIN);
        }
    }

    /**
     * Delete all data needed for remember me cookie connection on client and server side
     */
    private function deleteRememberMeCookie()
    {
    	return false;

        // if database connection opened
        if (Database::connect()) {
            // Reset rememberme token
            $sth = Database::$pdo->prepare("UPDATE users SET user_rememberme_token = NULL WHERE user_id = :user_id");
            $sth->execute(array(':user_id' => $_SESSION['user_id']));
        }

        // die("About setting cookie");

        // set the rememberme-cookie to ten years ago (3600sec * 365 days * 10).
        // that's obivously the best practice to kill a cookie via php
        // @see http://stackoverflow.com/a/686166/1114320
        setcookie('rememberme', false, time() - (3600 * 3650), '/', COOKIE_DOMAIN);
    }

    /**
     * Perform the logout, resetting the session
     */
    public function logout()
    {
        $this->deleteRememberMeCookie();

        $_SESSION = array();
        session_destroy();

        $this->user_is_logged_in = false;
        User::$currentUser = null;
    }


    /**
     * Simply return the current state of the user's login
     * @return bool user's login status
     */
    public function isUserLoggedIn()
    {
        return $this->user_is_logged_in;
    }

    /**
     * Gets the username
     * @return string username
     */
    public function getUsername()
    {
        return $this->user_name;
    }

    public function getDescriptiveName() {
        // This will return a good way to describe the user.
        // If first and last name are set, will return that
        // otherwise email address

        if ($this->user_firstname !== "" && $this->$user_lastname !== "") {
            return $this->user_firstname . " " . $this->user_lastname;
        }
        else if ($this->email != "") {
            return $this->email;
        }
    }

     /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     * Gravatar is the #1 (free) provider for email address based global avatar hosting.
     * The URL (or image) returns always a .jpg file !
     * For deeper info on the different parameter possibilities:
     * @see http://de.gravatar.com/site/implement/images/
     *
     * @param string $email The email address
     * @param string $s Size in pixels, defaults to 50px [ 1 - 2048 ]
     * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param array $atts Optional, additional key/value attributes to include in the IMG tag
     * @source http://gravatar.com/site/implement/images/php/
     */
    public function getGravatarImageUrl($s = 32, $d = 'mm', $r = 'g', $atts = array() )
    {

        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($this->email)));
        $url .= "?s=$s&d=$d";

        // die($url);

        // the image url (on gravatarr servers), will return in something like
        // http://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50?s=80&d=mm&r=g
        // note: the url does NOT have something like .jpg
        $this->user_gravatar_image_url = $url;

        // build img tag around
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val)
            $url .= ' ' . $key . '="' . $val . '"';
        $url .= ' />';

        // the image url like above but with an additional <img src .. /> around
        $this->user_gravatar_image_tag = $url;

        return $this->user_gravatar_image_url;
    }

}

?>
