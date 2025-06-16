const audio = document.getElementById('audio');
const playlist = document.getElementById('playlist');
const items = playlist.getElementsByTagName('li');

function playSong(item) {
    for (let li of items) li.classList.remove('active');
    item.classList.add('active');
    audio.src = item.getAttribute('data-src');
    audio.play();
}

for (let item of items) {
    item.addEventListener('click', () => playSong(item));
}

// Auto play next song
audio.addEventListener('ended', () => {
    let current = Array.from(items).findIndex(li => li.classList.contains('active'));
    let next = (current + 1) % items.length;
    playSong(items[next]);
});

// Play first song on load
if (items.length) playSong(items[0]);