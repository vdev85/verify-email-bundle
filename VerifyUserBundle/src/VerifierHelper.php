<?php

namespace JRushlow\Bundle\VerifyUser;

use JRushlow\Bundle\VerifyUser\Generator\TokenGenerator;
use JRushlow\Bundle\VerifyUser\Model\SignatureComponents;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 */
class VerifierHelper implements VerifierHelperInterface
{
    /**
     * @var TokenGenerator
     */
    private $generator;

    public function __construct(TokenGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @inheritDoc
     */
    public function generateSignature(string $userId, \DateTimeInterface $expiresAt = null): SignatureComponents
    {
        if (null === $expiresAt) {
            $expiresAt = (new \DateTimeImmutable('now'))
                ->modify(sprintf('+%d seconds', 450));
        }

        return new SignatureComponents($expiresAt, $this->generator->getToken($expiresAt, $userId));
    }

    /**
     * @inheritDoc
     */
    public function isValidSignature(string $signature, string $userId): bool
    {
        $timestamp = (int) substr($signature, 0, 10);
        $time = new \DateTimeImmutable();
        $expiresAt = $time->setTimestamp($timestamp);

        $expected = $this->generator->getToken($expiresAt, $userId);

        return hash_equals($expected, substr($signature, 10));
    }
}