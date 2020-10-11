<?php
//	SSB - Simple Social Board
//	(C) Chris Dorman, 2012 - 2020
//	License: CC-BY-NC-SA version 3.0
//	http://github.com/Pentium44/SSB

//error_reporting(E_ALL); 
//ini_set('display_errors', 1);

session_start();
include "config.php";
include "functions.php";
include "bbcode.php";

// check if flatfile database location is populated
if(!file_exists("ssb_db"))
{
	mkdir("ssb_db", 0777);
}

if(!file_exists("ssb_db/users"))
{
	mkdir("ssb_db/users", 0777);
}

if(!file_exists("ssb_db/posts"))
{
	mkdir("ssb_db/posts", 0777);
}

if(!file_exists("ssb_db/uploads"))
{
	mkdir("ssb_db/uploads", 0777);
}

if(!file_exists("ssb_db/friends"))
{
	mkdir("ssb_db/friends", 0777);
}

$username = $_SESSION['ssb-user'];
//$_SESSION['ssb-topic'] = $ssbtopic;



?>
<!DOCTYPE html>
<html lang="en-us">
<head>
<title><?php echo htmlentities(stripslashes($ssbtitle)); ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=.55, shrink-to-fit=yes"><meta name="description" content="<?php echo htmlentities($ssbtitle) . " - " . $desc; ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body <?php if($_GET['do']=="pubmsg" || $_GET['do']=="privmsg") { echo "onload=\"UpdateTimer();\""; } ?>>

<script type="text/javascript">

	function wrapBBCode(tag) {
		var msgInput = document.getElementById('msg');
		var content = msgInput.value;
		var selectedContent = content.substring(msgInput.selectionStart, msgInput.selectionEnd);
		var beforeContent = content.substring(0, msgInput.selectionStart);
		var afterContent = content.substring(msgInput.selectionEnd, content.length);
		msgInput.value = beforeContent + '[' + tag + ']' + selectedContent + '[/' + tag + ']' + afterContent;
	}

	function userTag(tag) {
                var msgInput = document.getElementById('msg');
                var content = msgInput.value;
                var beforeContent = content.substring(0, msgInput.selectionStart);
                var afterContent = content.substring(msgInput.selectionEnd, content.length);
                msgInput.value = beforeContent + '@' + tag + afterContent;
        }
</script>
<div class="maincontain">
<div id="navcontainer">
        <div id="navbar"><!--
        <?php if(isset($_SESSION['ssb-user']) && isset($_SESSION['ssb-pass'])) { ?>
	--><a style="width:50px;" href="?forms=post" title="Post on your feed!"><i style="padding:2px 2px 2px 2px;" class="fa fa-plus-square"></i></a><!--
        --><a style="width:50px;" href="?do=pubmsg" title="Public chat!"><i style="padding:2px 2px 2px 2px;" class="fa fa-comments-o"></i></a><!--
        --><a style="width:50px;" href="?userfeed=<?php echo $username; ?>" title="Your profile!"><i style="padding:2px 2px 2px 2px;" class="fa fa-user"></i></a><!--
        --><a href="index.php">Feed</a><!--
        --><a href="?do=friends">Friends</a><!--
        --><a href="?do=about">About</a><!--
	--><a style="width:50px;" href="?do=users" title="Public users!"><i style="padding:2px 2px 2px 2px;" class="fa fa-users"></i></a><!--
        --><a style="width:50px;" href="?do=userctrl"><i style="padding:2px 2px 2px 2px;" class="fa fa-cog"></i></a><!--
        --><a style="width:50px;" href="?do=logout"><i style="padding:2px 2px 2px 2px;" class="fa fa-sign-out"></i></a><!--
        <?php } else { ?>
        --><a href="?forms=login">Login</a><!--
        --><a href="?do=about">About</a><!--
        <?php } ?>
        --></div>
</div>
<div class='contain'>
<div class='title'><?php echo $ssbtitle; ?></div>

<?php

if(isset($username) && isset($_SESSION['ssb-pass']) && $_GET['do']!="avatarlocation") {
	// PM notifications
	$notifications = "ssb_db/friends/" . $username . ".notifications";
	$handle = fopen($notifications, "r");

	echo "<div class='notifications'>";
	echo "<table><tr><td><a class='button' href='?do=clrnote'>Clear notifications</a></td></tr>";

	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			echo "<tr><td><i class='fa fa-exclamation' aria-hidden='true'></i> " . $line . "</td></tr>";
		}
		fclose($handle);
	} else {
   		echo "<tr><td>No notifications</td></tr>";
	}

	echo "</table></div><br />";
}

