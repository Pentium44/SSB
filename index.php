<?php
//	SSB - Simple Script Board
//	(C) Chris Dorman, 2012-2013
//	License: CC-BY-NC version 3.0
//	http://github.com/crazycoder13/SSB

$title = ""; // Board Title
$pw = ""; // Password needed to make topics
$css = "@import url(http://fonts.googleapis.com/css?family=Open+Sans);\n body { color:#e3e3e3;background:#020202;font-size:13px;font-family:\"Open Sans\"; }\n .title { font-size:36px;text-align:center;padding:8px; }\n a {color:#A901DB;text-decoration:none;}\n a:hover {color:#e5e5e5;text-decoration:none;}\n .contain {max-width:600px;margin:auto;}\n ";
$header = "<html>\n<head>\n<title>$title</title>\n<style type='text/css'>\n$css\n</style>\n</head>\n<body>\n<div class='contain'>\n<div class='title'>$title</div>\n<br>\n";
$footer = "<br><center>Powered By SSB</center>\n</div>\n</body>\n</html>";

if(!file_exists(ssb_db))
{
	mkdir("ssb_db", 0777);
}

echo $header;
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
		print <<<EOD
		<form action="?do=reply&pid=$id" method="post">
		<table><tr><td>
		Username:</td><td> <input type="text" name="username" id="username"></td></tr><tr><td>
		Body:</td><td> <textarea rows="5" cols="30" name="body"></textarea></td></tr><tr><td>
		<input type="submit" name="reply" value="Reply"></td></tr>
		</table>
		</form>
EOD;
	}
	else if($forms=="post")
	{
		print <<<EOD
		<form action="?do=post" method="post">
		<table><tr><td>
		Topic:</td><td> <input type="text" name="topic" id="topic"></td></tr><tr><td>
		Username:</td><td> <input type="text" name="username" id="username"></td></tr><tr><td>
		Password:</td><td> <input type="password" name="password" id="password"></td></tr><tr><td>
		Body:</td><td> <textarea rows="5" cols="30" name="body"></textarea></td></tr><tr><td>
		<input type="submit" name="post" value="Post"></td></tr>
		</table>
		</form>
EOD;
	}
	else if($forms=="clean")
	{
		print <<<EOD
		<form action="?do=clean" method="post">
		<table><tr><td>
		Password:</td><td> <input type="password" name="password" id="password"></td></tr><tr><td>
		<input type="submit" name="post" value="Post"></td></tr>
		</table>
		</form>
EOD;
	}
	else { echo "ERROR: Unknown form-name<br>"; }
}

else if(isset($_GET['do']))
{
	$do = $_GET['do'];
	if($do=="post")
	{
		if(isset($_POST['post']) && $_POST['username']!="" && $_POST['topic']!="" && $_POST['body']!="" && $_POST['password']!="")
		{
			if($_POST['password']!=$pw) { echo "ERROR: Wrong Password"; } else {
			$rand_id = substr(md5(microtime()),rand(0,26),4);
			$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
			$username = stripcslashes(htmlentities($_POST['username']));
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
		}
		else
		{
			echo "ERROR: Missing form data<br>";
		}	
	}
	
	if($do=="reply")
	{
		if(!isset($_GET['pid']) or !file_exists("ssb_db/" . $_GET['pid'] . ".txt")) { echo "ERROR: Post ID is not set, or invalid"; } else {
		if(isset($_POST['reply']) && $_POST['username']!="" && $_POST['body']!="")
		{
			$pid = $_GET['pid'];
			$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
			$username = stripcslashes(htmlentities($_POST['username']));
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
}
else
{
	$post_list = file_get_contents("ssb_db/ssb_posts.txt");
	echo "<br><a href='?forms=post'>New Post</a><span style='float:right;'><a href='?forms=clean'>Clean Database</a></span><hr><br>";
	echo $post_list;
	echo "<br><hr><a href='?forms=post'>New Post</a><span style='float:right;'><a href='?forms=clean'>Clean Database</a></span>";
}
echo $footer;
?>