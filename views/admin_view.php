<?php include('_header.php'); ?>
<b>Name:</b> <?php echo $userinfo['name']." ";echo $userinfo['surname']."   "?> <br>
<a href="adminedit.php">Edit users</a><br>
<a href="adminmanagelocations.php">Manage Locations</a><br>
<a href="adminmanageworkinghours.php">Manage Working Hours</a><br><br>

<a href="../pwchange.php">Change password</a><br>
<a href="../index.php?logout">Logout</a>
<?php include('_footer.php'); ?>