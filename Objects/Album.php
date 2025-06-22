<?php

class Album
{
	private $albumID;
	private $name;
	private $artists;
	private $imageName;
	private $length;
	private $duration;

	public function __construct($albumID, $name, $artists, $imageName, $length, $duration)
	{
		$this->albumID = $albumID;
		$this->name = $name;
		$this->artists = $artists;
		$this->imageName = $imageName;
		$this->length = $length;
		$this->duration = new DateTime($duration);
	}

	// Getter Methods
	public function getAlbumID()
	{
		return $this->albumID;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getArtists()
	{
		return $this->artists;
	}

	public function getimageName()
	{
		return $this->imageName;
	}

	public function getLength()
	{
		return $this->length;
	}

	public function getDuration()
	{
		return $this->duration;
	}

	// Setter Methods
	public function setAlbumID($albumID)
	{
		$this->albumID = $albumID;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setArtists($artists)
	{
		$this->artists = $artists;
	}

	public function setimageName($imageName)
	{
		$this->imageName = $imageName;
	}

	public function setLength($length)
	{
		$this->length = $length;
	}

	public function setDuration($duration)
	{
		$this->duration = $duration;
	}
}
