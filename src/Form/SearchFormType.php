<?php

namespace App\Form;

use App\Entity\Anime;
use App\Entity\Categorie;
use App\Entity\Liste;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Anime', EntityType::class, [
                'class' => Anime::class,
                'choice_label' => 'nom',
                'placeholder' => 'Search an anime',
                'multiple' => false,
                'autocomplete' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Anime::class,
        ]);
    }
}
