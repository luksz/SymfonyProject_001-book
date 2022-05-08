<?php 
namespace App\MessageHandler;

use App\Entity\Comment;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CommentMessageHandler  implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private CommentRepository $commentRepository;
    private  SpamChecker $spamChecker;

    public function __construct(EntityManagerInterface $entityManager, CommentRepository $commentRepository,  SpamChecker $spamChecker)
    {
        $this->entityManager  = $entityManager;  
        $this->commentRepository = $commentRepository;  
        $this->spamChecker = $spamChecker;
    }


    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if(!$comment){
            return;
        }

        if (2 === $this->spamChecker->getSpamScore($comment, $message->getContext())) {
            $comment->setState(Comment::SPAM);
        } else {
            $comment->setState(Comment::PUBLISHED);
        }

        $this->entityManager->flush();
    }
}
