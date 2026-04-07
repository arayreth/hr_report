<?php
// mentions_legales.php - Page des Mentions Légales - Legatech
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mentions légales de la plateforme Legatech.">
    <title>Mentions Légales – Legatech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <link rel="icon" type="image/x-icon" href="/brand/logo_hr.ico">
</head>
<body>

<div class="page-wrapper">

    <div class="header-block ml-header">
        <span class="badge">Informations légales</span>
        <h1>Mentions Légales</h1>
        <p class="ml-intro subtitle">
            La plateforme Legatech est un portail de signalement interne conçu pour permettre aux salariés
            de déposer des alertes éthiques ou professionnelles dans un cadre légal sécurisé. En accédant
            à cette plateforme, l'utilisateur reconnaît avoir pris connaissance de l'ensemble de ces informations.
        </p>
    </div>

    <!-- SECTION 1 : Cadre légal -->
    <div class="ml-section">
        <div class="ml-section-header">
            <div class="ml-section-icon">⚖️</div>
            <h2 class="ml-section-title"><span class="step">01</span> Cadre Légal</h2>
        </div>

        <div class="ml-subsection">
            <p class="ml-subsection-title">Loi Sapin 2</p>
            <p>
                La loi Sapin 2, adoptée en décembre 2016, constitue le fondement juridique principal de cette
                plateforme. Elle représente la codification dans le droit français de l'obligation pour certaines
                entreprises d'adopter et de maintenir des programmes de conformité visant à dissuader la corruption,
                le trafic d'influence et tout autre manquement à la probité. Cette loi s'applique obligatoirement
                à toutes les entreprises comptant <strong>50 salariés ou plus</strong>.
            </p>
            <p>
                La plateforme Legatech met en place un dispositif centralisé permettant de recueillir, organiser
                et traiter les différentes alertes émises par les collaborateurs. Chaque signalement est traité
                dans un délai moyen d'environ <strong>3 mois</strong>, conformément aux exigences légales.
                Tout signalement manifestement abusif est déclaré irrecevable et peut engager la responsabilité
                de son auteur.
            </p>
            <p>
                La loi Sapin 2 garantit par ailleurs la confidentialité totale de l'identité du lanceur d'alerte.
                Seuls les responsables RH et les juristes habilités ont accès à ces informations. La plateforme
                offre également la possibilité de déposer un signalement de façon <strong>entièrement anonyme</strong>.
            </p>
        </div>

        <div class="ml-subsection">
            <p class="ml-subsection-title">RGPD – Règlement Général sur la Protection des Données</p>
            <p>
                Le RGPD, entré en application le 25 mai 2018, est un texte réglementaire européen qui harmonise
                les règles de traitement des données à caractère personnel dans toute l'Union européenne.
            </p>
            <p>
                Les données collectées se limitent strictement à ce qui est nécessaire au traitement des
                signalements : identité de l'auteur (optionnelle en cas d'anonymat), description des faits,
                pièces jointes éventuelles (PDF, JPG, PNG, MP3) et données de connexion.
            </p>
            <p>
                Conformément au RGPD, ces données sont conservées pour une durée maximale de <strong>2 ans</strong>
                à compter de la clôture du dossier, puis définitivement supprimées. Les utilisateurs bénéficient
                à tout moment des droits suivants :
            </p>
            <div class="ml-rights-grid">
                <div class="ml-right-pill"><span>→</span> Accès</div>
                <div class="ml-right-pill"><span>→</span> Rectification</div>
                <div class="ml-right-pill"><span>→</span> Effacement</div>
                <div class="ml-right-pill"><span>→</span> Opposition</div>
                <div class="ml-right-pill"><span>→</span> Limitation</div>
                <div class="ml-right-pill"><span>→</span> Réclamation CNIL</div>
            </div>
        </div>

        <div class="ml-subsection">
            <p class="ml-subsection-title">Code du travail</p>
            <p>
                Le Code du travail garantit une protection efficace du lanceur d'alerte contre toute forme de
                représailles. Aucun salarié ne peut être sanctionné, licencié ou faire l'objet de mesures
                discriminatoires pour avoir signalé de bonne foi un comportement qu'il juge contraire à
                l'éthique ou à la loi.
            </p>
            <p>
                La confidentialité des échanges est également imposée : toute personne prenant connaissance
                d'un signalement dans l'exercice de ses fonctions est tenue au <strong>secret professionnel</strong>.
                Il est formellement interdit de divulguer l'identité de l'auteur d'un signalement, sous peine
                de sanctions disciplinaires et/ou pénales.
            </p>
        </div>
    </div>

    <!-- SECTION 2 : Protection des données -->
    <div class="ml-section">
        <div class="ml-section-header">
            <div class="ml-section-icon">🔒</div>
            <h2 class="ml-section-title"><span class="step">02</span> Protection des Données et Anonymat</h2>
        </div>

        <div class="ml-subsection">
            <p class="ml-subsection-title">Mesures techniques de protection</p>
            <p>
                L'ensemble des mots de passe est haché à l'aide d'algorithmes cryptographiques robustes et
                n'est en aucun cas stocké en clair. Toutes les communications sont chiffrées via le protocole
                <strong>HTTPS / TLS</strong>, garantissant qu'aucune donnée ne peut être interceptée en transit.
            </p>
            <p>
                L'accès aux données est contrôlé par un système de gestion des rôles : chaque utilisateur
                ne consulte que les informations correspondant à son niveau d'habilitation. Toute session
                expire automatiquement après <strong>8 heures</strong> d'inactivité.
            </p>
        </div>

        <div class="ml-subsection">
            <p class="ml-subsection-title">Garanties relatives à l'anonymat</p>
            <p>
                Lors du dépôt d'un signalement, le salarié choisit librement de s'identifier ou de rester
                anonyme. Ce choix est respecté à toutes les étapes du traitement du dossier.
            </p>
            <p>
                Lorsqu'un signalement est déposé de façon anonyme, l'identité du déclarant est masquée
                techniquement par le système, y compris dans la messagerie interne. Seuls les RH et juristes
                habilités ont accès au contenu complet d'un dossier — aucun salarié ne peut consulter les
                signalements déposés par ses collègues.
            </p>
        </div>
    </div>

    <!-- SECTION 3 : Droits & Responsabilités -->
    <div class="ml-section">
        <div class="ml-section-header">
            <div class="ml-section-icon">📜</div>
            <h2 class="ml-section-title"><span class="step">03</span> Droits et Responsabilités</h2>
        </div>

        <div class="ml-subsection">
            <p class="ml-subsection-title">Droits des utilisateurs</p>
            <p>
                Conformément au RGPD, chaque utilisateur dispose de droits qu'il peut exercer à tout moment
                en contactant l'équipe responsable du traitement des données : droit d'accès, de rectification,
                à l'effacement, d'opposition, à la limitation du traitement, et de réclamation auprès de la CNIL.
            </p>
        </div>

        <div class="ml-subsection">
            <p class="ml-subsection-title">Engagements de l'éditeur</p>
            <p>
                L'équipe Legatech s'engage à traiter chaque signalement avec impartialité et confidentialité.
                L'auteur est informé de l'avancement de son dossier grâce à un <strong>code de suivi unique</strong>
                attribué lors du dépôt. Les données sont supprimées dans le strict respect des délais légaux,
                soit au maximum <strong>2 ans</strong> après la clôture du dossier.
            </p>
        </div>

        <div class="ml-subsection">
            <p class="ml-subsection-title">Engagements de l'utilisateur</p>
            <p>
                Tout signalement abusif, mensonger ou effectué dans le but de nuire est strictement prohibé
                et peut engager la responsabilité civile et/ou pénale de son auteur. L'utilisateur s'engage
                à ne pas tenter d'accéder aux dossiers d'autres utilisateurs ni à contourner les mécanismes
                de sécurité de la plateforme.
            </p>
        </div>

        <div class="ml-subsection">
            <p class="ml-subsection-title">Limite de responsabilité</p>
            <div class="ml-alert">
                <span class="ml-alert-icon">⚠️</span>
                <span>
                    La plateforme Legatech est un prototype développé dans un cadre pédagogique. Elle n'est pas
                    destinée à être mise en production en l'état et ne saurait être utilisée dans un environnement
                    professionnel réel sans audits de sécurité, validations juridiques et tests approfondis.
                    L'éditeur décline toute responsabilité en cas d'utilisation hors de son cadre pédagogique prévu.
                </span>
            </div>
        </div>
    </div>

    <div class="ml-footer-note">
        <p class="subtitle" style="font-size: 0.78rem; color: rgba(255,255,255,0.25);">
            Dernière mise à jour : <?php echo date('d/m/Y'); ?> &nbsp;|&nbsp;
            &copy; <?php echo date('Y'); ?> Legatech – Tous droits réservés
        </p>
    </div>

</div>

</body>
</html>