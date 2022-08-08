<?php

namespace Swe\RTS;

/**
 *
 */
class Settings
{
    /**
     * @var string
     */
    private string $rocketChatUrl;

    /**
     * @var string
     */
    private string $rocketChatUser;

    /**
     * @var string
     */
    private string $rocketChatPassword;

    /**
     * @var string
     */
    private string $spaceUrl;

    /**
     * @var string
     */
    private string $spaceClientId;

    /**
     * @var string
     */
    private string $spaceClientSecret;

    /**
     * @var string
     */
    private string $exportDirectory;

    /**
     * @param array $arguments
     */
    public function __construct(array $arguments = [])
    {
        foreach ($arguments as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $value);
            }
        }

        $this->exportDirectory = dirname(__DIR__) . '/export/';
    }

    /**
     * @return string
     */
    public function getRocketChatUrl(): string
    {
        return $this->rocketChatUrl;
    }

    /**
     * @param string $rocketChatUrl
     */
    public function setRocketChatUrl(string $rocketChatUrl): void
    {
        $this->rocketChatUrl = $rocketChatUrl;
    }

    /**
     * @return string
     */
    public function getRocketChatUser(): string
    {
        return $this->rocketChatUser;
    }

    /**
     * @param string $rocketChatUser
     */
    public function setRocketChatUser(string $rocketChatUser): void
    {
        $this->rocketChatUser = $rocketChatUser;
    }

    /**
     * @return string
     */
    public function getRocketChatPassword(): string
    {
        return $this->rocketChatPassword;
    }

    /**
     * @param string $rocketChatPassword
     */
    public function setRocketChatPassword(string $rocketChatPassword): void
    {
        $this->rocketChatPassword = $rocketChatPassword;
    }

    /**
     * @return string
     */
    public function getSpaceUrl(): string
    {
        return $this->spaceUrl;
    }

    /**
     * @param string $spaceUrl
     */
    public function setSpaceUrl(string $spaceUrl): void
    {
        $this->spaceUrl = $spaceUrl;
    }

    /**
     * @return string
     */
    public function getSpaceClientId(): string
    {
        return $this->spaceClientId;
    }

    /**
     * @param string $spaceClientId
     */
    public function setSpaceClientId(string $spaceClientId): void
    {
        $this->spaceClientId = $spaceClientId;
    }

    /**
     * @return string
     */
    public function getSpaceClientSecret(): string
    {
        return $this->spaceClientSecret;
    }

    /**
     * @param string $spaceClientSecret
     */
    public function setSpaceClientSecret(string $spaceClientSecret): void
    {
        $this->spaceClientSecret = $spaceClientSecret;
    }

    /**
     * @return string
     */
    public function getExportDirectory(): string
    {
        return $this->exportDirectory;
    }
}