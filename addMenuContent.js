// javascript
document.querySelectorAll('.song-card .song-menu-container').forEach(function (container) {
	const card = container.closest('.song-card .card-body');
	const parent = container.closest('.song-duration-menu-container') || container.parentElement;
	const songId = card.getAttribute('data-song-id');
	const projectRoot = '';

	const menuBtn = document.createElement('button');
	menuBtn.className = 'btn btn-light song-menu-btn';
	menuBtn.innerHTML = '&#x22EE;';

	const menu = document.createElement('div');
	menu.className = 'dropdown-menu song-dropdown-menu';
	menu.style.display = 'none';
	menu.innerHTML = `
		<button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_song.php?id=${songId}'; event.stopPropagation();">Download Audio</button>
		<div class="dropdown-divider"></div>
		<button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_image.php?id=${songId}&res=original&type=song'; event.stopPropagation();">Download Image (Original)</button>
		<button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_image.php?id=${songId}&res=large&type=song'; event.stopPropagation();">Download Image (Large)</button>
		<button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_image.php?id=${songId}&res=thumbnail&type=song'; event.stopPropagation();">Download Image (Thumbnail)</button>
	`;

	// Add "Add to Playlist…" button
	const divider = document.createElement('div');
	divider.className = 'dropdown-divider';
	const divider2 = document.createElement('div');
	divider2.className = 'dropdown-divider';
	const addBtn = document.createElement('button');
	addBtn.className = 'dropdown-item';
	addBtn.textContent = 'Add to Playlist…';
	addBtn.addEventListener('click', function (e) {
		e.stopPropagation();
		ensureAddToPlaylistModal(projectRoot);
		openAddToPlaylistModal(projectRoot, songId);
		// close the menu
		menu.style.display = 'none';
		container.classList.remove('menu-open');
		parent.classList.remove('menu-open');
	});

	menu.appendChild(divider);
	menu.appendChild(addBtn);
	menu.appendChild(divider2);

	const viewAlbumButton = document.createElement('button');
	viewAlbumButton.className = 'dropdown-item';
	viewAlbumButton.textContent = 'View Album';
	viewAlbumButton.addEventListener('click', async function (e) {
		e.stopPropagation();
		try {
			const res = await fetch(`${projectRoot}/api/get_song_album.php?id=${encodeURIComponent(songId)}`, {
				credentials: 'same-origin'
			});
			if (!res.ok) throw new Error('not found');

			const data = await res.json();
			if (!data || !data.albumID) throw new Error('invalid');

			// Optional: if you want to keep track index, you could add a hash or query param.
			// const target = `${projectRoot}/view/album.php?id=${encodeURIComponent(data.albumID)}#track-${data.index}`;
			window.location.href = `${projectRoot}/view/album.php?id=${encodeURIComponent(data.albumID)}`;
		} catch (_) {
			alert('Album not found for this song.');
		}
	});

	menu.appendChild(viewAlbumButton);

	menuBtn.addEventListener('click', function (e) {
		e.stopPropagation();
		const isOpen = menu.style.display === 'block';
		if (isOpen) {
			menu.style.display = 'none';
			container.classList.remove('menu-open');
			parent.classList.remove('menu-open');
		} else {
			menu.style.display = 'block';
			container.classList.add('menu-open');
			parent.classList.add('menu-open');
		}
	});

	// Prevent clicks inside the menu from bubbling up and closing it
	menu.addEventListener('click', function (e) {
		e.stopPropagation();
	});

	// Close only when clicking outside this parent container
	document.body.addEventListener('click', function (e) {
		if (!parent.contains(e.target)) {
			menu.style.display = 'none';
			container.classList.remove('menu-open');
			parent.classList.remove('menu-open');
		}
	});

	container.appendChild(menuBtn);
	container.appendChild(menu);
});

