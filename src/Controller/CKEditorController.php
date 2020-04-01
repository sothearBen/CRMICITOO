<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CKEditorController extends AbstractController
{
    /**
     * @Route("/ckeditor", name="ck_editor")
     */
    public function index()
    {
        return $this->render('ck_editor/index.html.twig', [
            
        ]);
    }
}
