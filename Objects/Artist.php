<?php

class Artist
{
	private int $artistID;
	private string $name;
	private string $imageName;
	private DateTime $activeSince;
	private int $userID;

	public function __construct($artistID, $name, $imageName, $activeSince, $userID)
	{
		$this->artistID = $artistID;
		$this->name = $name;
		$this->imageName = $imageName;
		try {
			$this->activeSince = new DateTime($activeSince);
		} catch (Exception $e) {
			throw new RuntimeException("Invalid date format for activeSince: " . $e->getMessage());
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

	public function getimageName(): string
	{
		return $this->imageName;
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
