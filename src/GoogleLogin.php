<?php

namespace Analgo\Tools;

/**
 * Service pour gérer l'authentification Google Sign-In côté backend.
 */
class GoogleAuthService {

    const ENDPOINT_GOOGLE = 'https://oauth2.googleapis.com/tokeninfo?id_token={IDTOKEN}';

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
        // URL de vérification du token auprès de Google
        $verifyUrl = str_replace('', urlencode($token), self::ENDPOINT_GOOGLE);

        // Appel à l'API Google
        $response = file_get_contents($verifyUrl);
        if ($response === false) {
            throw new \Exception('Impossible de vérifier le token auprès de Google.');
        }

        // Décodage JSON de la réponse
        $data = json_decode($response, true);
        if (!is_array($data) || empty($data)) {
            throw new \Exception('Réponse invalide lors de la vérification du token.');
        }

        // Vérification que le token est destiné à notre application
        if (!isset($data['aud']) || $data['aud'] !== $clientId) {
            throw new \Exception('Token invalide : audience non autorisée.');
        }

        return $data;
    }
}