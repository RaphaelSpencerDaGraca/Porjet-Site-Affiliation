<?php
/**
 * Modèle pour la gestion des boosts
 */
class BoostModel extends BaseModel {

    /**
     * Constructeur
     */
    public function __construct($db) {
        parent::__construct($db, 'boosts');
    }

    /**
     * Crée un nouveau boost
     */
    public function create($data) {
        try {
            $query = "INSERT INTO boosts (user_id, item_type, item_id, start_date, end_date, status, amount, payment_id) 
                    VALUES (:user_id, :item_type, :item_id, :start_date, :end_date, :status, :amount, :payment_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':item_type', $data['item_type'], PDO::PARAM_STR);
            $stmt->bindParam(':item_id', $data['item_id'], PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':end_date', $data['end_date']);
            $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
            $stmt->bindParam(':amount', $data['amount']);
            $stmt->bindParam(':payment_id', $data['payment_id'], PDO::PARAM_STR);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur dans BoostModel::create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trouve les boosts actifs d'un utilisateur
     */
    public function findActiveByUserId($userId) {
        try {
            $query = "SELECT * FROM boosts 
                     WHERE user_id = :user_id 
                     AND status = 'active' 
                     AND end_date > NOW()";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans BoostModel::findActiveByUserId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Trouve tous les boosts d'un utilisateur (actifs et expirés)
     */
    public function findAllByUserId($userId) {
        try {
            $query = "SELECT b.*, 
                        CASE 
                            WHEN b.item_type = 'link' THEN (SELECT brand_id FROM affiliate_links WHERE id = b.item_id)
                            WHEN b.item_type = 'code' THEN (SELECT brand_id FROM affiliate_codes WHERE id = b.item_id)
                        END as brand_id,
                        CASE 
                            WHEN b.item_type = 'link' THEN (SELECT brand_name FROM affiliate_links al JOIN brands br ON al.brand_id = br.id WHERE al.id = b.item_id)
                            WHEN b.item_type = 'code' THEN (SELECT brand_name FROM affiliate_codes ac JOIN brands br ON ac.brand_id = br.id WHERE ac.id = b.item_id)
                        END as brand_name,
                        CASE 
                            WHEN b.item_type = 'link' THEN (SELECT custom_link FROM affiliate_links WHERE id = b.item_id)
                            WHEN b.item_type = 'code' THEN (SELECT code FROM affiliate_codes WHERE id = b.item_id)
                        END as item_value
                     FROM boosts b
                     WHERE b.user_id = :user_id 
                     ORDER BY b.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans BoostModel::findAllByUserId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre de boosts actifs d'un utilisateur
     */
    public function countActiveByUserId($userId) {
        try {
            $query = "SELECT COUNT(*) FROM boosts 
                     WHERE user_id = :user_id 
                     AND status = 'active' 
                     AND end_date > NOW()";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erreur dans BoostModel::countActiveByUserId: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Vérifie si un élément est déjà boosté
     */
    public function isItemBoosted($itemType, $itemId) {
        try {
            $query = "SELECT COUNT(*) FROM boosts 
                     WHERE item_type = :item_type 
                     AND item_id = :item_id 
                     AND status = 'active' 
                     AND end_date > NOW()";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':item_type', $itemType, PDO::PARAM_STR);
            $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur dans BoostModel::isItemBoosted: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour le statut des boosts (expire les boosts en fin de validité)
     */
    public function updateStatus() {
        try {
            // Mettre à jour les boosts expirés
            $query = "UPDATE boosts 
                     SET status = 'expired' 
                     WHERE status = 'active' 
                     AND end_date < NOW()";
            $stmt = $this->db->prepare($query);
            $expiredCount = $stmt->execute() ? $stmt->rowCount() : 0;

            // Mettre à jour les tables affiliate_links et affiliate_codes
            $query = "UPDATE affiliate_links 
                     SET is_boosted = 0, boost_end_date = NULL 
                     WHERE boost_end_date < NOW()";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $query = "UPDATE affiliate_codes 
                     SET is_boosted = 0, boost_end_date = NULL 
                     WHERE boost_end_date < NOW()";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $expiredCount;
        } catch (PDOException $e) {
            error_log("Erreur dans BoostModel::updateStatus: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Crée une transaction
     */
    public function createTransaction($data) {
        try {
            $query = "INSERT INTO transactions (user_id, amount, type, status, payment_method, reference_id, item_type, item_id, boost_id) 
                    VALUES (:user_id, :amount, :type, :status, :payment_method, :reference_id, :item_type, :item_id, :boost_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':amount', $data['amount']);
            $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
            $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
            $stmt->bindParam(':payment_method', $data['payment_method'], PDO::PARAM_STR);
            $stmt->bindParam(':reference_id', $data['reference_id'], PDO::PARAM_STR);
            $stmt->bindParam(':item_type', $data['item_type'], PDO::PARAM_STR);
            $stmt->bindParam(':item_id', $data['item_id'], PDO::PARAM_INT);
            $stmt->bindParam(':boost_id', $data['boost_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur dans BoostModel::createTransaction: " . $e->getMessage());
            return false;
        }
    }

        // src/Models/BoostModel.php
    public function deleteByItem(string $itemType, int $itemId): bool
    {
        try {
            $sql = "DELETE FROM boosts WHERE item_type = :item_type AND item_id = :item_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':item_type', $itemType, PDO::PARAM_STR);
            $stmt->bindParam(':item_id',   $itemId,   PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur dans BoostModel::deleteByItem: " . $e->getMessage());
            return false;
        }
    }

}