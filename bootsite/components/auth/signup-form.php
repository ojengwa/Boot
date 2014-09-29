<style>

.form-signup
{
    max-width: 330px;
    padding: 15px;
    margin: 0 auto;
}
.form-signup .form-signup-heading, .form-signup .checkbox
{
    margin-bottom: 10px;
}
.form-signup .checkbox
{
    font-weight: normal;
}
.form-signup .form-control
{
    position: relative;
    font-size: 16px;
    height: auto;
    padding: 10px;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
.form-signup .form-control:focus
{
    z-index: 2;
}
.form-signup input[type="text"] input[type="password"]
{
    margin-bottom: -1px;
}

.remember-me {
    padding-left:20px;
}
.account-wall
{
    margin-top: 20px;
    padding: 40px 0px 20px 0px;
    background-color: #f7f7f7;
}
.signup-title
{
    color: #555;
    font-size: 14px;
    font-weight: 400;
    display: block;
    text-align: center;
}
.need-help
{
    margin-top: 10px;
}
.new-account
{
    display: block;
    margin-top: 10px;
}

</style>

<?php
    include_once 'auth-controller.php';

    if (array_key_exists("error_messages", $component_options))
        $error_messages = $component_options["error_messages"];
?>

<div class="container">
    <div class="row">
        <div class="col-sm-6 col-md-offset-3">

        	<h1 class="signup-title"><?php
        		if (array_key_exists("top_text", $component_options)) {
        			echo $component_options["top_text"];
        		} else {
        			echo "Create a Bootsite Account";
                } ?>
        	</h1>
        	<div class="account-wall">
             <div style="text-align:center;">
                <?php
                    if (isset($error_messages) && $error_messages) {
                        foreach ($error_messages as $key => $value) {
                            echo '<p style="color:red">' . $value . '</p>';
                        }
                    }
                ?>
            </div>
                <form class="form-signup" method="post" action="" name="registerform">
                	<fieldset>
                        <div class="control-group">
                            <!-- <label for="user_email"><?php //echo WORDING_REGISTRATION_EMAIL; ?></label> -->

                            <div class="controls">
                            	<input class="form-control" id="user_email" type="email" name="user_email" placeholder="<?php echo WORDING_REGISTRATION_EMAIL; ?>" title="<?php echo WORDING_REGISTRATION_EMAIL_HINT; ?>" required />
                            </div>
                        </div>
                        <br />
                        <div class="control-group">
                            <!-- <label for="user_password_new"><?php //echo WORDING_REGISTRATION_PASSWORD; ?></label> -->

                            <div class="controls">
                            	<input class="form-control" id="user_password_new" type="password" name="user_password_new" placeholder="<?php echo WORDING_REGISTRATION_PASSWORD; ?>" title="<?php echo WORDING_REGISTRATION_PASSWORD_HINT; ?>" pattern=".{6,}" required autocomplete="off" />
                            </div>
                        </div>
                        <br />
                        <div class="control-group">
                            <!-- <label for="user_password_repeat"><?php //echo WORDING_REGISTRATION_PASSWORD_REPEAT; ?></label> -->

                            <div class="controls">
                            	<input class="form-control" id="user_password_repeat" type="password" name="user_password_repeat" placeholder="<?php echo WORDING_REGISTRATION_PASSWORD_REPEAT; ?>" pattern=".{6,}" required autocomplete="off" />
                            </div>
                        </div>
                        <br />
                        <div class="control-group">
                            <div class="controls">
                        		<!-- <input type="submit" name="register" value="<?php //echo WORDING_REGISTER; ?>" /> -->
                        		<button class="btn btn-lg btn-primary btn-block" type="submit" name="register"><?php echo WORDING_REGISTER; ?></button>
                        	</div>
                        </div>
                   	</fieldset>
                </form>
                <br/>
                <a href="<?= $twitter_auth_url ?>">
                    <img style="max-width:300px; max-height:50px; text-align:center" width="300px" src="<?php echo BOOTSITE_BASE_URL . "media/img/facebook.png"; ?>" alt="Sign in with Facebook"></a>
                <br/>
                <a href="<?= $twitter_auth_url ?>" >
                    <img style="max-width:300px; max-height:50px; text-align:center" width="300px" src="<?php echo  BOOTSITE_BASE_URL . "media/img/twitter.png"; ?>" alt="Sign in with Twitter"> </a>
            </div>

 <a href="<?php

                			if (array_key_exists("login_url", $component_options)) {
                				echo $component_options["login_url"];
                            }
                            else {
                                echo BOOTSITE_BASE_URL . "login";
                            }

                		  ?>" class="text-center new-account">Already have an account? Login</a>

        </div>
    </div>
</div>
