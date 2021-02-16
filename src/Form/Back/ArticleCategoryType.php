<?php

namespace App\Form\Back;

use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Repository\ArticleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'article_category.label.name',
            ])
            ->add('displayedHome', CheckboxType::class, [
                'label' => 'article_category.label.displayed_home',
            ])
            ->add('displayedMenu', CheckboxType::class, [
                'label' => 'article_category.label.displayed_menu',
            ])
            ->add('articles', EntityType::class, [
                'label' => 'article_category.label.articles',
                'label_html' => true,
                'class' => Article::class,
                'query_builder' => function (ArticleRepository $er) {
                    return $er->createQueryBuilder('a')

                            ->orderBy('a.title', 'ASC');
                },
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ArticleCategory::class,
            'translation_domain' => 'back_messages',
        ]);
    }
}