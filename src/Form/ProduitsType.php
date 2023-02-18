<?php

namespace App\Form;

use App\Entity\Produits;
use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Form\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class ProduitsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ref')
            ->add('Description')
            ->add('Prix')
            ->add('image', FileType::class,array('data_class'=>null))
            ->add('Qte_stock')
            ->add('Categorie' ,EntityType::class,[
            'class' => Categories::class,
            'choice_label'=>'Nom_cat',
            'expanded' => false,
            'multiple' => false,
            ]);
            
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produits::class,
        ]);
    }
}
