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
	private $imageName;

	public function __construct($userID, $username, $email, $userPassword, $salt, $isAdmin, $isArtist, $imageName)
	{
		$this->userID = $userID;
		$this->username = $username;
		$this->email = $email;
		$this->userPassword = $userPassword;
		$this->salt = $salt;
		$this->isAdmin = $isAdmin;
		$this->isArtist = $isArtist;
		$this->imageName = $imageName;
	}

	// Getter methods
	public function getUserID()
	{
		return $this->userID;
	}

	public function setUserID($userID)
	{
		$this->userID = $userID;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function setUsername($username)
	{
		$this->username = $username;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function getUserPassword()
	{
		return $this->userPassword;
	}

	public function setUserPassword($userPassword)
	{
		$this->userPassword = $userPassword;
	}

	// Setter methods

	public function getSalt()
	{
		return $this->salt;
	}

	public function setSalt($salt)
	{
		$this->salt = $salt;
	}

	public function isAdmin()
	{
		return $this->isAdmin;
	}

	public function setIsAdmin($isAdmin)
	{
		$this->isAdmin = $isAdmin;
	}

	public function isArtist()
	{
		return $this->isArtist;
	}

	public function setIsArtist($isArtist)
	{
		$this->isArtist = $isArtist;
	}

	public function getimageName()
	{
		return $this->imageName;
	}

	public function setimageName($imageName)
	{
		$this->imageName = $imageName;
	}
}
