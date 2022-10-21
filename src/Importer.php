<?php

namespace Swe\RTS;

use GuzzleHttp\Exception\GuzzleException;
use Swe\SpaceSDK\Exception\MissingArgumentException;
use Swe\SpaceSDK\HttpClient;
use Swe\SpaceSDK\Space;

/**
 *
 */
class Importer extends Base
{
    /**
     *
     */
    const MAX_MESSAGES = 50;
    /**
     * @var Space
     */
    private Space $space;
    /**
     * @var string
     */
    private string $currentName;
    /**
     * @var int
     */
    private int $currentIndex;

    /**
     * @param Settings $settings
     * @param string $currentName
     * @param int $currentIndex
     * @throws GuzzleException
     */
    public function __construct(Settings $settings, string $currentName = '', int $currentIndex = 0)
    {
        parent::__construct($settings);
        $this->currentName = $currentName;
        $this->currentIndex = $currentIndex;
        $client = new HttpClient(
            $settings->getSpaceUrl(),
            $settings->getSpaceClientId(),
            $settings->getSpaceClientSecret()
        );
        $this->space = new Space($client);
    }

    /**
     * @return void
     */
    public function clearAll(): void
    {
        try {
            $channels = $this->space->chats()->channels()->listAllChannels([], ['data' => ['channelId']]);
            foreach ($channels as $channel) {
                $this->space->chats()->channels()->deleteChannel('id:' . $channel['channelId']);
            }
        } catch (GuzzleException|MissingArgumentException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @return void
     */
    public function import(): void
    {
        $files = $this->getExportFiles();

        foreach ($files as $fileInfo) {
            $this->importChannel($fileInfo);
        }
    }

    /**
     * @param array{
     *     path: string,
     *     name: string
     * } $fileInfo
     * @return void
     */
    private function importChannel(array $fileInfo): void
    {
        $messages = json_decode(file_get_contents($fileInfo['path']), true);
        $topic = array_shift($messages);
        $channelName = str_replace('.', '-', $fileInfo['name']);

        if (!empty($this->currentName) && $channelName < $this->currentName) {
            return;
        }

        $chunks = [];
        $newAuthors = [];
        $authors = [];
        $currentIndex = 0;

        foreach ($messages as $index => $message) {
            if ($channelName === $this->currentName) {
                if ($index < $this->currentIndex - self::MAX_MESSAGES) {
                    continue;
                }

                if ($index < $this->currentIndex) {
                    try {
                        $messageId = 'externalId:' . $message['messageId']['externalId'];
                        $channel = 'name:' . $channelName;
                        $this->space->chats()->messages()->getMessage($channel, $messageId);
                        continue;
                    } catch (GuzzleException $e) {
                    }
                }
            }

            $authorArray = explode(':', $message['author']);
            $author = array_pop($authorArray);

            if (!in_array($author, $authors) && !in_array($author, $newAuthors)) {
                $newAuthors[] = $author;
            }

            if ($index % self::MAX_MESSAGES === 0 && !empty($chunks)) {
                try {
                    $this->importChunk($chunks, $channelName, $topic, $newAuthors);
                    $chunks = [];
                    foreach ($newAuthors as $author) {
                        $authors[] = $author;
                    }
                    $newAuthors = [];
                } catch (GuzzleException|MissingArgumentException $e) {
                    print $e->getMessage();
                    print "\nChannel: " . $channelName;
                    print "\nIndex chunk: " . $index;
                    print "\n";
                    exit;
                }
            }

            $chunks[] = $message;
            $currentIndex = $index;
        }

        if (!empty($chunks)) {
            try {
                $this->importChunk($chunks, $channelName, $topic, $newAuthors);
            } catch (GuzzleException|MissingArgumentException $e) {
                print $e->getMessage();
                print "\nChannel: " . $channelName;
                print "\nIndex chunk: " . $currentIndex;
                print "\n";
                exit;
            }
        }
    }

    /**
     * @param array $messages
     * @param string $channelName
     * @param string $topic
     * @param array $subscribers
     * @return void
     * @throws GuzzleException
     * @throws MissingArgumentException
     */
    private function importChunk(array $messages, string $channelName, string $topic, array $subscribers): void
    {
        if ($this->space->chats()->channels()->isNameFree($channelName)) {
            $this->space->chats()->channels()->addNewChannel([
                'name' => $channelName,
                'description' => $topic,
                'private' => false,
            ]);
            sleep(1);
        }

        if (!empty($subscribers)) {
            $this->space->chats()->channels()->subscribers()->users()->addUsersToChannel(
                'channel:name:' . $channelName,
                [
                    'profiles' => array_map(function (string $username) {
                        return 'username:' . $username;
                    }, $subscribers),
                ]
            );
        }

        $this->space->chats()->messages()->importMessages([
            'channel' => 'channel:name:' . $channelName,
            'messages' => $messages,
        ]);
    }
}