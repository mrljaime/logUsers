<?php

namespace IndexBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use IndexBundle\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\View\ChoiceListView;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;



/**
 * Class PostController
 * @package IndexBundle\Controller
 * @Route("/admin/post")
 */
class PostController extends Controller
{
    /**
     * @Route("/", name="post.index")
     */
    public function postAction()
    {
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository("IndexBundle:Post")->findAll();

        return $this->render("IndexBundle:admin:post.html.twig", array(
            "active" => "publicaciones",
            "posts" => $post,
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/create", name="post.create")
     */
    public function handlePostAction(Request $request)
    {
        $post = new Post();
        $em = $this->getDoctrine()->getManager();

        $categories = $em->createQuery("select c from IndexBundle:Category c where c.isActive = true")
            ->getResult();

        $data = array();

        foreach($categories as $catego){
            $data[] = array(
                $catego->getName() => $catego->getId()
            );
        }


        $form = $this->createFormBuilder($post)
            ->add("title", TextType::class, array("label" => "Titulo"))
            ->add("shortDescription", TextType::class, array("label" => "Descripcion breve"))
            ->add("content", TextareaType::class, array("label" => "Contenido"))
            ->add("userId", HiddenType::class)
            ->add("bannerId", HiddenType::class)
            ->add("isActive", CheckboxType::class, array("label" => "Activo"))
            ->add("categoryId", ChoiceType::class, array(
                "label" => "Categorias",
                "choices" => $data
            ))
            ->add("save", SubmitType::class, array("label" => "Crear"))
            ->getForm();


        if($request->getMethod() == "POST"){
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();

                $category = $em
                        ->createQuery("select c from IndexBundle:Category c where c.id = :id")
                        ->setParameter("id", $data->getCategoryId())
                        ->getOneOrNullResult();

                $picture = $em
                    ->createQuery("select p from IndexBundle:Picture p where p.id = :id")
                    ->setParameter("id", $data->getBannerId())
                    ->getOneOrNullResult();

                $post->setTitle($data->getTitle());
                $post->setShortDescription($data->getShortDescription());
                $post->setContent($data->getContent());
                $post->setCreatedAt(new \DateTime());
                $post->setBannerId($picture);
                $post->setUserId($this->getUser());
                $post->setCategoryId($category);
                $post->setIsActive($this->handActiveCheck($data->getIsActive()));

                $em->persist($post);
                $em->flush();

                $this->addFlash("msg", "Publicacion creada con exito");
                return $this->redirectToRoute("post.index");
            }else{
                return $this->render("IndexBundle:admin:createPost.html.twig", array(
                    "active" => "publicaciones",
                    "form" => $form->createView(),
                ));
            }
        }else{
            return $this->render("IndexBundle:admin:createPost.html.twig", array(
                "active" => "publicaciones",
                "form" => $form->createView(),
            ));
        }

    }

    /**
     * @Route("/viewSelected", name="viewItems")
     */
    public function holaAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->createQuery("select c from IndexBundle:Category c where c.isActive = true")
            ->getResult();


        $data = array();

        foreach($categories as $category){
            $data [] = array(
                "id" => $category->getId(),
                "name" => $category->getName(),
            );
        }

        return new JsonResponse($data);
    }

    private function handActiveCheck($input)
    {
        if(is_null($input) || empty($input)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * @Route("/view")
     */
    public function viewAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository("IndexBundle:Category")->findAll();

        return $this->render("IndexBundle::selectedItem.html.twig", array(
            "categories" => $categories,
        ));
    }
}
