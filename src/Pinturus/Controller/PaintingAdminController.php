<?php

namespace Pinturus\Controller;

use Pinturus\Entity\Painting;
use Pinturus\Form\Type\PaintingType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

class PaintingAdminController
{
	public function indexAction(Request $request, Application $app)
	{
		return $app['twig']->render('Painting/index.html.twig');
	}

	public function indexDatatablesAction(Request $request, Application $app)
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
		
		$entities = $app['repository.painting']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.painting']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

		$output = array(
			"sEcho" => $request->query->get('sEcho'),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iTotal,
			"aaData" => array()
		);
		
		foreach($entities as $entity)
		{
			$row = array();
			$row[] = $entity->getId();
			$row[] = $entity->getTitle();
			
			$show = $app['url_generator']->generate('paintingadmin_show', array('id' => $entity->getId()));
			$edit = $app['url_generator']->generate('paintingadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">Lire</a> - <a href="'.$edit.'" alt="Edit">Modifier</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

    public function newAction(Request $request, Application $app)
    {
		$entity = new Painting();
        $form = $this->createForm($app, $entity);

		return $app['twig']->render('Painting/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app)
	{
		$entity = new Painting();
        $form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		if($entity->getPhoto() == null)
			$form->get("photo")->addError(new FormError('Ce champ ne peut pas être vide'));

		if($form->isValid())
		{
			$image = uniqid()."_".$entity->getPhoto()->getClientOriginalName();
			$entity->getPhoto()->move("photo/painting/", $image);
			$entity->setPhoto($image);
			$id = $app['repository.painting']->save($entity);

			$redirect = $app['url_generator']->generate('paintingadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
		
		return $app['twig']->render('Painting/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.painting']->find($id, true);
	
		return $app['twig']->render('Painting/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.painting']->find($id);
		$form = $this->createForm($app, $entity);
	
		return $app['twig']->render('Painting/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.painting']->find($id);
		$currentImage = $entity->getPhoto();
		$form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		
		if($form->isValid())
		{
			if(!is_null($entity->getPhoto()))
			{
				$image = uniqid()."_".$entity->getPhoto()->getClientOriginalName();
				$entity->getPhoto()->move("photo/painting/", $image);
			}
			else
				$image = $currentImage;

			$entity->setPhoto($image);
			$id = $app['repository.painting']->save($entity, $id);

			$redirect = $app['url_generator']->generate('paintingadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Painting/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	private function createForm($app, $entity)
	{
		$typeForms = $app['repository.type']->findAllForChoice();
		$locationForms = $app['repository.location']->findAllForChoice();
		$movementForms = $app['repository.movement']->findAllForChoice();
		$biographyForms = $app['repository.biography']->findAllForChoice();
		
		$form = $app['form.factory']->create(PaintingType::class, $entity, array("types" => $typeForms, "locations" => $locationForms, "movements" => $movementForms, "biographies" => $biographyForms));
		
		return $form;
	}
	
	private function checkForDoubloon($entity, $form, $app)
	{
		if($entity->getTitle() != null)
		{
			$checkForDoubloon = $app['repository.painting']->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("title")->addError(new FormError('Cette entrée existe déjà !'));
		}
	}
}