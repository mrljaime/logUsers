<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 17/12/2015
 * Time: 04:24 PM
 */

namespace IndexBundle\Controller;

use IndexBundle\Entity\Users;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class HomeController
 * @package IndexBundle\Controller
 * @Route("/admin")
 */

class HomeController extends Controller
{
    /**
     * @Route("/index", name="home")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        return $this->render("IndexBundle:admin:index.html.twig", array(
            'user' => $user,
            'active' => 'inicio'
        ));
    }

    /**
     * @Route("/createUser", name="create.user")
     */
    public function createUserAction(Request $request)
    {

        $user =  new Users();

        $form = $this->createFormBuilder($user)
            ->add('name', TextType::class, array('label' => 'Nombre'))
            ->add('username', TextType::class, array('label' => 'Nombre de usuario'))
            ->add('email', TextType::class, array('label' => 'Email'))
            ->add('password', PasswordType::class, array('label' => 'Contraseña'))
            ->add('save', SubmitType::class, array('label' => 'Crear Usuario',
                'attr' => array(
                    'class' => 'btn btn-primary margin-top-10'
                )))
            ->getForm();

        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

                $em = $this->getDoctrine()->getManager();
                $data = $form->getData();

                $result = $em->createQuery("select u from IndexBundle:Users u where u.username = :username or u.email "
                    ." = :email")
                    ->setParameter("username", $data->getUsername())
                    ->setParameter("email", $data->getEmail())
                    ->getOneOrNullResult();
                if(is_null($result)) {

                    $encoder = $this->container->get('security.password_encoder');
                    $user->setName($data->getName());
                    $user->setUsername($data->getUsername());
                    $user->setEmail($data->getEmail());
                    $user->setPassword($encoder->encodePassword($user, $data->getPassword()));
                    $user->setCreatedAt(new \DateTime());
                    $user->setPermissions("null");

                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('msg', 'El usuario ha sido creado');
                    return $this->redirectToRoute("users");
                }else{
                    $this->addFlash('mensaje', 'El nombre de usuario o email ya son usados por otro usuario');
                    return $this->redirectToRoute("create.user");
                }

            }else{
                return $this->render('IndexBundle:admin:createUser.html.twig', array(
                    'form' => $form->createView(),
                    "active" => "usuarios",
                ));
            }
        }

        return $this->render('IndexBundle:admin:createUser.html.twig', array(
            'form' => $form->createView(),
            'active' => "usuarios",
        ));
    }
}