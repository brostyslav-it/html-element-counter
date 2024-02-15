<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Request Method Check
 *
 * Check if the request method is POST, and exit with a 405 Method Not Allowed response if not.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit();
}

/**
 * Server Directory Path
 *
 * The relative path to the server directory.
 */
const SERVER_DIRECTORY_PATH = '../../server/';

/**
 * Required Files
 *
 * Include necessary files for the application to function.
 */
require_once SERVER_DIRECTORY_PATH . 'controllers/FetchController.php';
require_once SERVER_DIRECTORY_PATH . 'util/Validator.php';
require_once SERVER_DIRECTORY_PATH . 'db/Connection.php';
require_once SERVER_DIRECTORY_PATH . 'db/DbConstants.php';
require_once SERVER_DIRECTORY_PATH . 'Constants.php';
require_once SERVER_DIRECTORY_PATH . 'db/Migrations.php';
require_once SERVER_DIRECTORY_PATH . 'model/FetchModel.php';

/**
 * Fetch Controller Instance
 *
 * Create an instance of the FetchController for handling URL processing.
 */
$controller = new FetchController();

/**
 * Process URL
 *
 * Call the processUrl method on the FetchController instance, passing sanitized URL and element parameters.
 */
$controller->processUrl(
    isset($_POST['url']) ? htmlentities($_POST['url']) : null,
    isset($_POST['element']) ? htmlentities($_POST['element']) : null
);
