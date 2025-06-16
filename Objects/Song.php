<?php

class Song
{
	private $songID;
	private $title;
	private $artists;
	private $genre;
	private $releaseDate;
	private $rating;
	private $songLength;
	private $filePath;
	private $imagePath;

	/**
	 * @throws Exception
	 */
	function __construct($songID, $title, $artists, $genre, $releaseDate, $rating, $songLength, $filePath, $imagePath)
	{
		$this->songID = $songID;
		$this->title = $title;
		$this->artists = $artists;
		$this->genre = $genre;
		$this->releaseDate = new DateTime($releaseDate);
		$this->rating = $rating;
		$this->songLength = new DateTime($songLength);
		$this->filePath = $filePath;
		$this->imagePath = $imagePath;
	}

	// getter methods

	public function getSongID()
	{
		return $this->songID;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getArtists()
	{
		return $this->artists;
	}

	public function getGenre()
	{
		return $this->genre;
	}

	public function getReleaseDate(): DateTime
	{
		return $this->releaseDate;
	}

	public function getRating()
	{
		return $this->rating;
	}

	public function getSongLength(): DateTime
	{
		return $this->songLength;
	}

	public function getFilePath()
	{
		return $this->filePath;
	}

	public function getImagePath()
	{
		return $this->imagePath;
	}

	// setter methods

	public function setSongID($songID)
	{
		$this->songID = $songID;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function setArtists($artists)
	{
		$this->artists = $artists;
	}

	public function setGenre($genre)
	{
		$this->genre = $genre;
	}

	public function setReleaseDate($releaseDate)
	{
		$this->releaseDate = $releaseDate;
	}

	public function setRating($rating)
	{
		$this->rating = $rating;
	}

	public function setSongLength($songLength)
	{
		$this->songLength = $songLength;
	}

	public function setFilePath($filePath)
	{
		$this->filePath = $filePath;
	}

	public function setImagePath($imagePath)
	{
		$this->imagePath = $imagePath;
	}

	public function setAll($songID, $title, $artists, $genre, $releaseDate, $rating, $songLength, $filePath, $imagePath)
	{
		$this->songID = $songID;
		$this->title = $title;
		$this->artists = $artists;
		$this->genre = $genre;
		$this->releaseDate = $releaseDate;
		$this->rating = $rating;
		$this->songLength = $songLength;
		$this->filePath = $filePath;
		$this->imagePath = $imagePath;
	}

	public function getAll(): array
	{
		return [$this->songID, $this->title, $this->artists, $this->genre, $this->releaseDate, $this->rating, $this->songLength, $this->filePath, $this->imagePath];
	}

	public function toString()
	{
		$properties = [
			$this->songID,
			$this->title,
			$this->artists,
			$this->genre,
			$this->releaseDate->format('d.m.Y'),
			$this->rating,
			$this->songLength->format('i:s'),
			$this->filePath,
			$this->imagePath
		];
		echo implode(',<br>', $properties);
	}
}
