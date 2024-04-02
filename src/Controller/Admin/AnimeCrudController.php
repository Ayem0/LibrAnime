<?php

namespace App\Controller\Admin;

use App\Entity\Anime;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AnimeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Anime::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom'),
            AssociationField::new('categorie')->autocomplete(),
        ];
    }
    
}
