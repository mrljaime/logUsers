<?php

namespace IndexBundle\Controller;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use IndexBundle\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;


/**
 * Class UserController
 * @package IndexBundle\Controller
 * @Route("admin")
 */

class UserController extends Controller
{

    /**
     * @Route("/users", name="users")
     */
    public function usersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->createQuery("select u from IndexBundle:Users u")->getResult();

        return $this->render("IndexBundle:admin:users.html.twig", array(
            'active' => "usuarios",
            'users' => $users,
        ));
    }

    /**
     * @Route("/edituser/{id}", name="edit.user")
     */
    public function editUserAction(Request $request, $id)
    {
        $user = new Users();
        $em = $this->getDoctrine()->getManager();
        $editUser = $em->createQuery("select u from IndexBundle:Users u where u.id=:id")
            ->setParameter("id", $id)
            ->getOneOrNullResult();

        $form = $this->createFormBuilder($user)
            ->add("name", TextType::class, array('label' => "Nombre", "attr" => array(
                "value" => $editUser->getName(),
            )))
            ->add("username", TextType::class, array("label" => "Nombre de usuario", "attr" => array(
                "value" => $editUser->getUsername(),
            )))
            ->add("email", TextType::class, array("label" => "Email", "attr" => array(
                "value" => $editUser->getEmail(),
            )))
            ->add("save", SubmitType::class, array("label" => "Guardar usuario", "attr" => array(
                "class" => "btn btn-primary margin-top-10"
            )))
            ->getForm();


        if($request->getMethod() == "POST"){
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();

                //Don't fucking repeat yourself!!!!!
                if($this->sameAsEdit($editUser, $data) || ($this->selectUseAndEmail($em, $data->getUsername(),
                        $data->getEmail()))){
                    $editUser->setName($data->getName());
                    $editUser->setUsername($data->getUsername());
                    $editUser->setEmail($data->getEmail());
                    $em->persist($editUser);
                    $em->flush();

                    $this->addFlash("msg", sprintf("El usuario %s ha sido editado correctamente", $editUser->getName()));

                    return $this->redirectToRoute("users");
                }else
                    $this->addFlash("msg", "El nombre de usuario o email ya estan registrados");
                    return $this->redirect($request->server->get('HTTP_REFERER'));
                    }
                }
        return $this->render("IndexBundle:admin:editUser.html.twig", array(
            "active" => "usuarios",
            "form" => $form->createView(),
            "user" => $editUser,
        ));

    }

    /**
     * @Route("/deleteuser/{id}", name="delete.user")
     */
    public function deleteUserAction(Request $request, $id)
    {
        $authUser = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $user = $em->createQuery("select u from IndexBundle:Users u where u.id = :id")
            ->setParameter("id", $id)
            ->getOneOrNullResult();

        if($user->getId() != $authUser->getId()):
            $em->remove($user);
            $em->flush();
            $this->addFlash("msg", "La eliminación terminó con éxito");
            return $this->redirectToRoute("users");
        else:
            $this->addFlash("alert", "No puedes eliminarte a ti mismo!");
            return $this->redirect($request->server->get("HTTP_REFERER"));
        endif;
    }


    /**
     * Nos ayuda a identificar si estamos hablando del usuario editado
     * @param $user
     * @param $data
     * @return bool
     */
    private function sameAsEdit($user, $data)
    {
        if($user->getUsername() == $data->getUsername() && $user->getEmail() == $data->getEmail()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Comprueba que no exista un usuario o email en la base de datos
     * @param $user
     * @param $email
     */
    private function selectUseAndEmail(EntityManager $em, $username, $email)
    {
        return var_dump("Quiensabequepedo");
        $result = $em->createQuery("select u from IndexBundle:Users u where (u.username = :username"
        . " or u.email = :email)")
        ->setParameters(array(
            "username" => $username,
            "email" => $email,
        ))
        ->getOneOrNullResult();

        if(is_null($result)){
            return true;
        }else{
            return false;
        }

    }
}
