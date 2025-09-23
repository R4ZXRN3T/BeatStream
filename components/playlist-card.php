<?php
/**
 * Playlist Card Component
 * @param Playlist $playlist - The playlist object (required)
 * @param array $options - Additional options
 */

if (!isset($playlist)) {
	throw new InvalidArgumentException('$playlist parameter is required');
}

$options = $options ?? [];
$containerClass = $options['containerClass'] ?? 'col-12 col-md-6';
$showCreator = $options['showCreator'] ?? false;
$compact = $options['compact'] ?? false;
$isLarge = $options['large'] ?? false;

$imageSrc = $playlist->getThumbnailName()
		? "{$GLOBALS['PROJECT_ROOT']}/images/playlist/thumbnail/" . htmlspecialchars($playlist->getThumbnailName())
		: "{$GLOBALS['PROJECT_ROOT']}/images/defaultPlaylist.webp";

$playlistName = htmlspecialchars($playlist->getName());
$creatorName = htmlspecialchars($playlist->getCreatorName());
$playlistUrl = "{$GLOBALS['PROJECT_ROOT']}/view/playlist.php?id=" . $playlist->getPlaylistID();

// CSS classes similar to album cards
$cardClasses = "card shadow-sm border-0 playlist-card";
$imageClasses = "rounded playlist-image";

if ($compact) {
	$cardClasses .= " compact";
	$imageClasses .= " song-image";
	$titleClasses = "card-title mb-1 song-title";
	$creatorClasses = "card-text mb-0 song-artist";
} else {
	$cardClasses .= $isLarge ? " playlist-card-large" : " playlist-card-standard";
	$imageClasses .= $isLarge ? "" : " playlist-image";
	$titleClasses = "card-title mb-1 playlist-title";
	$creatorClasses = "card-text mb-0 playlist-creator";
}
?>

<div class="<?= $containerClass ?>">
	<a href="<?= $playlistUrl ?>" class="<?= $cardClasses ?> on-card-link">
		<?php if ($compact): ?>
			<!-- Compact Layout -->
			<div class="card-body d-flex align-items-center p-3 position-relative">
				<div class="position-relative me-3">
					<img src="<?= $imageSrc ?>" class="<?= $imageClasses ?>" alt="<?= $playlistName ?>" loading="lazy">
				</div>
				<div class="flex-grow-1">
					<h5 class="<?= $titleClasses ?>"><?= $playlistName ?></h5>
					<?php if ($showCreator): ?>
						<p class="<?= $creatorClasses ?>"><?= $creatorName ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php elseif ($isLarge): ?>
			<!-- Large Layout -->
			<img src="<?= $imageSrc ?>" class="mb-2 <?= $imageClasses ?>" alt="<?= $playlistName ?>" loading="lazy">
			<div class="card-body p-3">
				<h5 class="<?= $titleClasses ?>"><?= $playlistName ?></h5>
				<?php if ($showCreator): ?>
					<p class="<?= $creatorClasses ?>"><?= $creatorName ?></p>
				<?php endif; ?>
			</div>
		<?php else: ?>
			<!-- Standard Layout -->
			<div class="card-body d-flex align-items-center p-3">
				<img src="<?= $imageSrc ?>" class="me-3 <?= $imageClasses ?>" alt="<?= $playlistName ?>" loading="lazy">
				<div class="card-body">
					<h5 class="<?= $titleClasses ?>"><?= $playlistName ?></h5>
					<?php if ($showCreator): ?>
						<p class="<?= $creatorClasses ?>"><?= $creatorName ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</a>
</div>