<?php

namespace Pinturus\Repository;

use Doctrine\DBAL\Connection;
use Pinturus\Entity\City;

/**
 * Poem repository
 */
class CityRepository extends GenericRepository implements iRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
		'title' => $entity->getTitle(),
		'text' => $entity->getText(),
		'photo' => $entity->getPhoto(),
		'country_id' => ($entity->getCountry() == 0) ? null : $entity->getCountry()
		);

		if(empty($id))
		{
			$this->db->insert('city', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('city', $entityData, array('id' => $id));

		return $id;
	}

    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM city WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }

	public function findAllForChoice()
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("id, title")
		   ->from("city", "ci")
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
		   ->from("city", "pf")
		   ->where("pf.title = :title")
		   ->setParameter('title', $entity->getTitle());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		
		return $qb->execute()->fetchColumn();
	}

	protected function build($data, $show = false)
    {
        $entity = new City();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setText($data['text']);
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
}