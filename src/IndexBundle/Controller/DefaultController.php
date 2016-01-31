<?php

namespace IndexBundle\Controller;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
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
        $result = $em
            ->createQuery("select p from IndexBundle:Post p where p.isActive = true order by p.createdAt desc")
            ->setMaxResults(5)
            ->getResult();
        $categories = $this->getCategories($em);

        return $this->render('IndexBundle:Default:home.html.twig',
            array(
                'relevantes' => $result,
                'posts' => $result,
            	'categories' => $categories,
            ));
    }


    /**
     * @Route("/post/{id}/view", name="post_view")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewPost(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository("IndexBundle:Post")->find($id);
        if (!$post) {
            throw new InvalidArgumentException("No se encontró una publicación relacinada al id");
        }
        $categories = $this->getCategories($em);
        $slider = $em->createQuery("select s from IndexBundle:Slider s where s.postId = $id")
            ->getResult();
        return $this->render("IndexBundle:Default:viewPost.html.twig", array(
            'categories' => $categories,
            'post' => $post,
            'slider' => $slider,
        ));

    }

    private function getCategories($em)
    {
        return $em->createQuery("select c from IndexBundle:Category c where c.isActive = true")
            ->getResult();
    }
}
