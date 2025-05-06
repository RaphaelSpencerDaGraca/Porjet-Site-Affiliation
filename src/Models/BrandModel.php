<?php
require_once __DIR__ . '/BaseModel.php';

class BrandModel extends BaseModel 
{
    public function __construct($db) 
    {
        parent::__construct($db, 'brands');
    }

    /**
     * Récupère les marques actives qui ont au moins un lien ou un code,
     * en y joignant le nombre de liens (link_count) et de codes (code_count).
     */
    /**
 * Récupère les marques actives qui ont au moins
 * un lien ou un code, avec compteur distinct de liens et codes.
 */
public function findActive(): array
{
    $sql = <<<'SQL'
SELECT
    b.id,
    b.name,
    b.description_bonus   AS description,
    b.logo_url,
    b.website_url,
    b.bonus,
    b.is_active,
    b.created_at,

    -- compte distinct des liens
    COUNT(DISTINCT al.id) AS link_count,
    -- compte distinct des codes
    COUNT(DISTINCT ac.id) AS code_count

FROM brands b
    LEFT JOIN affiliate_links al  ON al.brand_id = b.id AND al.is_active = 1
    LEFT JOIN affiliate_codes ac  ON ac.brand_id = b.id AND ac.is_active = 1

WHERE b.is_active = 1

GROUP BY b.id
HAVING link_count > 0 OR code_count > 0
ORDER BY b.name ASC
SQL;

    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Récupère une seule marque active par son nom,
     * en renommant description_bonus → description.
     */
    public function findByName(string $name)
    {
        $sql = <<<'SQL'
SELECT
    b.id,
    b.name,
    b.description_bonus    AS description,
    b.logo_url,
    b.website_url,
    b.bonus,
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
     * Remarque : description → description_bonus en base.
     */
    public function create(array $data)
    {
        $sql = <<<'SQL'
INSERT INTO brands
    (name, description_bonus, logo_url, website_url, bonus)
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
     * Si $data['description'] est fourni, on mappe sur description_bonus.
     */
    public function update(int $id, array $data)
    {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            // mappe description → description_bonus
            $col = ($key === 'description') ? 'description_bonus' : $key;
            $fields[]      = "`{$col}` = :{$key}";
            $params[":{$key}"] = $value;
        }

        $sql = "UPDATE brands SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Récupère tous les liens (custom_link) valides pour une marque donnée.
     */
    public function findLinksByBrandId(int $brandId): array
    {
        $sql = <<<'SQL'
SELECT
    u.pseudo,
    al.custom_link
FROM affiliate_links al
JOIN users             u ON u.id = al.user_id
WHERE al.brand_id = :brandId
  AND al.is_active = 1
  AND al.custom_link IS NOT NULL
  AND al.custom_link <> ''
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
    ac.code
FROM affiliate_codes ac
JOIN users            u ON u.id = ac.user_id
WHERE ac.brand_id = :brandId
  AND ac.is_active = 1
  AND ac.code IS NOT NULL
  AND ac.code <> ''
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':brandId', $brandId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
