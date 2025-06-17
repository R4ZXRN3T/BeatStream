<!Doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>BeatStream - view users</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../viewStyle.css" rel="stylesheet">
	<link href="../../favicon.ico" rel="icon">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<div class="container-fluid">
		<div class="collapse navbar-collapse myNavbar">
			<ul class="navbar-nav">
				<li class="nav-item"><a class="nav-link" href="../songs">Home</a></li>
				<li class="nav-item"><a class="nav-link" href="../../add/song">Add content</a></li>
				<li class="nav-item"><a class="nav-link" href="../../view/songs">View content</a></li>
			</ul>
		</div>
	</div>
</nav>

<div class="tab">
	<ul class="nav nav-tabs justify-content-center">
		<li class="nav-item"><a class="nav-link" href="../songs">Songs</a></li>
		<li class="nav-item"><a class="nav-link" href="../artists">Artists</a></li>
		<li class="nav-item"><a class="nav-link active" href="../users">Users</a></li>
		<li class="nav-item"><a class="nav-link" href="../playlists">Playlists</a></li>
		<li class="nav-item"><a class="nav-link" href="../albums">Albums</a></li>
	</ul>
</div>

<?php
include("../../SongController.php");
$userList = SongController::getUserList();
?>


<table style="width:100%; font-family:segoe UI,serif;">
	<colgroup>
		<col span="9" style="background-color:lightgray">
	</colgroup>
	<tr>
		<th style="width:16.7%;">User ID</th>
		<th style="width:16.7%;">Username</th>
		<th style="width:16.7%;">E-Mail</th>
		<th style="width:16.7%;">User Password</th>
		<th style="width:16.7%;">Salt</th>
		<th style="width:16.7%;">Image Path</th>
		<th style="width:1%;"></th>
	</tr>
	<?php
	for ($i = 0; $i < count($userList); $i++) {
		?>
		<tr>
			<td><?php echo $userList[$i]->getUserID() ?></td>
			<td><?php echo $userList[$i]->getUsername() ?></td>
			<td><?php echo $userList[$i]->getEmail() ?></td>
			<td><?php echo $userList[$i]->getUserPassword() ?></td>
			<td><?php echo $userList[$i]->getSalt() ?></td>
			<td><?php echo $userList[$i]->getImagePath() ?></td>
		</tr>
		<?php
	}
	?>
</table>
</body>

</html>