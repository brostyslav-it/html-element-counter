<?php

/**
 * Class FetchController
 *
 * This class handles fetching and processing of URLs, providing responses in JSON format.
 */
class FetchController
{
    /**
     * @var string Error message for invalid URL
     */
    private const INVALID_URL_ERR_MESSAGE = 'Invalid url';

    /**
     * @var string Error message for URL exceeding maximum length
     */
    private const TOO_LONG_URL_ERR_MESSAGE = 'URL can\'t be longer than 500 symbols';

    /**
     * @var string Error message for empty HTML element
     */
    private const EMPTY_HTML_ELEMENT_ERR_MESSAGE = 'Enter HTML element to count';

    /**
     * @var string Error message for inaccessible URL
     */
    private const INACCESSIBLE_URL_ERR_MESSAGE = 'Inaccessible URL';

    /**
     * @var string Field name for indicating a successful response
     */
    private const OK_FIELD_NAME = 'ok';

    /**
     * @var string Field name for holding errors in the response
     */
    private const ERR_FIELD_NAME = 'errors';

    /**
     * @var int Maximum length for a URL
     */
    private const MAX_URL_LENGTH = 500;

    /**
     * @var string Error message for invalid HTML
     */
    private const INVALID_HTML_ERR_MESSAGE = 'Invalid HTML';

    /**
     * @var FetchModel Model instance for handling data related to fetched URLs
     */
    private FetchModel $model;

    /**
     * FetchController constructor.
     *
     * Initializes the FetchModel instance.
     */
    public function __construct()
    {
        $this->model = new FetchModel();
    }

    /**
     * Echoes a successful response in JSON format.
     *
     * @param string $url The URL being processed
     * @param string $domain The domain extracted from the URL
     * @param string $element The HTML element being counted
     * @param string $fetchDateTime The date and time of the fetch
     * @param int $responseTime The response time for the fetch
     * @param int $count The count of the specified HTML element
     * @param array $generalStatistics General statistics related to the domain and element
     */
    private function echoSuccessfulResponse(string $url, string $domain, string $element, string $fetchDateTime, int $responseTime, int $count, array $generalStatistics): void
    {
        echo json_encode(
            [
                self::OK_FIELD_NAME => true,
                'url' => $url,
                'domain' => $domain,
                'element' => $element,
                'fetchDateTime' => $fetchDateTime,
                'responseTime' => $responseTime,
                'count' => $count,
                'urlCountForDomain' => $generalStatistics[0],
                'averagePageFetchTimeForDomain' => $generalStatistics[1],
                'totalElementCountForDomain' => $generalStatistics[2],
                'totalElementCount' => $generalStatistics[3],
            ]
        );
    }

    /**
     * Echoes an error response in JSON format and exits the script.
     *
     * @param array $errors Array of error messages
     */
    private function echoErrorResponse(array $errors): void
    {
        echo json_encode([self::OK_FIELD_NAME => false, self::ERR_FIELD_NAME => $errors]);
        exit();
    }

    /**
     * Processes the given URL and HTML element, handling validation and providing a response.
     *
     * @param string|null $url The URL to be processed
     * @param string|null $element The HTML element to be counted
     */
    public function processUrl(?string $url, ?string $element): void
    {
        $this->handleValidation($url, $element);
        $domain = parse_url($url)['host'];

        $this->checkForRecentFetch($url, $domain, $element);

        [$fetchDateTime, $responseTime, $count] = $this->getResponseData($url, $element);

        $this->model->insertUrlData($url, $domain, $element, $fetchDateTime, $responseTime, $count);

        $this->echoSuccessfulResponse($url, $domain, $element, $fetchDateTime, $responseTime, $count, $this->model->getGeneralStatistics($domain, $element));
    }

    /**
     * Handles validation of the given URL and HTML element.
     *
     * @param string|null $url The URL to be validated
     * @param string|null $element The HTML element to be validated
     */
    private function handleValidation(?string $url, ?string $element): void
    {
        $validationResults = Validator::validate([
            [empty($url) || !filter_var($url, FILTER_VALIDATE_URL), self::INVALID_URL_ERR_MESSAGE],
            [mb_strlen($url) > self::MAX_URL_LENGTH, self::TOO_LONG_URL_ERR_MESSAGE],
            [empty($element), self::EMPTY_HTML_ELEMENT_ERR_MESSAGE]
        ]);

        if (!$validationResults[self::OK_FIELD_NAME]) {
            $this->echoErrorResponse($validationResults[self::ERR_FIELD_NAME]);
        }
    }

    /**
     * Checks for a recent fetch of the given URL and HTML element, and provides a response if found.
     *
     * @param string $url The URL being processed
     * @param string $domain The domain extracted from the URL
     * @param string $element The HTML element being counted
     */
    private function checkForRecentFetch(string $url, string $domain, string $element): void
    {
        if ($recentFetch = $this->model->checkForRecentFetch($url, $element)) {
            $this->echoSuccessfulResponse($url, $domain, $element, $recentFetch['time'], $recentFetch['duration'], $recentFetch['count'], $this->model->getGeneralStatistics($domain, $element));
            exit();
        }
    }

    /**
     * Fetches data (date and time, response time, count) for the given URL and HTML element.
     *
     * @param string $url The URL to be fetched
     * @param string $element The HTML element to be counted
     * @return array The fetched data
     */
    private function fetchUrl(string $url, string $element): array
    {
        if (!$result = $this->model->getUrlData($url)) {
            $this->echoErrorResponse([self::INACCESSIBLE_URL_ERR_MESSAGE]);
        }

        [$fetchDateTime, $pageContent, $responseTime] = $result;
        $count = $this->model->countHtmlElement($pageContent, $element);

        return [$fetchDateTime, $responseTime, $count];
    }

    /**
     * Retrieves response data (date and time, response time, count) for the given URL and HTML element.
     *
     * @param string|null $url The URL to be processed
     * @param string|null $element The HTML element to be counted
     * @return array The response data
     */
    private function getResponseData(?string $url, ?string $element): array
    {
        [$fetchDateTime, $responseTime, $count] = $this->fetchUrl($url, $element);

        if ($count === Constants::INVALID_HTML_RETURN) {
            $this->echoErrorResponse([self::INVALID_HTML_ERR_MESSAGE]);
        }

        return [$fetchDateTime, $responseTime, $count];
    }
}
