<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setMdp($userPasswordHasher->hashPassword($user, $plainPassword));

            $photoFile = $form->get('photoProfil')->getData();
            if ($photoFile) {
                $user->setPhotoProfil($this->uploadPhoto($photoFile, $user->getPseudo()));
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    /**
     * Enregistre la photo dans public/img/authors et renvoie le chemin à stocker en base.
     */
    private function uploadPhoto(UploadedFile $file, ?string $pseudo): string
    {
        $slug = (new AsciiSlugger())->slug((string) $pseudo)->lower()->toString() ?: 'user';
        $extension = $file->guessExtension() ?: 'jpg';
        $filename = $slug.'-'.uniqid().'.'.$extension;

        $file->move($this->getParameter('kernel.project_dir').'/public/img/authors', $filename);

        return 'img/authors/'.$filename;
    }
}