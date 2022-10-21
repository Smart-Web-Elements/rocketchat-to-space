<?php

namespace Swe\RTS;

/**
 * Class Base
 *
 * @package Swe\RTS
 * @author Luca Braun <l.braun@s-w-e.com>
 */
abstract class Base
{
    /**
     * @var Settings
     */
    private Settings $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return Settings
     */
    protected function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * @return array{
     *     array{
     *     path: string,
     *     name: string,
     *     }
     *     }
     */
    protected function getExportFiles(): array
    {
        if (!is_dir($this->settings->getExportDirectory())) {
            mkdir($this->settings->getExportDirectory());
        }

        $invalid = [
            '.',
            '..',
        ];
        $files = array_filter(scandir($this->settings->getExportDirectory()), function (string $fileName) use ($invalid) {
            return !in_array($fileName, $invalid);
        });

        return array_map(function (string $file) {
            $nameArray = explode('.', $file);
            array_pop($nameArray);

            return [
                'path' => $this->settings->getExportDirectory() . $file,
                'name' => implode('.', $nameArray),
            ];
        }, $files);
    }
}