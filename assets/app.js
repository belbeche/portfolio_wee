/*
 * Point d'entree des styles et scripts.
 *
 * ATTENTION A L'ORDRE : pixel.scss passe volontairement APRES bootstrap et
 * surcharge une partie du design. Ne pas le deplacer ni le retirer sans
 * comparer le rendu avant/apres, sous peine de casser tout le site.
 *
 * Menage du 04/08/2026, limite au strictement sur :
 *   - doublons stricts supprimes (email, magnific-popup, normalize, services
 *     etaient importes deux fois)
 *   - nucleo-icons et perfect-scrollbar retires : zero usage dans les
 *     gabarits, et composants isoles donc sans surcharge possible.
 */

import './styles/main.css';
import './styles/normalize.css';
import './styles/bootstrap.min.css';
import './styles/magnific-popup.css';
import './styles/email.css';
import './styles/pixel.scss';
import './styles/about.css';
import './styles/form_call_back.css';
// import './styles/new.css';
import './styles/processus.css';
import './styles/services.css';
import './styles/animations.css';
import './styles/font-awesome.css';
import './styles/app.css';

// start the Stimulus application
import './bootstrap';
