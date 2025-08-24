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
$creatorName = $options['creatorName'] ?? 'Unknown User';

$imageSrc = $playlist->getThumbnailName()
	? "/BeatStream/images/playlist/thumbnail/" . htmlspecialchars($playlist->getThumbnailName())
	: "/BeatStream/images/defaultPlaylist.webp";
?>

<div class="<?= $containerClass ?>">
	<div class="card shadow-sm border-0 song-card" style="border-radius: 10px; cursor: pointer;">
		<div class="card-body d-flex align-items-center p-3 position-relative">
			<div class="position-relative me-3">
				<img src="<?= $imageSrc ?>"
					 class="rounded song-image"
					 alt="<?= htmlspecialchars($playlist->getName()); ?>"
					 loading="lazy">
			</div>
			<div class="flex-grow-1">
				<h5 class="card-title mb-1 song-title">
					<?= htmlspecialchars($playlist->getName()); ?>
				</h5>
				<?php if ($showCreator): ?>
					<p class="card-text mb-0 song-artist">
						<?= htmlspecialchars($playlist->getCreatorName()); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>