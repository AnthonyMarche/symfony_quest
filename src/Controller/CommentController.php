<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/comment/delete/{id}', name: 'app_comment_delete')]
    public function deleteComment(CommentRepository $commentRepository, Comment $comment): Response
    {
        $commentRepository->remove($comment, true);

        $this->addFlash('danger', 'Your comment has been delete');

        return $this->redirectToRoute('program_episode_show', [
            'programSlug' => $comment->getEpisode()->getSeason()->getProgram()->getSlug(),
            'seasonId' => $comment->getEpisode()->getSeason()->getId(),
            'episodeSlug' => $comment->getEpisode()->getSlug()
        ]);
    }
}
