<?php

class Album
{
	private int $albumID;
	private string $name;
	private array $songIDs;
	private array $artists;
	private string $imageName;
	private int $length;
	private DateTime $duration;

	public function __construct(int $albumID, string $name, array $songIDs, array $artists, string $imageName, int $length, string $duration)
	{
		$this->albumID = $albumID;
		$this->name = $name;
		$this->songIDs = $songIDs;
		$this->artists = $artists;
		$this->imageName = $imageName;
		$this->length = $length;
		try {
			$this->duration = new DateTime($duration);
		} catch (Exception) {
		}
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

	public function getImageName(): string
	{
		return $this->imageName;
	}

	public function getLength(): int
	{
		return $this->length;
	}

	public function getDuration(): DateTime
	{
		return $this->duration;
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
}
