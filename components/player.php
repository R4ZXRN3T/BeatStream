<script>
	// Function to highlight the currently playing song
	function updateCurrentlyPlaying(songId) {
		// Remove highlight from all cards
		document.querySelectorAll('.card-body[data-song-id]').forEach(card => {
			card.classList.remove('playing');
		});

		// Add highlight to currently playing song
		if (songId) {
			const currentCard = document.querySelector(`.card-body[data-song-id="${songId}"]`);
			if (currentCard) {
				currentCard.classList.add('playing');
			}
		}
	}

	// Custom event listener for when a song starts playing
	document.addEventListener('songPlaying', function (e) {
		updateCurrentlyPlaying(e.detail.songId);
	});
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/mainStyle.css">
<link rel="stylesheet" href="<?= $GLOBALS['PROJECT_ROOT'] ?>/playerStyle.css">

<!-- Music Player -->
<div id="musicPlayer" class="fixed-bottom bg-dark text-white p-2 d-none">
	<div class="container-fluid">
		<div class="row align-items-center">
			<!-- Song Info -->
			<div class="col-md-3 d-flex align-items-center">
				<img id="playerCover" src="/BeatStream/images/defaultSong.webp" alt="Song Cover" class="me-2"
					 style="width: 50px; height: 50px; object-fit: cover;">
				<div>
					<h6 id="playerTitle" class="mb-0">No song selected</h6>
					<small id="playerArtist" class="text">Unknown artist</small>
				</div>
			</div>

			<!-- Player Controls -->
			<div class="col-md-6 text-center">
				<div class="d-flex flex-column align-items-center">
					<div class="d-flex align-items-center justify-content-center mb-1 gap-3">
						<!-- Main playback controls -->
						<div class="controls d-flex align-items-center justify-content-center">
							<button id="prevBtn" class="btn btn-sm btn-outline-light rounded-circle mx-2"><i
										class="bi bi-skip-backward-fill"></i></button>
							<button id="playPauseBtn" class="btn btn-sm btn-primary rounded-circle mx-2"><i
										class="bi bi-play-fill"></i></button>
							<button id="nextBtn" class="btn btn-sm btn-outline-light rounded-circle mx-2"><i
										class="bi bi-skip-forward-fill"></i></button>
							<button id="queueBtn" class="btn btn-sm btn-outline-light"><i
										class="bi bi-music-note-list"></i></button>
						</div>
					</div>

					<!-- Progress Bar -->
					<div class="progress w-100" style="height: 5px; cursor: pointer;">
						<div id="progressBar" class="progress-bar" role="progressbar" style="width: 0"></div>
					</div>
					<div class="d-flex justify-content-between w-100">
						<small id="currentTime">0:00</small>
						<small id="totalTime">0:00</small>
					</div>
				</div>
			</div>

			<!-- Volume Control -->
			<div class="col-md-3 text-end">
				<div class="d-flex align-items-center justify-content-end">
					<button id="killPlayerBtn" class="btn btn-sm btn-outline-danger me-2" title="Kill Player">
						<i class="bi bi-x-circle"></i>
					</button>
					<i id="volumeIcon" class="bi bi-volume-up me-2"></i>
					<input type="range" class="form-range" id="volumeControl" min="0" max="100" value="100"
						   style="width: 100px;">
				</div>
			</div>
		</div>
	</div>
	<audio id="audioPlayer"></audio>
</div>

