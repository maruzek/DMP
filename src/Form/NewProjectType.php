<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Název'
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => '3'
                ],
                'label' => 'Popis',
                'required' => false
            ])
            ->add('link', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Odkaz (např. na Facebookovou stránku)',
                'required' => false
            ])
            ->add('tag', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-select'
                ],
                'label' => 'Typ (kroužek/projekt)',
                'choices' => [
                    'Vyberte' => null,
                    'Kroužek' => 'kroužek',
                    'Projekt' => 'projekt'
                ]
            ])
            ->add('color', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-select'
                ],
                'label' => 'Barva (do jaké barvy bude stránka laděna)',
                'choices' => [
                    'Žádná (bílá)' => 'white',
                    'Červená' => 'red',
                    'Modrá' => 'blue',
                    'Zelená' => 'green',
                    'Žlutá' => 'yellow'
                ]
            ])
            ->add('attach', FileType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Profilový obrázek',
                'mapped' => false
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary mt-3'
                ],
                'label' => 'Vytvořit'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
