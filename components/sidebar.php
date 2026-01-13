<?php
// Reusable sidebar component
// Usage: set $activePage = 'home'|'search'|'discover'|'create'|'admin' before include.
// Optional: set $sidebarBgClass = 'bg-light' (default) or ''.

$activePage = $activePage ?? '';
$sidebarBgClass = $sidebarBgClass ?? 'bg-light';

$root = $GLOBALS['PROJECT_ROOT'] ?? '';

$links = [
	'home' => ['label' => 'Home', 'href' => $root . '/'],
	'discover' => ['label' => 'Discover', 'href' => $root . '/discover/'],
	'create' => ['label' => 'Create', 'href' => $root . '/create/'],
];

$isAdmin = !empty($_SESSION['isAdmin']);
?>
<nav class="col-md-2 d-none d-md-block <?= htmlspecialchars($sidebarBgClass) ?> sidebar py-4 fixed-top">
	<div class="nav flex-column py-4">
		<?php foreach ($links as $key => $link): ?>
			<a href="<?= htmlspecialchars($link['href']) ?>"
			   class="nav-link mb-2 <?= $activePage === $key ? 'active' : '' ?>">
				<?= htmlspecialchars($link['label']) ?>
			</a>
		<?php endforeach; ?>

		<?php if ($isAdmin): ?>
			<a href="<?= htmlspecialchars($root . '/admin/') ?>"
			   class="nav-link mb-2 <?= $activePage === 'admin' ? 'active' : '' ?>">
				Admin
			</a>
		<?php endif; ?>
	</div>
</nav>
