<?php

class Song
{
	private $songID;
	private $title;
	private $artists;
	private $genre;
	private $releaseDate;
	private $songLength;
	private $fileName;
	private $imageName;

	/**
	 * @throws Exception
	 */
	function __construct($songID, $title, $artists, $genre, $releaseDate, $songLength, $fileName, $imageName)
	{
		$this->songID = $songID;
		$this->title = $title;
		$this->artists = $artists;
		$this->genre = $genre;
		$this->releaseDate = new DateTime($releaseDate);
		$this->songLength = new DateTime($songLength);
		$this->fileName = $fileName;
		$this->imageName = $imageName;
	}

	// getter methods

	public function getSongID()
	{
		return $this->songID;
	}

	public function setSongID($songID)
	{
		$this->songID = $songID;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function getArtists()
	{
		return $this->artists;
	}

	public function setArtists($artists)
	{
		$this->artists = $artists;
	}

	public function getGenre()
	{
		return $this->genre;
	}

	public function setGenre($genre)
	{
		$this->genre = $genre;
	}

	// setter methods

	public function getReleaseDate(): DateTime
	{
		return $this->releaseDate;
	}

	public function setReleaseDate($releaseDate)
	{
		$this->releaseDate = $releaseDate;
	}

	public function getSongLength(): DateTime
	{
		return $this->songLength;
	}

	public function setSongLength($songLength)
	{
		$this->songLength = $songLength;
	}

	public function getfileName()
	{
		return $this->fileName;
	}

	public function setfileName($fileName)
	{
		$this->fileName = $fileName;
	}

	public function getimageName()
	{
		return $this->imageName;
	}

	public function setimageName($imageName)
	{
		$this->imageName = $imageName;
	}

	public function setAll($songID, $title, $artists, $genre, $releaseDate, $songLength, $fileName, $imageName)
	{
		$this->songID = $songID;
		$this->title = $title;
		$this->artists = $artists;
		$this->genre = $genre;
		$this->releaseDate = $releaseDate;
		$this->songLength = $songLength;
		$this->fileName = $fileName;
		$this->imageName = $imageName;
	}

	public function getAll(): array
	{
		return [$this->songID, $this->title, $this->artists, $this->genre, $this->releaseDate, $this->songLength, $this->fileName, $this->imageName];
	}

	public function toString()
	{
		$properties = [
			$this->songID,
			$this->title,
			$this->artists,
			$this->genre,
			$this->releaseDate->format('d.m.Y'),
			$this->songLength->format('i:s'),
			$this->fileName,
			$this->imageName
		];
		echo implode(',<br>', $properties);
	}
}
