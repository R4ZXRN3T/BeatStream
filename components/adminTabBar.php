<?php
$activePage = $activePage ?? null;
?>

<div class="tab">
	<ul class="nav nav-tabs justify-content-center">
		<li class="nav-item"><a class="nav-link <?php echo ($activePage === 'song') ? 'active' : ''; ?>"
		                        href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/song.php">Song</a></li>
		<li class="nav-item"><a class="nav-link <?php echo ($activePage === 'artist') ? 'active' : ''; ?>"
		                        href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/artist.php">Artist</a></li>
		<li class="nav-item"><a class="nav-link <?php echo ($activePage === 'user') ? 'active' : ''; ?>"
		                        href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/user.php">User</a></li>
		<li class="nav-item"><a class="nav-link <?php echo ($activePage === 'playlist') ? 'active' : ''; ?>"
		                        href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/playlist.php">Playlist</a></li>
		<li class="nav-item"><a class="nav-link <?php echo ($activePage === 'album') ? 'active' : ''; ?>"
		                        href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/album.php">Album</a></li>
	</ul>
</div>
