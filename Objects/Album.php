<?php
class Album
{
    private $albumID;
    private $name;
    private $artists;
    private $imagePath;
    private $length;
    private $duration;

    public function __construct($albumID, $name, $artists, $imagePath, $length, $duration)
    {
        $this->albumID = $albumID;
        $this->name = $name;
        $this->artists = $artists;
        $this->imagePath = $imagePath;
        $this->length = $length;
        $this->duration = $duration;
    }

    // Getter Methods
    public function getAlbumID()
    {
        return $this->albumID;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getArtists()
    {
        return $this->artists;
    }

    public function getImagePath()
    {
        return $this->imagePath;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    // Setter Methods
    public function setAlbumID($albumID)
    {
        $this->albumID = $albumID;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setArtists($artists)
    {
        $this->artists = $artists;
    }

    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
    }

    public function setLength($length)
    {
        $this->length = $length;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }
}
