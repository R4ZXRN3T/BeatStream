<?php

class User
{
	private int $userID;
	private string $username;
	private string $email;
	private string $userPassword;
	private string $salt;
	private bool $isAdmin;
	private bool $isArtist;
	private string $imageName;

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
	public function getUserID(): int
	{
		return $this->userID;
	}

	public function getUsername(): string
	{
		return $this->username;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function getUserPassword(): string
	{
		return $this->userPassword;
	}

	// Setter methods

	public function getSalt(): string
	{
		return $this->salt;
	}

	public function isAdmin(): bool
	{
		return $this->isAdmin;
	}

	public function isArtist(): bool
	{
		return $this->isArtist;
	}

	public function getimageName(): string
	{
		return $this->imageName;
	}
}
