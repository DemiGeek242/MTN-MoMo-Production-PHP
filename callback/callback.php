<?php
/*
 * Ce code définit une fonction de rappel (callback) appelée `callback_action`.
 * Elle est destinée à gérer les réponses envoyées par l'API MTN MoMo suite à une demande de paiement.
 * 
 * En général, lorsqu'une demande de paiement est initiée via l'API MTN MoMo, 
 * le serveur de MTN envoie une réponse pour indiquer si la transaction a été 
 * réussie, échouée ou est dans un autre statut.
 * 
 * Le rôle principal de cette fonction est de :
 * 1. Récupérer la réponse JSON envoyée par MTN MoMo.
 * 2. Enregistrer cette réponse dans un fichier log pour garder une trace des transactions reçues.
 * 3. Décoder la réponse JSON et vérifier sa validité.
 * 4. Extraire les informations clés de la transaction, comme le numéro de téléphone du payeur, 
 *    le montant, et le statut de la transaction.
 * 5. Si la transaction est réussie, effectuer une mise à jour en base de données 
 *    pour marquer le paiement comme validé et renvoyer un message de confirmation.
 * 6. Si la transaction échoue ou est dans un autre statut, envoyer un message d'erreur correspondant.
 *
 * Ce code est donc essentiel pour les développeurs qui veulent intégrer les API MTN MoMo 
 * et gérer automatiquement les notifications de statut des transactions en production.
 */

function callback_action(){

    // Récupération du corps de la requête envoyée à l'API (données de retour de MTN MoMo)
    $requesttopayResponse = file_get_contents('php://input');

    // Enregistrement de la réponse brute dans un fichier pour garder une trace des callbacks reçus
    file_put_contents("callback_log.txt", $requesttopayResponse);

    // Décodage de la réponse JSON en un tableau associatif PHP
    $data = json_decode($requesttopayResponse, true);

    // Vérification si le décodage du JSON a échoué
    if ($data === null) {
        // Gestion de l'erreur en cas d'échec de décodage du JSON
        header('Content-Type: application/json'); // Indiquer que la réponse est en JSON
        http_response_code(400); // Retourner le code d'erreur 400 (mauvaise requête)
        echo json_encode(['message' => 'Erreur de décodage JSON: ' . json_last_error_msg()]);
        return; // Terminer l'exécution de la fonction
    }

    // Récupération des données nécessaires de la réponse
    $phone = $data['payer']['partyId']; // ID de l'initiateur du paiement (téléphone)
    $montant = $data['amount']; // Montant de la transaction
    $status = $data['status']; // Statut de la transaction (SUCCESSFUL, FAILED, etc.)

    // Vérification si le statut de la transaction est "SUCCESSFUL" (réussite du paiement)
    if ($status == "SUCCESSFUL") {
        // Mettre à jour le statut de la transaction dans la base de données
        // (Vous pourriez insérer une commande SQL ici pour confirmer la transaction dans votre base)

        // Retourner une réponse en cas de succès du paiement
        echo "Paiement validé";
    } else {
        // Gérer les cas où la transaction échoue ou a un autre statut
        echo "Transaction échouée ou autre statut.";
    }
}