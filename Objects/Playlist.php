<?php

class Playlist
{
	private int $playlistID;
	private string $name;
	private array $songIDs = [];
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
		$this->duration = new DateTime($duration);
		$this->length = $length;
		$this->creatorID = $creatorID;
	}

	// Getter methods
	public function getPlaylistID()
	{
		return $this->playlistID;
	}

	public function setPlaylistID($playlistID)
	{
		$this->playlistID = $playlistID;
	}

	public function getimageName()
	{
		return $this->imageName;
	}

	public function setimageName($imageName)
	{
		$this->imageName = $imageName;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getSongIDs()
	{
		return $this->songIDs;
	}


	// Setter methods

	public function setSongIDs(array $songs)
	{
		$this->songIDs = $songs;
	}

	public function getDuration()
	{
		return $this->duration;
	}

	public function setDuration($duration)
	{
		$this->duration = $duration;
	}

	public function getLength()
	{
		return $this->length;
	}

	public function setLength($length)
	{
		$this->length = $length;
	}

	public function getCreatorID()
	{
		return $this->creatorID;
	}

	public function setCreatorID($creatorID)
	{
		$this->creatorID = $creatorID;
	}

	public function addSongID($songID)
	{
		if (!in_array($songID, $this->songIDs)) {
			$this->songIDs[] = $songID;
		}
	}
}
