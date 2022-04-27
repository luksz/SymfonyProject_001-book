<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
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


  
    
    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Conference Comment')
            ->setEntityLabelInPlural('Conference Comments')
            ->setSearchFields(['author', 'text', 'email'])
            ->setDefaultSort(['createdAt' => 'DESC'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
        {
            return $filters
                ->add(EntityFilter::new('conference'))
            ;
        }
    

    public function configureFields(string $pageName): iterable
{
    yield AssociationField::new('conference');
            yield TextField::new('author');
            yield EmailField::new('email');
            yield TextareaField::new('text')
                ->hideOnIndex()
            ;
            yield TextField::new('photoFilename')
                ->onlyOnIndex()
            ;
    
            $createdAt = DateTimeField::new('createdAt')->setFormTypeOptions([
                'html5' => true,
                'years' => range(date('Y'), date('Y') + 5),
                'widget' => 'single_text',
            ]);
            if (Crud::PAGE_EDIT === $pageName) {
                yield $createdAt->setFormTypeOption('disabled', true);
            } else {
                yield $createdAt;
            }

    // return [
    //     FormField::addPanel('Conference'),
    //     AssociationField::new('conference')
    //         ->setRequired(true)
    //         ->setHelp('help text'),
    //     FormField::addPanel('Comment'),
    //     TextField::new('author')
    //         ->setHelp('Your name'),
    //     TextEditorField::new('text', 'Comment')
    //         ->setHelp('help text'),
    //     EmailField::new('email', 'Email Address')
    //         ->setHelp('Your valid email address'),
    //     DateTimeField::new('createdAt'),
    //     TextField::new('photoFilename')
    // ];

}
}
