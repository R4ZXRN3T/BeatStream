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
		$this->duration = new DateTime($duration);
	}

	// Getter Methods

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getArtists()
	{
		return $this->artists;
	}

	public function setArtists($artists)
	{
		$this->artists = $artists;
	}

	public function getimageName()
	{
		return $this->imageName;
	}

	public function setimageName($imageName)
	{
		$this->imageName = $imageName;
	}

	public function getLength()
	{
		return $this->length;
	}

	// Setter Methods

	public function setLength($length)
	{
		$this->length = $length;
	}

	public function getDuration()
	{
		return $this->duration;
	}

	public function setDuration($duration)
	{
		$this->duration = $duration;
	}

	public function getAlbumID()
	{
		return $this->albumID;
	}

	public function setAlbumID($albumID)
	{
		$this->albumID = $albumID;
	}

	public function getSongIDs()
	{
		return $this->songIDs;
	}

	public function setSongIDs(array $songIDs)
	{
		$this->songIDs = $songIDs;
	}

	public function addSongID($songID)
	{
		if (!in_array($songID, $this->songIDs)) {
			$this->songIDs[] = $songID;
		}
	}
}
