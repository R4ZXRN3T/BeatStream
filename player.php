<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Music Player -->
<div id="musicPlayer" class="fixed-bottom bg-dark text-white p-2 d-none">
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
					<div class="progress w-100" style="height: 5px;">
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
					<i class="bi bi-volume-up me-2"></i>
					<input type="range" class="form-range" id="volumeControl" min="0" max="100" value="100"
						   style="width: 100px;">
				</div>
			</div>
		</div>
	</div>
	<audio id="audioPlayer" src=""></audio>
	<div id="queuePanel" class="position-absolute end-0 bottom-100 bg-dark p-3 d-none"
		 style="width: 300px; max-height: 400px; overflow-y: auto;">
		<h6 class="mb-3">Queue
			<button id="clearQueueBtn" class="btn btn-sm btn-outline-danger float-end">Clear</button>
		</h6>
		<ul id="queueList" class="list-group list-group-flush bg-transparent"></ul>
	</div>
</div>
<script>
	// Replace the existing song card click event listeners with this new version
	document.addEventListener('DOMContentLoaded', function () {
		// Get existing elements
		const musicPlayer = document.getElementById('musicPlayer');
		const audioPlayer = document.getElementById('audioPlayer');
		const playPauseBtn = document.getElementById('playPauseBtn');
		const progressBar = document.getElementById('progressBar');
		const volumeControl = document.getElementById('volumeControl');
		const playerCover = document.getElementById('playerCover');
		const playerTitle = document.getElementById('playerTitle');
		const playerArtist = document.getElementById('playerArtist');
		const currentTime = document.getElementById('currentTime');
		const totalTime = document.getElementById('totalTime');

		// Get new queue elements
		const prevBtn = document.getElementById('prevBtn');
		const nextBtn = document.getElementById('nextBtn');
		const queueBtn = document.getElementById('queueBtn');
		const queuePanel = document.getElementById('queuePanel');
		const queueList = document.getElementById('queueList');
		const clearQueueBtn = document.getElementById('clearQueueBtn');

		// Queue state
		let queue = [];
		let currentIndex = -1;
		let history = [];

		// Add click event listeners to all song cards
		const songCards = document.querySelectorAll('.card-body[data-song-id]');
		songCards.forEach(card => {
			card.addEventListener('click', function () {
				const clickedSongId = this.dataset.songId;

				// Collect all song IDs from recommended songs section
				const allSongIds = [];
				songCards.forEach(songCard => {
					allSongIds.push(songCard.dataset.songId);
				});

				// Clear current queue
				queue = [];
				currentIndex = -1;

				// Track which song to play first
				let songToPlayIndex = 0;

				// Add all songs to queue
				let songsLoaded = 0;
				allSongIds.forEach((songId, index) => {
					// Mark which song was clicked
					if (songId === clickedSongId) {
						songToPlayIndex = index;
					}

					fetch(`/BeatStream/api/getSong.php?id=${songId}`)
						.then(response => response.json())
						.then(data => {
							queue.push(data);
							songsLoaded++;

							// When all songs are loaded
							if (songsLoaded === allSongIds.length) {
								// Set current index to the clicked song
								currentIndex = songToPlayIndex;

								// Play the clicked song
								playSong(queue[currentIndex]);

								// Show the music player
								musicPlayer.classList.remove('d-none');

								// Update queue display
								updateQueueDisplay();
							}
						})
						.catch(error => {
							console.error('Error fetching song data:', error);
							songsLoaded++;
						});
				});
			});
		});

		// Function to play a song
		function playSong(song) {
			// Update the audio source
			audioPlayer.src = `/BeatStream/audio/${song.fileName}`;

			// Update player information
			playerTitle.textContent = song.title;
			playerArtist.textContent = song.artists;

			// Update cover image
			if (song.imageName) {
				playerCover.src = `/BeatStream/images/song/${song.imageName}`;
			} else {
				playerCover.src = '../images/defaultSong.webp';
			}

			// Play the song
			audioPlayer.play()
				.then(() => {
					// Update play button icon
					playPauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
				})
				.catch(error => {
					console.error('Error playing audio:', error);
				});
		}


		// Function to load and play a song by ID
		function loadSong(songId) {
			fetch(`/BeatStream/api/getSong.php?id=${songId}`)
				.then(response => response.json())
				.then(data => {
					addToQueue(songId, true);
				})
				.catch(error => {
					console.error('Error fetching song data:', error);
				});
		}

		// Update queue display
		function updateQueueDisplay() {
			queueList.innerHTML = '';

			queue.forEach((song, index) => {
				const li = document.createElement('li');
				li.className = 'list-group-item bg-dark text-white border-secondary d-flex align-items-center';
				if (index === currentIndex) {
					li.classList.add('active');
				}

				const img = document.createElement('img');
				img.src = song.imageName ? `/BeatStream/images/song/${song.imageName}` : '../images/defaultSong.webp';
				img.className = 'me-2';
				img.style = 'width: 30px; height: 30px; object-fit: cover;';

				const songInfo = document.createElement('div');
				songInfo.className = 'flex-grow-1';
				songInfo.innerHTML = `
        <div class="text-truncate">${song.title}</div>
        <small class="text-muted">${song.artists}</small>
      `;

				const removeBtn = document.createElement('button');
				removeBtn.className = 'btn btn-sm text-danger';
				removeBtn.innerHTML = '<i class="bi bi-x"></i>';
				removeBtn.onclick = (e) => {
					e.stopPropagation();
					removeFromQueue(index);
				};

				li.appendChild(img);
				li.appendChild(songInfo);
				li.appendChild(removeBtn);

				li.onclick = () => playFromQueue(index);

				queueList.appendChild(li);
			});
		}

		// Play a specific song from the queue
		function playFromQueue(index) {
			if (index >= 0 && index < queue.length) {
				// Add current song to history if one is playing
				if (currentIndex >= 0) {
					history.push(queue[currentIndex]);
				}

				currentIndex = index;
				playSong(queue[currentIndex]);
				updateQueueDisplay();
			}
		}

		// Remove a song from the queue
		function removeFromQueue(index) {
			if (index === currentIndex) {
				// If removing the currently playing song, play the next one
				playNext();
				queue.splice(index, 1);
				if (currentIndex >= index) {
					currentIndex--;
				}
			} else {
				queue.splice(index, 1);
				if (currentIndex > index) {
					currentIndex--;
				}
			}
			updateQueueDisplay();
		}

		// Play the next song in the queue
		function playNext() {
			if (queue.length > 0) {
				if (currentIndex >= 0) {
					history.push(queue[currentIndex]);
				}

				if (currentIndex < queue.length - 1) {
					currentIndex++;
					playSong(queue[currentIndex]);
				} else {
					// If at the end of the queue, reset to -1
					currentIndex = -1;
					audioPlayer.pause();
					playerTitle.textContent = 'No song selected';
					playerArtist.textContent = '';
					playerCover.src = '../images/defaultSong.webp';
				}

				updateQueueDisplay();
			}
		}

		// Play the previous song
		function playPrevious() {
			if (history.length > 0) {
				// Add current song back to queue if one is playing
				if (currentIndex >= 0) {
					queue.unshift(queue[currentIndex]);
					currentIndex = 0;
				}

				// Get the most recent song from history
				const previousSong = history.pop();
				queue.unshift(previousSong);
				currentIndex = 0;
				playSong(previousSong);
				updateQueueDisplay();
			}
		}

		// Event listeners for queue controls
		nextBtn.addEventListener('click', playNext);
		prevBtn.addEventListener('click', playPrevious);

		// Toggle queue panel
		queueBtn.addEventListener('click', function () {
			queuePanel.classList.toggle('d-none');
			updateQueueDisplay();
		});

		// Clear queue
		clearQueueBtn.addEventListener('click', function () {
			if (confirm('Are you sure you want to clear the queue?')) {
				queue = [];
				if (currentIndex >= 0) {
					// Keep the current song
					queue.push(audioPlayer.src ? {
						title: playerTitle.textContent,
						artists: playerArtist.textContent,
						fileName: audioPlayer.src.split('/').pop(),
						imageName: playerCover.src.includes('defaultSong') ? '' : playerCover.src.split('/').pop()
					} : null);
				}
				currentIndex = queue.length > 0 ? 0 : -1;
				updateQueueDisplay();
			}
		});

		// When song ends, play the next one
		audioPlayer.addEventListener('ended', playNext);

		// Play/Pause button functionality
		playPauseBtn.addEventListener('click', function () {
			if (audioPlayer.paused) {
				audioPlayer.play();
				this.innerHTML = '<i class="bi bi-pause-fill"></i>';
			} else {
				audioPlayer.pause();
				this.innerHTML = '<i class="bi bi-play-fill"></i>';
			}
		});

		// Update progress bar as song plays
		audioPlayer.addEventListener('timeupdate', function () {
			const progress = (audioPlayer.currentTime / audioPlayer.duration) * 100;
			progressBar.style.width = `${progress}%`;

			// Update current time display
			const minutes = Math.floor(audioPlayer.currentTime / 60);
			const seconds = Math.floor(audioPlayer.currentTime % 60).toString().padStart(2, '0');
			currentTime.textContent = `${minutes}:${seconds}`;
		});

		// Set total time when metadata is loaded
		audioPlayer.addEventListener('loadedmetadata', function () {
			const minutes = Math.floor(audioPlayer.duration / 60);
			const seconds = Math.floor(audioPlayer.duration % 60).toString().padStart(2, '0');
			totalTime.textContent = `${minutes}:${seconds}`;
		});

		// Volume control
		volumeControl.addEventListener('input', function () {
			audioPlayer.volume = this.value / 100;
		});

		// Click on progress bar to seek
		document.querySelector('.progress').addEventListener('click', function (e) {
			const percent = e.offsetX / this.offsetWidth;
			audioPlayer.currentTime = percent * audioPlayer.duration;
		});
	});
</script>