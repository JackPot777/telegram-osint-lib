<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Auth\Protocol\AppAuthorization;
use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Client\AuthKey\AuthKeyCreator;
use TelegramOSINT\Client\AuthKey\Versions\AuthKey_v2;
use TelegramOSINT\Client\BasicClient\BasicClientImpl;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\ClientDebugLogger;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TGConnection\SocketMessenger\MessageListener;

class AuthKeyCreateTest extends TestCase implements MessageListener
{
    /** @var bool */
    private $sessionCreated = false;

    /**
     * Test that telegram auth key is formatted correctly.
     */
    public function test_generate_auth_key(): void
    {
        Logger::setupLogger($this->createMock(ClientDebugLogger::class));

        $dc = DataCentre::getDefault();
        // perform several retries in case of failure
        for ($i = 0; $i < 5; $i++) {
            try {
                $this->performAuth($dc);
                break;
            } catch (TGException $e) {
                Logger::log(__CLASS__, $e->getMessage());
            }

            sleep(1);
        }
    }

    /**
     * @param AnonymousMessage $message
     */
    public function onMessage(AnonymousMessage $message)
    {
        if($message->getType() == 'msg_container') {
            $message = $message->getNodes('messages')[0];
        }

        if($message->getType() == 'new_session_created')
            $this->sessionCreated = true;
    }

    /**
     * @param DataCentre $dc
     *
     * @throws TGException
     */
    protected function performAuth(DataCentre $dc): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $auth = new AppAuthorization($dc);
        /* @noinspection PhpUnhandledExceptionInspection */
        $auth->createAuthKey(function (AuthKey $key) {
            $serializedKey = $key->getSerializedAuthKey();
            $authKey = AuthKeyCreator::createFromString($serializedKey);

            // check if key in good format
            $this->assertTrue($authKey instanceof AuthKey_v2);

            $client = new BasicClientImpl();
            $client->setMessageListener($this);
            $client->login($key);

            while (!$client->pollMessage()) {
                usleep(100000);
                true;
            }

            // check if key login-able
            $this->assertTrue($this->sessionCreated);
        });
    }
}
