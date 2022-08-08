<?php

namespace Swe\RTS;

use ATDev\RocketChat\Channels\Channel;
use ATDev\RocketChat\Channels\Collection;
use ATDev\RocketChat\Chat;
use ATDev\RocketChat\Messages\Message;

/**
 *
 */
class Collector extends Base
{

    /**
     * @var array<string, string>
     */
    private array $userMapping;

    /**
     * @param Settings $settings
     * @param array $userMapping
     */
    public function __construct(Settings $settings, array $userMapping = [])
    {
        parent::__construct($settings);
        $this->userMapping = $userMapping;
    }

    /**
     * @return void
     */
    public function collect(): void
    {
        if (!empty($this->getExportFiles())) {
            return;
        }

        $this->login();
        $offset = 0;
        $count = 100;

        do {
            $channels = Channel::listing($offset, $count);
            $offset += $count;

            if (!$channels instanceof Collection) {
                return;
            }

            foreach ($channels as $channel) {
                $this->collectMessagesFromChannel($channel);
            }
        } while ($channels->count() >= $count);

        $this->logout();
    }

    /**
     * @param Channel $channel
     * @return void
     */
    private function collectMessagesFromChannel(Channel $channel): void
    {
        $messageCollection = $channel->messages();

        if ($messageCollection === false) {
            return;
        }

        /** @var Message[] $messages */
        $messages = array_reverse($messageCollection->toArray());
        $parsed = [
                $channel->getTopic() ?? '',
        ];
        $creationDates = [];

        foreach ($messages as $message) {
            $messageText = trim($message->getMsg());
            $username = $this->getUsername($message->getUsername());

            if ($this->isUsername($messageText) || empty($username)) {
                continue;
            }

            $created = strtotime($message->getUpdatedAt());

            while (in_array($created, $creationDates)) {
                $created += 1;
            }

            $creationDates[] = $created;

            $parsed[] = [
                'className' => 'ImportMessage.Create',
                'messageId' => [
                    'externalId' => $message->getMessageId(),
                ],
                'message' => [
                    'className' => 'ChatMessage.Text',
                    'text' => $messageText,
                ],
                'author' => 'profile:username:' . $username,
                'createdAtUtc' => $created,
            ];
        }

        if (!empty($parsed)) {
            $file = sprintf($this->getSettings()->getExportDirectory() . '%s.json', $channel->getName());
            $this->exportFile($file, $parsed);
        }
    }

    /**
     * @param string $filePath
     * @param array $messages
     * @return void
     */
    private function exportFile(string $filePath, array $messages): void
    {
        $json = json_encode($messages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($filePath, $json);
    }

    /**
     * @param string $key
     * @return bool
     */
    private function isUsername(string $key): bool
    {
        return isset($this->userMapping[$key]) || in_array($key, $this->userMapping);
    }

    /**
     * @param string $key
     * @return string
     */
    private function getUsername(string $key): string
    {
        return $this->userMapping[$key] ?? '';
    }

    /**
     * @return void
     */
    private function login(): void
    {
        Chat::setUrl($this->getSettings()->getRocketChatUrl());
        Chat::login($this->getSettings()->getRocketChatUser(), $this->getSettings()->getRocketChatPassword());
    }

    /**
     * @return void
     */
    private function logout(): void
    {
        Chat::logout();
    }
}