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
<link rel="stylesheet" href="/BeatStream/mainStyle.css">
<link rel="stylesheet" href="/BeatStream/playerStyle.css">

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
					<i id="volumeIcon" class="bi bi-volume-up me-2"></i>
					<input type="range" class="form-range" id="volumeControl" min="0" max="100" value="100"
						   style="width: 100px;">
				</div>
			</div>
		</div>
	</div>
	<audio id="audioPlayer"></audio>
	<div id="queuePanel" class="position-absolute end-0 bottom-100 bg-dark p-3 d-none"
		 style="width: 600px; max-height: 400px; overflow-y: auto;">
		<h6 class="mb-3">Queue
			<button id="clearQueueBtn" class="btn btn-sm btn-outline-danger float-end">Clear</button>
		</h6>
		<ul id="queueList" class="list-group list-group-flush"></ul>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		class MusicPlayer {
			constructor() {
				this.basePath = '/BeatStream';
				this.audioBasePath = `${this.basePath}/audio/`;
				this.imageBasePath = `${this.basePath}/images/song/`;

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

				document.addEventListener('click', (e) => {
					if (!this.queuePanel.contains(e.target) && e.target !== this.queueBtn) {
						this.queuePanel.classList.add('d-none');
					}
				});
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

			loadQueueFromData(queueData, clickedSongId) {
				this.queue = queueData.map(song => ({
					songID: song.songID,
					title: song.title,
					artists: song.artists,
					fileName: song.fileName,
					imageName: song.imageName || ''
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
				this.playerArtist.textContent = song.artists;
				this.playerCover.src = song.imageName ?
					`${this.imageBasePath}${song.imageName}` :
					'../images/defaultSong.webp';

				this.audio.play().catch(error => console.error('Playback error:', error));

				document.dispatchEvent(new CustomEvent('songPlaying', {
					detail: {songId: song.songID}
				}));
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
				this.audio.paused ? this.audio.play() : this.audio.pause();
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
					this.playerCover.src = '/BeatStream/images/defaultSong.webp';
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
						this.playerCover.src = '/BeatStream/images/defaultSong.webp';
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

					li.innerHTML = `
                    <img src="${song.imageName ? `${this.imageBasePath}${song.imageName}` : '../images/defaultSong.webp'}"
                         class="me-2" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                    <div class="flex-grow-1">
                        <div class="text-truncate">${song.title}</div>
                        <small style="color: rgb(200, 200, 200)">${song.artists}</small>
                    </div>
                    <button class="btn btn-sm text-danger" onclick="event.stopPropagation(); player.removeFromQueue(${index})">
                        <i class="bi bi-x"></i>
                    </button>
                `;

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
		}

		window.player = new MusicPlayer();
	});
</script>