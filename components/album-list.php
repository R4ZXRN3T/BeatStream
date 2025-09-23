<?php
/**
 * Album List Component
 * @param array $albums - Array of album objects
 * @param array $options - Layout and display options
 */

$options = $options ?? [];
$containerClass = $options['containerClass'] ?? 'col-12 col-md-6 col-lg-4';
$emptyMessage = $options['emptyMessage'] ?? 'No albums available.';
$albums = $albumList ?? [];
?>

<div class="row g-3">
	<?php if (!empty($albums)): ?>
		<?php foreach ($albums as $album): ?>
			<?php
			$cardOptions = [
					'containerClass' => $containerClass
			];
			if (!empty($options['compact'])) {
				$cardOptions['compact'] = true;
			}
			include( $GLOBALS['PROJECT_ROOT_DIR'] . "/components/album-card.php");
			?>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="col-12">
			<p class="text-center"><?= htmlspecialchars($emptyMessage); ?></p>
		</div>
	<?php endif; ?>
</div>