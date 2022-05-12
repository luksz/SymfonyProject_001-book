<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ConferenceController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private MessageBusInterface $msgBus;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $msgBus)
    {

        $this->entityManager = $entityManager;
        $this->msgBus = $msgBus;
    }


    #[Route('/', name: 'homepage_index',)]
    public function index(ConferenceRepository $conferenceRepository): Response
    {
        return $this->redirectToRoute('homepage');
    }


    #[Route('/conference', name: 'homepage')]
    public function conference(ConferenceRepository $conferenceRepository): Response
    {
        $response =  $this->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll()
        ]);
        $response->setPublic();
        $response->setSharedMaxAge(60);

        return $response;
    }

    #[Route('/conference/header', name: 'conference_header')]
    public function conferenceHeader(ConferenceRepository $conferenceRepository): Response
    {
        $response =  $this->render('conference/header.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ]);
        $response->setSharedMaxAge(60);

        return $response;
    }

    #[Route('/conference/{slug}', name: 'conference')]
    public function show(
        Request $request,
        Conference $conference,
        CommentRepository $commentRepository,
        string $photoDir,
        NotifierInterface $notifierInterface
    ): Response {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setConference($conference);

            $this->sendPhotoToServer($form, $photoDir, $comment);

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $context = [
                'user_ip' => $request->getClientIp(),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];

            $this->msgBus->dispatch(new CommentMessage($comment->getId(), $context));
            $notifierInterface->send(new Notification('Thank you for the feedback; your comment will be posted after moderation.', ['browser']));
            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        if ($form->isSubmitted()) {
            $notifierInterface->send(new Notification('Can you check your submission? There are some problems with it.', ['browser']));
        }
        $offset = max(0, $request->query->get('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $form->createView()
        ]);
    }

    private function sendPhotoToServer(FormInterface $form, string $photoDir, Comment &$comment)
    {
        if ($photo = $form['photo']->getData()) {
            $filename = bin2hex(random_bytes(6)) . '.' . $photo->guessExtension();
            try {
                $photo->move($photoDir, $filename);
            } catch (FileException $e) {
                throw new \Exception("Nie udało się wgrać pliku");
            }
            $comment->setPhotoFilename($filename);
        }
    }
}
