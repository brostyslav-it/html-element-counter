<?php

/**
 * Class FetchModel
 *
 * This class represents the model for fetching and processing data related to URLs and HTML elements.
 */
class FetchModel
{
    /**
     * @var string User agent string for cURL requests
     */
    private const USERAGENT = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';

    /**
     * @var int Time difference threshold (in minutes) for considering a fetch as recent
     */
    private const RECENT_FETCH_TIME_DIFF = 5;

    /**
     * @var int Time period (in hours) for calculating average page fetch time for a domain
     */
    private const PAGE_FETCH_TIME_PERIOD = 24;

    /**
     * @var string File extension for SQL files
     */
    private const SQL_FILE_EXTENSION = '.sql';

    /**
     * @var string SQL file identifier for finding recent fetch
     */
    private const FIND_RECENT_FETCH_SQL = 'find_recent_fetch';

    /**
     * @var string SQL file identifier for finding a name entity
     */
    private const FIND_NAME_ENTITY_SQL = 'find_name_entity';

    /**
     * @var string SQL file identifier for adding a name entity
     */
    private const ADD_NAME_ENTITY_SQL = 'add_name_entity';

    /**
     * @var string SQL file identifier for adding a request
     */
    private const ADD_REQUEST_SQL = 'add_request';

    /**
     * @var string SQL file identifier for selecting URLs for a domain
     */
    private const SELECT_URLS_FOR_DOMAIN_SQL = 'select_urls_for_domain';

    /**
     * @var string SQL file identifier for getting average page fetch time for a domain
     */
    private const GET_AVERAGE_PAGE_FETCH_TIME_FOR_DOMAIN_SQL = 'get_average_page_fetch_time_for_domain';

    /**
     * @var string SQL file identifier for getting total element count for a domain
     */
    private const GET_TOTAL_ELEMENT_COUNT_FOR_DOMAIN_SQL = 'get_total_element_count_for_domain';

    /**
     * @var string SQL file identifier for getting total element count
     */
    private const GET_TOTAL_ELEMENT_COUNT_SQL = 'get_total_element_count';

    /**
     * @var string Entity type identifier for URL
     */
    private const URL_ENTITY = 'url';

    /**
     * @var string Entity type identifier for HTML element
     */
    private const ELEMENT_ENTITY = 'element';

    /**
     * @var string Entity type identifier for domain
     */
    private const DOMAIN_ENTITY = 'domain';

    /**
     * @var string Placeholder for table name replacement in SQL queries
     */
    private const TABLE_NAME_REPLACEMENT = ':table';

    /**
     * @var int HTTP status code indicating a successful response
     */
    private const HTTP_STATUS_OK = 200;

    /**
     * @var string Field name for total count in SQL queries
     */
    private const TOTAL_FIELD_NAME = 'total';

    /**
     * @var string Field name for average duration in SQL queries
     */
    private const AVERAGE_DURATION_FIELD_NAME = 'average_duration';

    /**
     * @var string Field name for identifier in SQL queries
     */
    private const ID_FIELD_NAME = 'id';

    /**
     * @var string Date and time format for fetchDateTime
     */
    private const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var int Base multiplier for converting milliseconds
     */
    private const MS_CONVERT_BASE = 1000;

    /**
     * @var string HTML tag name for checking if HTML content is valid
     */
    private const HTML_TAG_NAME = 'html';

    /**
     * @var Connection Database connection instance
     */
    private Connection $db;

    /**
     * FetchModel constructor.
     *
     * Initializes the database connection instance.
     */
    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Executes a SQL query using a specified SQL file and optional parameters and replacements.
     *
     * @param string $sqlFile The SQL file identifier
     * @param array|null $params Optional parameters for the query
     * @param array|null $replace Optional replacements for placeholders in the SQL query
     * @return mysqli_result|false The query result or false on failure
     */
    private function query(string $sqlFile, array $params = null, array $replace = null): mysqli_result|false
    {
        $sql = file_get_contents(DbConstants::SQL_PATH . $sqlFile . self::SQL_FILE_EXTENSION);

        if ($replace) {
            $sql = str_replace($replace[0], $replace[1], $sql);
        }

        return $this->db->query($sql, $params);
    }

    /**
     * Checks for a recent fetch of the given URL and HTML element.
     *
     * @param string $url The URL being checked
     * @param string $element The HTML element being checked
     * @return false|array The recent fetch data or false if not found
     */
    public function checkForRecentFetch(string $url, string $element): false|array
    {
        $urlId = $this->findEntity($url, self::URL_ENTITY);
        $elementId = $this->findEntity($element, self::ELEMENT_ENTITY);

        if (!$urlId || !$elementId) {
            return false;
        }

        $result = $this->query(self::FIND_RECENT_FETCH_SQL, ['iii', [$urlId, $elementId, self::RECENT_FETCH_TIME_DIFF]]);

        return $result->num_rows > 0 ? $result->fetch_assoc() : false;
    }

    /**
     * Retrieves data (date and time, response time, count) for the given URL.
     *
     * @param string $url The URL to be fetched
     * @return array|false The fetched data or false on failure
     */
    public function getUrlData(string $url): array|false
    {
        $fetchDateTime = date(self::DATE_TIME_FORMAT);
        $startTime = microtime(true);

        [$httpCode, $pageContent] = $this->fetch($url);

        $responseTime = round((microtime(true) - $startTime) * self::MS_CONVERT_BASE);

        if ($httpCode !== self::HTTP_STATUS_OK) {
            return false;
        }

        return [$fetchDateTime, $pageContent, $responseTime];
    }

