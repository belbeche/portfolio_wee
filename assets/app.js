/*
 * Point d'entree des styles et scripts.
 *
 * Regle de tenue : une feuille n'est importee que si elle sert reellement.
 * Historique du menage (04/08/2026) :
 *   - pixel.scss (685 Ko) retire : c'est un kit qui embarque Bootstrap 4
 *     en entier alors que le site tourne sur Bootstrap 5. Aucune de ses
 *     classes propres n'etait utilisee dans les gabarits.
 *   - nucleo-icons et perfect-scrollbar retires : zero usage.
 *   - les doublons d'import (email, magnific-popup, normalize, services)
 *     ont ete supprimes.
 */

// Socle
import './styles/normalize.css';
import './styles/bootstrap.min.css';   // Bootstrap 5.3.3, la grille du site

// Theme historique et ses dependances encore utilisees
import './styles/main.css';
import './styles/animations.css';
import './styles/font-awesome.css';
import './styles/magnific-popup.css';  // utilise par public/js/main.js

// Feuilles propres au site
import './styles/about.css';
import './styles/app.css';
import './styles/email.css';
import './styles/form_call_back.css';
import './styles/processus.css';
import './styles/services.css';

// start the Stimulus application
import './bootstrap';
