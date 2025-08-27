<?php
require_once __DIR__ . '/Model.php';

class CompanyModel extends Model {
    private $infoTable = 'company_info';
    private $contactsTable = 'company_contacts';
    private $registrationTable = 'company_registration';
    
    public function getInfo() {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->infoTable}
            ORDER BY id DESC
            LIMIT 1
        ");
        
        $stmt->execute();
        $info = $stmt->fetch();
        
        if ($info) {
            $info['stats'] = json_decode($info['stats'], true);
            $info['certifications'] = json_decode($info['certifications'], true);
        }
        
        return $info;
    }
    
    public function getContact() {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->contactsTable}
            ORDER BY id DESC
            LIMIT 1
        ");
        
        $stmt->execute();
        $contact = $stmt->fetch();
        
        if ($contact) {
            $contact['offices'] = json_decode($contact['offices'], true);
            $contact['key_personnel'] = json_decode($contact['key_personnel'], true);
        }
        
        return $contact;
    }
    
    public function getStats() {
        $info = $this->getInfo();
        return $info ? $info['stats'] : null;
    }
    
    public function getRegistration() {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->registrationTable}
            ORDER BY id DESC
            LIMIT 1
        ");
        
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function updateInfo($data) {
        if (isset($data['stats']) && is_array($data['stats'])) {
            $data['stats'] = json_encode($data['stats']);
        }
        if (isset($data['certifications']) && is_array($data['certifications'])) {
            $data['certifications'] = json_encode($data['certifications']);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO {$this->infoTable}
            (name, tagline, website, vision, mission, stats, certifications)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['name'] ?? null,
            $data['tagline'] ?? null,
            $data['website'] ?? null,
            $data['vision'] ?? null,
            $data['mission'] ?? null,
            $data['stats'] ?? null,
            $data['certifications'] ?? null
        ]);
        
        return $this->getInfo();
    }
    
    public function updateContact($data) {
        if (isset($data['offices']) && is_array($data['offices'])) {
            $data['offices'] = json_encode($data['offices']);
        }
        if (isset($data['key_personnel']) && is_array($data['key_personnel'])) {
            $data['key_personnel'] = json_encode($data['key_personnel']);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO {$this->contactsTable}
            (offices, key_personnel)
            VALUES (?, ?)
        ");
        
        $stmt->execute([
            $data['offices'] ?? null,
            $data['key_personnel'] ?? null
        ]);
        
        return $this->getContact();
    }
    
    public function updateRegistration($data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->registrationTable}
            (incorporation_certificate, vat_registration, pin_certificate, tax_compliance, etr_serial)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['incorporation_certificate'] ?? null,
            $data['vat_registration'] ?? null,
            $data['pin_certificate'] ?? null,
            $data['tax_compliance'] ?? null,
            $data['etr_serial'] ?? null
        ]);
        
        return $this->getRegistration();
    }
}
