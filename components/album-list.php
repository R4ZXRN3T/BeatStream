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

<div class="row g-4">
	<?php if (!empty($albums)): ?>
		<?php foreach ($albums as $album): ?>
			<?php
			$cardOptions = [
				'containerClass' => $containerClass
			];
			include($_SERVER['DOCUMENT_ROOT'] . "/BeatStream/components/album-card.php");
			?>
		<?php endforeach; ?>
	<?php else: ?>
		<div class="col-12">
			<p class="text-center"><?= htmlspecialchars($emptyMessage); ?></p>
		</div>
	<?php endif; ?>
</div>