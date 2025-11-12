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

<div id="coverPanel" class="position-fixed d-none">
	<img id="playerCoverLarge" src="<?= $GLOBALS['PROJECT_ROOT'] ?>/images/defaultSong.webp" alt="Song Cover"
		 loading="lazy">
</div>

<!-- Music Player -->
<div id="musicPlayer" class="fixed-bottom bg-dark text-white p-2 d-none">
	<div class="container-fluid">
		<div class="row align-items-center">
			<!-- Song Info -->
			<div class="col-md-3 d-flex align-items-center">
				<img id="playerCoverSmall" src="<?= $GLOBALS['PROJECT_ROOT'] ?>/images/defaultSong.webp"
					 alt="Song Cover"
					 class="me-2"
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
					<i id="volumeIcon" class="bi bi-volume-up me-2"></i>
					<label for="volumeControl"></label>
					<input type="range" class="form-range" id="volumeControl" min="0"
						   max="100" value="100"
						   style="width: 100px;">
					<button id="killPlayerBtn" class="btn btn-sm btn-outline-danger me-2" title="Kill Player"
							style="margin-left: 20px;">
						<i class="bi bi-x-circle"></i>
					</button>
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

				<?php require_once $GLOBALS['PROJECT_ROOT_DIR'] . '/controller/SongController.php'; ?>

				this.originalTitle = document.title;
				this.basePath = '<?= $GLOBALS['PROJECT_ROOT'] ?>';
				this.audioBasePath = `${this.basePath}/audio/${localStorage.getItem('audioFormat')}/`;
				this.imageBasePath = `${this.basePath}/images/song/thumbnail/`;
				this.largeImagePath = `${this.basePath}/images/song/large/`;

				// Cached DOM
				this.playerUI = document.getElementById('musicPlayer');
				this.audio = document.getElementById('audioPlayer');
				this.playPauseBtn = document.getElementById('playPauseBtn');
				this.progressBar = document.getElementById('progressBar');
				this.progressContainer = document.querySelector('#musicPlayer .progress');
				this.volumeControl = document.getElementById('volumeControl');
				this.volumeIcon = document.getElementById('volumeIcon');
				this.playerCoverLarge = document.getElementById('playerCoverLarge');
				this.playerCoverSmall = document.getElementById('playerCoverSmall');
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
				this.coverPanel = document.getElementById('coverPanel');

				this.icons = {
					play: '<i class="bi bi-play-fill"></i>',
					pause: '<i class="bi bi-pause-fill"></i>',
					spinner: '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'
				};
				this.isLoading = false;
				this.retryCount = 0;
				this.maxRetries = 3;
				this.retryDelayMs = 2000;
				this._retryTimeout = null;
				this.currentSongSrc = '';

				this.queue = [];
				this.currentIndex = -1;

				// Throttle / save control
				this._lastQueueSave = 0;
				this._queueSaveIntervalMs = 20_000; // 20 seconds
				this._lastDurationSave = 0;
				this._durationSaveIntervalMs = 1000; // 1 second

				this.init();
			}

			init() {
				this.audio.volume = this.volumeControl.value / 100;
				this.playerUI.classList.add('d-none');
				this.setupEventListeners();
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
				this.audio.addEventListener('timeupdate', () => {
					this.updateProgress();
					const now = Date.now();
					if (now - this._lastQueueSave > this._queueSaveIntervalMs) {
						this.saveQueue();
						this._lastQueueSave = now;
					}

					if (now - this._lastDurationSave > this._durationSaveIntervalMs) {
						this.saveDuration();
						this._lastDurationSave = now;
					}
				});
				this.audio.addEventListener('loadedmetadata', () => this.updateTotalTime());
				// Loading state events
				this.audio.addEventListener('loadstart', () => this.setLoading(true));
				this.audio.addEventListener('waiting', () => this.setLoading(true));
				this.audio.addEventListener('stalled', () => this.setLoading(true));
				this.audio.addEventListener('canplay', () => this.setLoading(false));
				this.audio.addEventListener('canplaythrough', () => this.setLoading(false));
				this.audio.addEventListener('playing', () => this.setLoading(false));
				this.audio.addEventListener('error', () => this.handleAudioError());
				this.audio.addEventListener('play', () => {
					if (!this.isLoading) this.playPauseBtn.innerHTML = this.icons.pause;
				});
				this.audio.addEventListener('pause', () => {
					if (!this.isLoading) this.playPauseBtn.innerHTML = this.icons.play;
				});

				this.progressContainer.addEventListener('click', (e) => this.seekTo(e));
				this.volumeControl.addEventListener('input', () => {
					this.audio.volume = this.volumeControl.value / 100;
					this.updateVolumeIcon();
				});
				this.volumeIcon.addEventListener('click', () => this.toggleMute());
				this.killPlayerBtn.addEventListener('click', () => this.killPlayer());

				// Event delegation for song cards - handles dynamic content
				document.addEventListener('click', (e) => {
					const card = e.target.closest('.card-body[data-song-id]');
					if (card) {
						e.preventDefault();
						const songId = card.dataset.songId;
						try {
							if (card.dataset.songQueue) {
								this.loadQueueFromData(JSON.parse(card.dataset.songQueue), songId);
							} else {
								this.loadSingleSong(songId);
							}
						} catch (err) {
							this.loadSingleSong(songId);
						}
					} else {
						// Hide queue when clicking outside
						if (!this.queuePanel.contains(e.target) && e.target !== this.queueBtn) {
							this.queuePanel.classList.add('d-none');
						}
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

				// Save on unload to keep state consistent
				window.addEventListener('beforeunload', () => this.saveQueue());
				window.addEventListener('beforeunload', () => this.saveDuration());
			}

			saveQueue() {
				// Save minimal state only when needed
				try {
					const state = {
						queue: this.queue,
						currentIndex: this.currentIndex
					};
					localStorage.setItem('queueState', JSON.stringify(state));
				} catch (err) {
					// ignore quota errors
				}
			}

			saveDuration() {
				// Save minimal state only when needed
				try {
					const currentTime = this.audio.currentTime;
					localStorage.setItem('currentTime', JSON.stringify(currentTime));
				} catch (err) {
					// ignore quota errors
				}
			}

			restoreState() {
				try {
					const queueState = JSON.parse(localStorage.getItem('queueState'));
					const currentTime = JSON.parse(localStorage.getItem('currentTime'));
					if (queueState && queueState.queue && queueState.queue.length > 0) {
						this.queue = queueState.queue;
						this.currentIndex = Math.min(Math.max(queueState.currentIndex || 0, 0), this.queue.length - 1);
						this.playerUI.classList.remove('d-none');
						this.updateQueueDisplay();
						this.playSong(this.queue[this.currentIndex]);
						this.audio.addEventListener('loadedmetadata', () => {
							this.audio.currentTime = currentTime || 0;
						}, {once: true});
					}
				} catch (err) {
					// ignore invalid state
				}
			}

			killPlayer() {
				this.audio.pause();
				this.audio.src = '';
				// reset loading/retry state
				this.setLoading(false);
				clearTimeout(this._retryTimeout);
				this.retryCount = 0;
				this.playerUI.classList.add('d-none');
				this.queue = [];
				this.currentIndex = -1;
				this.updateQueueDisplay();
				this.playerCoverLarge.classList.add('d-none');
				this.coverPanel.classList.add('d-none');
				document.title = this.originalTitle;
				localStorage.removeItem('playerState');
			}

			loadQueueFromData(queueData, clickedSongId) {
				this.audioBasePath = `${this.basePath}/audio/${localStorage.getItem('audioFormat')}/`;
				this.queue = queueData.map(song => ({
					songID: song.songID,
					title: song.title,
					artists: song.artists,
					artistIDs: song.artistIDs || [],
					fileName: (localStorage.getItem('audioFormat') === 'flac' ? song.flacFilename : song.opusFilename),
					thumbnailName: song.thumbnailName || '',
					imageName: song.imageName || ''
				}));

				this.currentIndex = this.queue.findIndex(s => String(s.songID) === String(clickedSongId));
				if (this.currentIndex < 0) this.currentIndex = 0;
				if (this.queue.length > 0) {
					this.playSong(this.queue[this.currentIndex]);
					this.playerUI.classList.remove('d-none');
					this.updateQueueDisplay();
				}
			}

			loadSingleSong(songId) {
				// load single song as a one-item queue via fetch or data attributes
				// keep simple: try to find matching card data-song-json if available
				const card = document.querySelector(`.card-body[data-song-id="${songId}"]`);
				if (card && card.dataset.songData) {
					try {
						const song = JSON.parse(card.dataset.songData);
						this.queue = [{
							songID: song.songID,
							title: song.title,
							artists: song.artists,
							artistIDs: song.artistIDs || [],
							fileName: (localStorage.getItem('audioFormat') === 'flac' ? song.flacFilename : song.opusFilename),
							thumbnailName: song.thumbnailName || '',
							imageName: song.imageName || ''
						}];
						this.currentIndex = 0;
						this.playSong(this.queue[0]);
						this.playerUI.classList.remove('d-none');
						this.updateQueueDisplay();
						return;
					} catch (err) { /* fallthrough */
					}
				}
				// fallback: set index and call playNext which will no-op if not found
				this.currentIndex = -1;
			}

			playSong(song) {
				if (!song) return;
				// start loading state and reset retry
				clearTimeout(this._retryTimeout);
				this.setLoading(true);
				this.retryCount = 0;
				this.audio.src = `${this.audioBasePath}${song.fileName}`;
				this.currentSongSrc = this.audio.src;
				this.playerTitle.textContent = song.title;
				if (song.artistIDs && song.artistIDs.length > 0) {
					this.playerArtist.innerHTML = this.generateArtistLinks(song.artists, song.artistIDs);
				} else {
					this.playerArtist.textContent = song.artists;
				}

				this.playerCoverLarge.src = song.imageName ? `${this.largeImagePath}${song.imageName}` : `${this.basePath}/images/defaultSong.webp`;
				this.playerCoverSmall.src = song.thumbnailName ? `${this.imageBasePath}${song.thumbnailName}` : `${this.basePath}/images/defaultSong.webp`;

				this.audio.play().catch(() => { /* ignore play errors */
				});

				if ('mediaSession' in navigator) {
					navigator.mediaSession.metadata = new MediaMetadata({
						title: song.title,
						artist: song.artists,
						album: fetch('<?= $GLOBALS['PROJECT_ROOT'] ?>/api/get_song_album.php?id=' + song.songID)
							.then(response => response.json())
							.then(data => data.albumName || '')
							.catch(() => ''),
						artwork: [{src: song.imageName ? `${location.origin}<?= $GLOBALS['PROJECT_ROOT'] ?>/images/song/large/${song.imageName}` : `${location.origin}<?= $GLOBALS['PROJECT_ROOT'] ?>/images/defaultSong.webp`}]
					})
					;
					navigator.mediaSession.setActionHandler('play', () => this.togglePlayPause());
					navigator.mediaSession.setActionHandler('pause', () => this.togglePlayPause());
					navigator.mediaSession.setActionHandler('previoustrack', () => this.playPrevious());
					navigator.mediaSession.setActionHandler('nexttrack', () => this.playNext());
				}

				document.dispatchEvent(new CustomEvent('songPlaying', {detail: {songId: song.songID}}));
				this.updateCurrentlyPlaying(song.songID);

				document.title = `▶ ${song.title} by ${song.artists} - BeatStream`;
				this.saveDuration();
				this.saveQueue();

				if (this.coverPanel) {
					this.coverPanel.classList.remove('d-none');
					this.playerCoverLarge.classList.remove('d-none');
				}
			}

			playFromQueue(index) {
				if (index >= 0 && index < this.queue.length) {
					this.currentIndex = index;
					this.playSong(this.queue[index]);
					this.updateQueueDisplay();
				}
			}

			togglePlayPause() {
				// disable toggling while loading
				if (this.isLoading) return;
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
				this.saveDuration();
			}

			playNext() {
				if (this.queue.length === 0) return;
				// If current track hasn't loaded yet, try to load it instead of skipping
				if (this.isLoading || this.audio.readyState < 2) {
					this.retryLoadCurrentSong();
					return;
				}
				if (this.currentIndex < this.queue.length - 1) {
					this.currentIndex++;
					this.playSong(this.queue[this.currentIndex]);
				} else {
					this.audio.pause();
					this.playerTitle.textContent = 'End of queue';
					this.playerArtist.textContent = 'Play again or add more songs';
					this.playerCoverSmall.src = `${this.basePath}/images/defaultSong.webp`;
					this.playerCoverLarge.classList.add('d-none');
				}
				this.updateQueueDisplay();
				this.saveQueue();
				this.saveDuration();
			}

			playPrevious() {
				if (this.queue.length === 0) return;
				if (this.audio.currentTime > 3) {
					this.audio.currentTime = 0;
					return;
				}
				if (this.currentIndex > 0) {
					this.currentIndex--;
					this.playSong(this.queue[this.currentIndex]);
					this.updateQueueDisplay();
					this.saveDuration();
					this.saveQueue();
				}
			}

			removeFromQueue(index) {
				if (index < 0 || index >= this.queue.length) return;
				const removingCurrent = index === this.currentIndex;
				this.queue.splice(index, 1);
				if (removingCurrent) {
					if (this.queue.length === 0) {
						this.currentIndex = -1;
						this.audio.pause();
						this.playerTitle.textContent = 'No song selected';
						this.playerArtist.textContent = '';
						this.playerCoverLarge.classList.add('d-none');
						this.playerCoverSmall.src = `${this.basePath}/images/defaultSong.webp`;
					} else {
						if (this.currentIndex >= this.queue.length) this.currentIndex = 0;
						this.playSong(this.queue[this.currentIndex]);
					}
				} else if (index < this.currentIndex) {
					this.currentIndex--;
				}
				this.updateQueueDisplay();
				this.saveQueue();
				this.saveDuration();
			}

			clearQueue() {
				if (!confirm('Clear queue?')) return;
				const currentSong = this.currentIndex >= 0 ? this.queue[this.currentIndex] : null;
				this.queue = currentSong ? [currentSong] : [];
				this.currentIndex = currentSong ? 0 : -1;
				this.updateQueueDisplay();
				this.saveQueue();
				this.saveDuration();
			}

			updateQueueDisplay() {
				this.queueList.innerHTML = '';
				if (this.queue.length === 0) {
					const emptyMessage = document.createElement('li');
					emptyMessage.className = 'list-group-item bg-dark text-white';
					emptyMessage.textContent = 'Queue is empty';
					this.queueList.appendChild(emptyMessage);
					this.updateCurrentlyPlaying(null);
					return;
				}

				this.queue.forEach((song, index) => {
					const li = document.createElement('li');
					li.className = `list-group-item bg-dark text-white d-flex align-items-center ${index === this.currentIndex ? 'active' : ''}`;

					const img = document.createElement('img');
					img.src = song.thumbnailName ? `${this.imageBasePath}${song.thumbnailName}` : `${this.basePath}/images/defaultSong.webp`;
					img.className = 'me-2';
					img.style.cssText = 'width:50px;height:50px;object-fit:cover;border-radius:5px;';
					img.alt = song.title;

					const info = document.createElement('div');
					info.className = 'flex-grow-1';
					const titleDiv = document.createElement('div');
					titleDiv.className = 'text-truncate';
					titleDiv.textContent = song.title;
					const small = document.createElement('small');
					small.style.color = 'rgb(200,200,200)';
					small.innerHTML = (song.artistIDs && song.artistIDs.length > 0) ? this.generateArtistLinks(song.artists, song.artistIDs) : song.artists;

					info.appendChild(titleDiv);
					info.appendChild(small);

					const removeBtn = document.createElement('button');
					removeBtn.className = 'btn btn-sm text-danger';
					removeBtn.innerHTML = '<i class="bi bi-x"></i>';
					removeBtn.addEventListener('click', (e) => {
						e.stopPropagation();
						this.removeFromQueue(index);
					});

					li.appendChild(img);
					li.appendChild(info);
					li.appendChild(removeBtn);

					li.addEventListener('click', () => this.playFromQueue(index));
					this.queueList.appendChild(li);
				});

				this.updateCurrentlyPlaying(this.currentIndex >= 0 ? this.queue[this.currentIndex].songID : null);
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
				if (isNaN(this.audio.duration)) return;
				const rect = this.progressContainer.getBoundingClientRect();
				const percent = Math.min(Math.max((event.clientX - rect.left) / rect.width, 0), 1);
				this.audio.currentTime = percent * this.audio.duration;
				this.saveDuration();
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
				if (!artistIDs || artistIDs.length === 0) return artistsString;
				const artists = artistsString.split(', ');
				return artists.map((artist, idx) => {
					const id = artistIDs[idx];
					return id ? `<a href="${this.basePath}/view/artist.php?id=${id}" class="custom-link">${artist}</a>` : artist;
				}).join(', ');
			}

			updateCurrentlyPlaying(songId) {
				document.querySelectorAll('.card-body[data-song-id]').forEach(card => {
					card.classList.toggle('playing', String(card.dataset.songId) === String(songId));
				});
			}

			// --- Loading / Retry helpers ---
			setLoading(isLoading) {
				this.isLoading = isLoading;
				if (isLoading) {
					this.playPauseBtn.innerHTML = this.icons.spinner;
				} else {
					// clear any pending retry when playback becomes possible
					clearTimeout(this._retryTimeout);
					this.playPauseBtn.innerHTML = this.audio.paused ? this.icons.play : this.icons.pause;
				}
			}

			handleAudioError() {
				this.retryLoadCurrentSong();
			}

			retryLoadCurrentSong() {
				if (this.currentIndex < 0 || !this.queue[this.currentIndex]) return;
				if (this.retryCount >= this.maxRetries) {
					this.setLoading(false);
					return;
				}
				this.retryCount++;
				this.setLoading(true);
				clearTimeout(this._retryTimeout);
				this._retryTimeout = setTimeout(() => {
					const song = this.queue[this.currentIndex];
					// Bust cache just in case
					this.audio.src = `${this.audioBasePath}${song.fileName}?r=${Date.now()}`;
					this.currentSongSrc = this.audio.src;
					this.audio.load();
					this.audio.play().catch(() => {
					});
				}, this.retryDelayMs);
			}
		}

		window.player = new MusicPlayer();
	});
</script>