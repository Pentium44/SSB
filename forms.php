<?php
//      SSB - Simple Social Board
//      (C) Chris Dorman, 2012 - 2020
//      License: CC-BY-NC-SA version 3.0
//      http://github.com/Pentium44/SSB

function loginForm() {
?>
        <br />
        <div class="login">
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?forms=register">Register</a>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?do=login" method="post">
                        Username: <input style="padding: 2px;" class="text" type="text" name="username"><br />
                        Password: <input style="padding: 2px;" class="text" type="password" name="password"><br />
                        <input style="padding: 2px;" class="text" type="submit" name="submitBtn" value="Login">
                </form>
        </div>
<?php
}

function registerForm() {
?>
        <br />
        <div class="login">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?do=register" method="post">
                        Username: <input style="padding: 2px;" class="text" type="text" name="username"><br />
			Full name: <input style="padding: 2px;" class="text" type="text" name="fullname"><br />
                        Password: <input style="padding: 2px;" class="text" type="password" name="password"><br />
                        Password Again: <input style="padding: 2px;" class="text" type="password" name="password-again"><br />
                        <input style="padding: 2px;" class="text" type="submit" name="submitBtn" value="Register">
                </form>
        </div>
<?php
}

function postForm() {
?>
	<br />
		<form action="?do=post" method="post">
                Topic: <input type="text" name="topic" id="topic"><br />
                Body: <br /><textarea rows="5" cols="60" name="body"></textarea><br />
                <input type="submit" name="post" value="Post">
                </form>
<?php
}

function replyForm($id, $puser) {
?>
	<br />
		<form action="?do=reply&pid=<?php echo $id; ?>&user=<?php echo $puser; ?>" method="post">
                <br /> <textarea rows="7" cols="60" name="body"></textarea><br />
                <input type="submit" name="reply" value="Reply">
                </form>

<?php
}

function cleanForm() {
?>

	<br />
		<form action="?do=clean" method="post">
                Password: <input type="password" name="password" id="password">	<br />
                <input type="submit" name="post" value="Post">
                </form>
<?php
}

function sendFriendRequest($user, $friend) {
	$pending = file_get_contents("ssb_db/friends/" . $user . ".pending");
	file_put_contents("ssb_db/friends/" . $friend . ".pending", $pending . "\n" . $user);
}
?>
