<?php

declare(strict_types=1);

namespace LBajsarowicz\MediaFallback\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface;

class MediaFallbackConfigProvider
{
    /** @var string[] */
    private const SUPPORTED_PATHS = ['media/'];

    public function __construct(private readonly ScopeConfigInterface $scopeConfig)
    {
    }

    public function getFallbackBaseUrl(): ?string
    {
        $fallbackUrl = $this->scopeConfig->getValue('dev/media_fallback/fallback_base_url');

        return $this->isMediaFallbackEnabled() && $fallbackUrl
            ? $fallbackUrl
            : null;
    }

    public function isMediaFallbackEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('dev/media_fallback/enabled');
    }

    /**
     * @return string[]
     */
    public function getSupportedPaths(): array
    {
        return self::SUPPORTED_PATHS;
    }
}
