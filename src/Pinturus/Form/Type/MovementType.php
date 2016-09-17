<?php

namespace Pinturus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MovementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "Titre"
            ))
			->add('text', TextareaType::class, array(
                'constraints' => new Assert\NotBlank(), "label" => "Texte", 'attr' => array('class' => 'redactor')
            ))
			->add('photo', FileType::class, array('data_class' => null, "label" => "Photo", "required" => true
            ))

            ->add('save', SubmitType::class, array('label' => 'Sauvegarder', 'attr' => array('class' => 'btn btn-success')))
			;
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array());
	}
	
    public function getName()
    {
        return 'movement';
    }
}