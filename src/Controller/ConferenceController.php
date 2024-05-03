<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    public function __construct(
        private ConferenceRepository $conferenceRepository,
        private CommentRepository $commentRepository,
        private EntityManagerInterface $em
    ) {

    }


    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('conference/index.html.twig');
    }

    #[Route('/conference/{slug}', name:'conference')]
    public function show(Conference $conference, Request $request)
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
}
