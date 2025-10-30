document.querySelectorAll('.song-card .song-menu-container').forEach(function (container) {
	const card = container.closest('.song-card .card-body');
	const songId = card.getAttribute('data-song-id');
	const projectRoot = ''; // Set this variable globally

	// Create menu button
	const menuBtn = document.createElement('button');
	menuBtn.className = 'btn btn-light song-menu-btn';
	menuBtn.innerHTML = '&#x22EE;'; // Vertical ellipsis

	// Create menu dropdown
	const menu = document.createElement('div');
	menu.className = 'dropdown-menu song-dropdown-menu';
	menu.style.display = 'none';
	menu.innerHTML = `
    <button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_song.php?id=${songId}'; event.stopPropagation();">Download Audio</button>
    <button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_image.php?id=${songId}&res=original&type=song'; event.stopPropagation();">Download Image (Original)</button>
    <button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_image.php?id=${songId}&res=large&type=song'; event.stopPropagation();">Download Image (Large)</button>
    <button class="dropdown-item" onclick="window.location.href='${projectRoot}/api/download_image.php?id=${songId}&res=thumbnail&type=song'; event.stopPropagation();">Download Image (Thumbnail)</button>
  `;

	// Toggle menu visibility
	menuBtn.onclick = function (e) {
		e.stopPropagation();
		menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
	};
	document.body.addEventListener('click', function () {
		menu.style.display = 'none';
	});

	container.appendChild(menuBtn);
	container.appendChild(menu);
});