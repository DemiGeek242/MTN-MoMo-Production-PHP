<?php
/**
 * Fichier index.php
 * 
 * Ce fichier est conçu pour traiter les requêtes de paiement via une API Mobile Money.
 * Il reçoit les données de paiement (numéro de téléphone et montant) par une requête POST,
 * et utilise la fonction `requestToPay()` pour initier une transaction.
 * 
 * Prérequis :
 * - Le fichier `requesttopay.php` doit être présent dans le même répertoire et doit
 *   contenir la fonction `requestToPay()` pour traiter les paiements.
 * 
 * Flux de travail :
 * - Ce fichier vérifie si la méthode de la requête est POST et si le paramètre `payment` est défini dans l'URL.
 * - Il récupère les données envoyées par la requête POST, en particulier le numéro de téléphone et le montant.
 * - Il appelle la fonction `requestToPay()` avec ces données pour initier le paiement.
 * - Le résultat de l'opération est renvoyé sous forme de JSON.
 * - Si la méthode de requête n'est pas POST ou que le paramètre `payment` est manquant, il renvoie un tableau vide en JSON.
 * 
 * @package Demi-Geek
 * @version 1.0
 */

require './requesttopay.php';  // Inclusion du fichier contenant la fonction requestToPay()

// Vérification que la requête est une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération du contenu de la requête POST
    $inputData = file_get_contents('php://input');
    
    // Décodage des données JSON reçues dans la requête en tableau PHP
    $data = json_decode($inputData, true);

    // Récupération des informations du formulaire : le numéro de téléphone et le montant
    $phoneNumber = $_POST['phone'];
    $amount = $_POST['montant'];

    // Appel de la fonction requestToPay() pour initier le paiement
    $result = requestToPay($phoneNumber, $amount);

    // Envoi du résultat du paiement sous forme de JSON
    echo json_encode($result);
} else {
    // Si la requête n'est pas POST ou le paramètre 'payment' est absent, renvoie un tableau vide en JSON
    echo json_encode([]);
}
?>
