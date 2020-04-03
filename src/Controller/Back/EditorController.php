<?php

namespace App\Controller\Back;

use App\Entity\Editor;
use App\Repository\EditorRepository;
use App\Form\Back\EditorBatchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Route("/back/editor")
 */
class EditorController extends AbstractController
{

    /**
     *
     * @var EditorRepository     */
    private $editorRepository;
    
    /**
     *
     * @var TranslatorInterface
     */
    private $translator;
    
    public function __construct(EditorRepository $editorRepository, TranslatorInterface $translator)
    {
        $this->editorRepository = $editorRepository;
        $this->translator = $translator;
    }
    
    /**
     * @Route("/update", name="back_editor_update", methods="GET|POST")
     */
    public function update(Request $request): Response
    {
        $oldEditors = new ArrayCollection($this->editorRepository->findAll() ?? []);
        $form = $this->createForm(EditorBatchType::class, null, [
            'editors' => clone $oldEditors,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $editors = $form->get('editors')->getData();
            
            $em = $this->getDoctrine()->getManager();
            foreach ($oldEditors as $oldEditor) {
                if (!$editors->contains($oldEditor)) {
                    $em->remove($oldEditor);
                }
            }
            foreach ($editors as $editor) {
                $em->persist($editor);
            }
            $em->flush();
            $msg = $this->translator->trans('editor.update.flash.success', [], 'back_messages');
            $this->addFlash('success', $msg);
            return $this->redirectToRoute('back_editor_update');
        }

        return $this->render('back/editor/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
