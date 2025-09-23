<?php
/**
 * Playlist List Component
 * @param array $playlists - Array of playlist objects
 * @param array $options - Layout and display options
 */

$options = $options ?? [];
$containerClass = $options['containerClass'] ?? 'col-12 col-md-6';
$showCreator = $options['showCreator'] ?? false;
$emptyMessage = $options['emptyMessage'] ?? 'No playlists available.';
$playlists = $playlistList ?? [];

?>

<div class="row g-3">
	<?php if (!empty($playlists)): ?>
		<?php foreach ($playlists as $playlist): ?>
			<?php
			$creatorID = $playlist->getCreatorID();
			$cardOptions = [
				'containerClass' => $containerClass,
				'showCreator' => $showCreator,
			];
			include( $GLOBALS['PROJECT_ROOT_DIR'] . "/components/playlist-card.php");
			?>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="col-12">
			<p class="text-center"><?= htmlspecialchars($emptyMessage); ?></p>
		</div>
	<?php endif; ?>
</div>