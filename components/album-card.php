<?php
// components/album-card.php
/**
 * Album Card Component
 * @param Album $album - The album object (required)
 * @param array $options - Additional options
 */
if (!isset($album)) {
	throw new InvalidArgumentException('$album parameter is required');
}

$options = $options ?? [];
$containerClass = $options['containerClass'] ?? 'col-12 col-md-6 col-lg-4';
$isLarge = $options['large'] ?? false;
$compact = $options['compact'] ?? false;

// Image handling
$imageFolder = $isLarge ? "{$GLOBALS['PROJECT_ROOT']}/images/album/large/" : "{$GLOBALS['PROJECT_ROOT']}/images/album/thumbnail/";
$imageSrc = !empty($album->getImageName())
		? $imageFolder . htmlspecialchars($album->getImageName())
		: "../images/defaultAlbum.webp";

// Common data
$albumName = htmlspecialchars($album->getName());
$artistNames = htmlspecialchars(implode(", ", $album->getArtists()));
$albumUrl = "{$GLOBALS['PROJECT_ROOT']}/view/album.php?id=" . $album->getAlbumID();
$albumInfo = $album->getLength() . " songs &bull; " . $album->getFormattedDuration();

// CSS classes
$cardClasses = "card shadow-sm border-0 album-card";
$imageClasses = "rounded album-image";

if ($compact) {
	$cardClasses .= " compact";
	$imageClasses .= " song-image";
	$titleClasses = "card-title mb-1 song-title";
	$artistClasses = "card-text mb-0 song-artist";
	$infoClasses = "card-text mb-0 song-duration";
} else {
	$cardClasses .= $isLarge ? " album-card-large" : " album-card-standard";
	$imageClasses .= $isLarge ? "" : " playlist-image";
	$titleClasses = "card-title mb-1 album-title";
	$artistClasses = "card-text mb-0 album-artist";
	$infoClasses = "card-text mb-0 album-info";
	$dateInfoClasses = "card-text mb-0 text-end album-date";
}
?>

<div class="<?= $containerClass ?>">
	<a href="<?= $albumUrl ?>" class="<?= $cardClasses ?> on-card-link">
		<?php if ($compact): ?>
			<!-- Compact Layout -->
			<div class="card-body d-flex align-items-center p-3 position-relative">
				<div class="position-relative me-3">
					<img src="<?= $imageSrc ?>" class="<?= $imageClasses ?>" alt="<?= $albumName ?>" loading="lazy">
				</div>
				<div class="flex-grow-1">
					<h5 class="<?= $titleClasses ?>"><?= $albumName ?></h5>
					<p class="<?= $artistClasses ?>"><?= $artistNames ?></p>
				</div>
				<div class="ms-auto">
					<p class="<?= $infoClasses ?>"><?= $albumInfo ?></p>
				</div>
			</div>
		<?php elseif ($isLarge): ?>
			<!-- Large Layout -->
			<img src="<?= $imageSrc ?>" class="mb-2 <?= $imageClasses ?>" alt="<?= $albumName ?>" loading="lazy">
			<div class="card-body p-3">
				<div class="mb-2">
					<h5 class="<?= $titleClasses ?>"><?= $albumName ?></h5>
					<p class="<?= $artistClasses ?>"><?= $artistNames ?></p>
					<div class="d-flex align-items-center justify-content-between">
						<p class="<?= $infoClasses ?> mb-0"><?= $albumInfo ?></p>
						<p class="<?= $dateInfoClasses ?> mb-0"><?= $album->getReleaseDate()->format('Y') ?></p>
					</div>
				</div>
			</div>
		<?php else: ?>
			<!-- Standard Layout -->
			<div class="card-body d-flex align-items-center p-3">
				<img src="<?= $imageSrc ?>" class="me-3 <?= $imageClasses ?>" alt="<?= $albumName ?>" loading="lazy">
				<div class="card-body">
					<h5 class="<?= $titleClasses ?>"><?= $albumName ?></h5>
					<p class="<?= $artistClasses ?>"><?= $artistNames ?></p>
				</div>
			</div>
		<?php endif; ?>
	</a>
</div>