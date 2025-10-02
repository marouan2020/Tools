<?php

namespace Analgo\Tools;

/**
 * Service pour gérer l'authentification Google Sign-In côté backend.
 */
class GoogleAuthService {

    /**
     * Vérifie un token Google ID (JWT) et récupère les informations utilisateur.
     *
     * Cette méthode appelle l'API Google pour valider le token côté serveur.
     * Elle s'assure que le token est destiné à l'application correcte (clientId).
     *
     * @param string $token     Le token ID reçu du frontend (response.credential)
     * @param string $clientId  Le Google Client ID autorisé pour votre application
     *
     * @return array  Tableau contenant les informations de l'utilisateur (email, name, sub, etc.)
     *
     * @throws \Exception Si le token est invalide ou ne peut pas être vérifié
     */
    public function getUserInfoFromGoogle(string $token, string $clientId): array {
        $verifyUrl = 'https://oauth2.googleapis.com/tokeninfo?id_token='.urlencode($token);
        $ch = curl_init($verifyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // set false if testing without proper cert
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($response === false || $httpCode !== 200) {
            throw new \Exception("Cannot verify token with Google API.");
        }
        $data = json_decode($response, true);
        if (!is_array($data) || empty($data)) {
            throw new \Exception('Réponse invalide lors de la vérification du token.');
        }
        if (!isset($data['aud']) || $data['aud'] !== $clientId) {
            throw new \Exception('Token invalide : audience non autorisée.');
        }

        return $data;
    }
}