<?php
class AffiliateCodeModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'affiliate_codes');  // Correction: affiliate_codes au lieu de affiliate_code
    }

    /**
     * Trouve les codes d'affiliation d'un utilisateur
     */
    public function findByUserId($userId) {
        $query = "SELECT a.*, b.name as brand_name, b.logo_url, b.website_url, b.bonus
                 FROM affiliate_codes a  /* Correction */
                 JOIN brands b ON a.brand_id = b.id
                 WHERE a.user_id = :user_id
                 ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve les codes d'affiliation d'une marque
     */
    public function findByBrandId($brandId) {
        $query = "SELECT a.*, u.pseudo as user_pseudo, u.email as user_email
                 FROM affiliate_codes a  /* Correction */
                 JOIN users u ON a.user_id = u.id
                 WHERE a.brand_id = :brand_id
                 ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':brand_id', $brandId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un code d'affiliation existe déjà pour un utilisateur et une marque avec le même code
     * Modification: permet plusieurs codes différents pour la même marque
     */
    public function exists($userId, $brandId, $code) {
        $query = "SELECT COUNT(*) FROM affiliate_codes  /* Correction */
                 WHERE user_id = :user_id 
                 AND brand_id = :brand_id 
                 AND code = :code";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':brand_id', $brandId, PDO::PARAM_INT);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Crée un nouveau code d'affiliation
     */
    public function create($data) {
        $query = "INSERT INTO affiliate_codes (user_id, brand_id, code, expiry_date, is_active)  /* Correction */
                 VALUES (:user_id, :brand_id, :code, :expiry_date, :is_active)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':brand_id', $data['brand_id'], PDO::PARAM_INT);
        $stmt->bindParam(':code', $data['code']);
        $stmt->bindParam(':expiry_date', $data['expiry_date']);
        $stmt->bindParam(':is_active', $data['is_active'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Supprime un code d'affiliation
     */
    public function delete($id) {
        $query = "DELETE FROM affiliate_codes WHERE id = :id";  /* Correction */
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Met à jour un code d'affiliation
     */
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        $fieldsStr = implode(', ', $fields);

        $query = "UPDATE affiliate_codes SET $fieldsStr WHERE id = :id";  /* Correction */
        $stmt = $this->db->prepare($query);

        return $stmt->execute($params);
    }

    /**
     * Désactive les codes d'affiliation expirés
     */
    public function deactivateExpired() {
        $now = date('Y-m-d H:i:s');
        $query = "UPDATE affiliate_codes SET is_active = 0  /* Correction */
                 WHERE expiry_date IS NOT NULL AND expiry_date < :now AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':now', $now);
        $stmt->execute();
        return $stmt->rowCount();
    }
}