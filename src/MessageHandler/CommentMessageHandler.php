<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Notification\CommentReviewNotification;
use App\Repository\CommentRepository;
use App\Service\ImageOptimizer;
use App\Service\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Workflow\WorkflowInterface;

final class CommentMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private SpamChecker $spamChecker,
        private CommentRepository $commentRepository,
        private WorkflowInterface $commentStateMachine,
        private LoggerInterface $logger,
        private MessageBusInterface $bus,
        private ParameterBagInterface $parameterBag,
        private ImageOPtimizer $imageOPtimizer,
        private NotifierInterface $notifier
    ) {

    }

    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());

        if(true === empty($comment)) {
            return false;
        }

        if(true === $this->commentStateMachine->can($comment, 'accept')) {
            $score = $this->spamChecker->getSpamScore($comment, $message->getContext());
            $transition = 'accept';
            if(2 === $score) {
                $transition =  'reject_spam';
            } elseif (1 === $score) {
                $transition = 'might_be_spam';
            }

            $this->commentStateMachine->apply($comment, $transition);
            $this->em->flush();

            $this->bus->dispatch($message);

        } elseif ($this->commentStateMachine->can($comment, 'publish') || $this->commentStateMachine->can($comment, 'publish_ham')) {
            $this->notifier->send(
                new CommentReviewNotification($comment, $message->getReviewUrl()),
                ...$this->notifier->getAdminRecipients()
            );


        } elseif($this->commentStateMachine->can($comment, 'optimize')) {
            if(empty($comment->getPhotoFilename()) === false) {
                $imagePath = $this->parameterBag->get('photo_dir').'/'.$comment->getPhotoFilename();
                $this->imageOPtimizer->resize($imagePath);
            }

            $this->commentStateMachine->apply($comment, 'optimize');
            $this->em->flush();
            $notification = (new Notification('Comment approved', ['email']))
                            ->content('Your comment approved');
            if(empty($comment->getEmail()) === false) {
                $this->notifier->send(
                    $notification,
                    new Recipient(
                        $comment->getEmail()
                    )
                );
            }
        } elseif (false === empty($this->logger)) {
            $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }

    }
}
