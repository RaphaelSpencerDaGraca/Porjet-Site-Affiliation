<?php
require_once __DIR__ . '/BaseModel.php';

class BrandModel extends BaseModel 
{
    public function __construct($db) 
    {
        parent::__construct($db, 'brands');
    }

    /**
     * Récupère les marques actives qui ont au moins
     * un lien ou un code, avec compteur distinct de liens et codes.
     */
    public function findActive(): array {
        $sql = <<<'SQL'
SELECT
  b.*,
  COUNT(DISTINCT al.id) AS link_count,
  COUNT(DISTINCT ac.id) AS code_count
FROM brands b
LEFT JOIN affiliate_links al
  ON al.brand_id = b.id
  AND al.is_active = 1
LEFT JOIN affiliate_codes ac
  ON ac.brand_id = b.id
  AND ac.is_active = 1
WHERE b.is_active = 1
GROUP BY b.id
ORDER BY b.name ASC
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une seule marque active par son nom,
     * en gardant description_bonus.
     */
    public function findByName(string $name)
    {
        $sql = <<<'SQL'
SELECT
    b.id,
    b.name,
    b.logo_url,
    b.website_url,
    b.bonus,
    b.description_bonus,
    b.is_active,
    b.created_at
FROM brands b
WHERE b.name      = :name
  AND b.is_active = 1
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    /**
     * Crée une nouvelle marque.
     */
    public function create(array $data)
    {
        $sql = <<<'SQL'
INSERT INTO brands
    (name, logo_url, website_url, bonus)
VALUES
    (:name, :description,     :logo_url, :website_url, :bonus)
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name',        $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':logo_url',    $data['logo_url']);
        $stmt->bindParam(':website_url', $data['website_url']);
        $stmt->bindParam(':bonus',       $data['bonus'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Met à jour une marque existante.
     */
    public function update(int $id, array $data)
    {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $col = ($key === 'description') ? 'description_bonus' : $key;
            $fields[]        = "`{$col}` = :{$key}";
            $params[":{$key}"] = $value;
        }

        $sql = "UPDATE brands SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // Dans BrandModel.php

/**
 * Récupère tous les liens (custom_link) valides pour une marque donnée.
 */
public function findLinksByBrandId(int $brandId): array
{
    $sql = <<<'SQL'
SELECT
    u.pseudo,
    al.custom_link,
    al.is_boosted,
    al.boost_end_date
FROM affiliate_links al
JOIN users u ON u.id = al.user_id
WHERE al.brand_id   = :brandId
  AND al.is_active  = 1
  AND al.custom_link IS NOT NULL
  AND al.custom_link <> ''
ORDER BY 
    -- d’abord les boostés (1 devant 0), puis par date de fin de boost (les plus récents en premier)
    al.is_boosted DESC,
    al.boost_end_date DESC
SQL;
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':brandId', $brandId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère tous les codes valides pour une marque donnée.
 */
public function findCodesByBrandId(int $brandId): array
{
    $sql = <<<'SQL'
SELECT
    u.pseudo,
    ac.code,
    ac.is_boosted,
    ac.boost_end_date
FROM affiliate_codes ac
JOIN users u ON u.id = ac.user_id
WHERE ac.brand_id  = :brandId
  AND ac.is_active = 1
  AND ac.code IS NOT NULL
  AND ac.code <> ''
ORDER BY
    ac.is_boosted DESC,
    ac.boost_end_date DESC
SQL;
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':brandId', $brandId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