if(isset($_GET['forms']))
{
	$forms = $_GET['forms'];
	$id = $_GET['pid'];
	if($forms=="register") {
		registerForm();
	}
	else if($forms=="login") {
		loginForm();
	}
	else if($forms=="friendreq") {
		friendReqForm();
	}
	else if($forms=="changepass") {
		changePassForm();
	}
	else if($forms=="deleteacct") {
		deleteAcctForm();
	}
	else if($forms=="avatarupload") {
                uploadAvatarForm();
        }
        else if($forms=="post")
        {
                postForm();
        }
        else if($forms=="clean")
        {
                cleanForm();
        }
        else { echo "ERROR: Unknown form-name<br>"; }
}
else if(isset($_GET['notify']))
{
        $notify = $_GET['notify'];
        if($notify=="1") { echo "Error: User not found"; }
        else if($notify=="2") { echo "Error: Incorrect password provided"; }
        else if($notify=="3") { echo "Error: Please fill out all the text boxes"; }
        else if($notify=="4") { echo "Error: The provided passwords did not match"; }
        else if($notify=="5") { echo "Error: Special characters cannot be used in your username"; }
        else if($notify=="6") { echo "Error: This username is already in use"; }
        else { echo "Error: unknown error... this is quite unusual..."; }
}
else if(isset($_GET['userfeed'])) 
{
	$userid = $_GET['userfeed'];
	// Make sure we're friends or is my account.
	include "ssb_db/users/" . $userid . ".php";
	if ($accttype == "private") {
		if (isset($_SESSION['ssb-user']) || isset($_SESSION['ssb-pass'])) {
			$friendcount = file_get_contents("ssb_db/friends/" . $username . ".count");
			include "ssb_db/friends/" . $username . ".php";
			for($x = 1; $x <= $friendcount; $x++)
			{

				// If private, and user is following. Allow
				if($userid == ${"friend" . $x}) {
					echo "<table><tr><td>";
					echo "<div class='avatar' style=\"background-image: url('index.php?do=avatarlocation&user=" . $userid . "');\" title='User Avatar'></div><br />";
					// DONE
					echo "</td><td>";
					echo "<h3>User information</h3>";
					echo "Username: " . $userid . "@" . $domain . "<br />";
					echo "Full name: " . $user_fullname . "<br />";
					echo "<h3>User posts</h3>";
					echo "</td></tr></table>";
				}
			}

			// Check if viewing your own profile
			if($userid == $username)
			{
				echo "<table><tr><td>";
				// Get user avatar if set
            	echo "<div class='avatar' style=\"background-image: url('index.php?do=avatarlocation&user=" . $userid . "');\" title='User Avatar'></div><br />";
             	// DONE
              	echo "</td><td>";
				echo "<h3>User information</h3>";
				echo "Username: " . $userid . "@" . $domain . "<br />";
				echo "Full name: " . $user_fullname . "<br />";
				echo "<h3>User posts</h3>";
				echo "</td></tr></table>";

			}

			// Lets generate the users feed now.
			foreach(array_reverse(glob("ssb_db/posts/post_" . $userid . "_" . "*.php")) as $postfile) {
				//echo $postfile;
				include $postfile;
				for($x = 1; $x <= $friendcount; $x++)
				{
					if($postowner == ${"friend" . $x}) {
						echo "<div class='post'><table><tr><td><div class='avatar_small' style=\"background-image: url('index.php?do=avatarlocation&user=$postowner');\" title='User Avatar'></div></td><td><h3>$postowner<span style='font-size: 11px; padding-left: 6px; color: #808080;'>$postdate</span><br /><a href='index.php?view=$postid&user=$postowner'><i class='fa fa-reply'></i></a></h3></td></tr></table>";
						echo "" . bbcode_format($postcontent) . "";
						echo "</div><br />\n";
					}
				}

				if($postowner == $username)
				{
					echo "<div class='post'><table><tr><td><div class='avatar_small' style=\"background-image: url('index.php?do=avatarlocation&user=$postowner');\" title='User Avatar'></div></td><td><h3>$postowner<span style='font-size: 11px; padding-left: 6px; color: #808080;'>$postdate</span><br /><a href='index.php?view=$postid&user=$postowner'><i class='fa fa-reply'></i></a> <a href='index.php?do=delpost&user=$username&pid=$postid'><i class='fa fa-trash-o'></i></a></h3></td></tr></table>";
					echo "" . bbcode_format($postcontent) . "";
					echo "</div><br />\n";
				}
			}
			echo "<!-- Gen done...-->";
		}
	}
	else
	{
		echo "<h3>User information</h3>";
		echo "<table><tr><td>";
		// Get user avatar if set
		if(isset($user_avatar)) { echo "<img class='avatar' src='ssb_db/uploads/" . $user_avatar . "' title='User Avatar'><br />"; }
			// DONE
			echo "</td><td>";
			// If not friend, allow to send friend request from right here!
			$friend = 0;
			$friendcount = file_get_contents("ssb_db/friends/" . $username . ".count");
			include "ssb_db/friends/" . $username . ".php";
			for($x = 1; $x <= $friendcount; $x++)
			{
				// If private, and user is following. Allow
				if($userid == ${"friend" . $x}) {
					$friend = 1;
				}
			}	
				
			if($friend!=1) {
				echo "<a class='button' href='index.php?do=sendfr&user=$userid'>Send friend request</a><br /><br />";
			}
             	
			echo "Username: " . $userid . "@" . $domain . "<br />";
			echo "Full name: " . $user_fullname;
			echo "</td></tr></table>";

		foreach(array_reverse(glob("ssb_db/posts/post_" . $userid . "_" . "*.php")) as $postfile) {
			//echo $postfile;
			include $postfile;
			
			echo bbcode_format($postcontent);
		}
	}
}
else if(isset($_GET['view']) && isset($_GET['user']))
{
	$puser = $_GET['user'];
	$id = $_GET['view'];
	$postc = file_get_contents("ssb_db/posts/reply_" . $puser . "_" . $id . ".count");
	include "ssb_db/posts/post_" . $puser . "_" . $id . ".php";

	echo "<div class='post'><table><tr><td><div class='avatar_small' style=\"background-image: url('index.php?do=avatarlocation&user=$postowner');\" title='User Avatar'></div></td><td><h3>$postowner<span style='font-size: 11px; padding-left: 6px; color: #808080;'>$postdate</span></h3></td></tr></table>";
	echo "" . bbcode_format($postcontent) . "";
	echo "</div><br />\n";

	for($x = 1; $x <= $postc; $x++) {
		$reply_content = ${"reply" . $x};
		$reply_user = ${"reply" . $x . "_user"};
		$reply_date = ${"reply" . $x . "_date"};
		
		echo "<div class='reply'>";
		echo "<table><tr><td><div class='avatar_small' style='background-image: url(\"index.php?do=avatarlocation&user=$reply_user\");' title='User Avatar'></div></td><td><h4>$reply_user <a onclick=\"userTag('$reply_user');\"><i class='fa fa-tag'></i></a> <span style='font-size: 8px; padding-left: 6px; color: #808080;'>$reply_date</span></h4></td></tr></table>";
		echo "<div class='reply_content'>" . bbcode_format($reply_content) . "</div>";
		echo "</div>\n";
	}

	echo "<br />";

	if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { 
		echo "Login to reply...";
	} else {
		$friendcount = file_get_contents("ssb_db/friends/" . $username . ".count");
                include "ssb_db/friends/" . $username . ".php";
                for($x = 1; $x <= $friendcount; $x++)
	        {
                        if($puser == ${"friend" . $x}) {
				$z = "1";
				replyForm($id, $puser);
			}
		}

		// Its you dummy
		if($puser == $username) {
                 	$z = "1";
                      	replyForm($id, $puser);
           	}


		if(!isset($z))
		{
			echo "Not following! Follow to reply...<br />";
		}
	}
}
else if(isset($_GET['do']))
{
	$do = $_GET['do'];
	if($do=="post")
	{
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			$date = date("YmdHis"); // timestamp in year, month, date, hour, minute, and second.
  			$titledate = date("m-d-Y h:i:sa"); // time stamp for people to read xD

			if(isset($_FILES["file"]["name"]) && isset($username)) {
				
				$uploaded = array(); // empty array for upload names
				// File selected, upload!
				for($i=0; $i<count($_FILES["file"]["name"]); $i++)
				{
					$allowedExts = array("gif", "jpeg", "jpg", "png", "bmp", "ico", "GIF", "JPEG", "JPG", "PNG", "BMP", "ICO");
					$temp = explode(".", $_FILES["file"]["name"][$i]);
					$extension = end($temp);
					if ((($_FILES["file"]["type"][$i] == "image/gif")
					|| ($_FILES["file"]["type"][$i] == "image/x-gif")
					|| ($_FILES["file"]["type"][$i] == "image/jpeg")
					|| ($_FILES["file"]["type"][$i] == "image/x-jpeg")
					|| ($_FILES["file"]["type"][$i] == "image/x-jpg")
					|| ($_FILES["file"]["type"][$i] == "image/jpg")
					|| ($_FILES["file"]["type"][$i] == "image/pjpeg")
					|| ($_FILES["file"]["type"][$i] == "image/x-png")
					|| ($_FILES["file"]["type"][$i] == "image/bmp")
					|| ($_FILES["file"]["type"][$i] == "image/x-icon")
					|| ($_FILES["file"]["type"][$i] == "application/octet-stream")
//					|| ($_FILES["file"]["type"][$i] == "video/mp4")
//					|| ($_FILES["file"]["type"][$i] == "video/ogg")
//					|| ($_FILES["file"]["type"][$i] == "video/webm")
//					|| ($_FILES["file"]["type"][$i] == "video/x-flv")
//					|| ($_FILES["file"]["type"][$i] == "video/mp4v-es")
					|| ($_FILES["file"]["type"][$i] == "image/png")
					|| ($_FILES["file"]["type"][$i] == ""))
					&& ($_FILES["file"]["size"][$i] < $user_max_upload)
					&& in_array($extension, $allowedExts))
					{
						
						if ($_FILES["file"]["error"][$i] > 0)
						{
							echo $_FILES["file"]["name"][$i] . " - Return Code: " . $_FILES["file"]["error"][$i] . "<br />";
						}
						else
						{
							if(file_exists("ssb_db/uploads/" . $_FILES["file"]["name"][$i]))
							{
								echo "Error: " . $_FILES["file"]["name"][$i] . " exists.<br />";
							}
							else
							{
								$randstring = getRandString("32");
								move_uploaded_file($_FILES["file"]["tmp_name"][$i],
								"ssb_db/uploads/" . $randstring . "." . $extension);
								array_push($uploaded, $randstring . "." . $extension);
								echo "Success: " . $_FILES["file"]["name"][$i] . " (" . tomb($_FILES["file"]["size"][$i]) . ") uploaded...<br />";
								//rename("ssb_db/uploads/" . $FILES["file"]["name"][$i], "ssb_db/uploads/" . $username . "_" . $date . $extension);
							}
						}
					}
					else
					{
						// Check if there was actually an issue
						if($_FILES["file"]["size"] == "0") {
							echo "Error: " . $_FILES["file"]["name"][$i] . " is too large, or is a invalid filetype";
						}
					}
				} // end of for loop
		
				$srchcont = stripslashes(htmlentities($_POST['body']));
				$srchcont .= " "; // doesn't find tag if there's not a fucking whitespace
				$checkForUserTag = searchForUserTag($srchcont);
				$taggedUser = substr($checkForUserTag, 1, -1);
				if(file_exists("ssb_db/users/" . $taggedUser . ".name")) {
					if($taggedUser!=$postowner) {
						$tagged_notifications = file_get_contents("ssb_db/friends/" . $taggedUser . ".notifications");
						file_put_contents("ssb_db/friends/" . $taggedUser . ".notifications", "<b>$username</b> <a href='index.php?view=$pid&user=$postowner'>tagged you in a comment</a>\n" . $tagged_notifications);
					}
				}

				$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
				//$username = stripcslashes(htmlentities($username));
				include "ssb_db/users/" . $username . ".php";
				$post_file = "ssb_db/posts/post_" . $username . "_" . $date . ".php";
				$post_attachments = "<br />";
				$post_string = "<?php\n\$postowner = \"" . $username . "\";\$postid=\"" . $date . "\";\$postdate=\"" . $titledate . "\";\$postcontent = \"" . $body . "<br />";
					
				$attachments = array();
				foreach($uploaded as &$upload)
				{
					if(file_exists("ssb_db/uploads/" . $upload)) {
						array_push($attachments, "<div class='attachment'><a href='ssb_db/uploads/" . $upload . "'><img src='ssb_db/uploads/" . $upload . "'></a></div>");
					}
				}
				
				foreach($attachments as &$attachvar)
				{
					$post_attachments .= $attachvar;
				}
				
				$post_string_end = "\";\n?>\n";
				
				file_put_contents($post_file, $post_string . $post_attachments . $post_string_end);
				file_put_contents("ssb_db/posts/" . $date . ".post", "post_" . $username . "_" . $date . ".php");
				file_put_contents("ssb_db/posts/reply_" . $username . "_" . $date . ".count", "0");
				echo "Post processed... Redirecting in 3 seconds, if redirection fails, <a href=\"?view=$date&user=$username\">Click Here</a><br />";
				//header( "refresh: 3; url=?view=$date&user=$username" );
			}
			else
			{
				echo "ERROR: Missing post data! Select an image to upload or let us know whats up!<br />";
			}		
		}
	}
	
	if($do=="avatarupload")
	{
		if(isset($_FILES["file"]["name"]) && isset($username)) {
			$date = date("YmdHis"); // timestamp in year, month, date, hour, minute, and second.

      		for($i=0; $i<count($_FILES["file"]["name"]); $i++)
			{
				$allowedExts = array("gif", "jpeg", "jpg", "png", "bmp", "ico", "png");
				$temp = explode(".", $_FILES["file"]["name"][$i]);
               	$extension = end($temp);
				if ((($_FILES["file"]["type"][$i] == "image/gif")
				|| ($_FILES["file"]["type"][$i] == "image/x-gif")
				|| ($_FILES["file"]["type"][$i] == "image/jpeg")
				|| ($_FILES["file"]["type"][$i] == "image/x-jpeg")
				|| ($_FILES["file"]["type"][$i] == "image/x-jpg")
				|| ($_FILES["file"]["type"][$i] == "image/jpg")
				|| ($_FILES["file"]["type"][$i] == "image/pjpeg")
				|| ($_FILES["file"]["type"][$i] == "image/x-png")
				|| ($_FILES["file"]["type"][$i] == "image/bmp")
				|| ($_FILES["file"]["type"][$i] == "image/x-icon")
				|| ($_FILES["file"]["type"][$i] == "image/png")
				|| ($_FILES["file"]["type"][$i] == ""))
				&& ($_FILES["file"]["size"][$i] < $user_max_upload)
				&& in_array($extension, $allowedExts))
				{
					if ($_FILES["file"]["error"][$i] > 0)
					{
						echo $_FILES["file"]["name"][$i] . " - Return Code: " . $_FILES["file"]["error"][$i] . "<br>";
					}
					else
					{
						if(file_exists("ssb_db/uploads/" . $_FILES["file"]["name"][$i]))
						{
							echo "Error: " . $_FILES["file"]["name"][$i] . " exists.<br>";
						}
						else
						{
							move_uploaded_file($_FILES["file"]["tmp_name"][$i],
							"ssb_db/uploads/" . $username . "_" . $date . "." . $extension);
							$oldcontent = file_get_contents("ssb_db/users/" . $username . ".php");
							file_put_contents("ssb_db/users/" . $username . ".php", $oldcontent . "<?php \$user_avatar = \"" . $username . "_" . $date . "." . $extension . "\"; ?>\n");
							echo "Avatar uploaded and set! <a href='index.php'>Redirecting</a> in 3 seconds...";
							header("refresh: 3;url=index.php");
						}
					}
				} else {
					echo "Error: " . $_FILES["file"]["name"][$i] . " is too large, or is a invalid filetype";
				}
			}
		}
	}

	if($do=="users")
        {
                 if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
                        include "ssb_db/users/" . $username . ".php";

			echo "<h2>Community</h2>";
                        foreach(array_reverse(glob("ssb_db/users/"."*.name")) as $userfile) {
                		$userhandle = file_get_contents($userfile);
				include "ssb_db/users/" . $userhandle . ".php";
                                if($accttype == "public") {
					echo "<div class='attachment'>";
                                	echo "<a href='index.php?userfeed=$userhandle'>$userhandle</a>";
					echo "</div>";
				}
			}
                }
        }

	if($do=="reply")
	{
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			if(!isset($_GET['pid']) or !file_exists("ssb_db/posts/" . $_GET['pid'] . ".post")) { echo "ERROR: Post ID is not set, or invalid"; } else {
				if(isset($_POST['reply']) && isset($username) && $_POST['body']!="")
				{
					$pid = $_GET['pid'];
					$post_file_name = file_get_contents("ssb_db/posts/$pid.post");
                    include "ssb_db/posts/" . $post_file_name;
					$srchcont = stripslashes(htmlentities($_POST['body']));
					$srchcont .= " ";
					$checkForUserTag = searchForUserTag($srchcont);
					$taggedUser = substr($checkForUserTag, 1, -1);
					if(file_exists("ssb_db/users/" . $taggedUser . ".name")) {
						if($taggedUser!=$postowner) {
							$tagged_notifications = file_get_contents("ssb_db/friends/" . $taggedUser . ".notifications");
							file_put_contents("ssb_db/friends/" . $taggedUser . ".notifications", "<b>$username</b> <a href='index.php?view=$pid&user=$postowner'>tagged you in a comment</a>\n" . $tagged_notifications);
						}
					}

					$replydate = date("m-d-Y h:i:sa"); // time stamp for people to read xD
					$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
					//$username = stripcslashes(htmlentities($username));
					$old_content = file_get_contents("ssb_db/posts/" . $post_file_name);
					$reply_count = file_get_contents("ssb_db/posts/reply_" . $postowner  . "_" . $pid . ".count");

					$reply_count = $reply_count+1;

					$post_string = "<?php \n\$reply" . $reply_count . " = \"" . $body . "\";\$reply" . $reply_count . "_user = \"" . $username . "\"; \$reply" . $reply_count . "_date = \"" . $replydate . "\";\n?>\n";
					file_put_contents("ssb_db/posts/" . $post_file_name, $old_content . $post_string);
					file_put_contents("ssb_db/posts/reply_" . $postowner . "_" . $pid . ".count", $reply_count);

					if($username!=$postowner) {
						$owner_notifications = file_get_contents("ssb_db/friends/" . $postowner . ".notifications");
						file_put_contents("ssb_db/friends/" . $postowner . ".notifications", "<b>$username</b> <a href='index.php?view=$pid&user=$postowner'>replied to your post</a>\n" . $owner_notifications);
					}

					echo "If you're seeing this; redirection failed: <a href=\"?view=$pid&user=$postowner\">Click Here</a><br>";
					header( "Location: index.php?view=$pid&user=$postowner" );
				}
				else
				{
					echo "ERROR: Missing form data<br>";
				}
			}
		}
	}
	
	if($do=="delpost") 
	{
		 if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			include "ssb_db/users/" . $username . ".php";
			if($user_password === $_SESSION['ssb-pass']) {
				if(isset($_GET['user']) && $_GET['user']!="" && isset($_GET['pid']) && $_GET['pid']!="") {
					if(file_exists("ssb_db/posts/post_" . stripslashes($_GET['user']) . "_" . stripslashes($_GET['pid']) . ".php") && $username == stripslashes($_GET['user'])) {
						$postuser = $_GET['user'];
						$pid = $_GET['pid'];
						unlink("ssb_db/posts/" . $pid . ".post");
						unlink("ssb_db/posts/post_" . $postuser . "_" . $pid . ".php");
						unlink("ssb_db/posts/reply_" . $postuser . "_" . $pid . ".count");
						echo "Post successfully deleted! <a href='index.php'>redirecting</a> in 3 seconds...<br />";
						header("refresh: 3;url=index.php");
						exit;
					} else { echo "ERROR: post doesn't exist or YOU ARE NOT THE OWNER OF SAID POST... THIS incident has been recorded!"; file_put_contents("ssb_db/log.txt", "Post deletion error: IP <" . $_SERVER['REMOTE_ADDR'] . "> post not found or not users post: post_" . $postuser . "_" . $pid . ".php\n"); }
				} else { echo "ERROR: USER and PID variables not set!"; }
			} else { echo "ERROR: PASSWORD FOR USER INCORRECT! IP LOGGED!"; file_put_contents("ssb_db/log.txt", "PASS MISMATCH: IP <" . $_SERVER['REMOTE_ADDR'] . "> Cookie spoofing detected from remote client!!!\n"); }
		}
	}
	
	if($do=="clrnote") 
	{
		 if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			include "ssb_db/users/" . $username . ".php";
			if($user_password === $_SESSION['ssb-pass']) {
				unlink("ssb_db/friends/" . $username . ".notifications");
				header("Location: index.php");
				exit;
			} else { echo "ERROR: PASSWORD FROM COOKIE INCORRECT! IP RECORDED!"; file_put_contents("ssb_db/log.txt", "PASS MISMATCH: IP <" . $_SERVER['REMOTE_ADDR'] . "> Cookie spoofing detected from remote client!!!\n"); }
		}
	}
	
	if($do=="clrpending") 
	{
		 if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			include "ssb_db/users/" . $username . ".php";
			if($user_password === $_SESSION['ssb-pass']) {
				unlink("ssb_db/friends/" . $username . ".pending");
				header("Location: index.php?do=friends");
				exit;
			} else { echo "ERROR: PASSWORD FROM COOKIE INCORRECT! IP RECORDED!"; file_put_contents("ssb_db/log.txt", "PASS MISMATCH: IP <" . $_SERVER['REMOTE_ADDR'] . "> Cookie spoofing detected from remote client!!!\n"); }
		}
	}

	// Server admin can just delete ssb_db
	/*if($do=="clean")
	{
		if($_POST['password']!="" && $_POST['password']==$pw)
		{
			$db_content = glob("ssb_db/" . '*', GLOB_MARK);
			foreach($db_content as $file)
			{
				unlink($file);
			}
			rmdir("ssb_db");
			echo "Database Cleaned<br>";
		}
		else
		{
			echo "ERROR: Wrong Password<br>";
		}
	}*/


	// grab session values and send friend request functions.
	if($do=="sendfr") {
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			if(isset($_POST['user']) || isset($_GET['user'])) {
				
				//check if user exists first lol
				if(isset($_POST['user'])) { 
					$givenUser = htmlentities(stripcslashes($_POST['user']));
				} else {
					$givenUser = htmlentities(stripcslashes($_GET['user']));
				}
				
				//check if user exists first lol
				if(file_exists("ssb_db/users/" . $givenUser . ".php")) {
					include "ssb_db/users/" . $givenUser . ".php";

					if($accttype == "private") {
						sendFriendRequest($_SESSION['ssb-user'], $givenUser);
						echo "Follow request sent to " . $givenUser . " <a href='?do=friends'>redirecting</a> in 3 seconds";
						header("refresh: 3;url=?do=friends");
					} else if($accttype == "public") {
						acceptPublicFriendRequest($username, $givenUser);
						header("Location: ?do=friends");
					} else {
						echo "ERROR: Issues parsing account type...";
					}
				} else {
					echo "Error: Provided username does not exist in the database!";
				}
			} else {
				echo "Error: users not set in GET value...";
			}
		}
	}

	if($do=="accfr") {
                if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
                        if(isset($_GET['user']) && isset($_GET['friend'])) {
                                acceptFriendRequest(stripslashes($_GET['user']), stripslashes($_GET['friend']));
				echo "Accepted friend request from  " . htmlentities(stripslashes($_GET['friend'])) . " <a href='?do=friends'>redirecting</a> in 3 seconds";
				header("refresh: 3;url=?do=friends");
                        } else {
                                echo "Error: users not set in GET &amp; SESSION value...";
                        }
                }
        }

	if($do=="userctrl")
	{
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
                        // Beginning of user control panel
			echo "<h3>User control panel</h3>";
			echo "<a class='button' href='?forms=changepass'>Change password</a><br />";
			echo "<a class='button' href='?forms=avatarupload'>Upload avatar</a><br />";
                }
	}

	if($do=="changepass")
	{
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			// Beginning password change
			// inputs
			$oldPassInput = htmlentities(stripslashes($_POST['oldpass']));
			$newPassInput = htmlentities(stripslashes($_POST['password']));
			$passwordAgainInput = htmlentities(stripslashes($_POST['password_again']));
			include "ssb_db/users/" . $username . ".php";
			if(sha1(md5($oldPassInput)) == $user_password) {
				if($newPassInput == $passwordAgainInput) {
					$oldcontent = file_get_contents("ssb_db/users/" . $username . ".php");
					$passString = "<?php \$user_password = \"" . sha1(md5($newPassInput)) . "\"; ?>\n";
					file_put_contents("ssb_db/users/" . $username . ".php", $oldcontent . $passString);
					echo "Password changed, <a href='index.php'>redirecting</a> in 3 seconds";
					$_SESSION['ssb-user'] = null;
					$_SESSION['ssb-pass'] = null;
					header("refresh: 3;url=index.php");
				}
			} else { echo "ERROR: password incorrect! IP recorded for constant monitoring of possible bots!"; file_put_contents("ssb_db/log.txt", "PASS MISMATCH: IP <" . $_SERVER['REMOTE_ADDR'] . "> Cookie spoofing detected from remote client!!!\n"); }
		}
	}

	if($do=="pubmsg")
	{
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
		?>
<script language="javascript" type="text/javascript">
    <!--
		var httpObject = null;
		var link = "";
		var timerID = 0;
		var nickName = "<?php echo $_SESSION['ssb-user']; ?>";
		var userColor = "<?php echo $_SESSION['ssb-color'];; ?>";

		// Get the HTTP Object
		function getHTTPObject() {
			if (window.ActiveXObject) return new ActiveXObject("Microsoft.XMLHTTP");
			else if (window.XMLHttpRequest) return new XMLHttpRequest();
			else {
				alert("Your browser does not support AJAX.");
				return null;
			}
		}   

		// Change the value of the outputText field
		function setHtml() {
			if(ajaxVar.readyState == 4){
				var response = ajaxVar.responseText;
				var msgBox = document.getElementById("msgs");
				msgBox.innerHTML += response;
				msgBox.scrollTop = msgBox.scrollHeight;
			}
		}

		// Change the value of the outputText field
		function setAll() {
			if(ajaxVar.readyState == 4){
				var response = ajaxVar.responseText;
				var msgBox = document.getElementById("msgs");
				msgBox.innerHTML = response;
				msgBox.scrollTop = msgBox.scrollHeight;
			}
		}

		// Implement business logic    
		function serverWrite() {    
			ajaxVar = getHTTPObject();
			if (ajaxVar != null) {
				link = "chatserver.php?nick="+nickName+"&msg="+document.getElementById('msg').value; 
				ajaxVar.open("GET", link , true);
				ajaxVar.onreadystatechange = setHtml;
				ajaxVar.send(null);
			}
		}
      
		function getInput() {
			// Send the server function the input
			var userInput = document.getElementById('msg');
			serverWrite(userInput.value);
			
			// Clean out the input values
			var msgBar = document.getElementById("msg");
			msgBar.value = "";
            msgBar.focus();
		}

		// Implement business logic    
		function serverReload() {    
			ajaxVar = getHTTPObject();
			//var randomnumber=Math.floor(Math.random()*10000);
			if (ajaxVar != null) {
				link = "chatserver.php?all=1";
				ajaxVar.open("GET", link , true);
				ajaxVar.onreadystatechange = setAll;
				ajaxVar.send(null);
			}
		}
	
		function UpdateTimer() {
			serverReload();   
			setTimeout(UpdateTimer, 1000);
		}
    
		function keypressed(e) {
			if(e.keyCode=='13'){
				getInput();
			}
		}
    //-->
    </script> 
<div class="replycontain">
		<div id="msgs">
		<?php 
			echo "<div class=\"msgbox\">";
			$get = file_get_contents($chat_db);
			echo $get;
			echo "</div>";
		?>
		</div>
		<div id="msgbox" onkeyup="keypressed(event);">

		<button onclick="javascript:wrapBBCode('i');">Italic</button>
     		<button onclick="javascript:wrapBBCode('u');">Underline</button>
        	<button onclick="javascript:wrapBBCode('b');">Bold</button>
    		<button onclick="javascript:wrapBBCode('url');">URL</button><br />
		<textarea style="width: 98%;" name="msg" id="msg"></textarea>
		<button style="width: 50px;" onclick="getInput();">Send</button>
		</div>
</div>

	<?php



		}
	}
	
	if($do=="privmsg")
	{
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			
		//check if friend is set
		if(!isset($_GET['friend'])) { echo "ERROR: No username defined!"; exit(1); } else {
		// set friend username
		$friendNick = htmlentities(stripslashes($_GET['friend']));
		
		$friendcount = file_get_contents("ssb_db/friends/" . $username . ".count");
		include "ssb_db/friends/" . $username . ".php";
		for($x = 1; $x <= $friendcount; $x++)
        {
			if($friendNick == ${"friend" . $x}) {
		?>
<script language="javascript" type="text/javascript">
    <!--
		var httpObject = null;
		var link = "";
		var timerID = 0;
		var friendNick = "<?php echo $friendNick; ?>";
		var nickName = "<?php echo $_SESSION['ssb-user']; ?>";
		var userColor = "<?php echo $_SESSION['ssb-color'];; ?>";

		// Get the HTTP Object
		function getHTTPObject() {
			if (window.ActiveXObject) return new ActiveXObject("Microsoft.XMLHTTP");
			else if (window.XMLHttpRequest) return new XMLHttpRequest();
			else {
				alert("Your browser does not support AJAX.");
				return null;
			}
		}   

		// Change the value of the outputText field
		function setHtml() {
			if(ajaxVar.readyState == 4){
				var response = ajaxVar.responseText;
				var msgBox = document.getElementById("msgs");
				msgBox.innerHTML += response;
				msgBox.scrollTop = msgBox.scrollHeight;
			}
		}

		// Change the value of the outputText field
		function setAll() {
			if(ajaxVar.readyState == 4){
				var response = ajaxVar.responseText;
				var msgBox = document.getElementById("msgs");
				msgBox.innerHTML = response;
				msgBox.scrollTop = msgBox.scrollHeight;
			}
		}

		// Implement business logic    
		function serverWrite() {    
			ajaxVar = getHTTPObject();
			if (ajaxVar != null) {
				link = "chatserver.php?nick="+nickName+"&friend="+friendNick+"&msg="+document.getElementById('msg').value; 
				ajaxVar.open("GET", link , true);
				ajaxVar.onreadystatechange = setHtml;
				ajaxVar.send(null);
			}
		}
      
		function getInput() {
			// Send the server function the input
			var userInput = document.getElementById('msg');
			serverWrite(userInput.value);
			// Clean out the input values
			var msgBar = document.getElementById("msg");
			msgBar.value = "";
            msgBar.focus();
		}

		// Implement business logic    
		function serverReload() {    
			ajaxVar = getHTTPObject();
			//var randomnumber=Math.floor(Math.random()*10000);
			if (ajaxVar != null) {
				link = "chatserver.php?get=<?php echo $friendNick; ?>";
				ajaxVar.open("GET", link , true);
				ajaxVar.onreadystatechange = setAll;
				ajaxVar.send(null);
			}
		}
	
		function UpdateTimer() {
			serverReload();   
			setTimeout(UpdateTimer, 1000);
		}
    
		function keypressed(e) {
			if(e.keyCode=='13'){
				getInput();
			}
		}
    //-->
    </script> 
<div class="replycontain">
		<?php
		
		// Header
		include "ssb_db/users/" . $friendNick . ".php";
		echo "<h3><a href='?userfeed=" . $friendNick . "'>" . $friendNick . ": " . $user_fullname . "</a></h3>";
		
		?>
		<div id="msgs">
		<?php 
			echo "<div class=\"msgbox\">";
			echo "</div>";
		?>
		</div>
		<div id="msgbox" onkeyup="keypressed(event);">
			<button onclick="javascript:wrapBBCode('i');">Italic</button>
     		<button onclick="javascript:wrapBBCode('u');">Underline</button>
        	<button onclick="javascript:wrapBBCode('b');">Bold</button>
        	<button onclick="javascript:wrapBBCode('img');">Image</button>
    		<button onclick="javascript:wrapBBCode('url');">URL</button><br />
			<textarea style="width: 98%;" name="msg" id="msg"></textarea>
			<button style="width: 50px;" onclick="getInput();">Send</button>
		</div>
</div>

	<?php
		} // Check friend end
		} // Check loop end
		} // GET friend set end
		} // session check end
	} // function end

	// Push user avatar to specific avatar image location
	if($do=="avatarlocation")
	{
		if(isset($_GET['user'])) {
			$user = htmlentities(stripslashes($_GET['user']));
			include "ssb_db/users/" . $user . ".php";
			if(file_exists("ssb_db/uploads/" . $user_avatar)) {
				echo "Direct to: ssb_db/uploads/" . $user_avatar;
				header("Location: ssb_db/uploads/" . $user_avatar . "");
				exit;
			} else {
				echo "Direct to: data/defaultprofile.png";
				header("Location: data/defaultprofile.png");
				exit;
			}
		} else {
			echo "User is NOT set!";
		}
	}

	if($do=="about")
        {
                echo "<h2>About</h2>";
		echo "<div class='dllink'><a class='button' href='download/securespace-v1.0.0.apk'>Download for Android!</a></div>";
		echo $desc;
        }

	if($do=="friends")
        {
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			
			$friendpend = "ssb_db/friends/" . $username . ".pending";
			$handle = fopen($friendpend, "r");

			echo "<h3>Friend requests</h3> <a class='button' href='?do=clrpending'>Clear history</a> <a class='button' href='?forms=friendreq'>Send friend request</a>";
			echo "<div class='notifications'>";

			if ($handle) {
    				while (($line = fgets($handle)) !== false) {
        				echo "Pending friend request from " . $line . "! <a class='button' href='?do=accfr&friend=" . $line . "&user=" . $username . "'>Accept</a><br />";
    				}
				fclose($handle);
			} else {
			    echo "No pending friend requests<br />";
			} 

			echo "</div>";

			// Friends list if you have any.
			echo "<h3>Friends list</h3><br />";

			$friendc = file_get_contents("ssb_db/friends/" . $username . ".count");
			if($friendc == "0")
			{
				echo "<b style='color:red;'>We're sorry... no friends found on your user account...</b>";
			}
			else
			{
				$friendcount = file_get_contents("ssb_db/friends/" . $username . ".count");
				include "ssb_db/friends/" . $username . ".php";
				echo "<table class='friendslist'>";
				for($x = 1; $x <= $friendcount; $x++)
				{
					if(isset(${"friend" . $x})) {
						echo "<tr><td>" . ${"friend" . $x} . "</td><td><a class='button' href='?userfeed=" . ${"friend" . $x} . "'>View user profile</a></td><td><a class='button' href='?do=privmsg&friend=" . ${"friend" . $x} . "'>Private message</a></td></tr>";
					}
				}
				echo "</table>";
			}
		}
        }

	if($do=="login")
	{
		$username = $_POST['username'];
    		if(file_exists("ssb_db/users/$username.php")) {
			include_once("ssb_db/users/$username.php");
			if($user_password==sha1(md5($_POST['password']))) {
				$pass = $user_password;
				$user = $username;
				$color = $user_color;
				$_SESSION['ssb-user'] = $user;
				$_SESSION['ssb-pass'] = $pass;
				$_SESSION['ssb-color'] = $color;
				header("Location: index.php");
			} else {
				echo "Wrong password!";
			}
		} else {
			echo "User $username not found!";
		}
	}

	if($do=="logout")
	{
	        $_SESSION['ssb-user'] = null;
	        $_SESSION['ssb-pass'] = null;
		header("Location: index.php?forms=login");
	}

	if($do=="register")
	{
		if($_POST['username']!="" && $_POST['password']!="" && $_POST['password-again']!="" && $_POST['fullname']!="" && isset($_POST['acct'])) {
			if($_POST['password']==$_POST['password-again']) {
				if(!preg_match('/[^a-z0-9]/i', $_POST['username'])) {
					if(!file_exists("ssb_db/users/" . $_POST['username'] . ".php")) {
						$colors = array("0000ff", "9900cc", "0080ff", "008000", "ededed");
						$acct = $_POST['acct'];
						file_put_contents("ssb_db/users/" . stripslashes(htmlentities($_POST['username'])) . ".php", "<?php\n\$accttype = \"" . $acct . "\";\n\$user_password = \"" . sha1(md5($_POST['password'])) . "\";\n \$user_color = \"" . $colors[array_rand($colors)] . "\"; \$user_fullname = \"" . stripslashes(htmlentities($_POST['fullname'])) . "\"; \$user_avatar = \"../../data/defaultprofile.png\"; \n?>");
						file_put_contents("ssb_db/users/" . stripslashes(htmlentities($_POST['username'])) . ".name", stripslashes(htmlentities($_POST['username'])));
						file_put_contents("ssb_db/users/" . stripslashes(htmlentities($_POST['username'])) . ".postnumber", "0");
						file_put_contents("ssb_db/friends/" . stripslashes(htmlentities($_POST['username'])) . ".count", "0");
						file_put_contents("ssb_db/friends/" . stripslashes(htmlentities($_POST['username'])) . ".php", "<?php ?>\n");
						header("Location: index.php");
					} else {
						header("Location: index.php?notify=6");
					}
				} else {
					header("Location: index.php?notify=5");
				}
			} else {
				header("Location: index.php?notify=4");
			}
		} else {
			header("Location: index.php?notify=3");
		}
		header("Location: index.php");
	}
}
else if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass']))
{
        loginForm();
} 
else
{
	// Watch feed, lets generate pages while we're at it
	$pagecall = $_GET['page'];
	$postcount = 1;
	if(isset($pagecall) && $pagecall!="")
	{
		if($pagecall == "1")
		{
			$poststart = $postcount;
		}
		else
		{
			$poststart = ($pagecall - 1) * 15; // 15 posts per page
		}
	}
	else
	{
		$poststart = $postcount;
	}
	
	
	
	// Lets actually generate some feed now.
	foreach(array_reverse(glob("ssb_db/posts/*.post")) as $postfile) {
		$postphp = file_get_contents($postfile);
		include "ssb_db/posts/$postphp";
		$friendcount = file_get_contents("ssb_db/friends/" . $username . ".count");
		include "ssb_db/friends/" . $username . ".php";
		
		for($x = 1; $x <= $friendcount; $x++)
		{
			if($postowner == ${"friend" . $x}) {
				// Found a post, post count goes up!
				$postcount++;
				
				if($poststart == "1" && $postcount < ($poststart + 15)) {
					echo "<div class='post'><table><tr><td><div class='avatar_small' style=\"background-image: url('index.php?do=avatarlocation&user=$postowner');\" title='User Avatar'></div></td><td><h3>$postowner<span style='font-size: 11px; padding-left: 6px; color: #808080;'>$postdate</span><br /><a href='index.php?view=$postid&user=$postowner'><i class='fa fa-reply'></i></a></h3></td></tr></table>";
					echo "" . bbcode_format($postcontent) . "";
					echo "</div><br />\n";
				}
				
				if($poststart > "1" && $postcount > $poststart && $postcount < ($poststart + 15)) {
					echo "<div class='post'><table><tr><td><div class='avatar_small' style=\"background-image: url('index.php?do=avatarlocation&user=$postowner');\" title='User Avatar'></div></td><td><h3>$postowner<span style='font-size: 11px; padding-left: 6px; color: #808080;'>$postdate</span><br /><a href='index.php?view=$postid&user=$postowner'><i class='fa fa-reply'></i></a></h3></td></tr></table>";
					echo "" . bbcode_format($postcontent) . "";
					echo "</div><br />\n";
				}
			}
		}

		if($postowner == $username)
		{
			// Found a post, post count goes up!
			$postcount++;
			
			if($poststart == "1" && $postcount < ($poststart + 15)) {
				echo "<div class='post'><table><tr><td><div class='avatar_small' style=\"background-image: url('index.php?do=avatarlocation&user=$postowner');\" title='User Avatar'></div></td><td><h3>$postowner<span style='font-size: 11px; padding-left: 6px; color: #808080;'>$postdate</span><br /><a href='index.php?view=$postid&user=$postowner'><i class='fa fa-reply'></i></a> <a href='index.php?do=delpost&user=$username&pid=$postid'><i class='fa fa-trash-o'></i></a></h3></td></tr></table>";
				echo "" . bbcode_format($postcontent) . "";
				echo "</div><br />\n";
			}
			
			if($poststart > "1" && $postcount > $poststart && $postcount < ($poststart + 15)) {
				echo "<div class='post'><table><tr><td><div class='avatar_small' style=\"background-image: url('index.php?do=avatarlocation&user=$postowner');\" title='User Avatar'></div></td><td><h3>$postowner<span style='font-size: 11px; padding-left: 6px; color: #808080;'>$postdate</span><br /><a href='index.php?view=$postid&user=$postowner'><i class='fa fa-reply'></i></a> <a href='index.php?do=delpost&user=$username&pid=$postid'><i class='fa fa-trash-o'></i></a></h3></td></tr></table>";
				echo "" . bbcode_format($postcontent) . "";
				echo "</div><br />\n";
			}
		}
	}
	
	
	// Page button generation
	echo "<div class='page-controls'>";
	
	if($poststart > "1") {
		$prevpage = $poststart / 15;
		echo "<a href='index.php?page=$prevpage'><i class='fa fa-arrow-left'></i> &nbsp; Prev page</a>";
	}
	
	echo "&nbsp;&nbsp;&nbsp;";
	
	if($poststart == "1" && $postcount > ($poststart + 15)) {
		echo "<a href='index.php?page=2'>Next page &nbsp; <i class='fa fa-arrow-right'></i></a>";
	}
	
	if($poststart > "1" && $postcount > ($poststart + 15)) {
		$nextpage = ($poststart / 15) + 2;
		echo "<a href='index.php?page=$nextpage'>Next page &nbsp; <i class='fa fa-arrow-right'></i></a>";
	}
	
	echo "</div>";
}

?>

<br /><br />
<center style="background-color: #555555; padding 3px;">Powered By SSB <?php echo $version; ?></center>
</div>
</div> <!-- main contain -->
</body>
</html>
