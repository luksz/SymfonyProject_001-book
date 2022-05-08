<?php

namespace App\MessageHandler;

use App\Entity\Comment;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class CommentMessageHandler  implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private  SpamChecker $spamChecker;
    private MessageBusInterface $bus;
    private WorkflowInterface $workflow;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository,
        SpamChecker $spamChecker,
        MessageBusInterface $bus,
        WorkflowInterface $commentStateMachine,
        LoggerInterface $logger = null
    ) {
        $this->entityManager  = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->spamChecker = $spamChecker;

        $this->bus = $bus;
        $this->logger = $logger;
        $this->workflow = $commentStateMachine;
    }


    public function __invoke(CommentMessage $message)
    {

        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return;
        }
        sleep(10);
        $this->logger->debug('Comment Message: ', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        if ($this->workflow->can($comment, Comment::ACCEPT)) {
            $this->logger->debug('Comment Message CAN ACCEPT: ', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
            $score = $this->spamChecker->getSpamScore($comment, $message->getContext());
            $transition = Comment::ACCEPT;
            if (2 === $score) {
                $transition = Comment::REJECT_SPAM;
            } elseif (1 === $score) {
                $transition = Comment::MIGHT_BE_SPAM;
            }
            $this->workflow->apply($comment, $transition);
            $this->entityManager->flush();
            sleep(10);
            $this->bus->dispatch($message);
        } elseif (
            $this->workflow->can($comment, Comment::PUBLISH) ||
            $this->workflow->can($comment, Comment::PUBLISH_HAM)
        ) {
            $this->logger->debug('Comment Message CAN PUBLISH: ', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
            $this->workflow->apply($comment, $this->workflow->can($comment, Comment::PUBLISH) ? Comment::PUBLISH : Comment::PUBLISH_HAM);
            $this->entityManager->flush();
            sleep(10);
        } elseif ($this->logger) {
            $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }
    }
}
