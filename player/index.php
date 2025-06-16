<head>
    <link href="../favicon.ico" rel="icon">
    <title>BeatStream Player</title>
</head>
<?php
$audioDir = 'audio/';
$files = array_filter(scandir($audioDir), function ($file) use ($audioDir) {
	return preg_match('/\.(mp3|wav|ogg|flac)$/i', $file);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Music Player</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="player">
    <audio id="audio" controls>
        <source id="audioSource" src="" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <ul id="playlist">
		<?php foreach ($files as $file): ?>
            <li data-src="<?php echo $audioDir . htmlspecialchars($file); ?>">
				<?php echo htmlspecialchars($file); ?>
            </li>
		<?php endforeach; ?>
    </ul>
</div>
<script src="player.js"></script>
</body>
</html>