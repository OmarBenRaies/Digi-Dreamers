<?php

namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class EvenementType extends AbstractType

{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date',\Symfony\Component\Form\Extension\Core\Type\DateType::class,['attr'=>['type'=>'date']])
            ->add('lieu')
            ->add('nbr_participant')
            ->add('titre')
            ->add('description')
            ->add('prix')
            ->add('url_image',FileType::class,array('data_class'=>null))

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
