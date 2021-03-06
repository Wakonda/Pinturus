<?php

namespace Pinturus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mail', TextType::class, array('constraints' => array(new Assert\Email(), new Assert\NotBlank()), "label" => "Email"))
            ->add('subject', TextType::class, array('constraints' => new Assert\NotBlank(), "label" => "Sujet"))
			->add('message', TextareaType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "Texte", 'attr' => array('class' => TextType::class)
            ))
			->add('send', SubmitType::class, array('label' => 'Envoyer', 'attr' => array('class' => 'btn btn-success')))
			;
    }

    public function getName()
    {
        return 'contact';
    }
}