<?php

declare(strict_types=1);

namespace LBajsarowicz\MediaFallback\Plugin;

use LBajsarowicz\MediaFallback\Provider\MediaFallbackConfigProvider;
use LBajsarowicz\MediaFallback\Service\CanSynchronizeMediaFile;
use LBajsarowicz\MediaFallback\Service\MediaSynchronizationService;
use Magento\MediaStorage\App\Media as MediaStorage;
use Magento\MediaStorage\Model\File\Storage\Request as MediaRequest;

class FileTransferHandleMissingMediaPlugin
{
    public function __construct(
        private readonly MediaRequest $mediaRequest,
        private readonly CanSynchronizeMediaFile $canSynchronizeMediaFile,
        private readonly MediaSynchronizationService $synchronizationService,
    ) {
    }

    public function beforeLaunch(MediaStorage $subject): void
    {
        $filePath = $this->mediaRequest->getPathInfo();

        if (empty($filePath)) {
            return; // Proceed with regular flow
        }

        if ($this->canSynchronizeMediaFile->execute($filePath)) {
            $this->synchronizationService->execute($filePath);
        }
    }
}
