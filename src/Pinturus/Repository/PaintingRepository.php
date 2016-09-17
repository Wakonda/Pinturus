<?php

namespace Pinturus\Repository;

use Doctrine\DBAL\Connection;
use Pinturus\Entity\Painting;

/**
 * Painting repository
 */
class PaintingRepository
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
		'yearStart' => $entity->getYearStart(),
		'yearEnd' => $entity->getYearEnd(),
		'height' => $entity->getHeight(),
		'width' => $entity->getWidth(),
		'photo' => $entity->getPhoto(),
		'type_id' => ($entity->getType() == 0) ? null : $entity->getType(),
		'location_id' => ($entity->getLocation() == 0) ? null : $entity->getLocation(),
		'movement_id' => ($entity->getMovement() == 0) ? null : $entity->getMovement(),
		'biography_id' => ($entity->getBiography() == 0) ? null : $entity->getBiography()
		);

		if(empty($id))
		{
			$this->db->insert('painting', $entityData);
			$id = $this->db->lastInsertId();
		}
		else
			$this->db->update('painting', $entityData, array('id' => $id));

		return $id;
	}
	
    public function find($id, $show = false)
    {
        $data = $this->db->fetchAssoc('SELECT * FROM painting WHERE id = ?', array($id));

        return $data ? $this->build($data, $show) : null;
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

	public function findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $datasObject, $count = false)
	{
		$aColumns = array( 'pa.title', 'pab.title', 'pac.title', 'pa.id');
		$qb = $this->db->createQueryBuilder();

		$qb->select("pa.*")
		   ->from("painting", "pa")
		   ->leftjoin("pa", "biography", "pab", "pa.biography_id = pab.id")
		   ->leftjoin("pab", "country", "pac", "pab.country_id = pac.id");

		if(!empty($datasObject->title))
		{
			$value = "%".$datasObject->title."%";
			$qb->andWhere("pa.title LIKE :title")
			   ->setParameter("title", $value);
		}

		if(!empty($datasObject->text))
		{
			$keywords = explode($datasObject->text, ",");
			$i = 0;
			foreach($keywords as $keyword)
			{
				$keyword = "%".$keyword."%";
				$qb->andWhere("pa.text LIKE :keyword".$i)
			       ->setParameter("keyword".$i, $keyword);
				$i++;
			}
		}

		if(!empty($datasObject->author))
		{
			$author = "%".$datasObject->author."%";
			$qb->leftjoin("pa", "biography", "pab", "pa.biography_id = pab.id")
			   ->andWhere("pab.title LIKE :username")
			   ->setParameter("username", $author);
		}

		if(!empty($datasObject->country))
		{
			$qb->andWhere("pac.country_id = :country")
			   ->setParameter("country", $datasObject->country);
		}

		if(!empty($datasObject->movement))
		{
			$movement = "%".$this->findByTable($datasObject->movement, 'movement', 'title')."%";
			$qb->leftjoin("pa", "movement", "pamo", "pa.movement_id = pamo.id")
			   ->andWhere("pamo.title LIKE :movement")
			   ->setParameter("movement", $movement);
		}

		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);
		
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
            $entitiesArray[] = $this->build($data, true);
        }

		return $entitiesArray;
	}

    public function findPaintingByAuthor($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
    {
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'bp.title', 'COUNT(pa.id)');
		
		$qb->select("bp.id AS id, bp.title AS author, COUNT(pa.id) AS number_paintings_by_author")
		   ->from("painting", "pa")
		   ->leftjoin("pa", "biography", "bp", "pa.biography_id = bp.id")
		   ->groupBy("bp.id");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('bp.title LIKE "'.$search.'"');
		}
		if($count)
		{
			$countRows = $this->db->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getSql().") AS SQ");
			$result = $countRows->fetch();

			return $result["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
    }

    public function findPaintingByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
    {
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'co.title', 'COUNT(pa.id)');
		
		$qb->select("pa.id AS id, co.id AS country_id, co.title AS country_title, COUNT(pa.id) AS number_paintings_by_country, co.flag AS flag")
		   ->from("painting", "pa")
		   ->leftjoin("pa", "biography", "bp", "pa.biography_id = bp.id")
		   ->leftjoin("bp", "country", "co", "bp.country_id = co.id")
		   ->groupBy("co.id")
		   ;
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('co.title LIKE "'.$search.'"');
		}
		if($count)
		{
			$countRows = $this->db->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getSql().") AS SQ");
			$result = $countRows->fetch();

			return $result["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
    }

    public function findPaintingByMovement($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
    {
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'mo.title', 'COUNT(pa.id)');
		
		$qb->select("mo.id AS id, mo.title AS movement, COUNT(pa.id) AS number_paintings_by_movement")
		   ->from("painting", "pa")
		   ->leftjoin("pa", "movement", "mo", "pa.biography_id = mo.id")
		   ->groupBy("mo.id");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('mo.title LIKE "'.$search.'"');
		}
		if($count)
		{
			$countRows = $this->db->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getSql().") AS SQ");
			$result = $countRows->fetch();

			return $result["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
    }

    public function findPaintingByLocation($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
    {
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'lo.title', 'COUNT(pa.id)');
		
		$qb->select("lo.id AS id, lo.title AS location, COUNT(pa.id) AS number_paintings_by_location")
		   ->from("painting", "pa")
		   ->leftjoin("pa", "location", "lo", "pa.location_id = lo.id")
		   ->groupBy("lo.id");
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('lo.title LIKE "'.$search.'"');
		}
		if($count)
		{
			$countRows = $this->db->executeQuery("SELECT COUNT(*) AS count FROM (".$qb->getSql().") AS SQ");
			$result = $countRows->fetch();

			return $result["count"];
		}
		else
			$qb->setFirstResult($iDisplayStart)->setMaxResults($iDisplayLength);

		$dataArray = $qb->execute()->fetchAll();

		return $dataArray;
    }

	public function getRandomPainting()
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS countRow")
		   ->from("painting", "pt");
		
		$count = $qb->execute()->fetchObject();
		$id = rand(1, $count->countRow);
		
		$qb = $this->db->createQueryBuilder();

		$qb->select("*")
		   ->from("painting", "pt")
		   ->where("pt.id = :id")
		   ->setParameter("id", $id);

		$result = $qb->execute()->fetch();
		
		if(!$result)
			return null;

		return $this->build($result, true);
	}

	public function getLastEntries()
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("*")
		   ->from("painting", "pt")
		   ->setMaxResults(7)
		   ->orderBy("pt.id", "DESC");
		   
		$dataArray = $qb->execute()->fetchAll();
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }
			
		return $entitiesArray;
	}
	
	public function getStat()
	{
		$qbPainting = $this->db->createQueryBuilder();

		$qbPainting->select("COUNT(*) AS count_painting")
			   ->from("painting", "pt");
		
		$resultPainting = $qbPainting->execute()->fetchAll();
		
		$qbBio = $this->db->createQueryBuilder();

		$qbBio->select("COUNT(*) AS count_biography")
		      ->from("biography", "bp");
		
		$resultBio = $qbBio->execute()->fetchAll();
		
		return array("count_painting" => $resultPainting[0]["count_painting"], "count_biography" => $resultBio[0]["count_biography"]);
	}

	protected function build($data, $show = false)
    {
        $entity = new Painting();

        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setText($data['text']);
        $entity->setYearStart($data['yearStart']);
        $entity->setYearEnd($data['yearEnd']);
        $entity->setheight($data['height']);
        $entity->setWidth($data['width']);
        $entity->setPhoto($data['photo']);
		
		if($show)
		{
			$entity->setType($this->findByTable($data['type_id'], 'type'));
			$entity->setLocation($this->findByTable($data['location_id'], 'location'));
			$entity->setMovement($this->findByTable($data['movement_id'], 'movement'));

			$biography = $this->findByTable($data['biography_id'], 'biography');
			$biography["country"] = $this->findByTable($biography['country_id'], 'country');
			$entity->setBiography($biography);
		}
		else
		{
			$entity->setType($data['type_id']);
			$entity->setLocation($data['location_id']);
			$entity->setMovement($data['movement_id']);
			$entity->setBiography($data['biography_id']);
		}

        return $entity;
    }

	public function getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("*")
		   ->from("painting", "pf");
		
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

	public function checkForDoubloon($entity)
	{
		$qb = $this->db->createQueryBuilder();

		$qb->select("COUNT(*) AS number")
		   ->from("painting", "pf")
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

	public function browsingPaintingShow($params, $poemId)
	{
		// Previous
		$subqueryPrevious = 'p.id = (SELECT MAX(p2.id) FROM painting p2 WHERE p2.id < '.$poemId.')';
		$qb_previous = $this->db->createQueryBuilder();
		
		$qb_previous->select("p.id, p.title")
		   ->from("painting", "p")
		   ->where('p.'.$params["field"].' = :biographyId')
		   ->setParameter('biographyId', $params["author"])
		   ->andWhere($subqueryPrevious);
		   
		// Next
		$subqueryNext = 'p.id = (SELECT MIN(p2.id) FROM painting p2 WHERE p2.id > '.$poemId.')';
		$qb_next = $this->db->createQueryBuilder();
		
		$qb_next->select("p.id, p.title")
		   ->from("painting", "p")
		   ->where('p.'.$params["field"].' = :biographyId')
		   ->setParameter('biographyId', $params["author"])
		   ->andWhere($subqueryNext);
		
		$res = array(
			"previous" => $qb_previous->execute()->fetch(),
			"next" => $qb_next->execute()->fetch()
		);

		return $res;
	}

	public function getPaintingByAuthorDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $authorId, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.title');
		
		$qb->select("pf.*")
		   ->from("painting", "pf")
		   ->where("pf.biography_id = :id")
		   ->setParameter("id", $authorId)
		   ;
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.title LIKE :search')
			   ->setParameter('search', $search);
		}
		if($count)
		{
			$qb->select("COUNT(DISTINCT pf.id) AS count");
			$results = $qb->execute()->fetch();

			return $results["count"];
		}
		else
		{
			$qb->groupBy("pf.id")
			   ->setFirstResult($iDisplayStart)
			   ->setMaxResults($iDisplayLength);
		}

		$dataArray = $qb->execute()->fetchAll();
		$entitiesArray = array();

        foreach ($dataArray as $data) {
            $entitiesArray[] = $this->build($data, true);
        }

		return $entitiesArray;
	}

	public function getPaintingByCountryDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $countryId, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("pf.title AS painting_title, bi.title AS biography_title, pf.id AS painting_id, bi.id AS biography_id")
		   ->from("painting", "pf")
		   ->innerjoin("pf", "biography", "bi", "pf.biography_id = bi.id")
		   ->where("bi.country_id = :id")
		   ->setParameter("id", $countryId)
		   ;
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.title LIKE :search')
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

	public function getPaintingByMovementDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $movementId, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("pf.title AS painting_title, bi.title AS biography_title, pf.id AS painting_id, bi.id AS biography_id")
		   ->from("painting", "pf")
		   ->innerjoin("pf", "biography", "bi", "pf.biography_id = bi.id")
		   ->where("pf.movement_id = :id")
		   ->setParameter("id", $movementId)
		   ;
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.title LIKE :search')
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

	public function getPaintingByLocationDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locationId, $count = false)
	{
		$qb = $this->db->createQueryBuilder();

		$aColumns = array( 'pf.id', 'pf.title', 'pf.id');
		
		$qb->select("pf.title AS painting_title, bi.title AS biography_title, pf.id AS painting_id, bi.id AS biography_id")
		   ->from("painting", "pf")
		   ->innerjoin("pf", "biography", "bi", "pf.biography_id = bi.id")
		   ->where("pf.location_id = :id")
		   ->setParameter("id", $locationId)
		   ;
		
		if(!empty($sortDirColumn))
		   $qb->orderBy($aColumns[$sortByColumn[0]], $sortDirColumn[0]);

		if(!empty($sSearch))
		{
			$search = "%".$sSearch."%";
			$qb->andWhere('pf.title LIKE :search')
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
}