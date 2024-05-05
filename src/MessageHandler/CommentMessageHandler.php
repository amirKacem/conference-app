<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Service\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
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
        private MailerInterface $mailer
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

            $adminEmail = $this->parameterBag->get('admin_email');
            $this->mailer
                ->send(
                    (new NotificationEmail())
                    ->subject('new Comment Posted')
                    ->htmlTemplate('emails/comment_notification.html.twig')
                    ->from($adminEmail)
                    ->to($adminEmail)
                    ->context(['comment' => $comment])
                );


        } elseif (false === empty($this->logger)) {
            $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }

    }
}
