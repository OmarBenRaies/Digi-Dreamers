<?php

namespace App\Form;

use App\Entity\Association;
use App\Entity\Don;
use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('somme' )
            ->add('evenement',EntityType::class,['class'=> Evenement::Class,
                'query_builder' => function (EvenementRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.total > 0')
                        ->andWhere('e.date < :currentDate')
                        ->setParameter('currentDate', new \DateTime())
                        ->orderBy('e.date', 'ASC');
                },
                'choice_label'=>'titre',
                'label'=>'Evenement',
                'required' => false,
            ])
            ->add('association',EntityType::class,['class'=> Association::Class,
                'choice_label'=>'nom',
                'label'=>'Association',
                'required' => false,
                ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Don::class,
        ]);
    }
}
