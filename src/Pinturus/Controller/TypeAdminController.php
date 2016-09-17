<?php

namespace Pinturus\Controller;

use Pinturus\Entity\Type;
use Pinturus\Form\Type\TypeType;
use Silex\Application;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;

class TypeAdminController
{
	public function indexAction(Request $request, Application $app)
	{
		return $app['twig']->render('Type/index.html.twig');
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
		
		$entities = $app['repository.type']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch);
		$iTotal = $app['repository.type']->getDatatablesForIndex($iDisplayStart, $iDisplayLength, $sortByColumn, $sortDirColumn, $sSearch, true);

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
			
			$show = $app['url_generator']->generate('typeadmin_show', array('id' => $entity->getId()));
			$edit = $app['url_generator']->generate('typeadmin_edit', array('id' => $entity->getId()));
			
			$row[] = '<a href="'.$show.'" alt="Show">Lire</a> - <a href="'.$edit.'" alt="Edit">Modifier</a>';

			$output['aaData'][] = $row;
		}

		$response = new Response(json_encode($output));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

    public function newAction(Request $request, Application $app)
    {
		$entity = new Type();
        $form = $this->createForm($app, $entity);

		return $app['twig']->render('Type/new.html.twig', array('form' => $form->createView()));
    }
	
	public function createAction(Request $request, Application $app)
	{
		$entity = new Type();
        $form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		if($entity->getPhoto() == null)
			$form->get("photo")->addError(new FormError('Ce champ ne peut pas être vide'));

		if($form->isValid())
		{
			$image = uniqid()."_".$entity->getPhoto()->getClientOriginalName();
			$entity->getPhoto()->move("photo/type/", $image);
			$entity->setPhoto($image);
			$id = $app['repository.type']->save($entity);

			$redirect = $app['url_generator']->generate('typeadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
		
		return $app['twig']->render('Type/new.html.twig', array('form' => $form->createView()));
	}
	
	public function showAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.type']->find($id, true);
	
		return $app['twig']->render('Type/show.html.twig', array('entity' => $entity));
	}
	
	public function editAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.type']->find($id);
		$form = $this->createForm($app, $entity);
	
		return $app['twig']->render('Type/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}

	public function updateAction(Request $request, Application $app, $id)
	{
		$entity = $app['repository.type']->find($id);
		$currentImage = $entity->getPhoto();
		$form = $this->createForm($app, $entity);
		$form->handleRequest($request);
		
		$this->checkForDoubloon($entity, $form, $app);
		
		if($form->isValid())
		{
			if(!is_null($entity->getPhoto()))
			{
				$image = uniqid()."_".$entity->getPhoto()->getClientOriginalName();
				$entity->getPhoto()->move("photo/type/", $image);
			}
			else
				$image = $currentImage;

			$entity->setPhoto($image);
			$id = $app['repository.type']->save($entity, $id);

			$redirect = $app['url_generator']->generate('typeadmin_show', array('id' => $id));

			return $app->redirect($redirect);
		}
	
		return $app['twig']->render('Type/edit.html.twig', array('form' => $form->createView(), 'entity' => $entity));
	}
	
	private function createForm($app, $entity)
	{
		$form = $app['form.factory']->create(TypeType::class, $entity);
		
		return $form;
	}
	
	private function checkForDoubloon($entity, $form, $app)
	{
		if($entity->getTitle() != null)
		{
			$checkForDoubloon = $app['repository.type']->checkForDoubloon($entity);

			if($checkForDoubloon > 0)
				$form->get("title")->addError(new FormError('Cette entrée existe déjà !'));
		}
	}
}