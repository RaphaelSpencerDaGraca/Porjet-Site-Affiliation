<?php
// src/Models/BillModel.php

require_once __DIR__ . '/BaseModel.php';

class BillModel extends BaseModel
{
    /**
     * @param PDO $db Votre instance PDO
     */
    public function __construct(PDO $db)
    {
        // La table sur laquelle ce modèle opère : 'transactions'
        parent::__construct($db, 'transactions');
    }

    /**
     * Récupère la dernière transaction « completed » d'un utilisateur
     *
     * @param int $userId
     * @return array|false
     */
    public function getLastCompletedTransaction(int $userId): array|false
    {
        $sql = <<<SQL
SELECT
    t.*,
    u.pseudo AS user_name,
    u.email  AS user_email
FROM transactions t
INNER JOIN users u ON u.id = t.user_id
WHERE t.user_id = :uid
  AND t.status  = 'completed'
ORDER BY t.created_at DESC
LIMIT 1
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    /**
     * Récupère les détails d'un boost
     *
     * @param int $boostId
     * @return array|false
     */
    public function getBoostDetails(int $boostId): array|false
    {
        $sql = <<<SQL
SELECT
    b.*,
    br.name AS brand_name
FROM boosts b
LEFT JOIN brands br ON br.id = b.item_id
WHERE b.id = :bid
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':bid', $boostId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }
}
