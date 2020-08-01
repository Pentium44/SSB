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

$username = $_SESSION['ssb-user'];

?>
<html>
<head>
<title><?php echo $title; ?></title>
<style type='text/css'>
@import url(http://fonts.googleapis.com/css?family=Open+Sans);
body {
	color: #e3e3e3;
	margin: 0 auto;
	background: #020202;
	font-size: 13px;
	font-family: "Open Sans";
}
.title {
	font-size: 36px;
	text-align: center;
	padding: 8px;
}

#navbar {
	margin: 0 auto;
	/*width: 100%;
	/*background-color: #ffffff;*/
	top: 1px;
	left: 1px;
	padding-bottom: 0px;
}

#navcontainer {
	width: 700px;
	margin: 0 auto;
	background-color: #ffffff;
}

#navbar a {
	text-decoration: none;
	font-family: "Time Burner", sans-serif;
	font-size: 24px;
	text-align: center;
	padding-top: 6px;
	padding-bottom: 5px;
	background-color: #ffffff;
	color: #555555;
	width: 120px;
	display: inline-block;
}

#navbar a:hover {
	background-color: #999999;
	color: #323232;
}

table { padding: 1px; border: solid 1px #888888; }
tr, td { padding: 2px; }

a {
	color: #A901DB;
	text-decoration: none;
}
a:hover {
	color: #e5e5e5;
	text-decoration: none;
}
.contain {
	max-width: 700px;
	margin: 0 auto;
}
</style>
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

if(isset($_GET['view']))
{
	$id = $_GET['view'];
	$post = file_get_contents("ssb_db/" . $_GET['view'] . ".txt");
	
	echo $post;
	echo "<br><a href='?forms=reply&pid=$id'>Reply</a>";
}
else if(isset($_GET['forms']))
{
	$forms = $_GET['forms'];
	$id = $_GET['pid'];
	if($forms=="reply")
	{
		replyForm($id);
	}
	else if($forms=="register") {
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
			$rand_id = substr(md5(microtime()),rand(0,26),4);
			$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
			//$username = stripcslashes(htmlentities($username));
			$topic = htmlentities(stripcslashes($_POST['topic']));
			$list_string = "<a href=\"?view=$rand_id\">$topic</a><span style='float:right;'>Posted by: $username</span><br>";
			$post_list = "ssb_db/ssb_posts.txt";
			if(file_exists($post_list))
			{
				$oldcontent = file_get_contents($post_list);
				file_put_contents($post_list, $list_string . $oldcontent);
			}
			else
			{
				file_put_contents($post_list, $list_string);
			}
			$post_string = "<h2><b>$topic</b></h2>\n<table border='1'><tr><td style='width:100px;padding:4px;vertical-align:top;'>$username</td><td style='width:492px;padding:4px;vertical-align:top;'>$body</td></tr></table>";
			file_put_contents("ssb_db/$rand_id.txt", $post_string);
			echo "Redirecting in 3 seconds, if redirection fails, <a href=\"?view=$rand_id\">Click Here</a><br>";
			header( "refresh:3;url=?view=$rand_id" );
		}
		else
		{
			echo "ERROR: Missing form data<br>";
		}	
	}
	
	if($do=="reply")
	{
		if(!isset($_GET['pid']) or !file_exists("ssb_db/" . $_GET['pid'] . ".txt")) { echo "ERROR: Post ID is not set, or invalid"; } else {
		if(isset($_POST['reply']) && isset($username) && $_POST['body']!="")
		{
			$pid = $_GET['pid'];
			$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
			//$username = stripcslashes(htmlentities($username));
			$old_content = file_get_contents("ssb_db/$pid.txt");
			$post_string = "<table border='1'><tr><td style='width:100px;padding:4px;vertical-align:top;'>$username</td><td style='width:492px;padding:4px;vertical-align:top;'>$body</td></tr></table>";
			file_put_contents("ssb_db/$pid.txt", $old_content . $post_string);
			echo "Redirecting in 3 seconds, if redirection fails, <a href=\"?view=$pid\">Click Here</a><br>";
			header( "refresh:3;url=?view=$pid" );
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
		if($_POST['username']!="" && $_POST['password']!="" && $_POST['password-again']!="") {
			if($_POST['password']==$_POST['password-again']) {
				if(!preg_match('/[^a-z0-9]/i', $_POST['username'])) {
					if(!file_exists("ssb_db/users/" . $_POST['username'] . ".php")) {
						$colors = array("0000ff", "9900cc", "0080ff", "008000", "ededed");
						file_put_contents("ssb_db/users/" . $_POST['username'] . ".php", "<?php\n \$user_password = \"" . sha1(md5($_POST['password'])) . "\";\n \$user_color = \"" . $colors[array_rand($colors)] . "\"; \n?>");
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
	echo "<br><a href='?forms=post'>New Post</a><span style='float:right;'><a href='?forms=clean'>Clean Database</a></span><hr><br>";
	echo $post_list;
	echo "<br><hr><a href='?forms=post'>New Post</a><span style='float:right;'><a href='?forms=clean'>Clean Database</a></span>";
}

?>

<br>
<center>Powered By SSB</center>
</div>
</body>
</html>
