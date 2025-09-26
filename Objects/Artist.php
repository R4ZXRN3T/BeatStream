<?php

class Artist
{
	private int $artistID;
	private string $name;
	private string $imageName;
	private string $thumbnailName;
	private DateTime $activeSince;
	private int $userID;

	public function __construct(int $artistID, string $name, string $imageName, string $thumbnailName, string $activeSince, int $userID)
	{
		$this->artistID = $artistID;
		$this->name = $name;
		$this->imageName = $imageName;
		$this->thumbnailName = $thumbnailName;
		try {
			$this->activeSince = new DateTime($activeSince);
		} catch (Exception) {
			$this->activeSince = new DateTime();
		}
		$this->userID = $userID;
	}

	// getter methods

	public function getArtistID(): int
	{
		return $this->artistID;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getImageName(): string
	{
		return $this->imageName;
	}

	public function getThumbnailName(): string
	{
		return $this->thumbnailName;
	}

	public function getActiveSince(): DateTime
	{
		return $this->activeSince;
	}

	public function getUserID(): int
	{
		return $this->userID;
	}
}
