<?php

namespace Pinturus\Controller;

use Silex\Application;
use Pinturus\Entity\Contact;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Pinturus\Form\Type\SendPaintingType;
use Pinturus\Service\MailerPinturus;

class SendPaintingController
{
    public function indexAction(Request $request, Application $app, $paintingId)
    {
		$form = $app['form.factory']->create(SendPaintingType::class, null);
		
		$app['locale'] = $app['request']->getLocale();

        return $app['twig']->render('Index/send_painting.html.twig', array('form' => $form->createView(), 'paintingId' => $paintingId));
    }
	
	public function sendAction(Request $request, Application $app, $paintingId)
	{
		parse_str($request->request->get('form'), $form_array);

        $form = $app['form.factory']->create(SendPaintingType::class, $form_array);
		
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid())
		{
			$data = (object)($request->request->get($form->getName()));
			$entity = $app['repository.painting']->find($paintingId, true);
		
			$content = $app['twig']->render('Index/send_painting_message_content.html.twig', array(
				"data" => $data,
				"entity" => $entity
			));

			$mailer = new MailerPinturus($app['swiftmailer.options']);
			
			$mailer->setSubject($data->subject);
			$mailer->setSendTo($data->recipientMail);
			$mailer->setBody($content);
			
			$mailer->send();
			
			$response = new Response(json_encode(array("result" => "ok")));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
		}

		$res = array("result" => "error");
		
		$res["content"] = $app['twig']->render('Index/send_painting_form.html.twig', array('form' => $form->createView(), 'paintingId' => $paintingId));
		
		$response = new Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
}