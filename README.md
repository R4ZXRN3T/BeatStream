# BeatStream

BeatStream is a web-based music streaming platform built with PHP. It enables users to discover, listen to, and manage
music collections including songs, albums, artists, and playlists. The platform supports user accounts, personalized
profiles, admin management, and rich search and discovery features.

## Features

- **User Accounts:** Sign up, log in, and manage personal profiles. Users can create playlists, mark favorite songs, and
  manage their music library.
- **Albums, Artists, and Songs:** Browse and search through albums, artists, and individual songs. Each entity features
  metadata, artwork, and relationships.
- **Playlists:** Users can create and manage playlists, add songs, view playlist details, and share playlists.
- **Admin Controls:** Admin users can add or edit albums, playlists, and perform user management tasks.
- **Search & Discovery:** Powerful search tools to find songs, albums, artists, or playlists. Discover recommended and
  trending music.
- **Image Support:** Album and playlist covers, artist images, and thumbnails are supported.
- **Music Playback:** Supports queueing and streaming music files.

## Directory Structure

- `account/`: User authentication, profile management, login, and signup pages.
- `admin/`: Admin-specific pages for adding/viewing albums and playlists.
- `controller`: Backend logic for handling requests and database interactions.
- `discover/`: Discover music by albums, artists, or songs.
- `search/`: Search functionality across all music entities.
- `Objects/`: PHP classes representing core entities like `Album`, `Artist`, `User`, and `Playlist`.
- `home/`: Main landing page and personalized recommendations.

## Entity Overview

- **User:** Represents a user account with fields for username, email, password, admin/artist status, and profile
  images.
- **Artist:** Metadata for artists including name, images, and active dates.
- **Album:** Collection of songs with name, images, artists, duration, and length.
- **Playlist:** User-created music collections with name, songs, creator, images, duration, and length.

## Getting Started

1. **Clone the repository:**
   ```bash
   git clone https://github.com/R4ZXRN3T/BeatStream.git
   ```

2. **Set up your web server:**  
   Ensure PHP and a MySQL database are available. Configure database credentials in `dbConnection.php`. Make sure you
   have ffmpeg installed on your server for converting uploaded audio. Please update the paths in `converter.php`. You
   will also need the ImageMagick extension for PHP to process the images for content upload.

3. **Configure file permissions:**  
   Ensure the `images/` and `audio/` upload directories are writable by the web server.

4. **Import database schema:**  
   Import any provided SQL files to create the necessary tables for users, songs, albums, artists, and playlists.

5. **Access BeatStream:**  
   Open your browser at your server URL (e.g., `http://localhost/BeatStream/`) and sign up/log in.

## Dependencies

- PHP 8.2.12+
- MySQL/MariaDB (MariaDB recommended, as development is done on MariaDB)
- Web server (Apache, Nginx, etc.)
- ffmpeg (for audio file conversion)
- ImageMagick (for image processing)
- Composer (if any dependencies are added for future development)

## Customization

- Album/artist/playlist images should be uploaded to the appropriate `images/` sub-directories.
- User/admin roles can be managed via the database or the admin interface.

## Contributing

Contributions, bug reports, and suggestions are welcome! Please fork the repository and open a pull request with your
changes.

## License

This project is licensed under the MIT License.

## Authors

Created by [R4ZXRN3T](https://github.com/R4ZXRN3T).