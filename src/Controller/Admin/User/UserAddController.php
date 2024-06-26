<?php

namespace App\Controller\Admin\User;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted(User::ROLE_ADMIN)]
final class UserAddController extends AbstractController
{

    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    #[Route('/new', name: 'admin_users_new', methods: ['POST', 'GET'])]
    public function index(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user)
            ->add('saveAndCreateNew', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));

            $entityManager->persist($user);
            $entityManager->flush();

            /** @var SubmitButton $submit */
            $submit = $form->get('saveAndCreateNew');
            if ($submit->isClicked()) {
                return $this->redirectToRoute('admin_users_new', [], Response::HTTP_SEE_OTHER);
            }
            return $this->redirectToRoute('admin_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}