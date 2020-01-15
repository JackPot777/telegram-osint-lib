<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherCallbacks;
use TelegramOSINT\Client\StatusWatcherClient\StatusWatcherClient;
use TelegramOSINT\Exception\TGException;

interface ClientGeneratorInterface
{
    /**
     * @return InfoClient
     */
    public function getInfoClient();

    /**
     * @param StatusWatcherCallbacks $callbacks
     *
     * @throws TGException
     *
     * @return StatusWatcherClient
     */
    public function getStatusWatcherClient(StatusWatcherCallbacks $callbacks);

    public function getAuthKeyInfo(): string;

    public function getAuthKeyStatus(): string;
}
