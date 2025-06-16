<?php

class Playlist
{
	private $playlistID;
	private $imagePath;
	private $name;
	private $duration;
	private $length;
	private $creatorID;

	public function __construct($playlistID, $imagePath, $name, $duration, $length, $creatorID)
	{
		$this->playlistID = $playlistID;
		$this->imagePath = $imagePath;
		$this->name = $name;
		$this->duration = $duration;
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
