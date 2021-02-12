<?php

namespace App\Controller\Back;

use App\Entity\ArticleCategory;
use App\Form\Back\ArticleCategoryBatchType;
use App\Form\Back\ArticleCategoryFilterType;
use App\Form\Back\ArticleCategoryType;
use App\Manager\Back\ArticleCategoryManager;
use App\Repository\ArticleCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/back/article/category")
 */
class ArticleCategoryController extends AbstractController
{
    /**
     * @var ArticleCategoryRepository     */
    private $articleCategoryRepository;

    /**
     * @var ArticleCategoryManager     */
    private $articleCategoryManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ArticleCategoryRepository $articleCategoryRepository, ArticleCategoryManager $articleCategoryManager, TranslatorInterface $translator)
    {
        $this->articleCategoryRepository = $articleCategoryRepository;
        $this->articleCategoryManager = $articleCategoryManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/search/{page}", name="back_article_category_search", methods="GET|POST")
     */
    public function search(Request $request, Session $session, $page = null)
    {
        $page ?: $page = $session->get('back_article_category_page', 1);

        $formFilter = $this->createForm(ArticleCategoryFilterType::class, null, ['action' => $this->generateUrl('back_article_category_search', ['page' => 1])]);
        $formFilter->handleRequest($request);
        $data = $this->articleCategoryManager->configFormFilter($formFilter)->getData();
        $articleCategories = $this->articleCategoryRepository->searchBack($request, $session, $data, $page);
        $queryData = $this->articleCategoryManager->getQueryData($data);
        $formBatch = $this->createForm(ArticleCategoryBatchType::class, null, [
            'action' => $this->generateUrl('back_article_category_search', array_merge(['page' => $page], $queryData)),
            'article_categories' => $articleCategories,
        ]);
        $formBatch->handleRequest($request);
        if ($formBatch->isSubmitted() && $formBatch->isValid()) {
            $url = $this->articleCategoryManager->dispatchBatchForm($formBatch);
            if ($url) {
                return $this->redirect($url);
            }
        }

        return $this->render('back/article_category/search/index.html.twig', [
            'article_categories' => $articleCategories,
            'form_filter' => $formFilter->createView(),
            'form_batch' => $formBatch->createView(),
            'form_delete' => $this->createFormBuilder()->getForm()->createView(),
            'number_page' => ceil(count($articleCategories) / $formFilter->get('number_by_page')->getData()) ?: 1,
            'page' => $page,
            'query_data' => $queryData,
        ]);
    }

    /**
     * @Route("/create", name="back_article_category_create", methods="GET|POST")
     */
    public function create(Request $request): Response
    {
        $articleCategory = new ArticleCategory();
        $form = $this->createForm(ArticleCategoryType::class, $articleCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($articleCategory);
            $em->flush();
            $msg = $this->translator->trans('article_category.create.flash.success', ['%identifier%' => $articleCategory], 'back_messages');
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('back_article_category_search');
        }

        return $this->render('back/article_category/create.html.twig', [
            'article_category' => $articleCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/update/{id}", name="back_article_category_update", methods="GET|POST")
     */
    public function update(Request $request, ArticleCategory $articleCategory): Response
    {
        $form = $this->createForm(ArticleCategoryType::class, $articleCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $msg = $this->translator->trans('article_category.update.flash.success', [], 'back_messages');
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('back_article_category_search');
        }

        return $this->render('back/article_category/update.html.twig', [
            'article_category' => $articleCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete", name="back_article_category_delete", methods="GET|POST")
     */
    public function delete(Request $request): Response
    {
        $articleCategories = $this->articleCategoryManager->getArticleCategories();
        $formBuilder = $this->createFormBuilder();
        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($articleCategories) {
            $result = $this->articleCategoryManager->validationDelete($articleCategories);
            if (true !== $result) {
                $event->getForm()->addError(new FormError($result));
            }
        });
        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach ($articleCategories as $articleCategory) {
                $em->remove($articleCategory);
            }
            try {
                $em->flush();
                $this->addFlash('success', $this->translator->trans('article_category.delete.flash.success', [], 'back_messages'));
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->addFlash('warning', $e->getMessage());
            }

            return $this->redirectToRoute('back_article_category_search');
        }

        return $this->render('back/article_category/delete.html.twig', [
            'article_categories' => $articleCategories,
            'form' => $form->createView(),
        ]);
    }
}