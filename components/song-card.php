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

$artistDisplay = '';
if ($showArtistLinks && !empty($song->getArtists())) {
	$artistLinks = [];
	$artists = $song->getArtists();
	$artistIDs = $song->getArtistIDs();

	for ($i = 0; $i < count($artists); $i++) {
		$artistName = htmlspecialchars($artists[$i]);
		$artistID = $artistIDs[$i] ?? null;

		if ($artistID) {
			$artistLinks[] = '<a href="/BeatStream/view/artist.php?id=' . $artistID . '" class="custom-link">' . $artistName . '</a>';
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
		? "/BeatStream/images/song/" . htmlspecialchars($song->getImageName())
		: "../images/defaultSong.webp";

$songData = htmlspecialchars(json_encode($songQueueData));
?>

<div class="<?= $containerClass ?>">
	<div class="card shadow-sm border-0 song-card" style="border-radius: 10px; cursor: pointer;">
		<div class="card-body d-flex align-items-center p-3 position-relative"
			 data-song-id="<?= $song->getSongID() ?>"
			 data-song-queue='<?= $songData ?>'>

			<div class="position-relative me-3">
				<img src="<?= $imageSrc ?>"
					 class="rounded song-image"
					 alt="<?= htmlspecialchars($song->getTitle()) ?>"
					 loading="lazy">

				<?php if ($showIndex): ?>
					<div class="position-absolute song-index">
						<?= $index + 1 ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="flex-grow-1">
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
						<?= $song->getSongLength()->format("i:s") ?>
					</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>