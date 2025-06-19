<?php

class Artist
{
	private $artistID;
	private $name;
	private $imagePath;
	private $activeSince;
	private $userID;

	public function __construct($artistID, $name, $imagePath, $activeSince, $userID)
	{
		$this->artistID = $artistID;
		$this->name = $name;
		$this->imagePath = $imagePath;
		try {
			$this->activeSince = new DateTime($activeSince);
		} catch (Exception $e) {
			throw new RuntimeException("Invalid date format for activeSince: " . $e->getMessage());
		}
		$this->userID = $userID;
	}

	// getter methods

	public function getArtistID()
	{
		return $this->artistID;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getImagePath()
	{
		return $this->imagePath;
	}

	public function getActiveSince(): DateTime
	{
		return $this->activeSince;
	}

	public function getUserID()
	{
		return $this->userID;
	}

	// setter methods

	public function setArtistID($artistID)
	{
		$this->artistID = $artistID;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setImagePath($imagePath)
	{
		$this->imagePath = $imagePath;
	}

	public function setActiveSince($activeSince)
	{
		try {
			$this->activeSince = new DateTime($activeSince);
		} catch (Exception $e) {
			throw new RuntimeException("Invalid date format for activeSince: " . $e->getMessage());
		}
	}

	public function setUserID($userID)
	{
		$this->userID = $userID;
	}
}
