<h1>RequestToPay - Intégration Mobile Money</h1>

Ce projet permet une intégration avec une API Mobile Money pour gérer les paiements via PHP. Il est composé de deux fichiers principaux :

  requesttopay.php : Contient les fonctions pour traiter les paiements et interagir avec l'API Mobile Money.
  index.php : Gère les requêtes HTTP et utilise les fonctions de requesttopay.php pour initier les paiements.

<h2>Prérequis</h2>

  PHP 7.4 ou supérieur.
  Extensions PHP activées : cURL et JSON.
  Identifiants API Mobile Money (User ID, Private Key, API Key).
  Serveur web (Apache, Nginx, etc.) avec HTTPS activé pour sécuriser les transactions.

<h2>Installation</h2>

  Clonez le dépôt :
      
      git clone https://github.com/PrinceWami/MTN-MoMo-Production-PHP.git
      
  <h3>requesttopay.php</h3>
  $PRIVATE_KEY = 'votre_clé_privée';<br>
  $USER_ID = 'votre_user_id';<br>
  $API_KEY = 'votre_api_key';<br>
  $baseURL = 'https://proxy.momoapi.mtn.com/';<br>
  $CURRENCY = 'XAF';<br>
  $CALLBACK_URL = 'https://votre_domaine.com/callback';<br>

<h2>Utilisation</h2>
<h3>1. Envoi d'une requête de paiement</h3>

Pour initier un paiement, envoyez une requête POST vers index.php avec les paramètres suivants :

 - phone : Le numéro de téléphone du payeur (format international MSISDN, par exemple : 243820000000).
 - montant : Le montant à payer.

<h3>Exemple de requête POST avec cURL :</h3>

    curl -X POST https://votre_domaine.com/requesttopay/index.php?payment=true \
     -H "Content-Type: application/json" \
     -d '{"phone": "242060000000", "montant": "5000"}'

<h3>Exemple de formulaire HTML :</h3>

    <form method="POST" action="https://votre_domaine.com/requesttopay/">
        <input type="text" name="phone" placeholder="Numéro de téléphone" required>
        <input type="text" name="montant" placeholder="Montant" required>
        <button type="submit">Payer</button>
    </form>

<h3>2. Réponse du serveur</h3>

Le serveur renverra une réponse JSON avec le résultat de la transaction. Voici les réponses possibles :

    Succès : Le paiement a été initié et le client doit valider sur son téléphone.

<h3>json</h3>

    {
        "result": "OK",
        "message": "Veuillez valider le paiement sur votre téléphone",
        "reference_id": "abc12345-6789-def0-1234-56789abcdef0"
    }
    
<h3>json Échec : Une erreur s'est produite lors de la requête.</h3>

    {
        "result": "KO",
        "message": "Une erreur est survenue. Veuillez réessayer plus tard.",
        "data" : []
    }

<h3>Gestion des erreurs</h3>

  Si la requête n'est pas POST ou si le paramètre payment n'est pas fourni, le serveur renverra un tableau vide :

  <h3>json</h3>

    []

  Si l'API échoue à générer un token d'accès ou à traiter le paiement, un message d'erreur approprié sera retourné.

<h3>Sécurité</h3>

  - Utilisez HTTPS pour protéger les données sensibles.
  - Ne partagez jamais votre clé privée ou vos identifiants API publiquement.
