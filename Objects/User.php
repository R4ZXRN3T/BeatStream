<?php

class User
{
	private $userID;
	private $username;
	private $email;
	private $userPassword;
	private $salt;
	private $isAdmin = false;
	private $isArtist = false;
	private $imagePath;

	public function __construct($userID, $username, $email, $userPassword, $salt, $isAdmin, $isArtist, $imagePath)
	{
		$this->userID = $userID;
		$this->username = $username;
		$this->email = $email;
		$this->userPassword = $userPassword;
		$this->salt = $salt;
		$this->isAdmin = $isAdmin;
		$this->isArtist = $isArtist;
		$this->imagePath = $imagePath;
	}

	// Getter methods
	public function getUserID()
	{
		return $this->userID;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function getUserPassword()
	{
		return $this->userPassword;
	}

	public function getSalt()
	{
		return $this->salt;
	}

	public function isAdmin()
	{
		return $this->isAdmin;
	}

	public function isArtist()
	{
		return $this->isArtist;
	}

	public function getImagePath()
	{
		return $this->imagePath;
	}

	// Setter methods
	public function setUserID($userID)
	{
		$this->userID = $userID;
	}

	public function setUsername($username)
	{
		$this->username = $username;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function setUserPassword($userPassword)
	{
		$this->userPassword = $userPassword;
	}

	public function setSalt($salt)
	{
		$this->salt = $salt;
	}

	public function setIsAdmin($isAdmin)
	{
		$this->isAdmin = $isAdmin;
	}

	public function setIsArtist($isArtist)
	{
		$this->isArtist = $isArtist;
	}

	public function setImagePath($imagePath)
	{
		$this->imagePath = $imagePath;
	}
}
