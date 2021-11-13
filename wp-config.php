<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/fr:Modifier_wp-config.php Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define('DB_NAME', 'hostingsolut_db');

/** Utilisateur de la base de données MySQL. */
define('DB_USER', 'root');

/** Mot de passe de la base de données MySQL. */
define('DB_PASSWORD', '');

/** Adresse de l’hébergement MySQL. */
define('DB_HOST', 'localhost');

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define('DB_CHARSET', 'utf8mb4');

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'jpJ!$_,S_,FT4G&F;bs:r[Cc[tX=[nY0W@C8~5WuIV|CGuWG/l{8v}LB_}VAdu{t');
define('SECURE_AUTH_KEY',  'yppAZ~t#e]t6IXBAeDN</UEb=(?m|k7,,[18FfR&`V5%3cGKNOeq?lUyi9ib#S&n');
define('LOGGED_IN_KEY',    '/oZVl0z_iAPt:U_;hsc0|g  %s,F(kY@g,.}Y)MAU(:2slXooL<%T=~mJZRk[=LS');
define('NONCE_KEY',        'x0 /JH$vhp2}C6D4C1`S700~Lwuwu3,t2-Skl$_XO5F^?wfsHU,k1U6F|Hhkl`fz');
define('AUTH_SALT',        'J|U6<]-`(o7?{ZH>5E&V;]6k9ePL1;e3;ujo<h&?2Z< [_o4#=E/uGQ[{WgpE{4a');
define('SECURE_AUTH_SALT', '*Q3k1jD[R&j?N>t-QktLC$?)Z9NB u!=roeMHE%l(^cB4~f{.gL*[[v#|5U!Hbue');
define('LOGGED_IN_SALT',   'kOIH(q[72d>C$: =wAX>Fx8vEA?1u^;&h/JPi*;lUU3bpcW*9mJuaLh_j85dhJSl');
define('NONCE_SALT',       '6]HrC_r*G}A`a (MN&qx*|_y}a*@3^vv~I~h~#:7P**6WMoazTqJ(c{c;0J^FWZf');
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix  = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');