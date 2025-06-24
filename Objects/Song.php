<?php

class Song
{
	private int $songID;
	private string $title;
	private string $artists;
	private string $genre;
	private DateTime $releaseDate;
	private DateTime $songLength;
	private string $fileName;
	private string $imageName;

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

	public function getSongID(): int
	{
		return $this->songID;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function getArtists(): string
	{
		return $this->artists;
	}

	public function getGenre(): string
	{
		return $this->genre;
	}

	public function getReleaseDate(): DateTime
	{
		return $this->releaseDate;
	}

	public function getSongLength(): DateTime
	{
		return $this->songLength;
	}

	public function getFileName(): string
	{
		return $this->fileName;
	}

	public function getImageName(): string
	{
		return $this->imageName;
	}

	public function setAll($songID, $title, $artists, $genre, $releaseDate, $songLength, $fileName, $imageName): void
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
}
