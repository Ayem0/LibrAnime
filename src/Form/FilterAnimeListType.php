<?php

namespace App\Form;
use App\Entity\Anime;
use App\Entity\Categorie;
use App\Entity\Format;
use App\Entity\Season;
use App\Entity\Status;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Data\AnimeListData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterAnimeListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class, [
                'label' => 'Search',
                'required' => false,
                'label_attr' => ['style' => 'color: white;']
            ])
            ->add('categories', EntityType::class, [
                'label' => 'Categorie',
                'required' => false,
                'class' => Categorie::class,
                'multiple' => true,
                'autocomplete' => true,
                'label_attr' => ['style' => 'color: white;']
            ])
            ->add('formats', EntityType::class, [
                'label' => 'Format',
                'required' => false,
                'class' => Format::class,
                'multiple' => true,
                'autocomplete' => true,
                'label_attr' => ['style' => 'color: white;']
            ])
            ->add('seasons', EntityType::class, [
                'label' => 'Season',
                'required' => false,
                'class' => Season::class,
                'multiple' => true,
                'autocomplete' => true,
                'label_attr' => ['style' => 'color: white;']
            ])
            ->add('status', EntityType::class, [
                'label' => 'Status',
                'required' => false,
                'class' => Status::class,
                'multiple' => true,
                'autocomplete' => true,
                'label_attr' => ['style' => 'color: white;']
            ])
            ->add('min', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'min' => 1940,
                    'max' => 2025,
                    'placeholder' => 'Year Min'
                ]
                
            ])
            ->add('max', IntegerType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'min' => 1940,
                    'max' => 2025,
                    'placeholder' => 'Year Max'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AnimeListData::class,
            'method' => 'GET',
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
