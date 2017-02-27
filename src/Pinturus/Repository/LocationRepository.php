<?php

namespace Pinturus\Repository;

use Doctrine\DBAL\Connection;
use Pinturus\Entity\Location;

/**
 * Location repository
 */
class LocationRepository extends GenericRepository implements iRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
		'title' => $entity->getTitle(),
		'text' => $entity->getText(),
		'photo' => $entity->getPhoto(),
		'address' => $entity->getAddress(),
		'latitude' => $entity->getLatitude(),
		'longitude' => $entity->getLongitude(),
		'officialWebsite' => $entity->getOfficialWebsite(),
		'city_id' => ($entity->getCity() == 0) ? null : $entity->getCity()
		);

		if(empty($id))
		{
			$this->db->insert('location', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('location', $entityData, array('id' => $id));

		return $id;
	}

    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM location WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }

	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("*")
		   ->from("location", "pf");
		
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
        $entity = new Location();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setText($data['text']);
        $entity->setPhoto($data['photo']);
        $entity->setAddress($data['address']);
        $entity->setLatitude($data['latitude']);
        $entity->setLongitude($data['longitude']);
        $entity->setOfficialWebsite($data['officialWebsite']);

		if($show)
		{
			$entity->setCity($this->findByTable($data['city_id'], 'city'));
		}
		else
		{
			$entity->setCity($data['city_id']);
		}

        return $entity;
    }

	public function findAllForChoice()
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select("id, title")
		   ->from("location", "pf")
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
		   ->from("location", "pf")
		   ->where("pf.title = :title")
		   ->setParameter('title', $entity->getTitle());

		if($entity->getId() != null)
		{
			$qb->andWhere("pf.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		
		return $qb->execute()->fetchColumn();
	}
	
	public function getCountryByCityId($cityId)
	{
		$qb = $this->db->createQueryBuilder();
		
		$qb->select('*')
		   ->from("location", "lo")
		   ->leftjoin("lo", "city", "ci", "lo.city_id = ci.id")
		   ->leftjoin("ci", "country", "co", "ci.country_id = co.id")
		   ->where("lo.city_id = :cityId")
		   ->setParameter("cityId", $cityId)
		   ->setMaxResults(1);
		   
		return $qb->execute()->fetch();
	}
}