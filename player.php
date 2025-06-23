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

<!-- Music Player -->
<div id="musicPlayer" class="fixed-bottom bg-dark text-white p-2">
	<div class="container-fluid">
		<div class="row align-items-center">
			<!-- Song Info -->
			<div class="col-md-3 d-flex align-items-center">
				<img id="playerCover" src="../images/defaultSong.webp" alt="Song Cover" class="me-2"
					 style="width: 50px; height: 50px; object-fit: cover;">
				<div>
					<h6 id="playerTitle" class="mb-0">No song selected</h6>
					<small id="playerArtist" class="text">Unknown artist</small>
				</div>
			</div>

			<!-- Player Controls -->
			<div class="col-md-6 text-center">
				<div class="d-flex flex-column align-items-center">
					<div class="controls d-flex align-items-center justify-content-center mb-1">
						<button id="prevBtn" class="btn btn-sm btn-outline-light rounded-circle mx-2"><i
									class="bi bi-skip-backward-fill"></i></button>
						<button id="playPauseBtn" class="btn btn-sm btn-primary rounded-circle mx-2"><i
									class="bi bi-play-fill"></i></button>
						<button id="nextBtn" class="btn btn-sm btn-outline-light rounded-circle mx-2"><i
									class="bi bi-skip-forward-fill"></i></button>
						<button id="queueBtn" class="btn btn-sm btn-outline-light ms-3"><i
									class="bi bi-music-note-list"></i></button>
					</div>

					<!-- Progress Bar -->
					<div class="progress w-100" style="height: 5px; cursor: pointer;">
						<div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
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
		 style="width: 300px; max-height: 400px; overflow-y: auto;">
		<h6 class="mb-3">Queue
			<button id="clearQueueBtn" class="btn btn-sm btn-outline-danger float-end">Clear</button>
		</h6>
		<ul id="queueList" class="list-group list-group-flush bg-transparent"></ul>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		class MusicPlayer {
			constructor() {
				// Base paths
				this.basePath = '/BeatStream';
				this.audioBasePath = `${this.basePath}/audio/`;
				this.imageBasePath = `${this.basePath}/images/song/`;

				// Player elements
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

				// Queue elements
				this.prevBtn = document.getElementById('prevBtn');
				this.nextBtn = document.getElementById('nextBtn');
				this.queueBtn = document.getElementById('queueBtn');
				this.queuePanel = document.getElementById('queuePanel');
				this.queueList = document.getElementById('queueList');
				this.clearQueueBtn = document.getElementById('clearQueueBtn');

				// Queue state
				this.queue = [];
				this.currentIndex = -1;
				this.history = [];

				// Initialize the player
				this.init();
			}

			init() {
				// Set initial volume
				this.audio.volume = this.volumeControl.value / 100;

				// Hide player initially
				this.playerUI.classList.add('d-none');

				// Initialize all event listeners
				this.setupEventListeners();
				this.setupSongCardListeners();

				// Listen for custom events
				document.addEventListener('playSingleSong', (e) => {
					this.loadSingleSong(e.detail.songId);
				});
			}

			setupEventListeners() {
				// Player controls
				this.playPauseBtn.addEventListener('click', () => this.togglePlayPause());
				this.nextBtn.addEventListener('click', () => this.playNext());
				this.prevBtn.addEventListener('click', () => this.playPrevious());

				// Queue controls
				this.queueBtn.addEventListener('click', (e) => {
					e.stopPropagation(); // Stop event from bubbling up to document
					this.queuePanel.classList.toggle('d-none');
					this.updateQueueDisplay();
				});

				this.clearQueueBtn.addEventListener('click', () => this.clearQueue());

				// Audio events
				this.audio.addEventListener('ended', () => this.playNext());
				this.audio.addEventListener('timeupdate', () => this.updateProgress());
				this.audio.addEventListener('loadedmetadata', () => this.updateTotalTime());
				this.audio.addEventListener('play', () => {
					this.playPauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
				});
				this.audio.addEventListener('pause', () => {
					this.playPauseBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
				});

				// Progress bar
				this.progressContainer.addEventListener('click', (e) => this.seekTo(e));

				// Volume control
				this.volumeControl.addEventListener('input', () => {
					this.audio.volume = this.volumeControl.value / 100;
					this.updateVolumeIcon();
				});

				this.volumeIcon.addEventListener('click', () => this.toggleMute());

				// Close queue panel when clicking outside
				document.addEventListener('click', (e) => {
					if (!this.queuePanel.contains(e.target) && e.target !== this.queueBtn) {
						this.queuePanel.classList.add('d-none');
					}
				});
			}

			setupSongCardListeners() {
				const songCards = document.querySelectorAll('.card-body[data-song-id]');
				songCards.forEach(card => {
					card.addEventListener('click', () => {
						const songId = card.dataset.songId;

						try {
							if (card.dataset.songQueue) {
								const queueData = JSON.parse(card.dataset.songQueue);
								this.loadQueueFromData(queueData, songId);
							} else {
								this.loadSingleSong(songId);
							}
						} catch (error) {
							console.error('Error processing song data:', error);
							this.loadSingleSong(songId);
						}
					});
				});
			}

			loadQueueFromData(queueData, clickedSongId) {
				// Reset queue
				this.queue = [];
				this.currentIndex = -1;

				// Process queue data
				queueData.forEach(song => {
					this.queue.push({
						songID: song.songID,
						title: song.title,
						artists: song.artists,
						fileName: song.fileName,
						imageName: song.imageName || ''
					});

					// Convert both to strings for comparison
					if (String(song.songID) === String(clickedSongId)) {
						this.currentIndex = this.queue.length - 1;
					}
				});

				// Play the selected song
				if (this.currentIndex >= 0) {
					this.playSong(this.queue[this.currentIndex]);
					this.playerUI.classList.remove('d-none');
					this.updateQueueDisplay();
				}
			}

			loadSingleSong(songId) {
				fetch(`${this.basePath}/api/getSong.php?id=${songId}`)
					.then(response => {
						if (!response.ok) {
							throw new Error(`Network response was not ok: ${response.status}`);
						}
						return response.json();
					})
					.then(data => {
						this.queue = [data];
						this.currentIndex = 0;
						this.playSong(data);
						this.playerUI.classList.remove('d-none');
						this.updateQueueDisplay();
					})
					.catch(error => {
						console.error('Error fetching song data:', error);
						alert('Failed to load song. Please try again.');
					});
			}

			playSong(song) {
				if (!song) return;

				// Update audio source
				const audioPath = `${this.audioBasePath}${song.fileName}`;
				this.audio.src = audioPath;

				// Update player information
				this.playerTitle.textContent = song.title;
				this.playerArtist.textContent = song.artists;

				// Update cover image
				if (song.imageName) {
					this.playerCover.src = `${this.imageBasePath}${song.imageName}`;
				} else {
					this.playerCover.src = '../images/defaultSong.webp';
				}

				// Play the song
				const playPromise = this.audio.play();

				if (playPromise !== undefined) {
					playPromise
						.then(() => {
							// Playback started successfully
						})
						.catch(error => {
							console.error('Error playing audio:', error);
							this.playPauseBtn.innerHTML = '<i class="bi bi-play-fill"></i>';

							// Handle autoplay policy
							if (error.name === 'NotAllowedError') {
								// User needs to interact first, so we'll wait for user to click play
							}
						});
				}
				document.dispatchEvent(new CustomEvent('songPlaying', {
					detail: {songId: song.songID}
				}));
			}

			togglePlayPause() {
				if (this.currentIndex < 0 && this.queue.length > 0) {
					this.currentIndex = 0;
					this.playSong(this.queue[0]);
					return;
				}

				if (this.audio.paused) {
					this.audio.play();
				} else {
					this.audio.pause();
				}
			}

			playNext() {
				if (this.queue.length === 0) return;

				// Add current song to history if one is playing
				if (this.currentIndex >= 0) {
					this.history.push(this.queue[this.currentIndex]);
				}

				if (this.currentIndex < this.queue.length - 1) {
					this.currentIndex++;
					this.playSong(this.queue[this.currentIndex]);
				} else {
					// End of queue
					this.audio.pause();
					this.currentIndex = -1;
					this.playerTitle.textContent = 'End of queue';
					this.playerArtist.textContent = 'Play again or add more songs';
				}

				this.updateQueueDisplay();
			}

			playPrevious() {
				if (this.audio.currentTime > 3) {
					// If current song has played more than 3 seconds, restart it
					this.audio.currentTime = 0;
					return;
				}

				if (this.history.length > 0) {
					// Get previous song from history
					const prevSong = this.history.pop();

					// Add the previous song to the queue and update index
					if (this.currentIndex >= 0) {
						// Insert the previous song at the current position
						this.queue.splice(this.currentIndex, 0, prevSong);
						// No need to change currentIndex since the song was inserted at that position
						this.playSong(prevSong);
					} else {
						// If no song is currently playing
						this.queue.unshift(prevSong);
						this.currentIndex = 0;
						this.playSong(prevSong);
					}

					this.updateQueueDisplay();
				}
			}

			playFromQueue(index) {
				if (index >= 0 && index < this.queue.length) {
					// Add current song to history if one is playing
					if (this.currentIndex >= 0) {
						this.history.push(this.queue[this.currentIndex]);
					}

					this.currentIndex = index;
					this.playSong(this.queue[index]);
					this.updateQueueDisplay();
				}
			}

			removeFromQueue(index) {
				if (index === this.currentIndex) {
					// If removing currently playing song
					if (this.queue.length > 1) {
						// Play next song if available
						this.queue.splice(index, 1);
						if (index >= this.queue.length) {
							this.currentIndex = this.queue.length - 1;
						}
						this.playSong(this.queue[this.currentIndex]);
					} else {
						// No more songs
						this.queue = [];
						this.currentIndex = -1;
						this.audio.pause();
						this.playerTitle.textContent = 'No song selected';
						this.playerArtist.textContent = 'Unknown artist';
						this.playerCover.src = '../images/defaultSong.webp';
					}
				} else {
					// Remove song and adjust current index if needed
					this.queue.splice(index, 1);
					if (index < this.currentIndex) {
						this.currentIndex--;
					}
				}

				this.updateQueueDisplay();
			}

			clearQueue() {
				if (confirm('Are you sure you want to clear the queue?')) {
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
					emptyMessage.className = 'list-group-item bg-dark text-white border-secondary';
					emptyMessage.textContent = 'Queue is empty';
					this.queueList.appendChild(emptyMessage);
					return;
				}

				this.queue.forEach((song, index) => {
					const li = document.createElement('li');
					li.className = 'list-group-item bg-dark text-white border-secondary d-flex align-items-center';
					if (index === this.currentIndex) {
						li.classList.add('active', 'bg-primary', 'bg-opacity-50');
					}

					// Create song image
					const img = document.createElement('img');
					img.src = song.imageName ? `${this.imageBasePath}${song.imageName}` : '../images/defaultSong.webp';
					img.alt = song.title;
					img.className = 'me-2';
					img.style = 'width: 30px; height: 30px; object-fit: cover;';

					// Create song info
					const songInfo = document.createElement('div');
					songInfo.className = 'flex-grow-1';
					songInfo.innerHTML = `<div class="text-truncate">${song.title}</div><small class="text-muted">${song.artists}</small>`;

					// Create remove button
					const removeBtn = document.createElement('button');
					removeBtn.className = 'btn btn-sm text-danger';
					removeBtn.innerHTML = '<i class="bi bi-x"></i>';
					removeBtn.onclick = (e) => {
						e.stopPropagation();
						this.removeFromQueue(index);
					};

					// Add elements to list item
					li.appendChild(img);
					li.appendChild(songInfo);
					li.appendChild(removeBtn);

					// Add click event to play this song
					li.onclick = () => this.playFromQueue(index);

					// Add to queue list
					this.queueList.appendChild(li);
				});
			}

			updateProgress() {
				if (isNaN(this.audio.duration)) return;

				const progress = (this.audio.currentTime / this.audio.duration) * 100;
				this.progressBar.style.width = `${progress}%`;

				// Update current time display
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
				if (this.audio.muted || this.audio.volume === 0) {
					this.volumeIcon.className = 'bi bi-volume-mute me-2';
				} else if (this.audio.volume < 0.5) {
					this.volumeIcon.className = 'bi bi-volume-down me-2';
				} else {
					this.volumeIcon.className = 'bi bi-volume-up me-2';
				}
			}
		}

		// Initialize the music player
		const player = new MusicPlayer();
	});
</script>