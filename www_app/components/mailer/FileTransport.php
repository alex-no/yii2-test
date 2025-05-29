<?php

namespace app\components\mailer;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mime\Address;

class FileTransport implements TransportInterface
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = rtrim($path, '/');
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        $filename = $this->path . '/' . date('Ymd_His') . '_' . uniqid() . '.eml';
        file_put_contents($filename, $message->toString());

        if ($envelope === null) {
            if (method_exists($message, 'getFrom')) {
                $from = $message->getFrom()[0] ?? new Address('admin@4n.com.ua');
                $to = $message->getTo()[0] ?? new Address('admin@4n.com.ua');
            } else {
                // Fallback if nothing can be extracted
                $from = new Address('admin@4n.com.ua');
                $to = new Address('admin@4n.com.ua');
            }

            $envelope = new Envelope($from, [$to]);
        }

        return new SentMessage($message, $envelope);
    }

    public function __toString(): string
    {
        return 'file://transport';
    }
}
