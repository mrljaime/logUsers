<?php

namespace IndexBundle\Controller;

use IndexBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BiographyController
 * @package IndexBundle\Controller
 * @Route("admin/biography")
 */
class BiographyController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/", name="bio.index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $result = $em->createQuery("select p from IndexBundle:Post p where p.section = 'bio'")
            ->getResult();

        return $this->render("IndexBundle:admin:biography.html.twig", array(
            "active" => "biografia",
            "bios" => $result
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/create", name="bio.create")
     */
    public function createBioAction(Request $request)
    {
        $post = new Post();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder($post)
            ->add("title", TextType::class, array("label" => "Titulo"))
            ->add("shortDescription", TextType::class, array("label" => "Descripción breve"))
            ->add("content", TextareaType::class, array("label" => "Contenido"))
            ->add("userId", HiddenType::class)
            ->add("bannerId", HiddenType::class)
            ->add("isActive", CheckboxType::class,
                array("label" => "Activo", "required" => false))
            ->add("save", SubmitType::class, array("label" => "Crear"))
            ->getForm();

        if($request->getMethod() == "POST"){
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();

                $picture = $em
                    ->createQuery("select p from IndexBundle:Picture p where p.id = :id")
                    ->setParameter("id", $data->getBannerId())
                    ->getOneOrNullResult();

                if(is_null($picture)){
                    $this->addFlash("alert", "No se encontro la iamgen de portada para vincularla");
                    return $this->redirectToRoute("bio.create");
                }

                $post->setTitle($data->getTitle());
                $post->setShortDescription($data->getShortDescription());
                $post->setContent($data->getContent());
                $post->setUserId($this->getUser());
                $post->setCreatedAt(new \DateTime());
                $post->setBannerId($picture);
                $post->setIsActive($this->handActiveCheck($data->getIsActive()));
                $post->setSection("bio");

                $em->persist($post);
                $em->flush();

                $this->addFlash("msg", "La publicación se ha creado con éxito");
                return $this->redirectToRoute("bio.index");
            }else{
                return $this->render("IndexBundle:admin:createBio.html.twig",
                    array(
                        "active" => "biografia",
                        "form" => $form->createView(),
                    ));
            }
        }else{
            return $this->render("IndexBundle:admin:createBio.html.twig",
                array(
                    "active" => "biografia",
                    "form" => $form->createView(),
                ));
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/delete/{id}", name="bio.delete")
     */
    public function deleteBioAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $result = $em->createQuery("select p from IndexBundle:Post p where p.id = :id")
            ->setParameter("id", $id)
            ->getOneOrNullResult();

        if($result){
            $em->remove($result);
            $em->flush();

            return $this->redirectToRoute("bio.index");
        }
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
}
