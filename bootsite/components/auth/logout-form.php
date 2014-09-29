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
	
	if (array_key_exists("margin-top", $component_options)) {
		echo '<div class="container" style="margin-top:' . $component_options["margin-top"] . 'px;">'  ;
	} else {
		echo '<div class="container">';
	} 
?>

    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4">
			
			<?php
				if (!User::currentUser()) {
					echo "<h2>You are now logged out</h2>";
				}
				else {
					echo '<button class="btn btn-lg btn-primary btn-block" type="submit" name="recover">
	                    Logout
	        </button>';
				}
			?>
        	
	            		  
        </div>
    </div>
</div>
