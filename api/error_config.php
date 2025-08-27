<?php
require_once 'env_config.php';

// Custom error handler with more detailed error types
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $errorType = match($errno) {
        E_ERROR => 'Fatal Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Standards',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
        default => 'Unknown Error'
    };

    $error = [
        'success' => false,
        'error' => $errstr,
        'details' => [
            'type' => $errorType,
            'file' => ENVIRONMENT === 'development' ? $errfile : basename($errfile),
            'line' => $errline
        ]
    ];

    // Log the detailed error
    error_log(json_encode($error));

    // Only show detailed error info in development
    if (ENVIRONMENT !== 'development') {
        unset($error['details']);
        $error['error'] = 'An internal server error occurred';
    }

    echo apiResponse($error['error'], false, 500);
    exit;
}

// Set the custom error handler for API requests
if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    set_error_handler("customErrorHandler");
}

// Enhanced exception handler with logging
function customExceptionHandler($exception) {
    $error = [
        'success' => false,
        'error' => $exception->getMessage(),
        'details' => [
            'type' => get_class($exception),
            'file' => ENVIRONMENT === 'development' ? $exception->getFile() : basename($exception->getFile()),
            'line' => $exception->getLine()
        ]
    ];

    if (ENVIRONMENT === 'development') {
        $error['details']['trace'] = $exception->getTraceAsString();
    }

    // Log the detailed error
    error_log(json_encode($error));

    // In production, show generic error
    if (ENVIRONMENT !== 'development') {
        unset($error['details']);
        $error['error'] = 'An internal server error occurred';
    }

    echo apiResponse($error['error'], false, 500);
    exit;
}

// Set the custom exception handler
set_exception_handler("customExceptionHandler");

// Handle fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $errorInfo = [
            'type' => 'Fatal Error',
            'message' => $error['message'],
            'file' => ENVIRONMENT === 'development' ? $error['file'] : basename($error['file']),
            'line' => $error['line']
        ];

        // Log the fatal error
        error_log(json_encode($errorInfo));

        // In production, show generic error
        if (ENVIRONMENT !== 'development') {
            $errorInfo = 'An internal server error occurred';
        }

        echo apiResponse($errorInfo, false, 500);
    }
});

// JSON parsing error handler
function handleJsonError() {
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return true;
        case JSON_ERROR_DEPTH:
            $error = 'Maximum stack depth exceeded';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON';
            break;
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters';
            break;
        default:
            $error = 'Unknown JSON error';
            break;
    }

    // Log the JSON error
    error_log("JSON Error: " . $error);

    if (ENVIRONMENT !== 'development') {
        $error = 'Invalid request format';
    }

    echo apiResponse($error, false, 400);
    exit;
}
