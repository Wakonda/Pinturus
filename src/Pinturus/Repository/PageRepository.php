<?php

namespace Pinturus\Repository;

use Doctrine\DBAL\Connection;
use Pinturus\Entity\Collection;
use Pinturus\Entity\Page;

/**
 * Page repository
 */
class PageRepository extends GenericRepository
{
	public function save($entity, $id = null)
	{
		$entityData = array(
		'title' => $entity->getTitle(),
		'internationalName' => $entity->getInternationalName(),
		'text' => $entity->getText(),
		'photo' => $entity->getPhoto()
		);

		if(empty($id))
		{
			$this->db->insert('page', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('page', $entityData, array('id' => $id));

		return $id;
	}

    public function findByName($name, $show = false)
    {
		$qb = $this->db->createQueryBuilder();

		$qb->select("pa.*")
		   ->from("page", "pa")
		   ->where('pa.internationalName = :internationalName')
		   ->setParameter('internationalName', $name);
		
		$data = $qb->execute()->fetch();

        return $data ? $this->build($data, $show) : null;
    }

    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM page WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
    }

	protected function build($data, $show = false)
    {
        $entity = new Page();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setText($data['text']);
        $entity->setPhoto($data['photo']);
        $entity->setInternationalName($data['internationalName']);

        return $entity;
    }

	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pa.id', 'pa.title', 'pa.id');
		
		$qb->select("pa.*")
		   ->from("page", "pa");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->where('pa.title LIKE :search')
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
            $entitiesArray[] = $this->build($data, true);
        }
			
		return $entitiesArray;
	}

	public function checkForDoubloon($entity)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS count")
		   ->from("page", "pa")
		   ->where("pa.title = :title")
		   ->setParameter('title', $entity->getTitle());

		if($entity->getId() != null)
		{
			$qb->andWhere("pa.id != :id")
			   ->setParameter("id", $entity->getId());
		}
		
		return $qb->execute()->fetchColumn();
	}
}