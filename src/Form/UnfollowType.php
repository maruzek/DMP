<?php

namespace App\Form;

use App\Entity\Follow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnfollowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-outline-secondary w-100 mb-3'
            ],
            'label' => 'Přestat sledovat'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Follow::class,
        ]);
    }
}
