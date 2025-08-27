<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../models/CompanyModel.php';

class CompanyController {
    private $auth;
    private $companyModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->companyModel = new CompanyModel();
    }
    
    public function info() {
        $info = $this->companyModel->getInfo();
        if (!$info) {
            Response::error('Company information not found', 404);
        }
        Response::json($info);
    }
    
    public function contact() {
        $contact = $this->companyModel->getContact();
        if (!$contact) {
            Response::error('Company contact information not found', 404);
        }
        Response::json($contact);
    }
    
    public function stats() {
        $stats = $this->companyModel->getStats();
        if (!$stats) {
            Response::error('Company statistics not found', 404);
        }
        Response::json($stats);
    }
    
    public function registration() {
        $registration = $this->companyModel->getRegistration();
        if (!$registration) {
            Response::error('Company registration information not found', 404);
        }
        Response::json($registration);
    }
    
    public function updateInfo() {
        $this->auth->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $info = $this->companyModel->updateInfo($data);
        Response::json($info);
    }
    
    public function updateContact() {
        $this->auth->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $contact = $this->companyModel->updateContact($data);
        Response::json($contact);
    }
    
    public function updateRegistration() {
        $this->auth->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $registration = $this->companyModel->updateRegistration($data);
        Response::json($registration);
    }
}
