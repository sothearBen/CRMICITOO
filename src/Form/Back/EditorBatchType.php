<?php

namespace App\Form\Back;

use App\Entity\Editor;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditorBatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('editors', CollectionType::class, [
                'label' => false,
                'entry_options' => [
                    'label' => false,
                ],
                'entry_type' => EditorType::class,
                'data' => $options['editors'],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => [
                    'class' => 'collection-selector',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'editors' => null,
            'translation_domain' => 'back_messages',
        ]);
    }
}
