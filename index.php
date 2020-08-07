<?php
//	SSB - Simple Social Board
//	(C) Chris Dorman, 2012 - 2020
//	License: CC-BY-NC-SA version 3.0
//	http://github.com/Pentium44/SSB

session_start();
include "config.php";
include "forms.php";
include "bbcode.php";

// check if flatfile database location is populated
if(!file_exists(ssb_db))
{
	mkdir("ssb_db", 0777);
}

if(!file_exists(ssb_db/users))
{
        mkdir("ssb_db/users", 0777);
}

if(!file_exists(ssb_db/posts))
{
	mkdir("ssb_db/posts", 0777);
}

if(!file_exists(ssb_db/uploads))
{
	mkdir("ssb_db/uploads", 0777);
}

if(!file_exists(ssb_db/friends))
{
	mkdir("ssb_db/friends", 0777);
}

if(isset($_SESSION['ssb-user'])) {
	$username = $_SESSION['ssb-user'];
}

$_SESSION['ssb-topic'] = $ssbtopic;

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
        --><a style="width:50px;" href="?do=userctrl"><i style="padding:2px 2px 2px 2px;" class="fa fa-cog"></i></a><!--
        --><a style="width:50px;" href="?do=logout"><i style="padding:2px 2px 2px 2px;" class="fa fa-sign-out"></i></a><!--
        <?php } else {?>
        --><a href="?forms=login">Login</a><!--
        --><a href="?do=about">About</a><!--
        <?php } ?>
  
        --></div>
</div>
<div class='contain'>
<div class='title'><?php echo $ssbtitle; ?></div>
<br>

