<?php
/**
 * Ce fichier a été conçu par Demi-Geek.
 * 
 * Licence : MIT
 * 
 * Informations :
 * - Ce fichier contient des fonctions pour gérer les paiements via Mobile Money.
 * - Les fonctions principales incluent la génération d'un UUID, l'obtention d'un access token, et l'envoi de requêtes de paiement.
 * - Veuillez vous assurer que les clés d'API et les informations globales sont correctement configurées.
 *
 * Conçu par : Demi-Geek
 * Date : 22 Octobre 2024
 * 
 * MIT License
 * 
 * Copyright (c) 2024 Demi-Geek
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

//URL de Production MTN Mobile Money API
$baseURL = "https://proxy.momoapi.mtn.com/";

// Primary KEY - Votre clé privée (Primaire ou secondaire) fournie par MTN
$PRIVATE_KEY = "";

// Votre Clé d'API - Fournie par MTN
$API_KEY = "";

// Votre USER ID - Fourni par MTN
$USER_ID = "de96944c-c1fe-4b7c-86c9-540c15516c4d";

// Devise -  selon la devise de votre Pays (Ici le Congo Brazzaville)
$CURRENCY = "XAF";

// Votre URL de callback - Le domaine doit etre le même que vous aviez communiqué à MTN pour le passe en Production
$CALLBACK_URL = "";

// ==================================================================

/**
 * Génère un UUID (Universally Unique Identifier) version 4 conforme à la RFC 4122.
 *
 * Cette fonction génère un identifiant unique de type UUID v4, qui est basé sur des nombres aléatoires.
 * Un UUID v4 a la structure suivante : xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx où :
 * - "4" indique que c'est un UUID de version 4.
 * - "y" indique le variant, qui est fixé à 8, 9, A ou B.
 *
 * @return string L'UUID généré sous forme de chaîne, au format xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx.
 *
 * Exemple de sortie : "f47ac10b-58cc-4372-a567-0e02b2c3d479"
 */
function generateUUID() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant RFC 4122
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


/**
 * Récupère un jeton d'accès (Access Token) en utilisant une authentification Basic.
 *
 * Cette fonction génère un jeton d'accès pour une API en utilisant une authentification de type "Basic" avec des 
 * informations d'identification encodées en Base64. Elle envoie une requête POST pour obtenir ce jeton à partir d'une URL spécifiée.
 *
 * @global string $PRIVATE_KEY La clé privée utilisée pour l'authentification.
 * @global string $USER_ID L'identifiant utilisateur associé à la clé privée.
 * @global string $API_KEY La clé d'abonnement API utilisée pour autoriser la requête.
 * @global string $baseURL L'URL de base de l'API utilisée pour obtenir le jeton d'accès.
 *
 * @return string|null Le jeton d'accès (access_token) si la requête est réussie, sinon `null`.
 *
 * Exemple de retour : "eyJhbGciOiJIUzI1NiIsInR..."
 *
 * Si la requête échoue ou si le jeton n'est pas présent dans la réponse, un message d'erreur est affiché et la fonction retourne `null`.
 */
function getAccessToken() {
    global $PRIVATE_KEY, $USER_ID, $API_KEY, $baseURL;
    $authString = "$USER_ID:$PRIVATE_KEY";
    $authBase64 = base64_encode($authString);
    
    $url = $baseURL . 'collection/token/';

    $headers = [
        "Ocp-Apim-Subscription-Key: $API_KEY",
        "Authorization: Basic $authBase64",
        "Content-Length: 0"
    ];

    $response = sendPostRequest($url, null, $headers);

    if (isset($response['body']['access_token'])) {
        return $response['body']['access_token'];
    } else {
        echo "Failed to generate access token\n";
        return null;
    }
}

/**
 * Effectue une requête de paiement via Mobile Money en utilisant l'API de collection.
 *
 * Cette fonction permet d'envoyer une requête de paiement à un numéro de téléphone Mobile Money. Elle génère un identifiant de référence unique (UUID),
 * obtient un jeton d'accès, et envoie une requête POST à l'API pour déclencher le paiement. Un message est ensuite envoyé au payeur pour valider l'opération
 * sur son téléphone mobile.
 *
 * @param string $phoneNumber Le numéro de téléphone du payeur au format MSISDN (ex : 243970000000).
 * @param float $amount Le montant à payer.
 * @param callable $callback La fonction de rappel (callback) qui sera appelée pour traiter la réponse du paiement.
 *
 * @global string $baseURL L'URL de base de l'API de paiement.
 * @global string $API_KEY La clé d'abonnement API utilisée pour l'authentification.
 * @global string $CURRENCY La devise utilisée pour le paiement (ex: "XAF").
 * @global string $CALLBACK_URL L'URL de callback à appeler une fois le paiement traité.
 *
 * @return array Un tableau associatif contenant :
 * - 'result' : "OK" si la requête de paiement a été acceptée, "KO" sinon.
 * - 'message' : Un message informant de l'état de la requête (succès ou échec).
 * - 'reference_id' : L'identifiant unique de la transaction (UUID), uniquement si la requête est acceptée.
 * - 'data' : Les données supplémentaires en cas d'erreur (disponible seulement si 'result' est "KO").
 *
 * Exemple de retour en cas de succès :
 * [
 *   'result' => 'OK',
 *   'message' => 'Veuillez valider le paiement sur votre téléphone',
 *   'reference_id' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479'
 * ]
 *
 * Exemple de retour en cas d'échec :
 * [
 *   'result' => 'KO',
 *   'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.',
 *   'data' => [ ... ]  // Données supplémentaires relatives à l'erreur
 * ]
 */
function requestToPay($phoneNumber, $amount) {
    global $baseURL, $API_KEY, $CURRENCY, $CALLBACK_URL;

    $referenceId = generateUUID();
    $accessToken = getAccessToken();

    if (!$accessToken) {
        return ['result' => 'KO', 'message' => 'Impossible de récupérer le token d\'accès'];
    }

    $url = $baseURL . "collection/v1_0/requesttopay";
    $externalId = rand(10000000, 99999999); // Générer un ID externe à 8 chiffres

    $body = json_encode([
        "amount" => $amount,
        "currency" => $CURRENCY,
        "externalId" => (string) $externalId,
        "payer" => [
            "partyIdType" => 'MSISDN',
            "partyId" => $phoneNumber
        ],
        "payerMessage" => 'Paiement DMKPAY',
        "payeeNote" => 'Merci pour votre paiement'
    ]);

    $headers = [
        "Authorization: Bearer $accessToken",
        "X-Reference-Id: $referenceId",
        "X-Callback-Url: $CALLBACK_URL",
        "X-Target-Environment: mtncongo",
        "Content-Type: application/json",
        "Ocp-Apim-Subscription-Key: $API_KEY"
    ];

    $response = sendPostRequest($url, $body, $headers);

    if ($response['http_code'] === 202) {
        return ['result' => 'OK', 'message' => 'Veuillez valider le paiement sur votre téléphone', 'reference_id' => $referenceId];
    } else {
        return ['result' => 'KO', 'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.','data'=>$response];
    }
}

