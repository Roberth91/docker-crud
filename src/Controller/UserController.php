<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Exceptions\UserLimitReachedException;
use App\Exceptions\RoleLimitReachedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="user_index", methods="GET")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', ['users' => $userRepository->findAll(), 'count' => $this->countActiveUsers()]);
    }

    /**
     * @Route("/new", name="user_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        if ($this->countActiveUsers() >= 10) {
            throw new UserLimitReachedException('The user limit has been reached, please delete or deactivate a user first.');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);

            if ($this->countActiveUsersByRole($user->getRole()) >= 4) {
                throw new RoleLimitReachedException('The role limit has been reached, please allocate a different role.');
            }

            $em->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods="GET")
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods="GET|POST")
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($this->countActiveUsersByRole($user->getRole()) >= 4) {
                throw new RoleLimitReachedException('The role limit has been reached, please allocate a different role.');
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods="DELETE")
     */
    public function delete(Request $request, User $user): Response
    {
        if(!$user) {
            throw new UserNotFoundException("This user does not exist.");
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    private function getUserRepository()
    {
        return $this->getDoctrine()->getRepository(User::class);
    }

    private function countActiveUsers()
    {
        return count($this->getUserRepository()->findActiveUsers());
    }

    private function countActiveUsersByRole($role)
    {
        return count($this->getUserRepository()->findActiveUsersByRole($role));
    }
}
