<?php

namespace App\MessageHandler;

use App\Entity\Comment;
use App\ImageOptimizer;
use App\Message\CommentMessage;
use App\Notification\CommentReviewNotification;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Stmt\Nop;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Workflow\WorkflowInterface;



class CommentMessageHandler  implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private  SpamChecker $spamChecker;
    private MessageBusInterface $bus;
    private WorkflowInterface $workflow;
    private LoggerInterface $logger;
    private string $adminEmail;
    private MailerInterface $mailer;
    private ImageOptimizer $imageOptimizer;
    private string $photoDir;
    private NotifierInterface $notifier;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository,
        SpamChecker $spamChecker,
        MessageBusInterface $bus,
        WorkflowInterface $commentStateMachine,
        LoggerInterface $logger = null,
        string $adminEmail,
        MailerInterface $mailer,
        ImageOptimizer $imageOptimizer,
        string $photoDir,
        NotifierInterface $notifier
    ) {
        $this->entityManager  = $entityManager;
        $this->commentRepository = $commentRepository;
        $this->spamChecker = $spamChecker;

        $this->bus = $bus;
        $this->logger = $logger;
        $this->workflow = $commentStateMachine;

        $this->adminEmail = $adminEmail;
        $this->mailer = $mailer;

        $this->imageOptimizer = $imageOptimizer;
        $this->photoDir = $photoDir;

        $this->notifier = $notifier;
    }


    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return;
        }
        sleep(2);
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
            // $this->workflow->apply($comment, $this->workflow->can($comment, Comment::PUBLISH) ? Comment::PUBLISH : Comment::PUBLISH_HAM);
            // $this->entityManager->flush();
            // $this->mailer->send((new NotificationEmail())
            //         ->subject('New commend posted')
            //         ->htmlTemplate('emails/comment_notification.html.twig')
            //         ->from($this->adminEmail)
            //         ->to($this->adminEmail)
            //         ->context(['comment' => $comment])
            // );

            $this->notifier->send(new CommentReviewNotification($comment), $this->notifier->getAdminRecipients()[0]);
            sleep(10);
        } elseif ($this->workflow->can($comment, Comment::OPTIMIZE)) {
            if ($comment->getPhotoFilename()) {
                $this->imageOptimizer->resize($this->photoDir . '/' . $comment->getPhotoFilename());
            }
            $this->workflow->apply($comment, Comment::OPTIMIZE);
            $this->entityManager->flush();
        } elseif ($this->logger) {
            $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }
    }
}
