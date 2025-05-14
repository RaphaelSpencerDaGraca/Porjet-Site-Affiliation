<?php
class AffiliateCodeModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'affiliate_codes'); // Nom correct de la table: affiliate_codes
    }

    /**
     * Trouve les codes d'affiliation d'un utilisateur
     */
    public function findByUserId($userId) {
        try {
            $query = "SELECT a.*, b.name as brand_name, b.logo_url, b.website_url, b.bonus
                     FROM affiliate_codes a  /* Nom correct de la table */
                     JOIN brands b ON a.brand_id = b.id
                     WHERE a.user_id = :user_id
                     ORDER BY a.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result ?: []; // Retourne un tableau vide si pas de résultats
        } catch (PDOException $e) {
            error_log("Erreur dans AffiliateCodeModel::findByUserId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Trouve un code d'affiliation par son ID
     */
    public function findById($id) {
        try {
            $query = "SELECT * FROM affiliate_codes WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans AffiliateCodeModel::findById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trouve les codes d'affiliation d'une marque
     */
    public function findByBrandId($brandId) {
        try {
            $query = "SELECT a.*, u.pseudo as user_pseudo, u.email as user_email
                    FROM affiliate_codes a  /* Nom correct de la table */
                    JOIN users u ON a.user_id = u.id
                    WHERE a.brand_id = :brand_id
                    ORDER BY a.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':brand_id', $brandId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans AffiliateCodeModel::findByBrandId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si un code d'affiliation existe déjà pour un utilisateur et une marque avec le même code
     * Modification: permet plusieurs codes différents pour la même marque
     */
    public function exists($userId, $brandId, $code) {
        try {
            $query = "SELECT COUNT(*) FROM affiliate_codes  /* Nom correct de la table */
                    WHERE user_id = :user_id 
                    AND brand_id = :brand_id 
                    AND code = :code";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':brand_id', $brandId, PDO::PARAM_INT);
            $stmt->bindParam(':code', $code);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur dans AffiliateCodeModel::exists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée un nouveau code d'affiliation
     */
    public function create($data) {
        try {
            $query = "INSERT INTO affiliate_codes (user_id, brand_id, code, expiry_date, is_active)  /* Nom correct de la table */
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
        } catch (PDOException $e) {
            error_log("Erreur dans AffiliateCodeModel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un code d'affiliation
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM affiliate_codes WHERE id = :id";  /* Nom correct de la table */
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur dans AffiliateCodeModel::delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour un code d'affiliation
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];

            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            $fieldsStr = implode(', ', $fields);

            $query = "UPDATE affiliate_codes SET $fieldsStr WHERE id = :id";  /* Nom correct de la table */
            $stmt = $this->db->prepare($query);

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur dans AffiliateCodeModel::update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Désactive les codes d'affiliation expirés
     */
    public function deactivateExpired() {
        try {
            $now = date('Y-m-d H:i:s');
            $query = "UPDATE affiliate_codes SET is_active = 0  /* Nom correct de la table */
                    WHERE expiry_date IS NOT NULL AND expiry_date < :now AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':now', $now);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Erreur dans AffiliateCodeModel::deactivateExpired: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère la connexion à la base de données
     */
    public function getDb() {
        return $this->db;
    }
}