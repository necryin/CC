<?php
/**
 * User: human
 * Date: 18.02.15
 */
namespace Necryin\CCBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Основной контроллер приложения для веб форм
 * Class DefaultController
 */
class DefaultController extends Controller
{

    /**
     * Отрисовка основной страницы приложения
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('NecryinCCBundle:default:index.html.twig');
    }

}
