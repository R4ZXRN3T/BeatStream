<?php
/**
 * Song Card Component
 * @param Song $song - The song object (required)
 * @param array $songQueueData - Queue data for the player (required)
 * @param array $options - Additional options
 */

// Validate required parameters
if (!isset($song)) {
	throw new InvalidArgumentException('$song parameter is required');
}
if (!isset($songQueueData)) {
	$songQueueData = []; // Default empty array
}

$options = $options ?? [];
$showIndex = $options['showIndex'] ?? false;
$showDuration = $options['showDuration'] ?? true;
$showArtistLinks = $options['showArtistLinks'] ?? true;
$index = $options['index'] ?? 0;
$containerClass = $options['containerClass'] ?? 'col-12 col-md-6';
$albumView = $options['albumView'] ?? false;

$artistDisplay = '';
if ($showArtistLinks && !empty($song->getArtists())) {
	$artistLinks = [];
	$artists = $song->getArtists();
	$artistIDs = $song->getArtistIDs();

	for ($i = 0; $i < count($artists); $i++) {
		$artistName = htmlspecialchars($artists[$i]);
		$artistID = $artistIDs[$i] ?? null;

		if ($artistID) {
			$artistLinks[] = '<a href="' . $GLOBALS['PROJECT_ROOT'] . '/view/artist.php?id=' . $artistID . '" class="custom-link" onclick="event.stopPropagation();">' . $artistName . '</a>';

		} else {
			$artistLinks[] = $artistName; // Fallback to plain text if no artist ID
		}
	}

	$artistDisplay = implode(", ", $artistLinks);
} else {
	$artistDisplay = htmlspecialchars(implode(", ", $song->getArtists()));
}

// Optimize image source
$imageSrc = $song->getImageName()
		? "{$GLOBALS['PROJECT_ROOT']}/images/song/thumbnail/" . htmlspecialchars($song->getThumbnailName())
		: "../images/defaultSong.webp";

$songData = htmlspecialchars(json_encode($songQueueData));
?>

<div class="<?= $containerClass ?>">
	<div class="card shadow-sm border-0 song-card">
		<div class="card-body d-flex align-items-center p-3 position-relative"
			 data-song-id="<?= $song->getSongID() ?>"
			 data-song-queue='<?= $songData ?>'>

			<div class="position-relative me-3">
				<?php if ($albumView): ?>
					<div class="song-cover-index-container">
						<p class="song-cover-index"><?= $index + 1 ?></p>
					</div>
				<?php else: ?>
					<img src="<?= $imageSrc ?>"
						 class="rounded song-image"
						 alt="<?= htmlspecialchars($song->getTitle()) ?>"
						 loading="lazy">
				<?php endif; ?>

				<?php if ($showIndex): ?>
					<div class="position-absolute song-index">
						<?= $index + 1 ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="flex-grow-1 song-info-container">
				<h5 class="card-title mb-1 song-title">
					<?= htmlspecialchars($song->getTitle()) ?>
				</h5>
				<p class="card-text mb-0 song-artist">
					<?= $artistDisplay ?>
				</p>
			</div>

			<?php if ($showDuration): ?>
				<div class="ms-auto">
					<p class="card-text mb-0 song-duration">
						<?= $song->getFormattedDuration() ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>