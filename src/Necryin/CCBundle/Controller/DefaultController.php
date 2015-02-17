<?php

namespace Necryin\CCBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 */
class DefaultController extends Controller
{

    /**
     * Отрисовка оосновной страницы приложения
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('NecryinCCBundle:default:index.html.twig');
    }

}
