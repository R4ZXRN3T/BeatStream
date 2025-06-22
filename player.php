<footer id="music-player-footer" style="position:fixed;bottom:0;left:0;width:100%;background:#222;color:#fff;z-index:9999;box-shadow:0 -2px 8px rgba(0,0,0,0.2);">
	<div class="container-fluid py-2 d-flex align-items-center">
		<img id="player-cover" src="../images/defaultSong.webp" alt="Cover" style="width:48px;height:48px;object-fit:cover;border-radius:6px;margin-right:16px;">
		<div style="flex:1;">
			<div id="player-title" style="font-weight:bold;">No song playing</div>
			<div id="player-artist" style="font-size:0.9em;color:#bbb;">-</div>
		</div>
		<button id="player-prev" class="btn btn-link text-white"><span>&#9664;&#9664;</span></button>
		<button id="player-play" class="btn btn-link text-white"><span>&#9654;</span></button>
		<button id="player-next" class="btn btn-link text-white"><span>&#9654;&#9654;</span></button>
		<input id="player-progress" type="range" min="0" max="100" value="0" style="width:150px;margin:0 16px;">
		<audio id="audio-player" preload="auto"></audio>
	</div>
</footer>
<script>
	let playlist = <?php echo json_encode(array_map(function($song) {
		return [
			'id' => $song->getSongID(),
			'title' => $song->getTitle(),
			'artist' => $song->getArtists(),
			'cover' => !empty($song->getImagePath()) ? "/BeatStream/images/song/" . htmlspecialchars($song->getImagePath()) : "../images/defaultSong.webp",
			'src' => "/BeatStream/audio/" . $song->getFilePath() // Adjust path as needed
		];
	}, $recommendedSongs)); ?>;
	let currentIndex = 0;
	const audio = document.getElementById('audio-player');
	const playBtn = document.getElementById('player-play');
	const prevBtn = document.getElementById('player-prev');
	const nextBtn = document.getElementById('player-next');
	const progress = document.getElementById('player-progress');
	const title = document.getElementById('player-title');
	const artist = document.getElementById('player-artist');
	const cover = document.getElementById('player-cover');

	function loadSong(index) {
		if (!playlist[index]) return;
		const song = playlist[index];
		audio.src = song.src;
		title.textContent = song.title;
		artist.textContent = song.artist;
		cover.src = song.cover;
		audio.load();
		playBtn.innerHTML = '<span>&#9654;</span>';
		progress.value = 0;
	}
	function playPause() {
		if (audio.paused) {
			audio.play();
			playBtn.innerHTML = '<span>&#10073;&#10073;</span>';
		} else {
			audio.pause();
			playBtn.innerHTML = '<span>&#9654;</span>';
		}
	}
	function nextSong() {
		currentIndex = (currentIndex + 1) % playlist.length;
		loadSong(currentIndex);
		audio.play();
	}
	function prevSong() {
		currentIndex = (currentIndex - 1 + playlist.length) % playlist.length;
		loadSong(currentIndex);
		audio.play();
	}
	audio.addEventListener('timeupdate', () => {
		if (audio.duration) {
			progress.value = (audio.currentTime / audio.duration) * 100;
		}
	});
	progress.addEventListener('input', () => {
		if (audio.duration) {
			audio.currentTime = (progress.value / 100) * audio.duration;
		}
	});
	audio.addEventListener('ended', nextSong);
	playBtn.addEventListener('click', playPause);
	nextBtn.addEventListener('click', nextSong);
	prevBtn.addEventListener('click', prevSong);

	// AJAX example: fetch song info (if needed)
	// function fetchSongInfo(songID) {
	//     fetch('/BeatStream/api/song.php?id=' + songID)
	//         .then(res => res.json())
	//         .then(data => { /* update UI */ });
	// }

	if (playlist.length > 0) loadSong(currentIndex);
</script>