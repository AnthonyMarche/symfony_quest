<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\CommentType;
use App\Form\ProgramType;
use App\Form\SearchProgramType;
use App\Repository\CommentRepository;
use App\Repository\ProgramRepository;
use App\Repository\UserRepository;
use App\Service\ProgramDuration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/program', name: 'program_')]
class ProgramController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, ProgramRepository $programRepository): Response
    {
        $form = $this->createForm(SearchProgramType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['search'];
            $programs = $programRepository->findLikeNameOrActorName($search);
        } else {
            $programs = $programRepository->findAll();
        }

        return $this->renderForm('program/index.html.twig', [
            'programs' => $programs,
            'form' => $form,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, MailerInterface $mailer, ProgramRepository $programRepository, SluggerInterface $slugger) : Response
    {
        $program = new Program();

        $form = $this->createForm(ProgramType::class, $program);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);
            $program->setOwner($this->getUser());

            $programRepository->save($program, true);

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('anthony.marche@hotmail.fr')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('Program/newProgramEmail.html.twig', ['program' => $program]));
            $mailer->send($email);

            $this->addFlash('success', 'The new program has been created');

            return $this->redirectToRoute('program_index');
        }

        // Render the form
        return $this->renderForm('program/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/show/{slug}', name: 'show', methods: ['GET'])]
    public function show(Program $program, ProgramDuration $programDuration): Response
    {
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'programDuration' => $programDuration->calculate($program)
        ]);
    }

    #[Route('/{slug}/seasons/{season}', name: 'season_show')]
    public function showSeason(Program $program, Season $season): Response
    {
        $episodes = $season->getEpisodes();

        return $this->render('/program/season_show.html.twig', [
            'episodes' => $episodes,
            'season' => $season,
            'program' => $program
        ]);
    }

    #[Route('/{programSlug}/season/{seasonId}/episode/{episodeSlug}', name: 'episode_show')]
    #[Entity('program', options: ['mapping' => ['programSlug' => 'slug']])]
    #[Entity('season', options: ['mapping' => ['seasonId' => 'id']])]
    #[Entity('episode', options: ['mapping' => ['episodeSlug' => 'slug']])]
    public function showEpisode(Program $program, Season $season, Episode $episode, Request $request, CommentRepository $commentRepository): Response
    {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setAuthor($this->getUser());
            $comment->setEpisode($episode);

            $commentRepository->save($comment, true);

            $this->addFlash('success', 'Your comment has been added');

            return $this->redirectToRoute('program_episode_show', [
                'programSlug' => $program->getSlug(),
                'seasonId' => $season->getId(),
                'episodeSlug' => $episode->getSlug()
                ]);
        }

        $comments = $episode->getComments();

        return $this->render('/program/episode_show.html.twig', [
            'episode' => $episode,
            'season' => $season,
            'program' => $program,
            'form' => $form->createView(),
            'comments' => $comments
        ]);
    }

    #[Route('/{slug}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Program $program, ProgramRepository $programRepository, SluggerInterface $slugger): Response
    {

        // Check wether the logged in user is the owner of the program
        if ($this->getUser() !== $program->getOwner()) {
            // If not the owner, throws a 403 Access Denied exception
            throw $this->createAccessDeniedException('Only the owner can edit the program!');
        }

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);

            $programRepository->save($program, true);

            $this->addFlash('success', 'The season has been updated');

            return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('program/edit.html.twig', [
            'program' => $program,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Program $program, ProgramRepository $programRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->request->get('_token'))) {
            $programRepository->remove($program, true);

            $this->addFlash('danger', 'The season has been deleted');
        }

        return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/watchlist', name: 'watchlist', methods: ['GET', 'POST'])]
    public function addToWatchlist(Program $program, UserRepository $userRepository): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with this id found in program\'s table.'
            );
        }

        /** @var \App\Entity\User */
        $user = $this->getUser();
        if ($user->isInWatchlist($program)) {
            $user->removeFromWatchlist($program);
        } else {
            $user->addToWatchlist($program);
        }

        $userRepository->save($user, true);

        return $this->redirectToRoute('program_show', ['slug' => $program->getSlug()], Response::HTTP_SEE_OTHER);
    }
}
