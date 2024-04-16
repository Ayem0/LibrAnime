<?php

namespace App\Form;

use App\Entity\Anime;
use App\Entity\Categorie;
use App\Entity\Liste;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\AnimeAutocompleteField;


class SearchFormType extends AbstractType
{
    
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, [
                'label' => 'Rechercher :',
                'attr' => [
                    'placeholder' => 'Search a anime',
                    'required' => true, // Le champ sera requis
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Search', // Texte du bouton de soumission
            ]);
    }
    
    /*
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('anime', AnimeAutocompleteField::class, [
            'required' => false,
            'attr' => ['data-controller' => 'autocomplete'],
            // Autres options...
        ]);
        ;
    }*/
}
