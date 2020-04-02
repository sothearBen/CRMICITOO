<?php

namespace App\Controller\ElFinder;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @Route("/elfinder")
 */
class ConnectorController extends AbstractController
{
    /**
     * @Route("/connector", name="elfinder_connector")
     */
    public function index(Request $request, KernelInterface $kernel, LoggerInterface $logger)
    {
        $logger->debug(print_r(['start'], true));
        $projectDir = $kernel->getProjectDir();
        $schemeAndHttpHost = $request->getSchemeAndHttpHost();
        // Documentation for connector options:
        // https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
        $opts = array(
            // 'debug' => true,
            'roots' => array(
                // Items volume
                array(
                    'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
                    'path'          => './files/',
                    'URL'           => $schemeAndHttpHost . '/files/', // URL to files (REQUIRED)
                    'trashHash'     => 't1_Lw',                     // elFinder's hash of trash folder
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
                    'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'),
                    'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
                    'accessControl' => 'access'                     // disable and hide dot starting files (OPTIONAL)
                ),
                // Trash volume
                array(
                    'id'            => '1',
                    'driver'        => 'Trash',
                    'path'          => './files/.trash/',
                    'tmbURL'        => $schemeAndHttpHost . '/./files/.trash/.tmb/',
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/', // to make hash same to Linux one on windows too
                    'uploadDeny'    => array('all'),                // Recomend the same settings as the original volume that uses the trash
                    'uploadAllow'   => array('image/x-ms-bmp', 'image/gif', 'image/jpeg', 'image/png', 'image/x-icon', 'text/plain'), // Same as above
                    'uploadOrder'   => array('deny', 'allow'),      // Same as above
                    'accessControl' => 'access',                    // Same as above
                ),
            )
        );

        // run elFinder
        $connector = new \elFinderConnector(new \elFinder($opts));
        $connector->run();
        
        $logger->debug(print_r(['end'], true));
        exit();
        return new Response();
    }
}