<!-- Queue Panel (separate from music player) -->
<div id="queuePanel" class="position-fixed bg-dark p-3 d-none">
	<h6 class="mb-3">Queue
		<button id="clearQueueBtn" class="btn btn-sm btn-outline-danger float-end">Clear</button>
	</h6>
	<ul id="queueList" class="list-group list-group-flush"></ul>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		class MusicPlayer {
			constructor() {
				this.originalTitle = document.title;

				this.basePath = '<?= $GLOBALS['PROJECT_ROOT'] ?>';
				this.audioBasePath = `${this.basePath}/audio/${localStorage.getItem('audioFormat')}/`;
				this.imageBasePath = `${this.basePath}/images/song/thumbnail/`;
				this.largeImagePath = `${this.basePath}/images/song/large/`;

				// Core elements only
				this.playerUI = document.getElementById('musicPlayer');
				this.audio = document.getElementById('audioPlayer');
				this.playPauseBtn = document.getElementById('playPauseBtn');
				this.progressBar = document.getElementById('progressBar');
				this.progressContainer = document.querySelector('.progress');
				this.volumeControl = document.getElementById('volumeControl');
				this.volumeIcon = document.getElementById('volumeIcon');
				this.playerCover = document.getElementById('playerCover');
				this.playerTitle = document.getElementById('playerTitle');
				this.playerArtist = document.getElementById('playerArtist');
				this.currentTimeEl = document.getElementById('currentTime');
				this.totalTimeEl = document.getElementById('totalTime');
				this.prevBtn = document.getElementById('prevBtn');
				this.nextBtn = document.getElementById('nextBtn');
				this.killPlayerBtn = document.getElementById('killPlayerBtn');
				this.queueBtn = document.getElementById('queueBtn');
				this.queuePanel = document.getElementById('queuePanel');
				this.queueList = document.getElementById('queueList');
				this.clearQueueBtn = document.getElementById('clearQueueBtn');

				this.queue = [];
				this.currentIndex = -1;
				this.history = [];

				this.init();
			}

			init() {
				this.audio.volume = this.volumeControl.value / 100;
				this.playerUI.classList.add('d-none');
				this.setupEventListeners();
				this.setupSongCardListeners();
				this.restoreState();
			}

			setupEventListeners() {
				this.playPauseBtn.addEventListener('click', () => this.togglePlayPause());
				this.nextBtn.addEventListener('click', () => this.playNext());
				this.prevBtn.addEventListener('click', () => this.playPrevious());
				this.queueBtn.addEventListener('click', (e) => {
					e.stopPropagation();
					this.queuePanel.classList.toggle('d-none');
					this.updateQueueDisplay();
				});
				this.clearQueueBtn.addEventListener('click', () => this.clearQueue());

				this.audio.addEventListener('ended', () => this.playNext());
				this.audio.addEventListener('timeupdate', () => this.updateProgress());
				this.audio.addEventListener('loadedmetadata', () => this.updateTotalTime());
				this.audio.addEventListener('play', () => {
					this.playPauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
				});
				this.audio.addEventListener('pause', () => {
					this.playPauseBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
				});

				this.progressContainer.addEventListener('click', (e) => this.seekTo(e));
				this.volumeControl.addEventListener('input', () => {
					this.audio.volume = this.volumeControl.value / 100;
					this.updateVolumeIcon();
				});
				this.volumeIcon.addEventListener('click', () => this.toggleMute());

				this.killPlayerBtn.addEventListener('click', () => this.killPlayer());

				document.addEventListener('click', (e) => {
					if (!this.queuePanel.contains(e.target) && e.target !== this.queueBtn) {
						this.queuePanel.classList.add('d-none');
					}
				});

				document.addEventListener('keydown', (e) => {
					if ((e.code === 'Space' && !e.target.matches('input, textarea')) || (e.code === 'KeyK' && !e.target.matches('input, textarea'))) {
						e.preventDefault();
						this.togglePlayPause();
					}
					if ((e.code === 'KeyN') && !e.target.matches('input, textarea')) {
						e.preventDefault();
						this.playNext();
					}
					if ((e.code === 'KeyP') && !e.target.matches('input, textarea')) {
						e.preventDefault();
						this.playPrevious();
					}
				});

				this.startAutoSave();
			}

			setupSongCardListeners() {
				document.querySelectorAll('.card-body[data-song-id]').forEach(card => {
					card.addEventListener('click', () => {
						const songId = card.dataset.songId;
						try {
							if (card.dataset.songQueue) {
								this.loadQueueFromData(JSON.parse(card.dataset.songQueue), songId);
							} else {
								this.loadSingleSong(songId);
							}
						} catch (error) {
							this.loadSingleSong(songId);
						}
					});
				});
			}

			startAutoSave() {
				if (this.autoSaveInterval) clearInterval(this.autoSaveInterval);
				this.autoSaveInterval = setInterval(() => {
					this.saveState();
				}, 2000); // 5000 ms = 5 seconds
			}

			saveState() {
				const state = {
					queue: this.queue,
					currentIndex: this.currentIndex,
					currentTime: this.audio.currentTime
				};
				localStorage.setItem('playerState', JSON.stringify(state));
			}

			restoreState() {
				const state = JSON.parse(localStorage.getItem('playerState'));
				if (state && state.queue && state.queue.length > 0) {
					this.queue = state.queue;
					this.currentIndex = state.currentIndex;
					this.playerUI.classList.remove('d-none');
					this.updateQueueDisplay();
					this.playSong(this.queue[this.currentIndex]);
					this.audio.addEventListener('loadedmetadata', () => {
						this.audio.currentTime = state.currentTime || 0;
					}, {once: true});
				}
			}

			killPlayer() {
				this.audio.pause();
				this.audio.src = '';
				this.playerUI.classList.add('d-none');
				this.queue = [];
				this.currentIndex = -1;
				this.updateQueueDisplay();
				document.title = this.originalTitle;
				localStorage.removeItem('playerState');
			}

			loadQueueFromData(queueData, clickedSongId) {
				this.audioBasePath = `${this.basePath}/audio/${localStorage.getItem('audioFormat')}/`;
				this.queue = queueData.map(song => ({
					songID: song.songID,
					title: song.title,
					artists: song.artists,
					artistIDs: song.artistIDs || [], // Add this line
					fileName: (localStorage.getItem('audioFormat') === 'flac' ? song.flacFilename : song.opusFilename),
					thumbnailName: song.thumbnailName || '',
					imageName: song.imageName || '',
				}));

				this.currentIndex = this.queue.findIndex(song =>
					String(song.songID) === String(clickedSongId)
				);

				if (this.currentIndex >= 0) {
					this.playSong(this.queue[this.currentIndex]);
					this.playerUI.classList.remove('d-none');
					this.updateQueueDisplay();
				}
			}

			playSong(song) {
				if (!song) return;

				this.audio.src = `${this.audioBasePath}${song.fileName}`;
				this.playerTitle.textContent = song.title;

				// Use artist links if available
				if (song.artistIDs && song.artistIDs.length > 0) {
					this.playerArtist.innerHTML = this.generateArtistLinks(song.artists, song.artistIDs);
				} else {
					this.playerArtist.textContent = song.artists;
				}

				this.playerCover.src = song.thumbnailName ?
					`${this.imageBasePath}${song.thumbnailName}` :
					'../images/defaultSong.webp';

				this.audio.play().catch(error => console.error('Playback error:', error));
				if ("mediaSession" in navigator) {
					navigator.mediaSession.metadata = new window.MediaMetadata({
						title: song.title,
						artist: song.artists,
						album: "test",
						artwork: [{
							src: (song.imageName
								? `${location.origin}<?= $GLOBALS['PROJECT_ROOT'] ?>/images/song/large/${song.imageName}`
								: `${location.origin}<?= $GLOBALS['PROJECT_ROOT'] ?>/images/defaultSong.webp`),
						}]
					});

					navigator.mediaSession.setActionHandler('play', () => this.togglePlayPause());
					navigator.mediaSession.setActionHandler('pause', () => this.togglePlayPause());
					navigator.mediaSession.setActionHandler('previoustrack', () => this.playPrevious());
					navigator.mediaSession.setActionHandler('nexttrack', () => this.playNext());
				}

				document.dispatchEvent(new CustomEvent('songPlaying', {
					detail: {songId: song.songID}
				}));

				document.title = `▶ ${song.title} by ${song.artists} - BeatStream`;
				this.saveState();
			}

			playFromQueue(index) {
				if (index >= 0 && index < this.queue.length) {
					this.currentIndex = index;
					this.playSong(this.queue[index]);
					this.updateQueueDisplay();
				}
			}

			togglePlayPause() {
				if (this.currentIndex < 0 && this.queue.length > 0) {
					this.currentIndex = 0;
					this.playSong(this.queue[0]);
					return;
				}
				if (this.audio.paused) {
					this.audio.play();
					document.title = `▶ ${this.playerTitle.textContent} by ${this.playerArtist.textContent} - BeatStream`;
				} else {
					this.audio.pause();
					document.title = `❚❚ ${this.playerTitle.textContent} by ${this.playerArtist.textContent} - BeatStream`;
				}
				this.saveState();
			}

			playNext() {
				if (this.queue.length === 0) return;

				if (this.currentIndex < this.queue.length - 1) {
					this.currentIndex++;
					this.playSong(this.queue[this.currentIndex]);
				} else {
					// End of queue
					this.audio.pause();
					this.playerTitle.textContent = 'End of queue';
					this.playerArtist.textContent = 'Play again or add more songs';
					this.playerCover.src = '<?= $GLOBALS['PROJECT_ROOT'] ?>/images/defaultSong.webp';
				}
				this.updateQueueDisplay();
			}

			playPrevious() {
				if (this.queue.length === 0) return;

				// If we're more than 3 seconds into the song, restart it
				if (this.audio.currentTime > 3) {
					this.audio.currentTime = 0;
					return;
				}

				// Navigate to previous song in queue
				if (this.currentIndex > 0) {
					this.currentIndex--;
					this.playSong(this.queue[this.currentIndex]);
					this.updateQueueDisplay();
				}
			}

			removeFromQueue(index) {
				if (index < 0 || index >= this.queue.length) return;

				// If removing currently playing song
				if (index === this.currentIndex) {
					this.audio.pause();
					this.queue.splice(index, 1);

					if (this.queue.length === 0) {
						this.currentIndex = -1;
						this.playerTitle.textContent = 'No song selected';
						this.playerArtist.textContent = '';
						this.playerCover.src = '<?= $GLOBALS['PROJECT_ROOT'] ?>/images/defaultSong.webp';
					} else {
						// Adjust current index and play next available song
						if (this.currentIndex >= this.queue.length) {
							this.currentIndex = 0;
						}
						this.playSong(this.queue[this.currentIndex]);
					}
				} else {
					// Remove song and adjust current index if necessary
					this.queue.splice(index, 1);
					if (index < this.currentIndex) {
						this.currentIndex--;
					}
				}

				this.updateQueueDisplay();
			}

			clearQueue() {
				if (confirm('Clear queue?')) {
					const currentSong = this.currentIndex >= 0 ? this.queue[this.currentIndex] : null;
					this.queue = currentSong ? [currentSong] : [];
					this.currentIndex = currentSong ? 0 : -1;
					this.history = [];
					this.updateQueueDisplay();
				}
			}

			updateQueueDisplay() {
				this.queueList.innerHTML = '';

				if (this.queue.length === 0) {
					const emptyMessage = document.createElement('li');
					emptyMessage.className = 'list-group-item bg-dark text-white';
					emptyMessage.textContent = 'Queue is empty';
					this.queueList.appendChild(emptyMessage);
					return;
				}

				this.queue.forEach((song, index) => {
					const li = document.createElement('li');
					li.className = `list-group-item bg-dark text-white d-flex align-items-center ${
						index === this.currentIndex ? 'active' : ''
					}`;

					const artistDisplay = song.artistIDs && song.artistIDs.length > 0
						? this.generateArtistLinks(song.artists, song.artistIDs)
						: song.artists;

					li.innerHTML = `<img src="${song.thumbnailName ? `${this.imageBasePath}${song.thumbnailName}` : '../images/defaultSong.webp'}"
									class="me-2" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
									<div class="flex-grow-1">
										<div class="text-truncate">${song.title}</div>
										<small style="color: rgb(200, 200, 200)">${artistDisplay}</small>
									</div>
									<button class="btn btn-sm text-danger" onclick="event.stopPropagation(); player.removeFromQueue(${index})">
										<i class="bi bi-x"></i>
									</button>`;

					li.onclick = () => this.playFromQueue(index);
					this.queueList.appendChild(li);
				});
			}

			updateProgress() {
				if (isNaN(this.audio.duration)) return;
				const progress = (this.audio.currentTime / this.audio.duration) * 100;
				this.progressBar.style.width = `${progress}%`;

				const minutes = Math.floor(this.audio.currentTime / 60);
				const seconds = Math.floor(this.audio.currentTime % 60).toString().padStart(2, '0');
				this.currentTimeEl.textContent = `${minutes}:${seconds}`;
			}

			updateTotalTime() {
				if (isNaN(this.audio.duration)) return;
				const minutes = Math.floor(this.audio.duration / 60);
				const seconds = Math.floor(this.audio.duration % 60).toString().padStart(2, '0');
				this.totalTimeEl.textContent = `${minutes}:${seconds}`;
			}

			seekTo(event) {
				const percent = event.offsetX / this.progressContainer.offsetWidth;
				this.audio.currentTime = percent * this.audio.duration;
			}

			toggleMute() {
				this.audio.muted = !this.audio.muted;
				this.updateVolumeIcon();
			}

			updateVolumeIcon() {
				const iconClass = this.audio.muted || this.audio.volume === 0 ? 'volume-mute' :
					this.audio.volume < 0.5 ? 'volume-down' : 'volume-up';
				this.volumeIcon.className = `bi bi-${iconClass} me-2`;
			}

			generateArtistLinks(artistsString, artistIDs) {
				if (!artistIDs || artistIDs.length === 0) {
					return artistsString;
				}

				const artists = artistsString.split(', ');
				const artistLinks = artists.map((artist, index) => {
					const artistID = artistIDs[index];
					if (artistID) {
						return `<a href="<?= $GLOBALS['PROJECT_ROOT'] ?>/view/artist.php?id=${artistID}" class="custom-link">${artist}</a>`;
					}
					return artist;
				});

				return artistLinks.join(', ');
			}
		}

		window.player = new MusicPlayer();
	});
</script>