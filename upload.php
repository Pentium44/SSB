<?php

session_start();

if(!isset($_SESSION['ssb-user']) or !isset($_SESSION['ssb-pass'])) { exit(1); }

$username = $_SESSION['ssb-user'];
$password = $_SESSION['ssb-pass'];

// config variables
include_once("config.php");
include("forms.php");

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
//	|| ($_FILES["file"]["type"][$i] == "video/mp4")
//	|| ($_FILES["file"]["type"][$i] == "video/ogg")
//	|| ($_FILES["file"]["type"][$i] == "video/webm")
//	|| ($_FILES["file"]["type"][$i] == "video/x-flv")
//	|| ($_FILES["file"]["type"][$i] == "video/mp4v-es")
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
				"ssb_db/uploads/" . $_FILES["file"]["name"][$i]);
				echo "Success: " . $_FILES["file"]["name"][$i] . " Uploaded! Size: " . tomb($_FILES["file"]["size"][$i]) . "<br>";
			}
		}
	}
	else
	{
		echo "Error: " . $_FILES["file"]["name"][$i] . " is too large, or is a invalid filetype";
	}
}

?>
