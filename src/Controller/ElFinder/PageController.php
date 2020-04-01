<?php

namespace App\Controller\ElFinder;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/elfinder")
*/
class PageController extends AbstractController
{
    /**
     * @Route("/read", name="elfinder_read")
     */
    public function index()
    {
        return $this->render('elfinder/index.html.twig', [
        
        ]);
    }
}