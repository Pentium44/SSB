<?php
//	SSB - Simple Social Board
//	(C) Chris Dorman, 2012 - 2020
//	License: CC-BY-NC-SA version 3.0
//	http://github.com/Pentium44/SSB

session_start();
include "config.php";
include "forms.php";

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

if(!file_exists(ssb_db/friends))
{
	mkdir("ssb_db/friends", 0777);
}

$username = $_SESSION['ssb-user'];

?>
<html lang="us-en">
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"><meta name="description" content="<?php echo $title; ?>">
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class='contain'>
<div id="navcontainer">
        <div id="navbar"><!--
        <?php if(!isset($username)) { ?>
        --><a href="?forms=login">Login</a><!--
        --><a href="?do=about">About</a><!--
        <?php } else { ?>
        --><a href="index.php">Feed</a><!--
        --><a href="?do=friends">Friends</a><!--
        --><a href="?do=about">About</a><!--
        --><a href="?do=logout">Logout</a><!--
        <?php } ?>
        --></div>
</div>
<div class='title'><?php echo $title; ?></div>
<br>

<?php

if(isset($_GET['view']) && isset($_GET['user']))
{
	$puser = $_GET['user'];
	$id = $_GET['view'];
	$postc = file_get_contents("ssb_db/posts/reply_" . $puser . "_" . $id . ".count");
	include "ssb_db/posts/post_" . $puser . "_" . $id . ".php";

	echo $postcontent;

	for($x = 0; $x <= $postc; $x++) {
		echo ${"reply" . $x};
	}

	replyForm($id, $puser);
}
else if(isset($_GET['forms']))
{
	$forms = $_GET['forms'];
	$id = $_GET['pid'];
	if($forms=="register") {
                registerForm();
        }
	else if($forms=="login") {
		loginForm();
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
else if(isset($_GET['do']))
{
	$do = $_GET['do'];
	if($do=="post")
	{
		if(isset($username) && $_POST['topic']!="" && $_POST['body']!="")
		{
			$date = date("YmdHis"); // timestamp in year, month, date, hour, minute, and second.
			$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
			//$username = stripcslashes(htmlentities($username));
			$topic = htmlentities(stripcslashes($_POST['topic']));
			$list_string = "<a href=\"?view=$rand_id\">$topic</a><span style='float:right;'>Posted by: $username</span><br>";
			$post_file = "ssb_db/posts/post_" . $username . "_" . $date . ".php";
			$post_string = "<?php\n\$postowner = \"" . $username . "\";\n\$postcontent = \"<h2><b><a href='?view=" . $date . "'>" . $topic . "</a></b></h2>\n<table border='1'><tr><td style='width:100px;padding:4px;vertical-align:top;'>" . $username . "</td><td style='width:492px;padding:4px;vertical-align:top;'>" . $body . "</td></tr></table>\";\n?>\n";
			file_put_contents($post_file, $post_string);
			file_put_contents("ssb_db/posts/" . $date . ".post", "post_" . $username . "_" . $date . ".php");
			file_put_contents("ssb_db/posts/reply_" . $username . "_" . $date . ".count", "0");
			echo "Redirecting in 3 seconds, if redirection fails, <a href=\"?view=$date&user=$username\">Click Here</a><br>";
			header( "refresh:3;url=?view=$date&user=$username" );
		}
		else
		{
			echo "ERROR: Missing form data<br>";
		}	
	}
	
	if($do=="reply")
	{
		if(!isset($_GET['pid']) or !file_exists("ssb_db/posts/" . $_GET['pid'] . ".post")) { echo "ERROR: Post ID is not set, or invalid"; } else {
		if(isset($_POST['reply']) && isset($username) && $_POST['body']!="")
		{
			$pid = $_GET['pid'];
			$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
			//$username = stripcslashes(htmlentities($username));
			$post_file_name = file_get_contents("ssb_db/posts/$pid.post");
			include "ssb_db/posts/" . $post_file_name;
			$old_content = file_get_contents("ssb_db/posts/" . $post_file_name);
			$reply_count = file_get_contents("ssb_db/posts/reply_" . $postowner  . "_" . $pid . ".count");

			$reply_count = $reply_count+1;

			$post_string = "<?php \n\$reply" . $reply_count . " = \"<table border='1'><tr><td style='width:100px;padding:4px;vertical-align:top;'>" . $username . "</td><td style='width:492px;padding:4px;vertical-align:top;'>" . $body . "</td></tr></table>\";\n?>\n";
			file_put_contents("ssb_db/posts/" . $post_file_name, $old_content . $post_string);
			file_put_contents("ssb_db/posts/reply_" . $postowner . "_" . $pid . ".count", $reply_count);
			echo "Redirecting in 3 seconds, if redirection fails, <a href=\"?view=$pid&user=$postowner\">Click Here</a><br>";
			header( "refresh:3;url=?view=$pid&user=$postowner" );
		}
		else
		{
			echo "ERROR: Missing form data<br>";
		}
		}
	}
	
	if($do=="clean")
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
	}

	if($do=="users")
	{
		echo "<h2>Registered Users";
		echo "<table>";
		foreach(glob("ssb_db/users/*.name") as $userfile) {
			$user = get_file_contents($userfile);
			include "ssb_db/users/$user.php";
			$userposts = file_get_contents("ssb_db/users/$user.postnumber");
			print <<<EOD
			<tr>
				<td style="width: 25%;">
					$user
				</td>
				<td style="width: 25%;">
					Posts: $userposts
				</td>
			</tr>
EOD;
		}
		echo "</table>\n";
	}

	if($do=="msg")
	{
		echo "<b>This page is still in development...</b>";
	}

	if($do=="about")
        {
                echo "<b>This page is still in development...</b>";
        }

	if($do=="friends")
        {
                echo "<b>This page is still in development...</b>";
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
			} else {
				header("Location: index.php?notify=2");
			}
			$name = isset($_POST['username']) ? $_POST['username'] : "Unnamed";
			$_SESSION['ssb-user'] = $name;
		} else {
			header("Location: index.php?notify=1");
		}

		header("Location: index.php");
	}

	if($do=="logout")
	{
	        $_SESSION['ssb-user'] = null;
	        $_SESSION['ssb-pass'] = null;
		header("Location: index.php?forms=login");
	}

	if($do=="register")
	{
		if($_POST['username']!="" && $_POST['password']!="" && $_POST['password-again']!="" && $_POST['fullname']!="") {
			if($_POST['password']==$_POST['password-again']) {
				if(!preg_match('/[^a-z0-9]/i', $_POST['username'])) {
					if(!file_exists("ssb_db/users/" . $_POST['username'] . ".php")) {
						$colors = array("0000ff", "9900cc", "0080ff", "008000", "ededed");
						file_put_contents("ssb_db/users/" . $_POST['username'] . ".php", "<?php\n \$user_password = \"" . sha1(md5($_POST['password'])) . "\";\n \$user_color = \"" . $colors[array_rand($colors)] . "\"; \$user_fullname = \"" . $_POST['fullname'] . "\"; \n?>");
						file_put_contents("ssb_db/users/" . $_POST['username'] . ".name", $_POST['username']);
						file_put_contents("ssb_db/users/" . $_POST['username'] . ".postnumber", "0");
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
        if($_GET['notify']=="1") { echo "Error: User not found"; }
        else if($_GET['notify']=="2") { echo "Error: Incorrect password provided"; }
        else if($_GET['notify']=="3") { echo "Error: Please fill out all the text boxes"; }
      	else if($_GET['notify']=="4") { echo "Error: The provided passwords did not match"; }
   	else if($_GET['notify']=="5") { echo "Error: Special characters cannot be used in your username"; }
      	else if($_GET['notify']=="6") { echo "Error: This username is already in use"; }
} 
else if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass']))
{
     	loginForm();
} 
else
{
	$post_list = file_get_contents("ssb_db/ssb_posts.txt");
	echo "<br><a href='?forms=post'>New Post</a><br />";
	echo $post_list;
}

?>

<br>
<center>Powered By SSB <?php echo $version; ?></center>
</div>
</body>
</html>
