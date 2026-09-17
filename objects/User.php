<?php

class User
{
	private int $userID;
	private string $username;
	private string $email;
	private string $userPassword;
	private string $salt;
	private bool $hasAccess;
	private bool $isAdmin;
	private bool $isArtist;
	private string $imageName;
	private string $thumbnailName;

	public function __construct(int $userID, string $username, string $email, string $userPassword, string $salt, bool $hasAccess, bool $isAdmin, bool $isArtist, string $imageName, string $thumbnailName)
	{
		$this->userID = $userID;
		$this->username = $username;
		$this->email = $email;
		$this->userPassword = $userPassword;
		$this->salt = $salt;
		$this->hasAccess = $hasAccess;
		$this->isAdmin = $isAdmin;
		$this->isArtist = $isArtist;
		$this->imageName = $imageName;
		$this->thumbnailName = $thumbnailName;
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

	public function getImageName(): string
	{
		return $this->imageName;
	}

	public function getThumbnailName(): string
	{
		return $this->thumbnailName;
	}

	public function isHasAccess(): bool
	{
		return $this->hasAccess;
	}

	public function setHasAccess(bool $hasAccess): void
	{
		$this->hasAccess = $hasAccess;
	}

	public function json_encode(): array
	{
		return [
			'userID' => $this->userID,
			'username' => $this->username,
			'email' => $this->email,
			'userPassword' => $this->userPassword,
			'salt' => $this->salt,
			'hasAccess' => $this->hasAccess,
			'isAdmin' => $this->isAdmin,
			'isArtist' => $this->isArtist,
			'imageName' => $this->imageName,
			'thumbnailName' => $this->thumbnailName
		];
	}
}
