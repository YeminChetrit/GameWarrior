<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Utilisateur;
use App\Entity\Genre;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GenreRepository;
use App\Repository\JVRepository;
use App\Entity\JV;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;


class Controller extends AbstractController
{
#[Route('/', name: 'app_home')]
public function home(JVRepository $jvRepository): Response
{
    $results = $jvRepository->findAllWithAverageNote(4);

    $features = array_map(function (array $row) {
        /** @var \App\Entity\JV $jv */
        $jv = $row[0];

        return [
            'id' => $jv->getId(),
            'slug' => $this->slugify($jv->getTitre()),
            'image' => $jv->getImage(),
            'category' => 'new',
            'genre' => $jv->getGenre()?->getNom() ?? 'Sans genre',
            'title' => $jv->getTitre(),
            'description' => $jv->getDescription(),
            'comments' => 0,
        ];
    }, $results);

    return $this->render('default.html.twig', [
        'games' => $features,
        'features' => $features,
    ]);
}

   #[Route('/games/{genre}', name: 'app_games', requirements: ['genre' => '[a-z0-9-]+'], defaults: ['genre' => null])]
public function games(?string $genre, JVRepository $jvRepository, GenreRepository $genreRepository, Request $request): Response
{
    $currentGenre = null;
    if ($genre !== null) {
        foreach ($genreRepository->findAll() as $candidate) {
            if ($this->slugify($candidate->getNom()) === $genre) {
                $currentGenre = $candidate;
                break;
            }
        }
        if (!$currentGenre) {
            throw $this->createNotFoundException('Genre introuvable.');
        }
    }

    $results = $jvRepository->findAllWithAverageNote(null, $currentGenre?->getId());

    $games = array_map(function (array $row) {
        /** @var \App\Entity\JV $jv */
        $jv = $row[0];
        $moyenne = $row['moyenneNote'] !== null ? round((float) $row['moyenneNote'], 1) : 0;

        return [
            'id' => $jv->getId(),
            'slug' => $this->slugify($jv->getTitre()),
            'image' => $jv->getImage(),
            'genre' => $jv->getGenre()?->getNom() ?? 'Sans genre',
            'title' => $jv->getTitre(),
            'description' => $jv->getDescription(),
            'score' => $moyenne,
            'scoreColor' => $moyenne >= 8 ? 'yellow' : ($moyenne >= 5 ? 'green' : 'pink'),
            'rating' => (int) round($moyenne / 2),
        ];
    }, $results);

    $recentGames = array_slice($games, 0, 4);

if ($request->isXmlHttpRequest()) {
    return $this->render('pages/_games_grid.html.twig', [
        'games' => $games,
    ]);
}

        return $this->render('pages/games.html.twig', [
        'games' => $games,
        'recentGames' => $recentGames,
        'genres' => $genreRepository->findAll(),
        'currentGenre' => $currentGenre,
    ]);
}

#[Route('/blog', name: 'app_blog_list')]
public function blogList(JVRepository $jvRepository): Response
{
    $posts = array_map(function (JV $jv) {
        return [
            'id' => $jv->getId(),
            'slug' => $this->slugify($jv->getTitre()),
            'image' => $jv->getImage(),
            'genre' => $jv->getGenre()?->getNom() ?? 'Sans genre',
            'title' => $jv->getTitre(),
            'description' => $jv->getDescription(),
            'commentsCount' => $jv->getAvis()->count(),
        ];
    }, $jvRepository->findBy([], ['id' => 'DESC']));

    return $this->render('pages/blog_list.html.twig', [
        'posts' => $posts,
    ]);
}

#[Route('/blog/{slug}', name: 'app_blog', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET', 'POST'])]
public function blog(
    string $slug,
    JVRepository $jvRepository,
    Request $request,
    EntityManagerInterface $em,
    UtilisateurRepository $utilisateurRepository
): Response {
    $jv = null;
    foreach ($jvRepository->findAll() as $candidate) {
        if ($this->slugify($candidate->getTitre()) === $slug) {
            $jv = $candidate;
            break;
        }
    }
    if (!$jv) {
        throw $this->createNotFoundException('Jeu introuvable.');
    }

    if ($request->isMethod('POST')) {
        $pseudo  = trim((string) $request->request->get('name'));
        $mail    = trim((string) $request->request->get('email'));
        $titre   = trim((string) $request->request->get('subject'));
        $message = trim((string) $request->request->get('message'));

        if (
            $this->isCsrfTokenValid('comment'.$jv->getId(), (string) $request->request->get('_token'))
            && $pseudo !== '' && $titre !== '' && $message !== ''
            && filter_var($mail, FILTER_VALIDATE_EMAIL)
        ) {
            $utilisateur = $utilisateurRepository->findOneBy(['mail' => $mail]);

            if (!$utilisateur) {
                $utilisateur = new Utilisateur();
                $utilisateur->setMail($mail);
                $utilisateur->setPseudo(mb_substr($pseudo, 0, 100));
                // mdp est obligatoire : mot de passe aléatoire, ce compte ne sert qu'à signer les avis
                $utilisateur->setMdp(password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT));
                $em->persist($utilisateur);
            }

            $avis = new Avis();
            $avis->setJv($jv);
            $avis->setUtilisateur($utilisateur);
            $avis->setTitre(mb_substr($titre, 0, 150));
            $avis->setDescription($message);
            $em->persist($avis);
            $em->flush();

            $this->addFlash('success', 'Votre commentaire a bien été ajouté.');
        } else {
            $this->addFlash('error', 'Merci de remplir tous les champs avec un email valide.');
        }

        return $this->redirectToRoute('app_blog', ['slug' => $slug]);
    }

    $comments = [];
    foreach ($jv->getAvis() as $avis) {
        $comments[] = [
            'author' => $avis->getUtilisateur()?->getPseudo(),
            'authorAvatar' => $avis->getUtilisateur()?->getPhotoProfil() ?: 'img/authors/1.jpg',
            'title' => $avis->getTitre(),
            'content' => $avis->getDescription(),
        ];
    }

    $post = [
        'id' => $jv->getId(),
        'slug' => $slug,
        'title' => $jv->getTitre(),
        'subtitle' => $jv->getDescription(),
        'headerImage' => $jv->getImage() ?: 'img/page-top-bg/2.jpg',
        'image' => $jv->getImage(),
        'category' => 'new',
        'genre' => $jv->getGenre()?->getNom() ?? 'Sans genre',
        'heading' => $jv->getTitre(),
        'body' => [$jv->getDescription()],
        'comments' => array_reverse($comments), // les plus récents d'abord
    ];

    return $this->render('pages/blog.html.twig', [
        'post' => $post,
    ]);
}

    #[Route('/forum', name: 'app_forum')]
    public function forum(): Response
    {
        return $this->render('pages/forum.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('pages/contact.html.twig');
    }

    public function genresMenu(GenreRepository $genreRepository): Response
{
    $genres = array_map(fn (Genre $g) => [
        'nom' => $g->getNom(),
        'slug' => $this->slugify($g->getNom()),
    ], $genreRepository->findBy([], ['nom' => 'ASC']));

    return $this->render('partials/_genres_menu.html.twig', [
        'genres' => $genres,
    ]);
}

    private function slugify(?string $titre): string
    {
        return (new AsciiSlugger())->slug((string) $titre)->lower()->toString();
    }
}