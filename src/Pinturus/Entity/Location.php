<?php

namespace Pinturus\Entity;

class Location
{
    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $title;

    /**
     *
     * @var string
     */
    protected $text;

    /**
     *
     * @var string
     */
    protected $photo;

    /**
     *
     * @var text
     */
    protected $address;

    /**
     *
     * @var double
     */
    protected $latitude;

    /**
     *
     * @var double
     */
    protected $longitude;

    /**
     *
     * @var string
     */
    protected $officialWebsite;

    /**
     *
     * @var \Pinturus\Entity\City
     */
    protected $city;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }
	
    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }
	
    public function getOfficialWebsite()
    {
        return $this->officialWebsite;
    }

    public function setOfficialWebsite($officialWebsite)
    {
        $this->officialWebsite = $officialWebsite;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }
}