<?php

namespace Pinturus\Controller;

use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Pinturus\Form\Type\IndexSearchType;
use Pinturus\Service\MailerPinturus;
use Pinturus\Service\Captcha;
use Pinturus\Service\Gravatar;

use MatthiasMullie\Minify;

require_once __DIR__.'/../../../src/html2pdf_v4.03/Html2Pdf.php';
require_once __DIR__.'/../../simple_html_dom.php';

class IndexController
{
    public function indexAction(Request $request, Application $app)
    {
		$form = $this->createForm($app, null);
		$random = $app['repository.painting']->getRandomPainting();
		
        return $app['twig']->render('Index/index.html.twig', array('form' => $form->createView(), 'random' => $random));
    }
	
	public function indexSearchAction(Request $request, Application $app)
	{
		$search = $request->request->get("index_search");

		return $app['twig']->render('Index/resultIndexSearch.html.twig', array('search' => json_encode($search)));
	}

	public function indexSearchDatatablesAction(Request $request, Application $app, $search)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}
		$sSearch = json_decode($search);
		$entities = $app['repository.painting']->findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.painting']->findIndexSearch($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity->getId()));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity->getTitle().'</a>';
			
			$biography = $entity->getBiography();
			$row[] = $biography['title'];

			$country = $biography['country'];
			$row[] = '<img src="'.$request->getBaseUrl().'/photo/country/'.$country['flag'].'" class="flag">';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	public function readAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.painting']->find($id, true);

		$params = array();
		$biography = $entity->getBiography();
		$params["author"] = $biography['id'];
		$params["field"] = "biography_id";
		
		$browsingPaintings = $app['repository.painting']->browsingPaintingShow($params, $id);

		return $app['twig']->render('Index/read.html.twig', array('entity' => $entity, 'browsingPaintings' => $browsingPaintings));
	}

	public function readPDFAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.painting']->find($id, true);
		$content = $app['twig']->render('Index/pdf_painting.html.twig', array('entity' => $entity));

		$html2pdf = new \HTML2PDF('P','A4','fr');
		$html2pdf->WriteHTML($content);
		$file = $html2pdf->Output('painting.pdf');

		$response = new Response($file);
		$response->headers->set('Content-Type', 'application/pdf');

		return $response;
	}

	public function lastPaintingAction(Request $request, Application $app)
    {
		$entities = $app['repository.painting']->getLastEntries();

		return $app['twig']->render('Index/lastPainting.html.twig', array('entities' => $entities));
    }
	
	public function statPaintingAction(Request $request, Application $app)
    {
		$statistics = $app['repository.painting']->getStat();

		return $app['twig']->render('Index/statPainting.html.twig', array('statistics' => $statistics));
    }

	// Author
	public function authorAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.biography']->find($id, true);

		return $app['twig']->render('Index/author.html.twig', array('entity' => $entity));
	}

	public function authorDatatablesAction(Request $request, Application $app, $authorId)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.painting']->getPaintingByAuthorDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $authorId);
		$iTotal = $app['repository.painting']->getPaintingByAuthorDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $authorId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity->getId()));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity->getTitle().'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	// ENDAUTHOR
	
	// BY AUTHORS
	public function byAuthorsAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/byauthor.html.twig');
    }

	public function byAuthorsDatatablesAction(Request $request, Application $app)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.painting']->findPaintingByAuthor($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.painting']->findPaintingByAuthor($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			if(!empty($entity['id']))
			{
				$row = array();
				$show = $app['url_generator']->generate('author', array('id' => $entity['id']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['author'].'</a>';
				$row[] = $entity['number_paintings_by_author'];

				$output['aaData'][] = $row;
			}
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}

	// COUNTRY
	public function countryAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.country']->find($id, true);

		return $app['twig']->render('Index/country.html.twig', array('entity' => $entity));
	}

	public function countryDatatablesAction(Request $request, Application $app, $countryId)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.painting']->getPaintingByCountryDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $countryId);
		$iTotal = $app['repository.painting']->getPaintingByCountryDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $countryId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity["painting_id"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["painting_title"].'</a>';
			
			$show = $app['url_generator']->generate('author', array('id' => $entity["biography_id"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["biography_title"].'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	// BY COUNTRIES
	public function byCountriesAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/bycountry.html.twig');
    }
	
	public function byCountriesDatatablesAction(Request $request, Application $app)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.painting']->findPaintingByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.painting']->findPaintingByCountry($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			if(!empty($entity['id']))
			{
				$row = array();

				$show = $app['url_generator']->generate('country', array('id' => $entity['country_id']));
				$row[] = '<a href="'.$show.'" alt="Show"><img src="'.$request->getBaseUrl().'/photo/country/'.$entity['flag'].'" class="flag" /> '.$entity['country_title'].'</a>';

				$row[] = $entity['number_paintings_by_country'];

				$output['aaData'][] = $row;
			}
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	// MOVEMENT
	public function movementAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.movement']->find($id, true);

		return $app['twig']->render('Index/movement.html.twig', array('entity' => $entity));
	}

	public function movementDatatablesAction(Request $request, Application $app, $movementId)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.painting']->getPaintingByMovementDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $movementId);
		$iTotal = $app['repository.painting']->getPaintingByMovementDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $movementId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity["painting_id"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["painting_title"].'</a>';
			
			$show = $app['url_generator']->generate('author', array('id' => $entity["biography_id"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["biography_title"].'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	// BY MOVEMENTS
	public function byMovementsAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/bymovement.html.twig');
    }

	public function byMovementsDatatablesAction(Request $request, Application $app)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.painting']->findPaintingByMovement($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.painting']->findPaintingByMovement($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			if(!empty($entity['id']))
			{
				$row = array();

				$show = $app['url_generator']->generate('movement', array('id' => $entity['id']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['movement'].'</a>';
				$row[] = $entity['number_paintings_by_movement'];

				$output['aaData'][] = $row;
			}
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	// LOCATION
	public function locationAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.location']->find($id, true);

		return $app['twig']->render('Index/location.html.twig', array('entity' => $entity));
	}

	public function locationDatatablesAction(Request $request, Application $app, $locationId)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.painting']->getPaintingByLocationDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locationId);
		$iTotal = $app['repository.painting']->getPaintingByLocationDatatables($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, $locationId, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$show = $app['url_generator']->generate('read', array('id' => $entity["painting_id"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["painting_title"].'</a>';
			
			$show = $app['url_generator']->generate('author', array('id' => $entity["biography_id"]));
			$row[] = '<a href="'.$show.'" alt="Show">'.$entity["biography_title"].'</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	// BY LOCATIONS
	public function byLocationsAction(Request $request, Application $app)
    {
        return $app['twig']->render('Index/bylocation.html.twig');
    }

	public function byLocationsDatatablesAction(Request $request, Application $app)
	{
		$iDisplayStart = $request->query->get('iDisplayStart');
		$iDisplayLength = $request->query->get('iDisplayLength');
		$sSearch = $request->query->get('sSearch');

		$sortByColumn = array();
		$sortDirColumn = array();
			
		for($i=0 ; $i<intval($request->query->get('iSortingCols')); $i++)
		{
			if ($request->query->get('bSortable_'.intval($request->query->get('iSortCol_'.$i))) == "true" )
			{
				$sortByColumn[] = $request->query->get('iSortCol_'.$i);
				$sortDirColumn[] = $request->query->get('sSortDir_'.$i);
			}
		}

		$entities = $app['repository.painting']->findPaintingByLocation($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.painting']->findPaintingByLocation($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);

		foreach($entities as $entity)
		{
			if(!empty($entity['id']))
			{
				$row = array();

				$show = $app['url_generator']->generate('location', array('id' => $entity['id']));
				$row[] = '<a href="'.$show.'" alt="Show">'.$entity['location'].'</a>';
				$row[] = $entity['number_paintings_by_location'];

				$output['aaData'][] = $row;
			}
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}
	
	public function reloadCaptchaAction(Request $request, Application $app)
	{
		$captcha = new Captcha($app);

		$wordOrNumberRand = rand(1, 2);
		$length = rand(3, 7);

		if($wordOrNumberRand == 1)
			$word = $captcha->wordRandom($length);
		else
			$word = $captcha->numberRandom($length);

		$response = new Response(json_encode(array("new_captcha" => $captcha->generate($word))));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function reloadGravatarAction(Request $request, Application $app)
	{
		$gr = new Gravatar();

		$response = new Response(json_encode(array("new_gravatar" => $gr->getURLGravatar())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function pageAction(Request $request, Application $app, $name)
	{
		$entity = $app['repository.page']->findByName($name);
		
		return $app['twig']->render('Index/page.html.twig', array("entity" => $entity));
	}

	private function createForm($app, $entity)
	{
		$countryForms = $app['repository.country']->findAllForChoice();
		$movementForms = $app['repository.movement']->findAllForChoice();
		$form = $app['form.factory']->create(IndexSearchType::class, null, array("countries" => $countryForms, "movements" => $movementForms));
		
		return $form;
	}
}