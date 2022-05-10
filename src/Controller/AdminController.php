<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

class AdminController extends AbstractController
{

    private Registry $workflowRegistry;
    private MessageBusInterface $messageBus;
    private EntityManagerInterface $entityManagerInterface;

    public function __construct(Registry $workflowRegistry, MessageBusInterface $messageBus, EntityManagerInterface $entityManagerInterface)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->messageBus = $messageBus;
        $this->entityManagerInterface = $entityManagerInterface;
    }


    #[Route('/admin/comment/review/{id}', name: 'review_comment')]
    public function reviewComment(Request $request, Comment $comment): Response
    {

        $reject = $request->query->get('reject');
        $workflow = $this->workflowRegistry->get($comment);
        if ($workflow->can($comment, Comment::PUBLISH)) {
            $transition = $reject ? Comment::REJECT : Comment::PUBLISH;
        } elseif ($workflow->can($comment, Comment::PUBLISH_HAM)) {
            $transition = $reject ? Comment::REJECT_HAM : Comment::PUBLISH;
        } else {
            return new Response('Comment already reviewed or not in the right state.');
        }

        $workflow->apply($comment, $transition);
        $this->entityManagerInterface->flush();

        if (!$reject) {
            $this->messageBus->dispatch(new CommentMessage($comment->getId()));
        }


        return $this->render('admin/review.html.twig', [
            'transition' => $transition,
            'comment' => $comment
        ]);
    }
}
