<?php
class BrandModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'brands');
    }

    /**
     * Trouve les marques actives
     */
    public function findActive(): array
{
    $sql = "
      SELECT
        b.*,
        -- Compte les lignes où custom_link n'est pas NULL ni vide
        SUM(CASE WHEN al.custom_link IS NOT NULL AND al.custom_link <> '' THEN 1 ELSE 0 END) AS link_count,
        -- Compte les lignes où code n'est pas NULL ni vide
        SUM(CASE WHEN al.code IS NOT NULL AND al.code <> '' THEN 1 ELSE 0 END)         AS code_count
      FROM brands b
      LEFT JOIN affiliate_links al 
        ON al.brand_id = b.id
      WHERE b.is_active = 1
      GROUP BY b.id
      HAVING link_count > 0 OR code_count > 0
      ORDER BY b.name ASC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    /**
     * Trouve une marque par son nom
     */
    public function findByName($name) {
        $query = "SELECT * FROM brands WHERE name = :name";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée une nouvelle marque
     */
    public function create($data) {
        $query = "INSERT INTO brands (name, description, logo_url, website_url, bonus) 
                 VALUES (:name, :description, :logo_url, :website_url, :bonus)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':logo_url', $data['logo_url']);
        $stmt->bindParam(':website_url', $data['website_url']);
        $stmt->bindParam(':bonus', $data['bonus'], PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Met à jour une marque
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

        $query = "UPDATE brands SET $fieldsStr WHERE id = :id";
        $stmt = $this->db->prepare($query);

        return $stmt->execute($params);
    }

    public function findLinksByBrandId(int $brandId): array
    {
        $sql = "
          SELECT u.pseudo, al.custom_link
          FROM affiliate_links al
          JOIN users u ON al.user_id = u.id
          WHERE al.brand_id = :brandId
            AND al.custom_link IS NOT NULL
            AND al.custom_link <> ''
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':brandId', $brandId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les codes de parrainage d'une marque
     */
    public function findCodesByBrandId(int $brandId): array
    {
        $sql = "
          SELECT u.pseudo, al.code 
          FROM affiliate_links al 
          JOIN users u ON al.user_id = u.id 
          WHERE al.brand_id = :brandId
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':brandId', $brandId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
