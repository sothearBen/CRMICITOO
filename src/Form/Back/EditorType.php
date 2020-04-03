<?php

namespace App\Form\Back;

use App\Entity\Editor;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('position', HiddenType::class, [
                'attr' => [
                    'class' => 'position',
                ],
            ])
            ->add('key', TextType::class, [
                'label' => 'editor.label.key',
            ])
            ->add('body', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'editor',
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Editor::class,
            'translation_domain' => 'back_messages',
        ]);
    }
}
