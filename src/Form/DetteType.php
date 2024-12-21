<?php

namespace App\Form;

use App\Entity\Dette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant', MoneyType::class, [
                'currency' => 'XOF', // Modifier selon votre devise
                'label' => 'Montant',
                'attr' => [
                    'placeholder' => 'Entrez le montant de la dette',
                ],
            ])
            ->add('montantVerser', MoneyType::class, [
                'currency' => 'XOF',
                'label' => 'Montant versé',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez le montant versé (optionnel)',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ajoutez une description ou des détails sur la dette',
                    'rows' => 3,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dette::class,
        ]);
    }
}
