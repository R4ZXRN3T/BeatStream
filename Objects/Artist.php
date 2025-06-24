<?php

class Artist
{
	private $artistID;
	private $name;
	private $imageName;
	private $activeSince;
	private $userID;

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

	public function getArtistID()
	{
		return $this->artistID;
	}

	public function setArtistID($artistID)
	{
		$this->artistID = $artistID;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getimageName()
	{
		return $this->imageName;
	}

	// setter methods

	public function setimageName($imageName)
	{
		$this->imageName = $imageName;
	}

	public function getActiveSince(): DateTime
	{
		return $this->activeSince;
	}

	public function setActiveSince($activeSince)
	{
		try {
			$this->activeSince = new DateTime($activeSince);
		} catch (Exception $e) {
			throw new RuntimeException("Invalid date format for activeSince: " . $e->getMessage());
		}
	}

	public function getUserID()
	{
		return $this->userID;
	}

	public function setUserID($userID)
	{
		$this->userID = $userID;
	}
}
