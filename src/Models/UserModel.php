<?php
/**
 * Modèle pour la gestion des utilisateurs
 */
class UserModel extends BaseModel {

    /**
     * Constructeur
     */
    public function __construct($db) {
        parent::__construct($db, 'users');
    }

    /**
     * Trouve un utilisateur par son email
     */
    public function findByEmail($email) {
        try {
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans UserModel::findByEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trouve un utilisateur par son pseudo
     */
    public function findByPseudo($pseudo) {
        try {
            $query = "SELECT * FROM users WHERE pseudo = :pseudo";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':pseudo', $pseudo);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans UserModel::findByPseudo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée un nouvel utilisateur
     * Corrigé pour utiliser "password" qui est le nom correct dans la BD
     */
    public function create($data) {
        try {
            // Vérifier une dernière fois si l'utilisateur existe déjà
            if (isset($data['email']) && $this->findByEmail($data['email'])) {
                return false; // L'email existe déjà
            }
            if (isset($data['pseudo']) && $this->findByPseudo($data['pseudo'])) {
                return false; // Le pseudo existe déjà
            }

            // Ajout des timestamps si non définis
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            if (!isset($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }

            // Définir is_active à 1 par défaut si non défini
            if (!isset($data['is_active'])) {
                $data['is_active'] = 1;
            }

            $fields = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));

            $query = "INSERT INTO users ($fields) VALUES ($placeholders)";

            // Débogage (à commenter en production)
            error_log("Requête SQL: $query");
            error_log("Données: " . print_r($data, true));

            $stmt = $this->db->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur dans UserModel::create: " . $e->getMessage());

            // Si l'erreur est une violation de contrainte d'unicité
            if ($e->getCode() == '23000') {
                // Violation de contrainte d'unicité (pseudo ou email dupliqué)
                return false;
            }

            // Relancer l'exception pour les autres erreurs
            throw $e;
        }
    }

    /**
     * Met à jour les informations d'un utilisateur
     */
    public function update($id, $data) {
        try {
            // Mise à jour du timestamp updated_at
            $data['updated_at'] = date('Y-m-d H:i:s');

            $fields = [];
            $params = [':id' => $id];

            foreach ($data as $key => $value) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }

            $fieldsStr = implode(', ', $fields);

            $query = "UPDATE users SET $fieldsStr WHERE id = :id";
            $stmt = $this->db->prepare($query);

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur dans UserModel::update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trouve un utilisateur par son ID
     */
    public function findById($id) {
        try {
            $query = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans UserModel::findById: " . $e->getMessage());
            return false;
        }
    }
}