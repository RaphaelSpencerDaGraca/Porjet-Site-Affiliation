<?php
class AffiliateLinkModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'affiliate_links');
    }

    /**
     * Trouve les liens d'affiliation d'un utilisateur
     */
    public function findByUserId($userId) {
        $query = "SELECT a.*, b.name as brand_name, b.logo_url, b.website_url, b.bonus
                 FROM affiliate_links a
                 JOIN brands b ON a.brand_id = b.id
                 WHERE a.user_id = :user_id
                 ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve les liens d'affiliation d'une marque
     */
    public function findByBrandId($brandId) {
        $query = "SELECT a.*, u.pseudo as user_pseudo, u.email as user_email
                 FROM affiliate_links a
                 JOIN users u ON a.user_id = u.id
                 WHERE a.brand_id = :brand_id
                 ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':brand_id', $brandId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un lien d'affiliation existe déjà pour un utilisateur et une marque
     */
    public function exists($userId, $brandId) {
        $query = "SELECT COUNT(*) FROM affiliate_links WHERE user_id = :user_id AND brand_id = :brand_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':brand_id', $brandId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Crée un nouveau lien d'affiliation
     */
    public function create($data) {
        $query = "INSERT INTO affiliate_links (user_id, brand_id, code, custom_link, expiry_date) 
                 VALUES (:user_id, :brand_id, :code, :custom_link, :expiry_date)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':brand_id', $data['brand_id'], PDO::PARAM_INT);
        $stmt->bindParam(':code', $data['code']);
        $stmt->bindParam(':custom_link', $data['custom_link']);
        $stmt->bindParam(':expiry_date', $data['expiry_date']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Met à jour un lien d'affiliation
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

        $query = "UPDATE affiliate_links SET $fieldsStr WHERE id = :id";
        $stmt = $this->db->prepare($query);

        return $stmt->execute($params);
    }

    /**
     * Désactive les liens d'affiliation expirés
     */
    public function deactivateExpired() {
        $now = date('Y-m-d H:i:s');
        $query = "UPDATE affiliate_links SET is_active = 0 
                 WHERE expiry_date IS NOT NULL AND expiry_date < :now AND is_active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':now', $now);
        $stmt->execute();
        return $stmt->rowCount();
    }
}