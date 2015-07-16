<?php
/**
 * @author Kirilenko Georgii
 */
namespace Necryin\CCBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Основной контроллер приложения для веб форм
 */
class MainController extends Controller
{

    /**
     * Отрисовка основной страницы приложения
     */
    public function indexAction()
    {
        return $this->render('NecryinCCBundle::base.html.twig');
    }

}
