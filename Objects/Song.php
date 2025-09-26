<?php

class Song
{
	private int $songID;
	private string $title;
	private array $artists;
	private array $artistIDs;
	private string $genre;
	private DateTime $releaseDate;
	private int $songLength;
	private string $flacFileName;
	private string $opusFileName;
	private string $imageName;
	private string $thumbnailName;

	function __construct(int $songID, string $title, array $artists, array $artistIDs, string $genre, string $releaseDate, int $songLength, string $flacFileName, $opusFileName, string $imageName, string $thumbnailName)
	{
		$this->songID = $songID;
		$this->title = $title;
		$this->artists = $artists;
		$this->artistIDs = $artistIDs;
		$this->genre = $genre;
		try {
			$this->releaseDate = new DateTime($releaseDate);
		} catch (Exception) {
			$this->releaseDate = new DateTime();
		}
		$this->songLength = $songLength;
		$this->flacFileName = $flacFileName;
		$this->opusFileName = $opusFileName;
		$this->imageName = $imageName;
		$this->thumbnailName = $thumbnailName;
	}

	// getter methods

	public function getSongID(): int
	{
		return $this->songID;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function getArtists(): array
	{
		return $this->artists;
	}

	public function setArtists(array $artists): void
	{
		$this->artists = $artists;
	}

	public function getArtistIDs(): array
	{
		return $this->artistIDs;
	}

	public function setArtistIDs(array $artistIDs): void
	{
		$this->artistIDs = $artistIDs;
	}

	public function getGenre(): string
	{
		return $this->genre;
	}

	public function getReleaseDate(): DateTime
	{
		return $this->releaseDate;
	}

	public function getSongLength(): int
	{
		return $this->songLength;
	}

	public function getFormattedDuration(): string
	{
		$seconds = intval($this->songLength / 1000);
		$minutes = intval($seconds / 60);
		$hours = floor($minutes / 60);
		$remainingSeconds = $seconds % 60;
		$remainingMinutes = $minutes % 60;

		return $hours >= 1 ? sprintf("%dh %02d min %02 sec", $hours, $remainingMinutes, $remainingSeconds) : sprintf("%d:%02d", $minutes, $remainingSeconds);
	}

	public function getFlacFileName(): string
	{
		return $this->flacFileName;
	}

	public function getOpusFileName(): string
	{
		return $this->opusFileName;
	}

	public function getImageName(): string
	{
		return $this->imageName;
	}

	public function getThumbnailName(): string
	{
		return $this->thumbnailName;
	}
}
