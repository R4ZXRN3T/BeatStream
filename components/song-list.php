<?php
/**
 * Song List Component
 * @param array $songs - Array of song objects
 * @param array $songQueueData - Queue data for the player
 * @param array $options - Layout and display options
 */

$options = $options ?? [];
$layout = $options['layout'] ?? 'list'; // 'list' or 'grid'
$showIndex = $options['showIndex'] ?? false;
$showDuration = $options['showDuration'] ?? true;
$showArtistLinks = $options['showArtistLinks'] ?? true;
$containerClass = $options['containerClass'] ?? 'col-12 col-md-6';
$emptyMessage = $options['emptyMessage'] ?? 'No songs available.';
$albumView = $options['albumView'] ?? false;
?>

<div class="row g-3">
	<?php if (!empty($songs)): ?>
		<?php foreach ($songs as $index => $song): ?>
			<?php
			$options = [
					'showIndex' => $showIndex,
					'showDuration' => $showDuration,
					'showArtistLinks' => $showArtistLinks,
					'index' => $index,
					'containerClass' => $containerClass,
					'albumView' => $albumView
			];
			// Pass required variables to song-card.php
			include('song-card.php');
			?>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="col-12">
			<p class="text-center"><?php echo htmlspecialchars($emptyMessage); ?></p>
		</div>
	<?php endif; ?>
</div>