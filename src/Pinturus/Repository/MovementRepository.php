<?php

namespace Pinturus\Repository;

use Doctrine\DBAL\Connection;
use Pinturus\Entity\Movement;

/**
 * Movement repository
 */
class MovementRepository
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
		$entityData = array(
		'title' => $entity->getTitle(),
		'text' => $entity->getText(),
		'photo' => $entity->getPhoto()
		);

		if(empty($id))
		{
			$this->db->insert('movement', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('movement', $entityData, array('id' => $id));

		return $id;
	}

    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM movement WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }

	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("*")
		   ->from("movement", "pf");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('pf.title LIKE :search')
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
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data);
        }
			
		return $entitiesArray;
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

	protected function build($data, $show = false)
    {
        $entity = new Movement();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setText($data['text']);
        $entity->setPhoto($data['photo']);

        return $entity;
    }

	public function findAllForChoice()
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("id, title")
		   ->from("movement", "pf")
		   ->orderBy("title", "ASC");

		$results = $qb->execute()->fetchAll();
		$choiceArray = array();
		
		foreach($results as $result)
		{
			$choiceArray[$result["title"]] = $result["id"];
		}
		
        return $choiceArray;
	}
	
	public function checkForDoubloon($entity)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS number")
		   ->from("movement", "pf")
		   ->where("pf.title = :title")
		   ->setParameter('title', $entity->getTitle());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		$results = $qb->execute()->fetchAll();
		
		return $results[0]["number"];
	}
}