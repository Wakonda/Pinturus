<?php

namespace Pinturus\Entity;

class PaintingVote
{
    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var integer
     */
    protected $vote;

    /**
     *
     * @var \Pinturus\Entity\Painting
     */
    protected $painting;

    /**
     *
     * @var \Pinturus\Entity\User
     */
    protected $user;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getVote()
    {
        return $this->vote;
    }

    public function setVote($vote)
    {
        $this->vote = $vote;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getPainting()
    {
        return $this->painting;
    }

    public function setPainting($painting)
    {
        $this->painting = $painting;
    }
}
