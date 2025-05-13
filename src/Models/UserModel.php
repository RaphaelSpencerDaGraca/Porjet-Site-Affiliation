<?php

/**
 * Modèle pour la table Users
 */
class UserModel extends BaseModel {
    public function __construct($db) {
        parent::__construct($db, 'users');
    }

    /**
     * Vérifie les identifiants de connexion
     */
    public function authenticate($email, $password) {
        $query = "SELECT id, pseudo, email, password_hash FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Supprimer la vérification d'activation
        // Ne pas retourner le hash du mot de passe
        unset($user['password_hash']);
        return $user;
    }

    /**
     * Trouve un utilisateur par son email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Trouve un utilisateur par son pseudo
     */
    public function findByPseudo($pseudo) {
        $query = "SELECT * FROM users WHERE pseudo = :pseudo";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':pseudo', $pseudo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function create($data) {
        $query = "INSERT INTO users (pseudo, email, password_hash, activation_token) 
                 VALUES (:pseudo, :email, :password_hash, :activation_token)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':pseudo', $data['pseudo']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password_hash', $data['password_hash']);
        $stmt->bindParam(':activation_token', $data['activation_token']);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Met à jour un utilisateur
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

        $query = "UPDATE users SET $fieldsStr WHERE id = :id";
        $stmt = $this->db->prepare($query);

        return $stmt->execute($params);
    }
}

/**
 * Modèle pour la table Brands
 */