<?php

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
				// Get user avatar if set
				if(isset($user_avatar)) { echo "<img class='avatar' src='?do=avatarlocation&user=" . $userid . "' title='User Avatar'><br />"; }
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
            		if(isset($user_avatar)) { echo "<img class='avatar' src='?do=avatarlocation&user=" . $userid . "' title='User Avatar'><br />"; }
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
                                	echo bbcode_format($postcontent);
					$imgExts = array("gif", "jpeg", "jpg", "png", "bmp", "ico", "png");
                                	foreach(array_reverse(glob("ssb_db/uploads/" . $postowner . "_" . $postid . ".*")) as $postfile)
                                	{
                                	        if(in_array(end(explode(".", $postfile)), $imgExts))
                                	        {
                                	                echo "<div class='attachment'>";
                                	                echo "<a href='ssb_db/uploads/" . $postowner . "_" . $postid . "." . end(explode(".", $postfile)) . "' title='Attachment: left click to enlarge, right click to download...'>";
                                	                echo "<img src='ssb_db/uploads/" . $postowner . "_" . $postid . "." . end(explode(".", $postfile)) . "'>";
                                	                echo "</a></div>";
                               	        	}
					}
					echo "<br />";
				}
			}

			if($postowner == $username) {
                   		echo bbcode_format($postcontent);
                  		$imgExts = array("gif", "jpeg", "jpg", "png", "bmp", "ico", "png");
                   		foreach(array_reverse(glob("ssb_db/uploads/" . $postowner . "_" . $postid . ".*")) as $postfile)
                       		{
                             		if(in_array(end(explode(".", $postfile)), $imgExts))
                       			{
                                 		echo "<div class='attachment'>";
                              			echo "<a href='ssb_db/uploads/" . $postowner . "_" . $postid . "." . end(explode(".", $postfile)) . "' title='Attachment: left click to enlarge, right click to download...'>";
                        			echo "<img src='ssb_db/uploads/" . $postowner . "_" . $postid . "." . end(explode(".", $postfile)) . "'>";
                        			echo "</a></div>";
                                	}
                 		}
                             	echo "<br />";
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
              	echo "Username: " . $userid . "@" . $domain . "<br />";
             	echo "Full name: " . $user_fullname;
              	echo "</td></tr></table>";

		foreach(array_reverse(glob("ssb_db/posts/post_" . $userid . "_" . "*.php")) as $postfile) {
                        //echo $postfile;
                        include $postfile;
    			echo bbcode_format($postcontent);
   			$imgExts = array("gif", "jpeg", "jpg", "png", "bmp", "ico", "png");
                 	foreach(array_reverse(glob("ssb_db/uploads/" . $postowner . "_" . $postid . ".*")) as $postfile)
         		{
                        	if(in_array(end(explode(".", $postfile)), $imgExts))
                              	{
                               		echo "<div class='attachment'>";
                                   	echo "<a href='ssb_db/uploads/" . $postowner . "_" . $postid . "." . end(explode(".", $postfile)) . "' title='Attachment: left click to enlarge, right click to download...'>";
                                       	echo "<img src='ssb_db/uploads/" . $postowner . "_" . $postid . "." . end(explode(".", $postfile)) . "'>";
                                       	echo "</a></div>";
                                }
                        }
                        echo "<br />";
      		}
     	}
}
else if(isset($_GET['view']) && isset($_GET['user']))
{
	$puser = $_GET['user'];
	$id = $_GET['view'];
	$postc = file_get_contents("ssb_db/posts/reply_" . $puser . "_" . $id . ".count");
	include "ssb_db/posts/post_" . $puser . "_" . $id . ".php";

	echo "<div class='post'>";

	// Let the text process if no images xD
        echo bbcode_format($postcontent) . "</div>";


	$imgExts = array("gif", "jpeg", "jpg", "png", "bmp", "ico", "png");
	foreach(array_reverse(glob("ssb_db/uploads/" . $puser . "_" . $id . ".*")) as $postfile) 
	{
		if(in_array(end(explode(".", $postfile)), $imgExts))
		{
			echo "<div class='attachment'>";
			echo "Attachment: left click to enlarge, right click to download...<br />";
			echo "<a href='ssb_db/uploads/" . $puser . "_" . $id . "." . end(explode(".", $postfile)) . "'>";
			echo "<img src='ssb_db/uploads/" . $puser . "_" . $id . "." . end(explode(".", $postfile)) . "'>";
			echo "</a></div>";
		}
	}

	for($x = 0; $x <= $postc; $x++) {
		echo bbcode_format(${"reply" . $x});
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
								//echo "Success: " . $_FILES["file"]["name"][$i] . " Uploaded! Size: " . tomb($_FILES["file"]["size"][$i]) . "<br>";
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
				}
			}

			if(isset($username) && $_POST['body']!="")
			{
				$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
				//$username = stripcslashes(htmlentities($username));
				include "ssb_db/users/" . $username . ".php";
				$post_file = "ssb_db/posts/post_" . $username . "_" . $date . ".php";
				if(isset($user_avatar)) {
					$post_string = "<?php\n\$postowner = \"" . $username . "\";\n\$postid=\"" . $date . "\";\n\$postcontent = \"<div class='post'><table><tr><td><img class='avatar_small' src='?do=avatarlocation&user=" . $username . "' title='User Avatar'></td><td><h3>" . $username . " <a href='?view=" . $date . "&user=" . $username . "'> <i class='fa fa-reply'></i></a> <span style='font-size: 8px; color: #888888;'>" . $titledate . "</span></h3><p>" . $body . "</p></td></tr></table></div>\";\n?>\n";
				} else {
					$post_string = "<?php\n\$postowner = \"" . $username . "\";\n\$postid=\"" . $date . "\";\n\$postcontent = \"<div class='post'><h3>" . $username . " <a href='?view=" . $date . "&user=" . $username . "'> <i class='fa fa-reply'></i></a> <span style='font-size: 8px; color: #888888;'>" . $titledate . "</span></h3><p>" . $body . "</p></div>\";\n?>\n";
				}
				file_put_contents($post_file, $post_string);
				file_put_contents("ssb_db/posts/" . $date . ".post", "post_" . $username . "_" . $date . ".php");
				file_put_contents("ssb_db/posts/reply_" . $username . "_" . $date . ".count", "0");
				echo "Post processed... Redirecting in 3 seconds, if redirection fails, <a href=\"?view=$date&user=$username\">Click Here</a><br>";
				header( "refresh: 3; url=?view=$date&user=$username" );
			}
			else
			{
				echo "ERROR: Missing form data<br>";
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

	if($do=="reply")
	{
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			if(!isset($_GET['pid']) or !file_exists("ssb_db/posts/" . $_GET['pid'] . ".post")) { echo "ERROR: Post ID is not set, or invalid"; } else {
				if(isset($_POST['reply']) && isset($username) && $_POST['body']!="")
				{
					$pid = $_GET['pid'];
					$replydate = date("m-d-Y h:i:sa"); // time stamp for people to read xD
					$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
					//$username = stripcslashes(htmlentities($username));
					$post_file_name = file_get_contents("ssb_db/posts/$pid.post");
					include "ssb_db/posts/" . $post_file_name;
					$old_content = file_get_contents("ssb_db/posts/" . $post_file_name);
					$reply_count = file_get_contents("ssb_db/posts/reply_" . $postowner  . "_" . $pid . ".count");

					$reply_count = $reply_count+1;

					$post_string = "<?php \n\$reply" . $reply_count . " = \"<div class='reply'><b>" . $username . "</b>&nbsp;<span style='font-size: 8px; color: #888888;'>" . $replydate . "</span><br />" . $body . "</div>\";\n?>\n";
					file_put_contents("ssb_db/posts/" . $post_file_name, $old_content . $post_string);
					file_put_contents("ssb_db/posts/reply_" . $postowner . "_" . $pid . ".count", $reply_count);
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
	
	if($do=="clrnote") 
	{
		 if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			include "ssb_db/users/" . $username . ".php";
			if($user_password === $_SESSION['ssb-pass']) {
				unlink("ssb_db/friends/" . $username . ".notifications");
				header("Location: index.php?do=friends");
			}
		}
	}
	
	if($do=="clrpending") 
	{
		 if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			include "ssb_db/users/" . $username . ".php";
			if($user_password === $_SESSION['ssb-pass']) {
				unlink("ssb_db/friends/" . $username . ".pending");
				header("Location: index.php?do=friends");
			}
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
			if(isset($_POST['user'])) {
				//check if user exists first lol
				$givenUser = htmlentities(stripcslashes($_POST['user']));
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
                                acceptFriendRequest($_GET['user'], $_GET['friend']);
				echo "Follow request sent to " . $_GET['user'] . " <a href='?do=friends'>redirecting</a> in 3 seconds";
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
			} 
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
			header("Location: ssb_db/uploads/" . $user_avatar);
		}
	}

	if($do=="about")
        {
                echo "<h2>About</h2>";
		echo $desc;
        }

	if($do=="friends")
        {
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			
			$friendpend = "ssb_db/friends/" . $username . ".pending";
			$handle = fopen($friendpend, "r");

			echo "<h3>Friend requests</h3> <a class='button' href='?do=clrpending'>Clear history</a> <a class='button' href='?forms=friendreq'>Send friend request</a>";
			echo "<div style='background:#545454;border: solid 1px #898989;'>";

			if ($handle) {
    				while (($line = fgets($handle)) !== false) {
        				echo "Pending friend request from " . $line . "! <a href='?do=accfr&friend=" . $line . "&user=" . $username . "'>Accept</a><br />";
    				}
				fclose($handle);
			} else {
			    echo "No pending friend requests<br />";
			} 

			echo "</div>";

			// PM notifications
			$notifications = "ssb_db/friends/" . $username . ".notifications";
			$handle = fopen($notifications, "r");

			echo "<h3>Notifications</h3><a class='button' href='?do=clrnote'>Clear history</a>";
			echo "<div style='background:#545454;border: solid 1px #898989;'>";

			if ($handle) {
    				while (($line = fgets($handle)) !== false) {
        				echo $line;
    				}
				fclose($handle);
			} else {
			    echo "No messages<br />";
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
				for($x = 1; $x <= $friendcount; $x++)
				{
					if(isset(${"friend" . $x})) {
						echo ${"friend" . $x} . " &bull; <a href='?userfeed=" . ${"friend" . $x} . "'>View user profile</a> &bull; <a href='?do=privmsg&friend=" . ${"friend" . $x} . "'>Private message</a><br />";
					}
				}
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
						file_put_contents("ssb_db/users/" . $_POST['username'] . ".php", "<?php\n\$accttype = \"" . $acct . "\";\n\$user_password = \"" . sha1(md5($_POST['password'])) . "\";\n \$user_color = \"" . $colors[array_rand($colors)] . "\"; \$user_fullname = \"" . $_POST['fullname'] . "\"; \$user_avatar = \"../../data/defaultprofile.png\"; \n?>");
						file_put_contents("ssb_db/users/" . $_POST['username'] . ".name", $_POST['username']);
						file_put_contents("ssb_db/users/" . $_POST['username'] . ".postnumber", "0");
						file_put_contents("ssb_db/friends/" . $_POST['username'] . ".count", "0");
						file_put_contents("ssb_db/friends/" . $_POST['username'] . ".php", "<?php ?>\n");
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
else if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass']))
{
        loginForm();
} 
else
{
	// Lets actually generate some feed now.
	foreach(array_reverse(glob("ssb_db/posts/*.post")) as $postfile) {
		$postphp = file_get_contents($postfile);
		include "ssb_db/posts/$postphp";
		$friendcount = file_get_contents("ssb_db/friends/" . $username . ".count");
               	include "ssb_db/friends/" . $username . ".php";
             	for($x = 1; $x <= $friendcount; $x++)
           	{
        		if($postowner == ${"friend" . $x}) {
				echo bbcode_format($postcontent);
				$imgExts = array("gif", "jpeg", "jpg", "png", "bmp", "ico", "png");
        			foreach(array_reverse(glob("ssb_db/uploads/" . $postowner . "_" . $postid . ".*")) as $postfile)
        			{
        			        if(in_array(end(explode(".", $postfile)), $imgExts))
                			{
						echo "<div class='attachment'>";
  			               		echo "Attachment: left click to enlarge, right click to download...<br />";
                        			echo "<a href='ssb_db/uploads/" . $postowner . "_" . $postid . "." . end(explode(".", $postfile)) . "'>";
                        			echo "<img src='ssb_db/uploads/" . $postowner . "_" . $postid . "." . end(explode(".", $postfile)) . "'>";
                        			echo "</a></div>";
                			}
        			}
				echo "<br />\n";
			}
              	}

		if($postowner == $username)
		{
			echo bbcode_format($postcontent);
			$imgExts = array("gif", "jpeg", "jpg", "png", "bmp", "ico", "png");
                        foreach(array_reverse(glob("ssb_db/uploads/" . $postowner . "_" . $postid . ".*")) as $postfile)
                        {
                   		if(in_array(end(explode(".", $postfile)), $imgExts))
                    		{
                                	echo "<div class='attachment'>";
                                        echo "Attachment: left click to enlarge, right click to download...<br />";
                                        echo "<a href='ssb_db/uploads/" . $postowner . "_" . $postid . "." . end(explode(".", $postfile)) . "'>";
                                       	echo "<img src='ssb_db/uploads/" . $postowner . "_" . $postid . "." . end(explode(".", $postfile)) . "'>";
                                       	echo "</a></div>";
                               	}
                     	}
			echo "<br />\n";
		}
	}
}

?>

<br /><br />
<center style="background-color: #555555; padding 3px;">Powered By SSB <?php echo $version; ?></center>
</div>
</div> <!-- main contain -->
</body>
</html>
