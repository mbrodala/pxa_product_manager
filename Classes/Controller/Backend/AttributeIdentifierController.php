<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Controller\Backend;

use Pixelant\PxaProductManager\Utility\MainUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AttributeIdentifierController
 */
class AttributeIdentifierController
{
    /**
     * Convert string for attribute identifier field
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function attributeIdentifierConvertAction(ServerRequestInterface $request): ResponseInterface
    {
        $queryParameters = $request->getParsedBody();
        $output = trim($queryParameters['value']);

        if (!empty($output)) {
            $output = GeneralUtility::underscoredToLowerCamelCase(
                MainUtility::normalizeString($output)
            );
        }

        $response = new HtmlResponse('');
        $response->getBody()->write(json_encode(['success' => true, 'output' => $output]));

        return $response;
    }
}
