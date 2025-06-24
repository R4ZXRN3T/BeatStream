<?php

class Album
{
	private int $albumID;
	private string $name;
	private array $songIDs;
	private string $artists;
	private string $imageName;
	private int $length;
	private DateTime $duration;

	public function __construct(int $albumID, string $name, array $songIDs, string $artists, string $imageName, int $length, string $duration)
	{
		$this->albumID = $albumID;
		$this->name = $name;
		$this->songIDs = $songIDs;
		$this->artists = $artists;
		$this->imageName = $imageName;
		$this->length = $length;
		try {
			$this->duration = new DateTime($duration);
		} catch (DateMalformedStringException) {
		}
	}

	// Getter Methods

	public function getName(): string
	{
		return $this->name;
	}

	public function getArtists(): string
	{
		return $this->artists;
	}

	public function getimageName(): string
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

	public function addSongID($songID): void
	{
		if (!in_array($songID, $this->songIDs)) {
			$this->songIDs[] = $songID;
		}
	}
}
