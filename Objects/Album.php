<?php

class Album
{
	private int $albumID;
	private string $name;
	private array $songIDs;
	private array $artists;
	private array $artistIDs;
	private string $imageName;
	private string $thumbnailName;
	private int $length;
	private int $duration;
	private DateTime $releaseDate; // Add releaseDate property
	private bool $isSingle;

	public function __construct(int $albumID, string $name, array $songIDs, array $artists, array $artistIds, string $imageName, string $thumbnailName, int $length, int $duration, string $releaseDate, bool $isSingle = false)
	{
		$this->albumID = $albumID;
		$this->name = $name;
		$this->songIDs = $songIDs;
		$this->artists = $artists;
		$this->artistIDs = $artistIds;
		$this->imageName = $imageName;
		$this->thumbnailName = $thumbnailName;
		$this->length = $length;
		$this->duration = $duration;
		$this->releaseDate = new DateTime($releaseDate); // Store as DateTime
		$this->isSingle = $isSingle;
	}

	// Getter Methods

	public function getName(): string
	{
		return $this->name;
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

	public function getImageName(): string
	{
		return $this->imageName;
	}

	public function getThumbnailName(): string
	{
		return $this->thumbnailName;
	}

	public function getLength(): int
	{
		return $this->length;
	}

	public function getDuration(): int
	{
		return $this->duration;
	}

	public function getFormattedDuration(): string
	{
		$seconds = intval($this->duration / 1000);
		$minutes = intval($seconds / 60);
		$remainingSeconds = $seconds % 60;

		return sprintf("%d:%02d", $minutes, $remainingSeconds);
	}

	public function getAlbumID(): int
	{
		return $this->albumID;
	}

	public function getSongIDs(): array
	{
		return $this->songIDs;
	}

	public function setSongIDs(array $songIDs): void
	{
		$this->songIDs = $songIDs;
	}

	public function addSongID($songID): void
	{
		if (!in_array($songID, $this->songIDs)) {
			$this->songIDs[] = $songID;
		}
	}

	public function getReleaseDate(): DateTime
	{
		return $this->releaseDate;
	}

	public function setReleaseDate(string $releaseDate): void
	{
		$this->releaseDate = new DateTime($releaseDate);
	}

	public function isSingle(): bool
	{
		return $this->isSingle;
	}

	public function setIsSingle(bool $isSingle): void
	{
		$this->isSingle = $isSingle;
	}
}
