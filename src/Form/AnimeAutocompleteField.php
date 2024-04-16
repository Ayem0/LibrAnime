<?php

namespace App\Form;

use App\Entity\Anime;
use App\Repository\AnimeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class AnimeAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Anime::class,
            'placeholder' => 'Search a Anime',
            'choice_label' => 'nom',
            'attr' => [
                'data-controller' => 'custom-autocomplete',
            ],
            'query_builder' => function (AnimeRepository $animeRepository) {
                return $animeRepository->createQueryBuilder('anime');
            },
            // 'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
