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

function friendReqForm() {
?>

	<h2>Request friendship!</h2>
                <form action="?do=sendfr" method="post">
                Username: <input type="text" name="user" id="user"> <br />
                <input type="submit" name="post" value="Send">
                </form>
<?php
}


function sendFriendRequest($user, $friend) {
	$friendLocation = "ssb_db/friends/" . $friend . ".pending";
	if(file_exists($friendLocation)) {
		$pending = file_get_contents("ssb_db/friends/" . $friend . ".pending");
		file_put_contents("ssb_db/friends/" . $friend . ".pending", $pending . "\n" . $user);
	} else {
		file_put_contents("ssb_db/friends/" . $friend . ".pending", $user);
	}
}

function acceptFriendRequest($user, $friend) {
	$friendpending = "ssb_db/friends/" . $user . ".pending";
	$friendlist = file_get_contents("ssb_db/friends/" . $user . ".php");
	$frienddb = file_get_contents("ssb_db/friends/" . $friend . ".php");
    	// check if friend request is really pending.

	$handle = fopen($friendpending, "r");
        if ($handle) {
 		while (($line = fgets($handle)) !== false) {
			if($friend == $line)
			{
				// populate both users databases with each other.
				$friendcountFriend = file_get_contents("ssb_db/friends/" . $friend . ".count");
				$friendcountFriend = $friendcountFriend + 1;
				echo $friendcountFriend;
				file_put_contents("ssb_db/friends/" . $friend . ".php", $frienddb . "\n <?php \$friend" . $friendcountFriend ." = \"" . $user . "\";?>");
				$friendcount = file_get_contents("ssb_db/friends/" . $user . ".count");
				$friendcount = $friendcount + 1;
				echo $friendcount;
     				file_put_contents("ssb_db/friends/" . $user . ".php", $friendlist . "\n <?php \$friend" . $friendcount . " = \"" . $friend . "\";?>");
				file_put_contents("ssb_db/friends/" . $user . ".count", $friendcount);
				file_put_contents("ssb_db/friends/" . $friend . ".count", $friendcountFriend);
				break;
			}
      		}
   		fclose($handle);
     	} else {
      		echo "ERROR: Friend: " . $friend . " not found in friend pending database.<br />";
    	}
}
?>