// Player-level three-dot menu, always visible near the queue button
(function initPlayerMenu() {
	const container = document.getElementById('playerMenuContainer');
	if (!container) return;

	const projectRoot = '';

	const menuBtn = document.createElement('button');
	menuBtn.className = 'btn btn-light song-menu-btn';
	menuBtn.innerHTML = '&#x22EE;';

	const menu = document.createElement('div');
	menu.className = 'dropdown-menu song-dropdown-menu';
	menu.style.display = 'none';

	function getCurrentSongId() {
		if (!window.player || !Array.isArray(window.player.queue)) return null;
		const idx = window.player.currentIndex;
		if (idx == null || idx < 0 || idx >= window.player.queue.length) return null;
		return window.player.queue[idx].songID;
	}

	function buildMenuItems() {
		const songId = getCurrentSongId();
		menu.innerHTML = '';

		if (!songId) {
			const disabled = document.createElement('div');
			disabled.className = 'dropdown-item text-muted';
			disabled.textContent = 'No song playing';
			menu.appendChild(disabled);
			return;
		}

		// Download buttons
		menu.innerHTML = `
			<button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_song.php?id=${songId}'; event.stopPropagation();">Download Audio</button>
			<div class="dropdown-divider"></div>
			<button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_image.php?id=${songId}&res=original&type=song'; event.stopPropagation();">Download Image (Original)</button>
			<button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_image.php?id=${songId}&res=large&type=song'; event.stopPropagation();">Download Image (Large)</button>
			<button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_image.php?id=${songId}&res=thumbnail&type=song'; event.stopPropagation();">Download Image (Thumbnail)</button>
		`;

		// Add to playlist
		const divider = document.createElement('div');
		divider.className = 'dropdown-divider';
		const divider2 = document.createElement('div');
		divider2.className = 'dropdown-divider';
		const addBtn = document.createElement('button');
		addBtn.className = 'dropdown-item';
		addBtn.textContent = 'Add to Playlist…';
		addBtn.addEventListener('click', function (e) {
			e.stopPropagation();
			ensureAddToPlaylistModal(projectRoot);
			openAddToPlaylistModal(projectRoot, songId);
			menu.style.display = 'none';
			container.classList.remove('menu-open');
		});

		menu.appendChild(divider);
		menu.appendChild(addBtn);
		menu.appendChild(divider2);

		// View album
		const viewAlbumButton = document.createElement('button');
		viewAlbumButton.className = 'dropdown-item';
		viewAlbumButton.textContent = 'View Album';
		viewAlbumButton.addEventListener('click', async function (e) {
			e.stopPropagation();
			try {
				const res = await fetch(`${projectRoot}/api/get_song_album.php?id=${encodeURIComponent(songId)}`, {
					credentials: 'same-origin'
				});
				if (!res.ok) throw new Error('not found');
				const data = await res.json();
				if (!data || !data.albumID) throw new Error('invalid');
				window.location.href = `${projectRoot}/view/album.php?id=${encodeURIComponent(data.albumID)}`;
			} catch (_) {
				alert('Album not found for this song.');
			}
		});

		menu.appendChild(viewAlbumButton);
	}

	menuBtn.addEventListener('click', function (e) {
		e.stopPropagation();
		const isOpen = menu.style.display === 'block';
		if (isOpen) {
			menu.style.display = 'none';
			container.classList.remove('menu-open');
		} else {
			buildMenuItems(); // rebuild with current song id
			menu.style.display = 'block';
			container.classList.add('menu-open');
		}
	});

	menu.addEventListener('click', function (e) {
		e.stopPropagation();
	});

	document.body.addEventListener('click', function (e) {
		if (!container.contains(e.target)) {
			menu.style.display = 'none';
			container.classList.remove('menu-open');
		}
	});

	container.appendChild(menuBtn);
	container.appendChild(menu);
})();


// ----- Modal helpers -----

function redirectToLogin(projectRoot) {
	const url = `${projectRoot}/account/login.php?redirect=${encodeURIComponent(location.href)}`;
	window.location.assign(url);
}

