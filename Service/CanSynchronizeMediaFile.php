<?php

declare(strict_types=1);

namespace LBajsarowicz\MediaFallback\Service;

use LBajsarowicz\MediaFallback\Provider\MediaFallbackConfigProvider;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class CanSynchronizeMediaFile
{
    private Filesystem\Directory\ReadInterface $pubDirectory;

    public function __construct(private readonly MediaFallbackConfigProvider $configProvider, Filesystem $filesystem)
    {
        $this->pubDirectory = $filesystem->getDirectoryRead(DirectoryList::PUB);
    }

    public function execute(string $filePath): bool
    {
        if (!$this->configProvider->isMediaFallbackEnabled()) {
            return false;
        }

        $relativePath = $this->pubDirectory->getRelativePath($filePath);

        if ($this->pubDirectory->isExist($relativePath)) {
            return false;
        }

        foreach ($this->configProvider->getSupportedPaths() as $supportedPath) {
            if (str_starts_with($filePath, $supportedPath)) {
                return true;
            }
        }

        return false;
    }
}
