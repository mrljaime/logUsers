<?php

namespace IndexBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use IndexBundle\Entity\Post;
use IndexBundle\Repository\CategoryRepository;
use IndexBundle\Repository\SubCatRepository;
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
        $user = $this->getUser();

        $post = $em->getRepository("IndexBundle:Post")
            ->findBy(array("userId" => $user->getId(), "section" => "blog"));

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
        $data = $this->getCategories($em);

        $form = $this->createFormBuilder($post)
            ->add("title", TextType::class, array("label" => "Titulo"))
            ->add("shortDescription", TextType::class, array("label" => "Descripcion breve"))
            ->add("content", TextareaType::class, array("label" => "Contenido"))
            ->add("userId", HiddenType::class)
            ->add("bannerId", HiddenType::class)
            ->add("isActive", CheckboxType::class,
                array("label" => "Activo", "required" => false))
            ->add("categoryId", EntityType::class, array(
                "label" => "Categorias",
                'class' => 'IndexBundle:SubCat',
                'choice_label' => 'name',
            ))
            ->add("save", SubmitType::class, array("label" => "Crear"))
            ->getForm();


        if($request->getMethod() == "POST"){
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();

                if(is_null($data->getCategoryId())){
                    $this->addFlash("alert", "No se puede crear la publcación sin una"
                        ." categoría activa");

                    return $this->redirect($request->server->get("HTTP_REFERER"));
                }

                $category = $em
                        ->createQuery("select c from IndexBundle:SubCat c where c.id = :id")
                        ->setParameter("id", $data->getCategoryId())
                        ->getOneOrNullResult();

                $picture = $em
                    ->createQuery("select p from IndexBundle:Picture p where p.id = :id")
                    ->setParameter("id", $data->getBannerId())
                    ->getOneOrNullResult();

                if(!$picture){
                    $this->addFlash("alert", "No se encontró una imagen de portada");
                    return $this->redirectToRoute("post.create");
                }

                $post->setTitle($data->getTitle());
                $post->setShortDescription($data->getShortDescription());
                $post->setContent($data->getContent());
                $post->setCreatedAt(new \DateTime());
                $post->setBannerId($picture);
                $post->setUserId($this->getUser());
                $post->setCategoryId($category);
                $post->setSection("blog");
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
     * @Route("/delete/{id}", name="post.delete");
     */
    public function deletePostAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->createQuery("select p from IndexBundle:Post p where p.id = :id")
            ->setParameter("id", $id)
            ->getOneOrNullResult();

        if($result){
            $em->remove($result);
            $em->flush();
            $this->addFlash("msg", "La publicación ha sido eliminada con éxito");
            return $this->redirectToRoute("post.index");
        }

    }

    /**
     * @param Request $request
     * @param $id
     * @Route("/edit/{id}", name="post.edit")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editPostAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = new Post();
        $result = $em->createQuery("select p from IndexBundle:Post p where p.id = :id")
            ->setParameter("id", $id)
            ->getOneOrNullResult();

        if(!$result){
            $this->addFlash("alert", "No se encontró una publicación asociada");
            return $this->redirect($request->server->get("HTTP_REFERER"));
        }
        $slider = $em->createQuery("select s from IndexBundle:Slider s where s.postId=:id")
            ->setParameter("id", $id)
            ->getResult();

        $form = $this->createFormBuilder($post)
            ->add("title", TextType::class, array("label" => "Título",
                "attr"=> array( "value" => $result->getTitle())))
            ->add("shortDescription", TextType::class,
                array("label" => "Descripcion breve", "attr" => array( "value" => $result->getShortDescription())))
            ->add("content", TextareaType::class,
                array("label" => "Contenido"))
            ->add("userId", HiddenType::class, array("attr" => array("value" => $result->getUserId()->getId())))
            ->add("bannerId", HiddenType::class, array("attr" => array("value" => $result->getBannerId()->getId())))
            ->add("isActive", CheckboxType::class,
                array("label" => "Activo", "required" => false, "attr" => array('checked' => $result->getIsActive())))
            ->add("categoryId", EntityType::class, array(
                "label" => "Categorias",
                'class' => 'IndexBundle:SubCat',
                'choice_label' => 'name',
            ))
            ->add("save", SubmitType::class, array("label" => "Guardar"))
            ->getForm();


        if($request->getMethod() == "POST"){
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();

                $user = $em->getRepository("IndexBundle:Users")->find($data->getUserId());
                $picture = $em->getRepository("IndexBundle:Picture")->find($data->getBannerId());
                $category = $em->getRepository("IndexBundle:SubCat")->find($data->getCategoryId());


                $result->setTitle($data->getTitle());
                $result->setShortDescription($data->getShortDescription());
                $result->setContent($data->getContent());
                $result->setUserId($user);
                $result->setBannerId($picture);
                $result->setIsActive($this->handActiveCheck($data->getIsActive()));
                $result->setCategoryId($category);

                $em->persist($result);
                $em->flush();

                $this->addFlash("msg", "La publicación se ha editado con éxito");
                return $this->redirectToRoute("post.index");

            }else{
                return $this->render("IndexBundle:admin:editPost.html.twig", array(
                    'post' => $result,
                    'slider' => $slider,
                    "active" => "publicaciones",
                    "form" => $form->createView(),
                    "content" => $result->getContent(),
                    'urlPicture' => $result->getBannerId()->getPath(),
                ));
            }
        }

            return $this->render("IndexBundle:admin:editPost.html.twig", array(
                'post' => $result,
                'slider' => $slider,
                "active" => "publicaciones",
                "form" => $form->createView(),
                "content" => $result->getContent(),
                'urlPicture' => $result->getBannerId()->getPath(),
            ));



    }

    /**
     * @param $input
     * @return bool
     */
    private function handActiveCheck($input)
    {
        if(is_null($input) || empty($input)){
            return false;
        }else{
            return true;
        }
    }

    private function getCategories(EntityManager $em)
    {
        $data = array();

        $result = $em->createQuery("select c from IndexBundle:Category c where c.isActive = true")
            ->getResult();

        foreach($result as $category){
            $data[] = array(
                $category->getName() => $category->getId(),
            );
        }

        return $data;
    }

}