    /**
     * Performs a cURL request to fetch HTML content and HTTP status code for the given URL.
     *
     * @param string $url The URL to be fetched
     * @return array The cURL response containing HTTP status code and HTML content
     */
    private function fetch(string $url): array
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, self::USERAGENT);

        $pageContent = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return [$httpCode, $pageContent];
    }

    /**
     * Counts the occurrences of a specified HTML element in the given HTML content.
     *
     * @param string $html The HTML content to be processed
     * @param string $element The HTML element to be counted
     * @return int The count of the specified HTML element or -1 on failure
     */
    public function countHtmlElement(string $html, string $element): int
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);

        if ($dom->getElementsByTagName(self::HTML_TAG_NAME)->length === 0) {
            return Constants::INVALID_HTML_RETURN;
        }

        return $dom->getElementsByTagName($element)->length;
    }

    /**
     * Inserts data related to a fetched URL into the database.
     *
     * @param string $url The URL being inserted
     * @param string $domain The domain of the URL being inserted
     * @param string $element The HTML element being inserted
     * @param string $fetchDateTime Fetch date and time
     * @param int $responseTime The response time for the fetch
     * @param int $count The count of the specified HTML element
     */
    public function insertUrlData(string $url, string $domain, string $element, string $fetchDateTime, int $responseTime, int $count): void
    {
        $this->query(self::ADD_REQUEST_SQL, ['iiisii', [
            $this->findEntity($domain, self::DOMAIN_ENTITY) ?? $this->insertEntity($domain, self::DOMAIN_ENTITY),
            $this->findEntity($url, self::URL_ENTITY) ?? $this->insertEntity($url, self::URL_ENTITY),
            $this->findEntity($element, self::ELEMENT_ENTITY) ?? $this->insertEntity($element, self::ELEMENT_ENTITY),
            $fetchDateTime,
            $responseTime,
            $count
        ]]);
    }

    /**
     * Finds the identifier of an entity (URL, HTML element, or domain) in the database.
     *
     * @param string $find The entity to be found
     * @param string $table The table representing the entity type
     * @return null|int The identifier of the entity or null if not found
     */
    private function findEntity(string $find, string $table): null|int
    {
        $result = $this->query(self::FIND_NAME_ENTITY_SQL, ['s', [$find]], [self::TABLE_NAME_REPLACEMENT, $table]);

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc()[self::ID_FIELD_NAME];
    }

    /**
     * Inserts a new entity (URL, HTML element, or domain) into the database and returns its identifier.
     *
     * @param string $insert The entity to be inserted
     * @param string $table The table representing the entity type
     * @return int The identifier of the newly inserted entity
     */
    private function insertEntity(string $insert, string $table): int
    {
        $this->query(self::ADD_NAME_ENTITY_SQL, ['s', [$insert]], [self::TABLE_NAME_REPLACEMENT, $table]);
        return $this->db->id();
    }

    /**
     * Retrieves general statistics related to a domain and HTML element.
     *
     * @param string $domain The domain for which statistics are retrieved
     * @param string $element The HTML element for which statistics are retrieved
     * @return array General statistics array
     */
    public function getGeneralStatistics(string $domain, string $element): array
    {
        $domainId = $this->findEntity($domain, self::DOMAIN_ENTITY);
        $elementId = $this->findEntity($element, self::ELEMENT_ENTITY);

        return [
            $this->getUrlCountForDomain($domainId),
            $this->getAveragePageFetchTimeForDomain($domainId),
            $this->getTotalElementCountForDomain($domainId, $elementId),
            $this->getTotalElementCount($elementId)
        ];
    }

    /**
     * Retrieves the count of URLs for a given domain.
     *
     * @param int $domainId The identifier of the domain
     * @return int The count of URLs for the domain
     */
    private function getUrlCountForDomain(int $domainId): int
    {
        return $this->query(self::SELECT_URLS_FOR_DOMAIN_SQL, ['i', [$domainId]])->num_rows;
    }

    /**
     * Retrieves the average page fetch time for a given domain.
     *
     * @param int $domainId The identifier of the domain
     * @return int The average page fetch time for the domain
     */
    private function getAveragePageFetchTimeForDomain(int $domainId): int
    {
        return round($this->query(self::GET_AVERAGE_PAGE_FETCH_TIME_FOR_DOMAIN_SQL, ['ii', [$domainId, self::PAGE_FETCH_TIME_PERIOD]])->fetch_assoc()[self::AVERAGE_DURATION_FIELD_NAME]);
    }

    /**
     * Retrieves the total element count for a given domain and HTML element.
     *
     * @param int $domainId The identifier of the domain
     * @param int $elementID The identifier of the HTML element
     * @return int The total element count for the domain and HTML element
     */
    private function getTotalElementCountForDomain(int $domainId, int $elementID): int
    {
        return $this->query(self::GET_TOTAL_ELEMENT_COUNT_FOR_DOMAIN_SQL, ['ii', [$elementID, $domainId]])->fetch_assoc()[self::TOTAL_FIELD_NAME];
    }

    /**
     * Retrieves the total element count for a given HTML element.
     *
     * @param int $elementId The identifier of the HTML element
     * @return int The total element count for the HTML element
     */
    private function getTotalElementCount(int $elementId): int
    {
        return $this->query(self::GET_TOTAL_ELEMENT_COUNT_SQL, ['i', [$elementId]])->fetch_assoc()[self::TOTAL_FIELD_NAME];
    }
}
