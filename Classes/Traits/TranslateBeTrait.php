<?php

namespace Pixelant\PxaProductManager\Traits;

use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * Use if you need to translate in BE
 * @package Pixelant\PxaProductManager\Traits
 */
trait TranslateBeTrait
{
    /**
     * Path to the locallang file
     *
     * @var string
     */
    protected static $LLPATH = 'LLL:EXT:pxa_product_manager/Resources/Private/Language/locallang_be.xlf:';

    /**
     * Translate by key
     *
     * @param string $key
     * @param array $arguments
     * @return string
     */
    protected function translate(string $key, array $arguments = null): string
    {
        $label = $this->getLanguageService()->sL(self::$LLPATH . $key);

        if (!empty($arguments)) {
            $label = vsprintf($label, $arguments);
        }

        return $label;
    }

    /**
     * Return language service instance
     *
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
