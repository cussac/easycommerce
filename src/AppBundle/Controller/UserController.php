<?php
/**
 * Created by PhpStorm.
 * User: nacho
 * Date: 14/01/16
 * Time: 18:47
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;


class UserController extends Controller
{
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        // obtiene el error de inicio de sesión si lo hay
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('AppBundle:Default:login.html.twig', array(
            // el último nombre de usuario ingresado por el usuario
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }

    public function registroAction($id)
    {
        $sesione = $this->get('request')->getSession();

        $userLogin = $this->getUser();
        $sesion = $this->getRequest()->getSession();

        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $params['id'] = $id;

        if ($id === -1)
            $usuario = new User();
        else
        {
            $usuario = $em->getRepository('AppBundle:User')
                ->find($id);
            if(!$usuario)
            {
                throw $this->createNotFoundException(
                    'No existe nigun usuario con id '.$id);
            }
        }

        $form = $this->createForm(new UserType(), $usuario);

        if ($this->getRequest()->isMethod('POST'))
        {

            $form->bind($this->getRequest());
            if ($form->isValid())
            {

                if ($id != -1)
                {
                    $usuario->getRoles();
                }
                else
                {
                    $usuario->setRol('ROLE_REGISTRADO');
                }

                //PASSWORD ENCODER
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($usuario);
                // Evidentemente, nuestro password vendrá de un formulario
                $password = $encoder->encodePassword($usuario->getPassword(), $usuario->getSalt());
                $usuario->setPassword($password);

                $em->persist($usuario);
                $em->flush();

                if ($id === -1)
                {
                    $this->get('session')->getFlashBag()->add(
                        'success',
                        ' Ya estás registrado como:<strong> '.$usuario->getUsername().'</strong> ahora puedes entrar.'
                    );

                }
                else
                {
                    $this->get('session')->getFlashBag()->add(
                        'success',
                        ' <strong> '.$usuario->getUsername().'</strong> cambios realizados.'
                    );
                }

                if ($id === -1)
                    return $this->redirect($this->generateUrl('appBundle_login'));
                else
                {
                    return $this->redirect($this->generateUrl('appBundle_homepage'));
                }
            }
            else
            {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'Error, revise los campos del formulario'
                );
                $this->redirect($this->generateUrl("appBundle_registro"));
            }

        }
        return $this->render('AppBundle:Default:registro.html.twig',array(
            'form' => $form->createView()
        ));
    }

} 