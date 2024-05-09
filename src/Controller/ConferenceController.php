<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\Service\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ConferenceController extends AbstractController
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
        private CommentRepository $commentRepository,
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
        private Environment $twig
    ) {

    }


    #[Route('/', name: 'homepage')]
    public function index(): Response
    {

        $response = new Response($this->twig->render('conference/index.html.twig', [
            'conferences' => $this->conferenceRepository->findAll()
        ]));

        $response->setSharedMaxAge(3600);
        return $response;
    }

    #[Route('/conference/{slug}', name:'conference')]
    public function show(Conference $conference, Request $request, SpamChecker $spamChecker)
    {
        $commentForm = $this->createForm(CommentFormType::class, new Comment());
        $commentForm->handleRequest($request);

        if($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment = $commentForm->getData();
            $comment->setConference($conference);
            $photo = $commentForm->get('photo')->getData();
            if(empty($photo) === false) {
                $photoDirectoryPath = $this->getParameter('photo.dir');
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDirectoryPath, $filename);
                } catch (FileException $e) {

                }
                $comment->setPhotoFileName($filename);
            }
            $this->em->persist($comment);

            $this->em->flush();
            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referrer'),
                'permalink' => $request->getUri()
            ];


            $this->bus->dispatch(
                new CommentMessage($comment->getId(), $context)
            );

            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }
        $offset = max(0, $request->query->getInt('offset', 0));
        $comments = $this->commentRepository->getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $comments,
            'previous' => $offset - $this->commentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($comments), $offset + $this->commentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $commentForm->createView(),
        ]);
    }

    #[Route('/conference_header', name: 'conference_header')]
    public function conferenceHeader()
    {
        $response = new Response($this->twig->render('conference/header.html.twig', [
            'conferences' => $this->conferenceRepository->findAll(),
        ]));
        $response->setSharedMaxAge(3600);

        return $response;
    }
}
