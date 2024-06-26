<?php

namespace App\Controller\Admin\User;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted(User::ROLE_ADMIN)]
final class UserListController extends AbstractController
{

    #[Route('/', name: 'admin_users_index', methods: ['GET'])]
    public function index(
        UserRepository $userRepository
    ): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
}