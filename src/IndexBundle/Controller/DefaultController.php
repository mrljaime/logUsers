<?php

namespace IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="principal")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->createQuery("select p from IndexBundle:Post p")->getResult();

        return $this->render('IndexBundle:Default:index.html.twig',
            array(
                'relevantes' => $result,
            ));
    }
}
