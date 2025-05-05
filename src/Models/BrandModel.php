<?php
class BrandModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'brands');
    }

    /**
     * Trouve les marques actives
     */
    public function findActive() {
        $query = "SELECT * FROM brands WHERE is_active = 1 ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
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
