<?php

namespace Pinturus\Repository;

use Doctrine\DBAL\Connection;
use Pinturus\Entity\Biography;

/**
 * Biography repository
 */
class BiographyRepository extends GenericRepository implements iRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
		'title' => $entity->getTitle(),
		'text' => $entity->getText(),
		'dayBirth' => $entity->getDayBirth(),
		'monthBirth' => $entity->getMonthBirth(),
		'yearBirth' => $entity->getYearBirth(),
		'dayDeath' => $entity->getDayDeath(),
		'monthDeath' => $entity->getMonthDeath(),
		'yearDeath' => $entity->getYearDeath(),
		'photo' => $entity->getPhoto(),
		'country_id' => ($entity->getCountry() == 0) ? null : $entity->getCountry()
		);

		if(empty($id))
		{
			$this->db->insert('biography', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('biography', $entityData, array('id' => $id));

		return $id;
	}
	
    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM biography WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }
	
    public function findAll($show = false)
    {
		$qb = $this->db->createQueryBuilder();

		$qb->select("bo.*")
		   ->from("biography", "bo");

		$dataArray = $qb->execute()->fetchAll();

		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

        return $entitiesArray;
    }
	
	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("*")
		   ->from("biography", "pf");
		
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
			return $qb->execute()->fetchColumn();
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
	
	public function build($data, $show = false)
    {
        $entity = new Biography();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setText($data['text']);
        $entity->setDayBirth($data['dayBirth']);
        $entity->setMonthBirth($data['monthBirth']);
        $entity->setYearBirth($data['yearBirth']);
        $entity->setDayDeath($data['dayDeath']);
        $entity->setMonthDeath($data['monthDeath']);
        $entity->setYearDeath($data['yearDeath']);
        $entity->setPhoto($data['photo']);
		
		if($show)
		{
			$entity->setCountry($this->findByTable($data['country_id'], 'country'));
		}
		else
		{
			$entity->setCountry($data['country_id']);
		}

        return $entity;
    }
	
	public function findAllForChoice()
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("id, title")
		   ->from("biography", "pf")
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

		$qb->select("COUNT(*) AS count")
		   ->from("biography", "pf")
		   ->where("pf.title = :title")
		   ->setParameter('title', $entity->getTitle());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}

		return $qb->execute()->fetchColumn();
	}

	// Combobox
	public function getDatasCombobox($params, $count = false)
	{
		$qb = $this->db->createQueryBuilder();
		
		if(array_key_exists("pkey_val", $params))
		{
			$qb->select("b.id, b.title")
			   ->from("biography", "b")
			   ->where('b.id = :id')
			   ->setParameter('id', $params['pkey_val']);
			   
			return $qb->execute()->fetch();
		}
		
		$params['offset']  = ($params['page_num'] - 1) * $params['per_page'];

		$qb->select("b.id, b.title")
		   ->from("biography", "b")
		   ->where("b.title LIKE :title")
		   ->setParameter("title", "%".implode(' ', $params['q_word'])."%")
		   ->setMaxResults($params['per_page'])
		   ->setFirstResult($params['offset'])
		   ;
		
		if($count)
		{
			$qb->select("COUNT(b.id)")
			   ->from("biography", "b")
			   ->where("b.title LIKE :title")
			   ->setParameter("title", "%".implode(' ', $params['q_word'])."%")
			   ;
			   
			return $qb->execute()->fetchColumn();
		}

		return $qb->execute()->fetchAll();
	}
}