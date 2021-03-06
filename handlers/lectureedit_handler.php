<?php
require_once(dirname(__DIR__).'/includes/lecture.php');
require_once(dirname(__DIR__).'/includes/homework.php');
require_once(dirname(__DIR__).'/includes/location.php');
$addtask = "";
$addsolution = "";
$addresult = "";
$deleteresult = "";
$changeresult = "";
session_start();
if (isset($_SESSION['LAST_ACTIVITY'])==0 || (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minutes ago
    $_SESSION = array();    // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    header('Location: ../login.php');
}
else
{
		$_SESSION['LAST_ACTIVITY'] = time();
}
if ($_SESSION['loggedin'] == true && $_SESSION['role'] >= 2)
{
	if ($_SERVER['REQUEST_METHOD'] == 'GET')
	{
		if (isset($_GET["getid"]))
		{
			$lecture = new lecture();
			$lectureinfo = $lecture->getLectureInfo($_GET["getid"]);
			if (empty($lectureinfo))
				{
					header('Location: ../index.php');
				}
			$homework = new homework();
			$allhomeworks = $homework->listAllHomeworks($_GET["getid"]);
		}
		else if (isset($_GET["add"]))
		{
			$lecture = new lecture();
			$lectureinfo = $lecture->getLectureInfo($_GET["add"]);
			$homework = new homework();
			$allhomeworks = $homework->listAllHomeworks($_GET["add"]);
			$location = new location();
			$alllocations = $location->listAllLocations();

		}
		else if (isset($_GET["deleteid"]))
		{
			$homework = new homework();
			$lecture_id = $homework->deleteHomework($_GET["deleteid"]);
			if ($lecture_id > 0)
			{
				$deleteresult = "Task successfully deleted";
			}
			else 
			{
				$deleteresult = "Error";
			}

		}
		else if (isset($_GET["homework_id"]))
		{
			$homework = new homework();
			$homework_info = $homework->getHomeworkInfo($_GET["homework_id"]);
			$homework_location = $homework->getHomeworkLocations($_GET["homework_id"]);
			$lecture = new lecture();
			$lectureinfo = $lecture->getLectureInfo($homework_info["lecture_id"]);
			$allhomeworks = $homework->listAllHomeworks($homework_info["lecture_id"]);
			$location = new location();
			$alllocations = $location->listAllLocations();
		}
		else
		{
			header('Location: ../login.php');
		}
	}
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{

		if (isset($_POST['Add']))
		{
			$location = new location();
			$alllocations = $location->listAllLocations();
			$target_dir_task = "uploads/task/";
			$target_dir_solution = "uploads/solution/";
			$target_file_task = "";
			$target_file_solution = "";
			$solutionUploadOk = 1;
			$taskUploadOk = 1;
			if ($_FILES["task"]["error"] == 0)
			{
				$target_file_task = $target_dir_task.basename(pathinfo($_FILES['task']['name'], PATHINFO_FILENAME))."+".mt_rand().".".(pathinfo($_FILES['task']['name'], PATHINFO_EXTENSION));
				$fileType = mime_content_type($_FILES["task"]["tmp_name"]);
				if (file_exists("../".$target_file_task)) 
				{
    				$addtask = "Sorry, task file already exists.";
    				//$taskUploadOk = 0;
    				//$target_file_task = "";
				}
				else if ($_FILES["task"]["size"] > 10000000) 
				{
				    $addtask = "Sorry, your task file is too large.";
    				$taskUploadOk = 0;
    				$target_file_task = "";
    			}
    			else if ($fileType != "image/jpeg" && $fileType != "image/bmp" && $fileType != "image/jpg" 
    				&& $fileType != "image/gif" && $fileType != "image/gif" && $fileType != "application/pdf" 
    				&& $fileType != "application/zip" && $fileType != "application/x-rar")
    			{
    				$addtask = "Only pdf, zip, rar and images are allowed for task";
    				$taskUploadOk = 0;
    				$target_file_task = "";
    			}
    			if ($taskUploadOk == 1)
    			{
    				if (!move_uploaded_file($_FILES["task"]["tmp_name"], "../".$target_file_task))
    				{
    					$addtask = "An error occured with file upload. Please inform administrator.";
    					$target_file_task = "";
    					$taskUploadOk = 0;
    				}
    			}
			}
			if ($_FILES["solution"]["error"] == 0)
			{
				$target_file_solution = $target_dir_solution.basename(pathinfo($_FILES['solution']['name'], PATHINFO_FILENAME))."+".mt_rand().".".(pathinfo($_FILES['solution']['name'], PATHINFO_EXTENSION));
				$fileType = mime_content_type($_FILES["solution"]["tmp_name"]);
				if (file_exists("../".$target_file_solution)) 
				{
    				$addsolution = "Sorry, solution file already exists.";
    				//$solutionUploadOk = 0;
    				//$target_file_solution = "";
				}
				else if ($_FILES["solution"]["size"] > 10000000) 
				{
				    $addsolution = "Sorry, your solution file is too large.";
    				$solutionUploadOk = 0;
    				$target_file_solution = "";
    			}
    			else if ($fileType != "application/zip" && $fileType != "application/x-rar")
    			{
    				$addsolution = "Only zip and rar archives are allowed for solution (Please upload only encrypted files)";
    				$solutionUploadOk = 0;
    				$target_file_solution= "";
    			}
    			if ($solutionUploadOk == 1)
    			{
    				if (!move_uploaded_file($_FILES["solution"]["tmp_name"], "../".$target_file_solution))
    				{
    					$addsolution = "An error occured with file upload. Please inform administrator.";
    					$target_file_solution = "";
    					$solutionUploadOk = 0;
    				}
    			}
			}
			if ($taskUploadOk == 1 && $solutionUploadOk == 1)
			{
				$homework = new homework();
				$newHomeworkId = $homework->addHomework($_POST["name"], $_POST["lecture_id"], $_POST["start_date"], $_POST["end_date"],$_POST["max_points"], $target_file_task, $target_file_solution);
				if ($newHomeworkId != 0)
				{
					foreach ($alllocations as $key1 => $value1) 
					{
						foreach ($_POST as $key2 => $value2) 
						{
							if($value1["id"] == $key2)
							{
								$homeworkLocationId = $homework->addHomeworkLocation($newHomeworkId, $value1["id"]);	
							}
						}
					}
					$addresult = "New task successfully added";
				}
			}
		}
		if (isset($_POST['Change']))
		{
			$location = new location();
			$alllocations = $location->listAllLocations();
			$target_dir_task = "uploads/task/";
			$target_dir_solution = "uploads/solution/";
			$target_file_task = "";
			$target_file_solution = "";
			$solutionUploadOk = 1;
			$taskUploadOk = 1;
			if ($_FILES["task"]["error"] == 0)
			{
				$target_file_task = $target_dir_task.basename(pathinfo($_FILES['task']['name'], PATHINFO_FILENAME))."+".mt_rand().".".(pathinfo($_FILES['task']['name'], PATHINFO_EXTENSION));
				$fileType = mime_content_type($_FILES["task"]["tmp_name"]);
				if (file_exists("../".$target_file_task)) 
				{
    				$addtask = "Sorry, task file already exists.";
    				//$taskUploadOk = 0;
    				//$target_file_task = "";
				}
				else if ($_FILES["task"]["size"] > 10000000) 
				{
				    $addtask = "Sorry, your task file is too large.";
    				$taskUploadOk = 0;
    				$target_file_task = "";
    			}
    			else if ($fileType != "image/jpeg" && $fileType != "image/bmp" && $fileType != "image/jpg" 
    				&& $fileType != "image/gif" && $fileType != "image/gif" && $fileType != "application/pdf" 
    				&& $fileType != "application/zip" && $fileType != "application/x-rar")
    			{
    				$addtask = "Only pdf, zip, rar and images are allowed for task";
    				$taskUploadOk = 0;
    				$target_file_task = "";
    			}
    			if ($taskUploadOk == 1)
    			{
    				if (!move_uploaded_file($_FILES["task"]["tmp_name"], "../".$target_file_task))
    				{
    					$addtask = "An error occured with file upload. Please inform administrator.";
    					$target_file_task = "";
    					$taskUploadOk = 0;
    				}
    			}
			}
			if ($_FILES["solution"]["error"] == 0)
			{
				$target_file_solution = $target_dir_solution.basename(pathinfo($_FILES['solution']['name'], PATHINFO_FILENAME))."+".mt_rand().".".(pathinfo($_FILES['solution']['name'], PATHINFO_EXTENSION));
				$fileType = mime_content_type($_FILES["solution"]["tmp_name"]);
				if (file_exists("../".$target_file_solution)) 
				{
    				$addsolution = "Sorry, solution file already exists.";
    				//$solutionUploadOk = 0;
    				//$target_file_solution = "";
				}
				else if ($_FILES["solution"]["size"] > 10000000) 
				{
				    $addsolution = "Sorry, your solution file is too large.";
    				$solutionUploadOk = 0;
    				$target_file_solution = "";
    			}
    			else if ($fileType != "application/zip" && $fileType != "application/x-rar")
    			{
    				$addsolution = "Only zip and rar archives are allowed for solution (Please upload only encrypted files)";
    				$solutionUploadOk = 0;
    				$target_file_solution= "";
    			}
    			if ($solutionUploadOk == 1)
    			{
    				if (!move_uploaded_file($_FILES["solution"]["tmp_name"], "../".$target_file_solution))
    				{
    					$addsolution = "An error occured with file upload. Please inform administrator.";
    					$target_file_solution = "";
    					$solutionUploadOk = 0;
    				}
    			}
			}
			if (empty($target_file_task))
			{
				$target_file_task = $_POST["old_task"];
			}
			if (empty($target_file_solution))
			{
				$target_file_solution = $_POST["old_solution"];
			}
			$homework = new homework();
			$editHomeworkResult = $homework->editHomework($_POST["homework_id"], $_POST["name"], $_POST["start_date"], $_POST["end_date"],$_POST["max_points"], $target_file_task, $target_file_solution);
			if ($editHomeworkResult != 0)
			{
				$homework->deleteHomeworkLocations	($_POST["homework_id"]);
				foreach ($alllocations as $key1 => $value1) 
				{
					foreach ($_POST as $key2 => $value2) 
					{
						if($value1["id"] == $key2)
						{
							$homeworkLocationId = $homework->addHomeworkLocation($_POST["homework_id"], $value1["id"]);	
						}
					}
				}
				$changeresult = "Task successfully changed";
			}
		}
		if (isset($_POST['Change_lecture']))
		{
			$lecture = new lecture();
			$lectureChangeResult = $lecture->editLecture($_POST["lecture_id"],$_POST["name"], $_POST["teacher"], $_POST["max_group_size"] );
			if ($lectureChangeResult)
			{
				$changeresult = "Lecture info successfully changed";
			}
			else 
			{
				$changeresult = "Error";
			}
		}
	}
}
?>