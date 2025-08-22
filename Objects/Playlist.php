<?php

class Playlist
{
	private int $playlistID;
	private string $name;
	private array $songIDs;
	private DateTime $duration;
	private int $length;
	private string $imageName;
	private int $creatorID;

	public function __construct(int $playlistID, string $name, array $songIDs, string $duration, int $length, string $imageName, int $creatorID)
	{
		$this->playlistID = $playlistID;
		$this->imageName = $imageName;
		$this->name = $name;
		$this->songIDs = $songIDs;
		try {
			$this->duration = new DateTime($duration);
		} catch (Exception) {
		}
		$this->length = $length;
		$this->creatorID = $creatorID;
	}

	// Getter methods
	public function getPlaylistID(): int
	{
		return $this->playlistID;
	}

	public function getimageName(): string
	{
		return $this->imageName;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getSongIDs(): array
	{
		return $this->songIDs;
	}

	public function setSongIDs(array $songIDs): void
	{
		$this->songIDs = $songIDs;
	}

	public function getDuration(): DateTime
	{
		return $this->duration;
	}

	public function getLength(): int
	{
		return $this->length;
	}

	public function getCreatorID(): int
	{
		return $this->creatorID;
	}

	public function addSongID($songID): void
	{
		if (!in_array($songID, $this->songIDs)) {
			$this->songIDs[] = $songID;
		}
	}
}
