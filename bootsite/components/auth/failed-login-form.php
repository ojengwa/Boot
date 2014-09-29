<style>

    .form-signin
    {
        max-width: 330px;
        padding: 15px;
        margin: 0 auto;
    }
    .form-signin .form-signin-heading, .form-signin .checkbox
    {
        margin-bottom: 10px;
    }
    .form-signin .checkbox
    {
        font-weight: normal;
    }
    .form-signin .form-control
    {
        position: relative;
        font-size: 16px;
        height: auto;
        padding: 10px;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }
    .form-signin .form-control:focus
    {
        z-index: 2;
    }
    .form-signin input[type="text"]
    {
        margin-bottom: -1px;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
    .form-signin input[type="password"]
    {
        margin-bottom: 10px;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
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
    .login-title
    {
        color: #555;
        font-size: 18px;
        font-weight: 400;
        display: block;
    }
    .profile-img
    {
        width: 50px;
        height: auto;
        margin: 0 auto 10px;
        display: block;
        -moz-border-radius: 50%;
        -webkit-border-radius: 50%;
        border-radius: 50%;
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

    .errors{
        text-align:center;
    }
    .errors .errormsg
    {
        text-align: center;
        color:red;
    }
</style>

<?php

if (array_key_exists("error_messages", $component_options))
    $error_messages = $component_options["error_messages"];

if (array_key_exists("margin-top", $component_options)) {
    echo '<div class="container" style="margin-top:' . $component_options["margin-top"] . 'px;">'  ;
} else {
    echo '<div class="container">';
}
?>

<div class="container">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4">

            <h1 class="text-center login-title">
                <?php
if (array_key_exists("top_text", $component_options)) {
    echo $component_options["top_text"];
} else {
    echo "Sign in to continue to BootSite";
} ?>
            </h1>
            <div class="account-wall">
                <img class="profile-img" src="<?php

$curUser = User::currentUser();
if ($curUser) {
    echo $curUser->getGravatarImageUrl(50);
}
else if (array_key_exists("logo", $component_options)) {
    echo $component_options["logo"];
} else {
    echo BOOTSITE_BASE_URL . "/media/img/user.png";
}

                                              ?>"
                     alt="">
                <div class="errors">
                    <?php
if (isset($error_messages) && $error_messages) {
    foreach ($error_messages as $key => $value) {
        echo '<p class="errormsg">' . $value . '</p>';
    }
}


if ($curUser) {
    echo "<b> Currently logged in as " . $curUser->getEmail() . "</b><br>Logout";
}
else
    echo "<b> Forgot you password? </b>";
                    ?>
                </div>
                <form class="form-signin" action="" method="post" name="recoeryform">
                    <input type="text" class="form-control" placeholder="Email" required autofocus name="user_email">
                    <br/>
                    <button class="btn btn-lg btn-primary btn-block" type="submit" name="recover">
                        <?php

if (array_key_exists("button_title", $component_options)) {
    echo $component_options["button_title"];
} else {
    echo "Recover";
}

                        ?></button>
                </form>
            </div>
            <br>
            <center style="text-align:center"><strong>OR</strong></center>

            <div class="account-wall">
                <form class="form-signin" action="" method="post" name="loginform">
                    <input type="text" class="form-control" placeholder="Email" required autofocus name="user_email">
                    <input type="hidden" name="redirect_url" value="<?= $component_options["redirect_url"] ?>">
                    <input type="password" class="form-control" name="user_password" autocomplete="off" placeholder="Password" required>
                    <button class="btn btn-lg btn-primary btn-block" type="submit" name="login">
                        Sign in</button>
                    <label class="checkbox pull-left remember-me">
                        <input type="checkbox" value="remember-me" name="user_rememberme" checked >
                        Remember me
                    </label>
                    <span class="clearfix"></span>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
