<?php

namespace Pinturus\Entity;

class Painting
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
     * @var integer
     */
    protected $yearStart;

    /**
     *
     * @var integer
     */
    protected $yearEnd;

    /**
     *
     * @var float
     */
    protected $height;

    /**
     *
     * @var float
     */
    protected $width;

    /**
     *
     * @var string
     */
    protected $photo;

    /**
     *
     * @var \Pinturus\Entity\Type
     */
    protected $type;

    /**
     *
     * @var \Pinturus\Entity\Location
     */
    protected $location;

    /**
     *
     * @var \Pinturus\Entity\Movement
     */
    protected $movement;

    /**
     *
     * @var \Pinturus\Entity\biography
     */
    protected $biography;

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

    public function getYearStart()
    {
        return $this->yearStart;
    }

    public function setYearStart($yearStart)
    {
        $this->yearStart = $yearStart;
    }

    public function getYearEnd()
    {
        return $this->yearEnd;
    }

    public function setYearEnd($yearEnd)
    {
        $this->yearEnd = $yearEnd;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function setHeight($height)
    {
        $this->height = $height;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }
	
    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
	
    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function getMovement()
    {
        return $this->movement;
    }

    public function setMovement($movement)
    {
        $this->movement = $movement;
    }

    public function getBiography()
    {
        return $this->biography;
    }

    public function setBiography($biography)
    {
        $this->biography = $biography;
    }
}