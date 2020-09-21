<?php
//      SSB - Simple Social Board
//      (C) Chris Dorman, 2012 - 2020
//      License: CC-BY-NC-SA version 3.0
//      http://github.com/Pentium44/SSB

// get filesize for uploaded files
function tomb($size, $precision = 2)
{
    $base = log($size) / log(1024);
    $suffixes = array('', 'KB', 'MB', 'GB', 'TB');

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}

function getRandString($n) { 
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
    $randomString = ''; 
  
    for ($i = 0; $i < $n; $i++) { 
        $index = rand(0, strlen($characters) - 1); 
        $randomString .= $characters[$index]; 
    } 
  
    return $randomString; 
}

function searchForUserTag($str) {
	if(preg_match("/\s*@(.+?)\s/", $str, $matches)) {
		return $matches["0"];
	} else {
		return false;
	}
}

function loginForm() {
?>
        <br />
        <div class="login">
                <a class="button" href="<?php echo $_SERVER['PHP_SELF']; ?>?forms=register">Register</a>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?do=login" method="post">
			<table><tr><td>
                        Username:</td><td> <input class="text" type="text" name="username"></td></tr><tr><td>
                        Password:</td><td> <input class="text" type="password" name="password"></td></tr></table>
                        <input style="padding: 2px;" class="text" type="submit" name="submitBtn" value="Login">
                </form>
        </div>
<?php
}

function changePassForm() {
?>
        <br />
        <div class="chgpass">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?do=changepass" method="post">
			<table><tr><td>
                        Old password:</td><td> <input class="text" type="password" name="oldpass"></td></tr><tr><td>
                        Password:</td><td> <input class="text" type="password" name="password"></td></tr><tr><td>
			Password Again:</td><td> <input class="text" type="password" name="password_again"></td></tr>
			</table>
                        <input class="text" type="submit" name="submitBtn" value="Change">
                </form>
        </div>
<?php
}

function uploadAvatarForm() {
?>
        <br />
        <div class="upload">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?do=avatarupload" method="post" enctype="multipart/form-data">
                        Choose profile avatar: <br />
			<label class="input-upload">
			    <input type="file" name="file[]" id="file" multiple>
			    <i class="fa fa-cloud-upload"></i> Upload image
			</label>
                        <input class="text" type="submit" name="submit" value="Set">
                </form>
        </div>
<?php
}


/*function uploadForm() {

       	print <<<EOD
			Upload
			<table style="margin:auto;">
				
				<form action="upload.php" method="post" enctype="multipart/form-data">
				<tr>
					<td>
					<input type="file" name="file[]" id="file" multiple><br>
					</td>
					<td>
					<input type="submit" name="submit" value="Upload">
					</td>
				</tr>
				</form>
				
				</table>
EOD;

}*/

function registerForm() {
?>
        <br />
        <div class="login">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?do=register" method="post">
                        <table><tr><td>
			Username:</td><td> <input class="text" type="text" name="username"></td></tr><tr><td>
			Full name:</td><td> <input class="text" type="text" name="fullname"></td></tr><tr><td>
                        Password:</td><td> <input class="text" type="password" name="password"></td></tr><tr><td>
                        Password Again:</td><td> <input class="text" type="password" name="password-again"></td></tr><tr><td>
			<label for="acct">Profile type:</label>
			<select id="acct" name="acct">
			  <option value="private">Private</option>
			  <option value="public">Public</option>
			</select></td><td>
                        <input class="text" type="submit" name="submitBtn" value="Register">
			</td></tr></table>
                </form>
        </div>
<?php
}

function postForm() {
	print <<<EOD
		<button onclick="javascript:wrapBBCode('i');">Italic</button>
                <button onclick="javascript:wrapBBCode('u');">Underline</button>
                <button onclick="javascript:wrapBBCode('b');">Bold</button>
                <button onclick="javascript:wrapBBCode('url');">URL</button>
		<form action="?do=post" method="post" enctype="multipart/form-data">
                <label class="input-upload">
                   <input type="file" name="file[]" id="file" multiple>
                   <i class="fa fa-cloud-upload"></i> Upload image
                </label>
               	<br /><textarea rows="5" cols="60" id="msg" name="body"></textarea><br />
               	<input type="submit" name="post" value="Post">
		</form>
EOD;
}

function replyForm($id, $puser) {
?>
		<button onclick="javascript:wrapBBCode('i');">Italic</button>
                <button onclick="javascript:wrapBBCode('u');">Underline</button>
                <button onclick="javascript:wrapBBCode('b');">Bold</button>
                <button onclick="javascript:wrapBBCode('url');">URL</button>
		<form action="?do=reply&pid=<?php echo $id; ?>&user=<?php echo $puser; ?>" method="post">
		<textarea rows="7" cols="60" id="msg" name="body"></textarea><br />
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
	$handle = fopen($friendLocation, "r");
    if ($handle) {
		while (($line = fgets($handle)) !== false) {
			if($line == $user) { break; } // request already pending
		}
		fclose($handle);
	}
	
	// Check if user is itself
	if($user == $friend) { header("Location: index.php?do=friends"); exit(1); } // dont request from self.
	
	$friendc = file_get_contents("ssb_db/friends/" . $user . ".count");
	$friendcount = file_get_contents("ssb_db/friends/" . $user . ".count");
	include "ssb_db/friends/" . $user . ".php";
       for($x = 1; $x <= $friendcount; $x++)
       {
		if(${"friend" . $x} == $friend) { header("Location: index.php?do=friends"); echo "Already following!"; exit(1); }
	}
	
	if(file_exists($friendLocation)) {
		$pending = file_get_contents("ssb_db/friends/" . $friend . ".pending");
		file_put_contents("ssb_db/friends/" . $friend . ".pending", $pending . "\n" . $user);
	} else {
		file_put_contents("ssb_db/friends/" . $friend . ".pending", $user);
	}
	
}

