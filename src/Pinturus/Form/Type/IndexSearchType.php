<?php

namespace Pinturus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class IndexSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$countryArray = $options["countries"];
		$movementArray = $options["movements"];
	
        $builder
            ->add('title', TextType::class, array("label" => "Titre", "required" => false))
			->add('text', TextType::class, array("label" => "Mots-clés", "required" => false, "attr" => array("class" => "tagit full_width")))
			->add('author', TextType::class, array("label" => "Auteur", "required" => false))
			->add('country', ChoiceType::class, array(
											'label' => 'Pays', 
											'required' => false, 
											'placeholder' => 'Sélectionnez un pays', 
											'multiple' => false, 
											'expanded' => false,
										    'choices' => $countryArray))
			->add('movement', ChoiceType::class, array(
											'label' => 'Mouvement', 
											'required' => false, 
											'placeholder' => 'Sélectionnez un mouvement', 
											'multiple' => false, 
											'expanded' => false,
										    'choices' => $countryArray))
            ->add('search', SubmitType::class, array('label' => 'Rechercher', "attr" => array("class" => "btn btn-primary")))
			;
    }
	
	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
			"countries" => null,
			"movements" => null
		));
	}

    public function getName()
    {
        return 'index_search';
    }
}