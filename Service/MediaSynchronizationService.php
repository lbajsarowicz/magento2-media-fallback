<?php

declare(strict_types=1);

namespace LBajsarowicz\MediaFallback\Service;

use LBajsarowicz\MediaFallback\Provider\MediaFallbackConfigProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Client\Curl as CurlRequest;
use Magento\Framework\HTTP\Client\CurlFactory as CurlRequestFactory;

class MediaSynchronizationService
{

    public function __construct(
        private readonly MediaFallbackConfigProvider $config,
        private readonly CurlRequestFactory $curlFactory,
        private readonly Filesystem $filesystem
    ) {
    }

    public function execute(string $filePath): void
    {
        if (!$this->config->isMediaFallbackEnabled() || !$this->config->getFallbackBaseUrl()) {
            return;
        }

        $filePath = preg_replace('~/cache/[0-9a-z]+~', '', $filePath);
        $this->fetchMediaFile($filePath);
    }

    private function fetchMediaFile(string $filePath, bool $force = false): void
    {
        $relativePath = $this->getRelativePath($filePath);
        $fallbackUrl = $this->getMediaFallbackUrl($relativePath);

        if (!$force && $this->getMediaWrite()->isFile($relativePath)) {
            return;
        }

        try {
            /** @var CurlRequest $fetchRequest */
            $fetchRequest = $this->curlFactory->create();
            $fetchRequest->setOption(CURLOPT_FOLLOWLOCATION, true);
            $fetchRequest->get($fallbackUrl);

            if ((int)$fetchRequest->getStatus() < 400) {
                $this->getMediaWrite()->writeFile($relativePath, $fetchRequest->getBody());
            }
        } catch (\Exception $e) {
            return;
        }
    }

    private function getMediaFallbackUrl(string $filePath): string
    {
        return rtrim($this->config->getFallbackBaseUrl(), '/') . '/' . ltrim($filePath, '/');
    }

    private function getMediaWrite(): Filesystem\Directory\WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    private function getRelativePath(string $filePath): string
    {
        foreach ($this->config->getSupportedPaths() as $supportedPath) {
            $filePath = preg_replace('~^' . preg_quote($supportedPath, '~') . '~', '', $filePath);
        }

        return $filePath;
    }
}