function acceptPublicFriendRequest($user, $friend) {
        $friendpending = "ssb_db/friends/" . $user . ".pending";
        $friendlist = file_get_contents("ssb_db/friends/" . $user . ".php");
        $frienddb = file_get_contents("ssb_db/friends/" . $friend . ".php");
        // check if already on friends list.

	$friendc = file_get_contents("ssb_db/friends/" . $user . ".count");
            	$friendcount = file_get_contents("ssb_db/friends/" . $user . ".count");
                include "ssb_db/friends/" . $user . ".php";
                for($x = 1; $x <= $friendcount; $x++)
                {
               		if(${"friend" . $x} == $friend) { echo "Already following!"; exit(1); }
                }

        // populate both users databases with each other.
        $friendcountFriend = file_get_contents("ssb_db/friends/" . $friend . ".count");
       	$friendcountFriend = $friendcountFriend + 1;
       	//echo $friendcountFriend;
      	file_put_contents("ssb_db/friends/" . $friend . ".php", $frienddb . "\n <?php \$friend" . $friendcountFriend ." = \"" . $user . "\";\n\$friend_chat_db" . $friendcountFriend . " = \"" . $user . $friend . "\";?>");
      	$friendcount = file_get_contents("ssb_db/friends/" . $user . ".count");
       	$friendcount = $friendcount + 1;
       	//echo $friendcount;
       	file_put_contents("ssb_db/friends/" . $user . ".php", $friendlist . "\n <?php \$friend" . $friendcount . " = \"" . $friend . "\";\n\$friend_chat_db" . $friendcount . " = \"" . $user . $friend . "\";?>");
    	file_put_contents("ssb_db/friends/" . $user . ".count", $friendcount);
       	file_put_contents("ssb_db/friends/" . $friend . ".count", $friendcountFriend);
		file_put_contents("ssb_db/friends/" . $user . $friend . ".count", "1");
		file_put_contents("ssb_db/friends/" . $user . $friend . ".php", "<?php \$msg1 = \"" . $user . " and " . $friend . " are now friends!<br />\";?>");

}

function acceptFriendRequest($user, $friend) {
	$friendpending = "ssb_db/friends/" . $user . ".pending";
	$friendlist = file_get_contents("ssb_db/friends/" . $user . ".php");
	$frienddb = file_get_contents("ssb_db/friends/" . $friend . ".php");
    	// check if friend request is really pending.

	$friendc = file_get_contents("ssb_db/friends/" . $user . ".count");
        include "ssb_db/friends/" . $user . ".php";
        for($x = 1; $x <= $friendc; $x++)
        {
             if(${"friend" . $x} == $friend) { echo "Already following!"; exit(1); }
        }

	$handle = fopen($friendpending, "r");
        if ($handle) {
		$xx = 0;
 		while (($line = fgets($handle)) !== false) {
			if($xx >= "1") {
				$line = str_replace("\n","",$line);
			}
			$xx++;
			//echo $line . "<br />";
			//echo $friend . "<br />";
			if($friend == $line)
			{
				// populate both users databases with each other.
				$friendcountFriend = file_get_contents("ssb_db/friends/" . $friend . ".count");
				$friendcountFriend = $friendcountFriend + 1;
				//echo $friendcountFriend;
				file_put_contents("ssb_db/friends/" . $friend . ".php", $frienddb . "\n <?php \$friend" . $friendcountFriend ." = \"" . $user . "\";\n\$friend_chat_db" . $friendcountFriend . " = \"" . $user . $friend . "\";?>");
				$friendcount = file_get_contents("ssb_db/friends/" . $user . ".count");
				$friendcount = $friendcount + 1;
				//echo $friendcount;
				file_put_contents("ssb_db/friends/" . $user . ".php", $friendlist . "\n <?php \$friend" . $friendcount . " = \"" . $friend . "\";\n\$friend_chat_db" . $friendcount . " = \"" . $user . $friend . "\";?>");
				file_put_contents("ssb_db/friends/" . $user . ".count", $friendcount);
				file_put_contents("ssb_db/friends/" . $friend . ".count", $friendcountFriend);
				file_put_contents("ssb_db/friends/" . $user . $friend . ".count", "1");
				file_put_contents("ssb_db/friends/" . $user . $friend . ".php", "<?php \$msg1 = \"" . $user . " and " . $friend . " are now friends!<br />\";?>");
			}
      		}
   		fclose($handle);
     	} else {
      		echo "ERROR: Friend: " . $friend . " not found in friend pending database.<br />";
    	}
}
?>
