<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class EmailController extends BaseController
{
    public function __construct(
        RequestStack    $request,
        LoggerInterface $appLogger,
    ) {
        parent::__construct($request, $appLogger);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/api/email/reset_pwd', methods: 'POST')]
    public function emailResetPwd(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('sender@example.com') // 발신자
            ->to('recipient@example.com') // 수신자
            ->subject('Test Email from Symfony') // 타이틀
            ->text('This is a plain text email.') // 텍스트
            ->html('<p>This is an <strong>HTML</strong> email.</p>'); // HTML

        // 발송
        $mailer->send($email);

        return new Response('Email sent successfully!');
    }
}