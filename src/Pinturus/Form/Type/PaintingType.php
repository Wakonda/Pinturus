<?php

namespace Pinturus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PaintingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$typeArray = $options["types"];
		$locationArray = $options["locations"];
		$movementArray = $options["movements"];
		$biographyArray = $options["biographies"];

        $builder
            ->add('title', TextType::class, array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Titre'
            ))
			->add('text', TextareaType::class, array(
                'attr' => array('class' => 'redactor'), 'label' => 'Texte'
            ))
			->add('yearStart', IntegerType::class, array(
                'label' => 'Année de début'
            ))
			->add('yearEnd', IntegerType::class, array(
                'label' => 'Année de fin'
            ))
			->add('height', IntegerType::class, array(
                'label' => 'Hauteur'
            ))
			->add('width', IntegerType::class, array(
                'label' => 'Largeur'
            ))
			->add('photo', FileType::class, array('data_class' => null, "label" => "Image", "required" => true))
            ->add('type', ChoiceType::class, array(
											'label' => 'Type', 
											'multiple' => false,
											'required' => false,
											'expanded' => false,
											'placeholder' => 'Choisissez une option',
											'choices' => $typeArray
											))
            ->add('location', ChoiceType::class, array(
											'label' => 'Localisation', 
											'multiple' => false,
											'required' => false,
											'expanded' => false,
											'placeholder' => 'Choisissez une option',
											'choices' => $locationArray
											))
            ->add('movement', ChoiceType::class, array(
											'label' => 'Mouvement', 
											'multiple' => false,
											'required' => false,
											'expanded' => false,
											'placeholder' => 'Choisissez une option',
											'choices' => $movementArray
											))
            ->add('biography', ChoiceType::class, array(
											'label' => 'Biographie',
											'multiple' => false,
											'required' => false,
											'expanded' => false,
											'placeholder' => 'Choisissez une option',
											'choices' => $biographyArray
            ))

            ->add('save', SubmitType::class, array('label' => 'Sauvegarder', 'attr' => array('class' => 'btn btn-success')));
    }

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"biographies" => null,
			"types" => null,
			"locations" => null,
			"movements" => null
		));
	}
	
    public function getName()
    {
        return 'painting';
    }
}