function ensureAddToPlaylistModal(projectRoot) {
	if (document.getElementById('addToPlaylistModal')) return;

	const modal = document.createElement('div');
	modal.id = 'addToPlaylistModal';
	modal.style.cssText = `
		position: fixed; inset: 0; display: none; align-items: center; justify-content: center;
		background: rgba(0,0,0,0.4); z-index: 1050;
	`;
	modal.innerHTML = `
		<div style="background:#787878; min-width:320px; max-width:90vw; border-radius:8px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,.2);">
			<div style="padding:12px 16px; font-weight:600; border-bottom:1px solid #eee;">Add to Playlist</div>
			<div style="padding:16px;">
				<div style="margin-bottom:12px;">
					<label for="playlistSelect" style="display:block; font-size:.9rem; margin-bottom:6px;">Choose playlist</label>
					<select id="playlistSelect" style="width:100%; padding:8px;"></select>
				</div>
				<div id="playlistError" style="color:#b00020; font-size:.9rem; display:none;"></div>
			</div>
			<div style="display:flex; gap:8px; justify-content:flex-end; padding:12px 16px; border-top:1px solid #eee;">
				<button id="cancelAddToPlaylistBtn" class="btn btn-light">Cancel</button>
				<button id="saveToPlaylistBtn" class="btn btn-primary">Add</button>
			</div>
		<input type="hidden" id="addToPlaylistSongId" />
		</div>
	`;
	document.body.appendChild(modal);

	// Wire buttons
	modal.querySelector('#cancelAddToPlaylistBtn').addEventListener('click', closeAddToPlaylistModal);
	modal.addEventListener('click', function (e) {
		if (e.target === modal) closeAddToPlaylistModal();
	});

	// Save action
	// In ensureAddToPlaylistModal(projectRoot) -> save button click handler:
	modal.querySelector('#saveToPlaylistBtn').addEventListener('click', async function () {
		const songId = document.getElementById('addToPlaylistSongId').value;
		const select = document.getElementById('playlistSelect');
		const playlistId = select.value;
		const err = document.getElementById('playlistError');
		err.style.display = 'none';
		err.textContent = '';

		if (!playlistId) {
			err.textContent = 'Please select a playlist.';
			err.style.display = 'block';
			return;
		}

		try {
			const res = await fetch(`${projectRoot}/api/add_song_to_playlist.php`, {
				method: 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				credentials: 'same-origin',
				body: new URLSearchParams({songID: songId, playlistID: playlistId})
			});

			if (res.status === 401) {
				redirectToLogin(projectRoot);
				return;
			}
			if (!res.ok) throw new Error('Request failed');

			const data = await res.json().catch(() => ({}));
			if (data && data.error) {
				err.textContent = data.error;
				err.style.display = 'block';
				return;
			}
			closeAddToPlaylistModal();
		} catch (_) {
			err.textContent = 'Failed to add to playlist.';
			err.style.display = 'block';
		}
	});
}

// In openAddToPlaylistModal(projectRoot, songId):
function openAddToPlaylistModal(projectRoot, songId) {
	const modal = document.getElementById('addToPlaylistModal');
	const select = document.getElementById('playlistSelect');
	const err = document.getElementById('playlistError');
	document.getElementById('addToPlaylistSongId').value = songId;
	err.style.display = 'none';
	err.textContent = '';
	select.innerHTML = '<option value="">Loading…</option>';
	modal.style.display = 'flex';

	fetch(`${projectRoot}/api/list_playlists.php`, {credentials: 'same-origin'})
		.then(r => {
			if (r.status === 401) {
				redirectToLogin(projectRoot);
				throw new Error('unauthorized');
			}
			if (!r.ok) throw new Error('bad');
			return r.json();
		})
		.then(list => {
			select.innerHTML = '';
			if (!Array.isArray(list) || list.length === 0) {
				select.innerHTML = '<option value="">No playlists found</option>';
				return;
			}
			for (const p of list) {
				const opt = document.createElement('option');
				opt.value = p.playlistID;
				opt.textContent = p.name;
				select.appendChild(opt);
			}
		})
		.catch(() => {
			// If not redirected already, show a friendly message
			if (select) select.innerHTML = '<option value="">Failed to load</option>';
		});
}

function closeAddToPlaylistModal() {
	const modal = document.getElementById('addToPlaylistModal');
	if (modal) modal.style.display = 'none';
}