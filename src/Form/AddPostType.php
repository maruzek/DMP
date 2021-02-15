<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'floatingTextarea2',
                    'placeholder' => 'Leave a comment here',
                    'style' => 'height: 70px;'
                ],
                'label' => 'Nový příspěvek'
            ])
            ->add('privacy', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label' => 'Pouze pro členy',
                'required' => false,
                'value' => true
            ])
            ->add('media', FileType::class, [
                'attr' => [
                    'class' => 'form-control form-control-sm',
                ],
                'mapped' => false,
                'required' => false,
                'multiple' => true
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-success'
                ],
                'label' => 'Přidat příspěvek'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
