<?php

namespace PicboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class DefaultController
 * @package PicboardBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("picboard/login", name="login")
     */
    public function indexAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $data = array(
            'error' => $error,
        );

        return $this->render("PicboardBundle:picboard:login.html.twig", $data);
    }

    /**
     * @Route("picboard/dologin", name="pic_dologin")
     */
    public function doLoginAction()
    {
    }

    /**
     * @Route("picboard/index", name="picboard.index")
     */
    public function homeAction()
    {
        return $this->render("PicboardBundle::base.html.twig");
    }
}
