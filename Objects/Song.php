<?php

class Song
{
	private int $songID;
	private string $title;
	private array $artists;
	private array $artistIDs;
	private string $genre;
	private DateTime $releaseDate;
	private DateTime $songLength;
	private string $flacFileName;
	private string $opusFileName;
	private string $imageName;
	private string $thumbnailName;

	/**
	 * @throws Exception
	 */
	function __construct(int $songID, string $title, array $artists, array $artistIDs, string $genre, string $releaseDate, string $songLength, string $flacFileName, $opusFileName, string $imageName, string $thumbnailName)
	{
		$this->songID = $songID;
		$this->title = $title;
		$this->artists = $artists;
		$this->artistIDs = $artistIDs;
		$this->genre = $genre;
		$this->releaseDate = new DateTime($releaseDate);
		$this->songLength = new DateTime($songLength);
		$this->flacFileName = $flacFileName;
		$this->opusFileName = $opusFileName;
		$this->imageName = $imageName;
		$this->thumbnailName = $thumbnailName;
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

	public function getArtists(): array
	{
		return $this->artists;
	}

	public function setArtists(array $artists): void
	{
		$this->artists = $artists;
	}

	public function getArtistIDs(): array
	{
		return $this->artistIDs;
	}

	public function setArtistIDs(array $artistIDs): void
	{
		$this->artistIDs = $artistIDs;
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

	public function getFlacFileName(): string
	{
		return $this->flacFileName;
	}

	public function getOpusFileName(): string
	{
		return $this->opusFileName;
	}

	public function getImageName(): string
	{
		return $this->imageName;
	}

	public function getThumbnailName(): string
	{
		return $this->thumbnailName;
	}
}
