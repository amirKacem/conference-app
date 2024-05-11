<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
               ->setEntityLabelInSingular('Conference Comment')
               ->setEntityLabelInPlural('Conference Comments')
               ->setSearchFields(['author','text','email'])
               ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add(EntityFilter::new('conference'));
    }

    public function configureFields(string $pageName): iterable
    {

        return [
            TextField::new('author'),
            EmailField::new('email'),
            TextField::new('state'),
            AssociationField::new('conference'),
            TextareaField::new('text')->hideOnIndex(),
            DateTimeField::new('createdAt')->onlyOnIndex(),
            ImageField::new('photoFilename')
                ->setBasePath('/uploads/photos')
                ->setLabel('Photo')
                ->onlyOnIndex()
        ];
    }

}
