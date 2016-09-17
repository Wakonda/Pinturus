<?php

namespace Pinturus\Repository;

use Doctrine\DBAL\Connection;
use Pinturus\Entity\PaintingVote;

/**
 * PaintingVote repository
 */
class PaintingVoteRepository
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

	public function save($entity, $id = null)
	{
		// die(var_dump($entity->getUser()->getId()));
		$entityData = array(
        'vote'  => $entity->getVote(),
        'user_id' => ($entity->getUser()->getId() == null) ? null : $entity->getUser()->getId(),
        'painting_id' => ($entity->getPainting()->getId() == 0) ? null : $entity->getPainting()->getId()
		);

		if(empty($id))
		{
			$this->db->insert('paintingvote', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('paintingvote', $entityData, array('id' => $id));

		return $id;
	}
	
	public function checkIfUserAlreadyVote($idPainting, $idUser)
	{
		$data = $this->db->fetchAssoc('SELECT COUNT(*) AS votes_number FROM paintingvote WHERE painting_id = ? AND user_id = ?', array($idPainting, $idUser));
		
		return $data['votes_number'];
	}
	
	public function countVoteByPainting($idPainting, $vote)
	{
		$data = $this->db->fetchAssoc('SELECT COUNT(*) AS votes_number FROM paintingvote WHERE painting_id = ? AND vote = ?', array($idPainting, $vote));
		
		return $data['votes_number'];
	}

	public function findVoteByUser($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $username, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array('pf.title', 'vo.vote');
		
		$qb->select("pf.id, pf.title, vo.vote")
		   ->from("paintingvote", "vo")
		   ->leftjoin("vo", "user", "bp", "vo.user_id = bp.id")
		   ->leftjoin("vo", "painting", "pf", "vo.painting_id = pf.id")
		   ->where("bp.username = :username")
		   ->setParameter("username", $username);
		   
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andhere('pf.title LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(*) AS count");
			$results = $qb->execute()->fetchAll();
			return $results[0]["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
	}


	protected function build($data, $show = false)
    {
        $entity = new PaintingVote();

        $entity->setId($data['id']);
        $entity->setVote($data['vote']);
		
		if($show)
		{
			$entity->setUser($this->findByTable($data['user_id'], 'user', 'username'));
			$entity->setPainting($this->findByTable($data['painting_id'], 'painting', 'title'));
		}
		else
		{
			$entity->setUser($data['user_id']);
			$entity->setPainting($data['painting_id']);
		}

        return $entity;
    }

    public function findByTable($id, $table, $field = null)
    {
		if(empty($id))
			return null;
			
        $data = $this->db->fetchAssoc('SELECT * FROM '.$table.' WHERE id = ?', array($id));

		if(empty($field))
			return $data;
		else
			return $data[$field];
    }
}