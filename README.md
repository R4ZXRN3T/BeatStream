# BeatStream

BeatStream is a full-featured web application for music streaming and discovery. It allows users to listen to songs,
create and manage playlists, discover new music, and interact with a rich community of artists and listeners. The
project includes user account management, artist profiles, playlist creation, browsing and searching features, and a
robust music player UI.

## Features

- **User Accounts**: Sign up, login, manage profiles, and upload a profile picture.
- **Music Player**: Play, pause, skip tracks, control volume, view song progress, and manage playback queue.
- **Playlist Management**: Create, edit, and share playlists. View your own and others’ playlists.
- **Song & Album Discovery**: Explore recommended songs, search for music by title, artist, album, or playlist.
- **Artist Profiles**: Artists can create accounts, manage their content, and have special privileges.
- **Admin Panel**: Admin users can view, add, and manage songs, albums, playlists, and users.
- **Responsive UI**: Modern interface with dark mode toggle and mobile-friendly design.
- **Session Handling**: User sessions are managed for persistent login and personalized experiences.

## Getting Started

### Prerequisites

- PHP 7.4+
- MySQL or compatible database
- Web server (e.g., Apache, Nginx)
- Composer (for dependency management, if used)

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/R4ZXRN3T/BeatStream.git
   ```
2. Import the database schema from `/database/` (if included).
3. Configure your web server to serve the `/BeatStream` directory as the document root.
4. Update database credentials in the relevant PHP config files.
5. (Optional) Run `composer install` if there is a `composer.json`.

### Folder Structure

- `/account/` — Login, signup, profile management
- `/admin/` — Admin controls for users, songs, albums, playlists
- `/components/` — Reusable UI components (player, top bar, etc.)
- `/create/` — Content creation (playlists, uploads for artists)
- `/discover/`, `/search/`, `/view/` — Browse, discover, and search for music content
- `/home/` — Main landing page after login
- `/images/`, `/audio/` — Static assets (cover art, audio files)

## Usage

- Browse and discover music from the home page or discover section.
- Search for songs, artists, albums, or playlists using the search bar.
- Create playlists and add your favorite songs.
- Artists can upload their own music and manage their artist profile.
- Access admin features (if you are an admin) to manage site content.

## Technologies Used

- **PHP** — Server-side scripting
- **HTML, CSS (Bootstrap 5), JavaScript** — Front-end
- **MySQL** — Database
- **Session Management** — PHP sessions for user state
- **Responsive Design** — Mobile and desktop support

## Contributing

1. Fork the repository.
2. Create your feature branch (`git checkout -b feature/my-feature`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature/my-feature`).
5. Open a Pull Request.

## License

This project is licensed under the MIT License.

## Acknowledgements

Thanks to all contributors and users who help make BeatStream better!

---

**BeatStream**: Stream, discover, and create music your way.