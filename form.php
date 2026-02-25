<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // récupérer et nettoyer les données du formulaire
    $nom = trim($_POST['user_name']);
    $email = trim($_POST['user_mail']);
    $message = trim($_POST['user_message']);
    
    $to = 'nathan.cobat@coda-student.school'; 

    $errors = [];

    if (empty($nom)) {
        $errors[] = "Le champ Nom est obligatoire.";
    }
    if (empty($email)) {
        $errors[] = "Le champ E-mail est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse e-mail n'est pas valide.";
    }
    if (empty($message)) {
        $errors[] = "Le champ Message est obligatoire.";
    }

    if (empty($errors)) {
        $headers = 'From: ' . $nom . ' <' . $email . '>' . "\r\n";
        $headers .= 'Reply-To: ' . $email . "\r\n";
        $headers .= 'Content-Type: text/plain; charset="utf-8"' . "\r\n";
        $headers .= 'Content-Transfer-Encoding: 8bit';

        $subject = 'Nouvelle idée pour Puls\'Orléans de ' . $nom;

        $email_body = "Vous avez reçu un nouveau message depuis le formulaire de contact de Puls'Orléans.\n\n";
        $email_body .= "Nom: " . htmlspecialchars($nom) . "\n";
        $email_body .= "E-mail: " . htmlspecialchars($email) . "\n\n";
        $email_body .= "Message:\n" . htmlspecialchars($message) . "\n";

        if (mail($to, $subject, $email_body, $headers)) {
            $_SESSION['form_message'] = '<p class="success-message">Merci ! Votre message a été envoyé avec succès.</p>';
        } else {
            $_SESSION['form_message'] = '<p class="error-message">Une erreur est survenue lors de l\'envoi. Veuillez réessayer plus tard.</p>';
        }
    } else {
        $error_string = implode('<br>', $errors);
        $_SESSION['form_message'] = '<p class="error-message">' . $error_string . '</p>';
    }

    header("Location: index.php#contact-form");
    exit(); 

} else {
    header("Location: index.php");
    exit();
}
?>