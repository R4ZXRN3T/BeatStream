<?php

class Playlist
{
	private int $playlistID;
	private string $name;
	private array $songIDs = [];
	private DateTime $duration;
	private int $length;
	private string$imagePath;
	private int $creatorID;

	public function __construct(int $playlistID, string $name, array $songIDs, string $duration, int $length, string $imagePath, int $creatorID)
	{
		$this->playlistID = $playlistID;
		$this->imagePath = $imagePath;
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

	public function getImagePath()
	{
		return $this->imagePath;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getSongIDs()
	{
		return $this->songIDs;
	}

	public function getDuration()
	{
		return $this->duration;
	}

	public function getLength()
	{
		return $this->length;
	}

	public function getCreatorID()
	{
		return $this->creatorID;
	}


	// Setter methods
	public function setPlaylistID($playlistID)
	{
		$this->playlistID = $playlistID;
	}

	public function setImagePath($imagePath)
	{
		$this->imagePath = $imagePath;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setSongIDs(array $songs)
	{
		$this->songIDs = $songs;
	}

	public function setDuration($duration)
	{
		$this->duration = $duration;
	}

	public function setLength($length)
	{
		$this->length = $length;
	}

	public function setCreatorID($creatorID)
	{
		$this->creatorID = $creatorID;
	}
}
