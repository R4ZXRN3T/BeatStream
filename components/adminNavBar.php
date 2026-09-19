<?php
$activePage = $activePage ?? null;
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-secondary admin-nav">
	<div class="container-fluid">
		<ul class="navbar-nav">
			<li class="nav-item"><a class="nav-link <?= $activePage === 'view' ? 'active' : '' ?>"
									href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/view/">View</a>
			</li>
			<li class="nav-item"><a class="nav-link <?= $activePage === 'add' ? 'active' : '' ?>"
									href="<?= $GLOBALS['PROJECT_ROOT'] ?>/admin/add/">Add
					content</a></li>
		</ul>
	</div>
</nav>
