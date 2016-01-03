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
        if($request->get("debug") == "true") {
            $url = $request->getUriForPath("/admin/index");
            return $this->render('IndexBundle:Default:index.html.twig',
                array(
                    "url" => $url,
                ));
        }else{
            return $this->render('IndexBundle:Default:index.html.twig');
        }
    }
}
