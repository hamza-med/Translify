<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Form\VersionType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Plugin' => 'Plugin',
                    'Theme' => 'Theme',
                ]
            ])
            ->add('poFile', FileType::class, [
                'label' => 'PO file',
                'constraints'=> [
                new NotBlank([
                    'message' => 'Please add a file to your project'
                ])],
                'mapped' => false,
                'required' => false,
            ])
            ->add('versions', CollectionType::class, [
                'entry_type' => VersionType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
