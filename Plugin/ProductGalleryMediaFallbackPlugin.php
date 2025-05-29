<?php

declare(strict_types=1);

namespace LBajsarowicz\MediaFallback\Plugin;

use LBajsarowicz\MediaFallback\Service\CanSynchronizeMediaFile;
use LBajsarowicz\MediaFallback\Service\MediaSynchronizationService;
use Magento\Catalog\Block\Product\Gallery;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;

class ProductGalleryMediaFallbackPlugin
{
    public function __construct(
        private readonly MediaSynchronizationService $mediaSynchronizationService,
        private readonly CanSynchronizeMediaFile $canSynchronizeMediaFile,
        private readonly Filesystem $filesystem
    ) {
    }

    public function aroundGetImageWidth(Gallery $productGallery, callable $proceed): bool
    {
        try {
            return $proceed();
        } catch (FileSystemException $e) {
            $filePath = $productGallery->getCurrentImage()->getPath();
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::PUB);

            $relativePath = $mediaDirectory->getRelativePath($filePath);

            if ($this->canSynchronizeMediaFile->execute($relativePath)) {
                $this->mediaSynchronizationService->execute($relativePath);

                return $proceed();
            }

            throw $e;
        }
    }
}
