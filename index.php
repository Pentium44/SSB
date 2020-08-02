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

if(!file_exists(ssb_db/friends))
{
	mkdir("ssb_db/friends", 0777);
}

$username = $_SESSION['ssb-user'];

?>
<!DOCTYPE html>
<html lang="en-us">
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"><meta name="description" content="<?php echo $title; ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body <?php if($_GET['do']=="pubmsg" || $_GET['do']=="msg") { echo "onload=\"UpdateTimer();\""; } ?>>

<div id="navcontainer">
        <div id="navbar"><!--
        <?php if(!isset($username)) { ?>
        --><a href="?forms=login">Login</a><!--
        --><a href="?do=about">About</a><!--
        <?php } else { ?>
        --><a style="width:50px;" href="?forms=post" title="Create a Post!"><i style="padding:2px 2px 3px 2px;" class="fa fa-plus-square"></i></a><!--
        --><a style="width:50px;" href="?do=pubmsg" title="Create a Post!"><i style="padding:2px 2px 3px 2px;" class="fa fa-comments-o"></i></a><!--
        --><a href="index.php">Feed</a><!--
        --><a href="?do=friends">Friends</a><!--
        --><a href="?do=about">About</a><!--
        --><a href="?do=logout">Logout</a><!--
        <?php } ?>
        --></div>
</div>
<div class='contain'>
<div class='title'><?php echo $title; ?></div>
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
else if(isset($_GET['userfeed'])) 
{
	
}
else if(isset($_GET['view']) && isset($_GET['user']))
{
	$puser = $_GET['user'];
	$id = $_GET['view'];
	$postc = file_get_contents("ssb_db/posts/reply_" . $puser . "_" . $id . ".count");
	include "ssb_db/posts/post_" . $puser . "_" . $id . ".php";

	echo "<div class='post'>" . bbcode_format($postcontent) . "</div>";

	for($x = 0; $x <= $postc; $x++) {
		echo bbcode_format(${"reply" . $x});
	}

	if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { 
		echo "Login to reply...";
	} else {
		replyForm($id, $puser);
	}
}
else if(isset($_GET['do']))
{
	$do = $_GET['do'];
	if($do=="post")
	{
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			if(isset($username) && $_POST['body']!="")
			{
				$date = date("YmdHis"); // timestamp in year, month, date, hour, minute, and second.
				$titledate = date("m-d-Y h:i:sa"); // time stamp for people to read xD
				$body = nl2br(htmlentities(stripcslashes($_POST['body'])));
				//$username = stripcslashes(htmlentities($username));
				$post_file = "ssb_db/posts/post_" . $username . "_" . $date . ".php";
				$post_string = "<?php\n\$postowner = \"" . $username . "\";\n\$postcontent = \"<div class='post'><h3>" . $username . " <a href='?view=" . $date . "&user=" . $username . "'> <i class='fa fa-reply'></i></a> <span style='font-size: 8px; color: #888888;'>" . $titledate . "</span></h3><p>" . $body . "</p></div>\";\n?>\n";
				file_put_contents($post_file, $post_string);
				file_put_contents("ssb_db/posts/" . $date . ".post", "post_" . $username . "_" . $date . ".php");
				file_put_contents("ssb_db/posts/reply_" . $username . "_" . $date . ".count", "0");
				echo "Redirecting in 3 seconds, if redirection fails, <a href=\"?view=$date&user=$username\">Click Here</a><br>";
				header( "Location: index.php?view=$date&user=$username" );
			}
			else
			{
				echo "ERROR: Missing form data<br>";
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
					echo "Redirecting in 3 seconds, if redirection fails, <a href=\"?view=$pid&user=$postowner\">Click Here</a><br>";
					header( "Location: index.php?view=$pid&user=$postowner" );
				}
				else
				{
					echo "ERROR: Missing form data<br>";
				}
			}
		}
	}
	
	if($do=="clrpending") 
	{
		 if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			include "ssb_db/users/" . $username . ".php";
			if($user_password === $_SESSION['ssb-pass']) {
				file_put_contents("ssb_db/friends/" . $username . ".pending", "");
				header("Location: index.php?do=friends");
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


	// grab session values and send friend request functions.
	if($do=="sendfr") {
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
			if(isset($_POST['user'])) {
				//check if user exists first lol
				$givenUser = htmlentities(stripcslashes($_POST['user']));
				if(file_exists("ssb_db/users/" . $givenUser . ".php")) {
					sendFriendRequest($_SESSION['ssb-user'], $givenUser);
					header("Location: ?do=friends");
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
				header("Location: ?do=friends");
                        } else {
                                echo "Error: users not set in GET &amp; SESSION value...";
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

		function wrapBBCode(tag) {
			var msgInput = document.getElementById('msg');
			var content = msgInput.value;
			var selectedContent = content.substring(msgInput.selectionStart, msgInput.selectionEnd);
			var beforeContent = content.substring(0, msgInput.selectionStart);
			var afterContent = content.substring(msgInput.selectionEnd, content.length);
			msgInput.value = beforeContent + '[' + tag + ']' + selectedContent + '[/' + tag + ']' + afterContent;
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
			<table>
				<tr>
					<td>
						<textarea style="width: 450px;" name="msg" id="msg"></textarea>
					</td>
					<td>
						<button onclick="javascript:wrapBBCode('i');"><img alt="Italic" src="img/italic.png"></button>
						<button onclick="javascript:wrapBBCode('u');"><img alt="Underline" src="img/underline.png"></button> 
						<button onclick="javascript:wrapBBCode('b');"><img alt="Bold" src="img/bold.png"></button> 
						<button onclick="javascript:wrapBBCode('url');"><img alt="URL" src="img/link.png"></button><br>
						<button style="width: 172px;" onclick="getInput();">Send</button>
					</td>
				</tr>
			</table>
		</div>
</div>

	<?php



		}
	}

	if($do=="about")
        {
                echo "<b>This page is still in development...</b>";
        }

	if($do=="friends")
        {
		if (!isset($_SESSION['ssb-user']) || !isset($_SESSION['ssb-pass'])) { loginForm(); } else {
        	        echo "<b>This page is still in development...</b><br />";
			$friendpend = "ssb_db/friends/" . $username . ".pending";
			$handle = fopen($friendpend, "r");

			echo "<h4>Notifications &bull; <a href='?do=clrpending'>Clear history</a> &bull; <a href='?forms=friendreq'>Send friend request</a></h4>";
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
					echo "&bull; " . ${"friend" . $x} . " &bull; <a href='?userfeed=" . ${"friend" . $x} . "'>View user's feed!</a><br />";
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
			}
              	}

		if($postowner == $username)
		{
			echo bbcode_format($postcontent);
		}
	}
}

?>

<br>
<center>Powered By SSB <?php echo $version; ?></center>
</div>
</body>
</html>
