<?php
class Response {
    public static function json($data = null, $success = true, $error = null, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = [
            'success' => $success,
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if ($error !== null) {
            $response['error'] = $error;
        }
        
        echo json_encode($response);
        exit;
    }

    public static function error($message, $code = 400, $details = null) {
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if ($details !== null) {
            $response['details'] = $details;
        }
        
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
