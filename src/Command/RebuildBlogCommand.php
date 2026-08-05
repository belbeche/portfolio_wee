<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Refonte du blog : remplace les articles existants par des articles qui
 * repondent chacun a une question reellement tapee dans un moteur de
 * recherche par la cible du site (dirigeants de TPE, commerces,
 * associations, agences web).
 *
 * Chaque article existe en francais et en anglais.
 *
 *   php bin/console app:blog:refonte              -> montre ce qui sera fait
 *   php bin/console app:blog:refonte --confirmer  -> supprime et recree
 *   php bin/console app:blog:refonte --confirmer --garder-anciens
 *
 * ATTENTION : sans --garder-anciens, TOUS les articles existants sont
 * supprimes. C'est demande explicitement, mais c'est irreversible.
 */
class RebuildBlogCommand extends Command
{
    protected static $defaultName = 'app:blog:refonte';
    protected static $defaultDescription = 'Remplace les articles du blog par des articles orientes questions clients';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addOption('confirmer', null, InputOption::VALUE_NONE, 'Executer reellement')
            ->addOption('garder-anciens', null, InputOption::VALUE_NONE, 'Ne pas supprimer les articles existants');
    }

    /** @return array<int, array<string, string>> */
    private function articles(): array
    {
        return [
            [
                'slug' => 'prix-site-internet-petite-entreprise',
                'categorie' => 'Conseils & services',
                'titre' => 'Combien coute un site internet pour une petite entreprise ?',
                'contenu' => '<p>C\'est la premiere question que l\'on me pose, et la reponse honnete est : cela depend de ce que le site doit faire. Voici des fourchettes reelles, celles que je pratique, pour que vous sachiez a quoi vous attendre avant meme de demander un devis.</p>

<h2>Trois situations, trois budgets</h2>
<p><strong>Un site vitrine simple, de 1 500 a 3 000 euros.</strong> Quelques pages, vos services, vos coordonnees, un formulaire de contact. C\'est ce qu\'il faut a un artisan ou a un commerce qui veut exister sur internet et etre trouve quand on cherche son nom.</p>
<p><strong>Un site avec une fonction metier, de 4 900 a 12 000 euros.</strong> Prise de rendez vous, commande en ligne, espace adherent, catalogue avec stock. Le prix vient de la fonction, pas du nombre de pages : c\'est la partie invisible qui prend le temps.</p>
<p><strong>Une application metier ou mobile, a partir de 12 000 euros.</strong> Quand l\'outil devient le coeur de votre activite et remplace un fichier Excel partage ou un logiciel qui ne vous convient plus.</p>

<h2>Ce qui fait vraiment varier le prix</h2>
<ul>
<li><strong>Les comptes utilisateurs.</strong> Des qu\'il y a des connexions, des roles et des donnees personnelles, la securite devient un vrai sujet.</li>
<li><strong>Le paiement en ligne.</strong> Techniquement accessible, mais il impose de la rigueur : rien ne doit se perdre entre la commande et l\'encaissement.</li>
<li><strong>Les connexions a vos outils existants.</strong> Logiciel de caisse, comptabilite, agenda. C\'est souvent la que se cache le travail.</li>
<li><strong>Le contenu.</strong> Un site ne se remplit pas tout seul. Si vous n\'avez ni textes ni photos, il faut compter ce temps la.</li>
</ul>

<h2>Les couts que l\'on oublie</h2>
<p>Le developpement n\'est pas la seule depense. Il faut aussi prevoir le nom de domaine, entre 10 et 20 euros par an, l\'hebergement, entre 5 et 30 euros par mois selon les besoins, et surtout la maintenance. Un site laisse sans mise a jour pendant deux ans devient une porte ouverte, et le remettre a niveau coute souvent plus cher que de l\'avoir entretenu.</p>

<h2>Ma facon de faire</h2>
<p>Je chiffre ligne par ligne, avant de commencer, et le prix ne bouge pas en cours de route. Vous validez a chaque etape avant de regler. A la fin, le code, la documentation et les droits vous appartiennent : vous n\'etes lie a personne, pas meme a moi.</p>
<p>Si vous hesitez encore sur l\'ampleur du projet, le Diagnostic a 290 euros vous donne un plan d\'action chiffre, et il est deduit du prix si nous continuons ensemble.</p>',
                'titre_en' => 'How much does a website cost for a small business?',
                'contenu_en' => '<p>This is the first question I get, and the honest answer is: it depends on what the site has to do. Here are real ranges, the ones I actually charge, so you know what to expect before you even ask for a quote.</p>

<h2>Three situations, three budgets</h2>
<p><strong>A simple showcase site, 1,500 to 3,000 euros.</strong> A few pages, your services, your contact details, a contact form. This is what a craftsman or a local shop needs to exist online and be found by name.</p>
<p><strong>A site with a business function, 4,900 to 12,000 euros.</strong> Booking, online ordering, a member area, a catalogue with stock. The price comes from the function, not the number of pages: the invisible part is what takes the time.</p>
<p><strong>A business or mobile application, from 12,000 euros.</strong> When the tool becomes the heart of your activity and replaces a shared spreadsheet or software that no longer fits.</p>

<h2>What really moves the price</h2>
<ul>
<li><strong>User accounts.</strong> As soon as there are logins, roles and personal data, security becomes a real subject.</li>
<li><strong>Online payment.</strong> Technically accessible, but it demands rigour: nothing may be lost between the order and the payment.</li>
<li><strong>Connections to your existing tools.</strong> Point of sale, accounting, calendar. This is often where the work hides.</li>
<li><strong>Content.</strong> A site does not fill itself. If you have neither texts nor photos, that time has to be counted.</li>
</ul>

<h2>The costs people forget</h2>
<p>Development is not the only expense. You also need a domain name, 10 to 20 euros a year, hosting, 5 to 30 euros a month depending on needs, and above all maintenance. A site left without updates for two years becomes an open door, and bringing it back up to standard often costs more than maintaining it would have.</p>

<h2>How I work</h2>
<p>I quote line by line, before starting, and the price does not move along the way. You approve each step before you pay. At the end, the code, the documentation and the rights belong to you: you are tied to no one, not even to me.</p>
<p>If you are still unsure about the scope, the 290 euro Diagnostic gives you a priced action plan, and it is deducted if we continue together.</p>',
            ],
            [
                'slug' => 'refaire-ou-reparer-son-site',
                'categorie' => 'Conseils & services',
                'titre' => 'Faut il refaire son site ou le reparer ?',
                'contenu' => '<p>Refaire un site coute cher et prend du temps. Avant de vous lancer, voici comment savoir si le votre merite une reparation ou une reconstruction. J\'utilise cette grille a chaque diagnostic.</p>

<h2>Quatre signes qu\'une reparation suffit</h2>
<ul>
<li><strong>Le site est lent, mais la structure tient.</strong> Des images trop lourdes et un hebergement sous dimensionne se corrigent en quelques heures.</li>
<li><strong>Le design a vieilli, le contenu est bon.</strong> Changer l\'apparence sans toucher au fond est un chantier court.</li>
<li><strong>Il manque une fonction precise.</strong> Ajouter un formulaire ou une prise de rendez vous ne justifie pas de tout jeter.</li>
<li><strong>Il n\'est pas adapte au mobile.</strong> Genant, mais souvent reparable sur une base saine.</li>
</ul>

<h2>Quatre signes qu\'il faut reconstruire</h2>
<ul>
<li><strong>La technologie n\'est plus maintenue.</strong> Si les mises a jour de securite n\'existent plus, chaque mois qui passe augmente le risque.</li>
<li><strong>Personne ne sait comment il fonctionne.</strong> Sans code source ni acces, vous etes dependant de quelqu\'un qui n\'est plus la.</li>
<li><strong>Chaque modification en casse une autre.</strong> C\'est le signe d\'une base fragile : reparer coute alors plus cher que reconstruire.</li>
<li><strong>Le site ne correspond plus a votre activite.</strong> Si votre metier a change, retoucher l\'existant revient a rafistoler la mauvaise maison.</li>
</ul>

<h2>Le calcul a faire</h2>
<p>Additionnez ce que vous avez depense en corrections sur les deux dernieres annees. Si cela depasse la moitie du prix d\'un site neuf, la question est deja tranchee. Ajoutez le temps que vous perdez chaque semaine a contourner ses limites : c\'est un cout invisible, mais bien reel.</p>

<h2>Ce que je regarde en premier</h2>
<p>Avant de vous conseiller quoi que ce soit, je verifie trois choses : est ce que les sauvegardes existent et fonctionnent, est ce que vous possedez vos acces et votre nom de domaine, et est ce que les dependances recoivent encore des correctifs de securite. Ces trois reponses decident souvent a elles seules.</p>
<p>C\'est exactement ce que contient le Diagnostic : un audit, les risques reels, et un plan chiffre ligne par ligne qui vous appartient, meme si vous confiez ensuite le travail a quelqu\'un d\'autre.</p>',
                'titre_en' => 'Should you rebuild your website or repair it?',
                'contenu_en' => '<p>Rebuilding a website is expensive and takes time. Before you commit, here is how to tell whether yours deserves a repair or a rebuild. I use this grid at every diagnostic.</p>

<h2>Four signs a repair is enough</h2>
<ul>
<li><strong>The site is slow, but the structure holds.</strong> Oversized images and undersized hosting are fixed in a few hours.</li>
<li><strong>The design has aged, the content is good.</strong> Changing the appearance without touching the substance is a short job.</li>
<li><strong>One specific function is missing.</strong> Adding a form or a booking system does not justify throwing everything away.</li>
<li><strong>It is not mobile friendly.</strong> Annoying, but often repairable on a healthy base.</li>
</ul>

<h2>Four signs you should rebuild</h2>
<ul>
<li><strong>The technology is no longer maintained.</strong> If security updates no longer exist, every passing month increases the risk.</li>
<li><strong>Nobody knows how it works.</strong> With no source code and no access, you depend on someone who is no longer around.</li>
<li><strong>Every change breaks something else.</strong> That is the sign of a fragile base: repairing then costs more than rebuilding.</li>
<li><strong>The site no longer matches your business.</strong> If your work has changed, patching the existing one means fixing the wrong house.</li>
</ul>

<h2>The calculation to make</h2>
<p>Add up what you have spent on fixes over the last two years. If it exceeds half the price of a new site, the question is already settled. Add the time you lose every week working around its limits: an invisible cost, but a real one.</p>

<h2>What I check first</h2>
<p>Before advising anything, I check three things: whether backups exist and actually work, whether you own your access and your domain name, and whether the dependencies still receive security patches. Those three answers often decide on their own.</p>
<p>This is exactly what the Diagnostic contains: an audit, the real risks, and a priced plan line by line that belongs to you, even if you then hand the work to someone else.</p>',
            ],
            [
                'slug' => 'freelance-ou-agence-web',
                'categorie' => 'Conseils & services',
                'titre' => 'Developpeur independant ou agence web : lequel choisir ?',
                'contenu' => '<p>Je suis independant, donc mon avis n\'est pas neutre. Je vais quand meme vous donner les criteres honnetes, y compris ceux qui plaident contre moi. Un projet mal oriente coute plus cher a tout le monde.</p>

<h2>Ce que l\'agence apporte</h2>
<p>Une equipe absorbe les imprevus : si quelqu\'un tombe malade, un autre prend le relais. Elle couvre plusieurs metiers, du design a la publicite en ligne. Et pour un grand compte, sa taille rassure les services achats.</p>
<p>En contrepartie, vous parlez souvent a un commercial puis a un chef de projet, et rarement a la personne qui ecrit le code. L\'information se perd en chemin, et les couts de structure se retrouvent dans le devis.</p>

<h2>Ce que l\'independant apporte</h2>
<p>Vous parlez directement a celui qui fait le travail. Ce que vous dites le lundi est dans le code le mardi, sans intermediaire pour reformuler. Les frais de structure etant faibles, le meme budget achete plus de travail reel.</p>
<p>En contrepartie, un independant a une capacite limitee et une specialite. S\'il tombe malade, le projet attend. C\'est le risque a regarder en face.</p>

<h2>Les trois questions qui tranchent</h2>
<ul>
<li><strong>Votre projet demande t il plusieurs metiers en meme temps ?</strong> Si vous avez besoin de design, de developpement, de publicite et de redaction simultanement, une agence sera plus simple.</li>
<li><strong>Qui portera le projet chez vous ?</strong> Si personne n\'a le temps de suivre, une agence structuree compensera. Si vous voulez etre implique, un independant ira plus vite.</li>
<li><strong>Que se passe t il apres la livraison ?</strong> Posez cette question aux deux. C\'est la que la difference se voit vraiment.</li>
</ul>

<h2>La question a poser dans les deux cas</h2>
<p>A qui appartient le code a la fin ? Si la reponse n\'est pas claire et ecrite, passez votre chemin. Vous devez pouvoir partir avec votre site, sans negociation et sans rancune. C\'est la seule garantie qui compte vraiment.</p>',
                'titre_en' => 'Freelance developer or web agency: which should you choose?',
                'contenu_en' => '<p>I am a freelancer, so my opinion is not neutral. I will still give you the honest criteria, including the ones that argue against me. A badly oriented project costs everyone more.</p>

<h2>What an agency brings</h2>
<p>A team absorbs the unexpected: if someone falls ill, another takes over. It covers several trades, from design to online advertising. And for a large account, its size reassures procurement departments.</p>
<p>In exchange, you often speak to a salesperson then a project manager, and rarely to the person writing the code. Information gets lost along the way, and structural costs end up in the quote.</p>

<h2>What a freelancer brings</h2>
<p>You speak directly to the person doing the work. What you say on Monday is in the code on Tuesday, with no intermediary to rephrase it. With low structural costs, the same budget buys more actual work.</p>
<p>In exchange, a freelancer has limited capacity and one speciality. If he falls ill, the project waits. That is the risk to face honestly.</p>

<h2>The three questions that decide</h2>
<ul>
<li><strong>Does your project need several trades at once?</strong> If you need design, development, advertising and copywriting simultaneously, an agency will be simpler.</li>
<li><strong>Who will carry the project on your side?</strong> If nobody has time to follow it, a structured agency compensates. If you want to be involved, a freelancer moves faster.</li>
<li><strong>What happens after delivery?</strong> Ask both. That is where the difference really shows.</li>
</ul>

<h2>The question to ask in both cases</h2>
<p>Who owns the code at the end? If the answer is not clear and written down, walk away. You must be able to leave with your site, without negotiation and without hard feelings. It is the only guarantee that really counts.</p>',
            ],
            [
                'slug' => 'proprietaire-du-code-de-mon-site',
                'categorie' => 'Conseils & services',
                'titre' => 'Qui est proprietaire du code de mon site internet ?',
                'contenu' => '<p>Beaucoup de dirigeants decouvrent la reponse le jour ou ils veulent changer de prestataire. Et ce jour la, il est trop tard pour negocier. Voici ce qu\'il faut verifier, et quand.</p>

<h2>Le principe legal</h2>
<p>En droit francais, le code est protege comme une oeuvre. Sauf mention contraire ecrite, <strong>le developpeur en reste l\'auteur</strong>, meme si vous l\'avez paye. Payer une prestation n\'emporte pas automatiquement la cession des droits : celle ci doit etre explicite, ecrite, et preciser son etendue et sa duree.</p>
<p>Autrement dit, sans clause de cession dans votre contrat, vous avez paye l\'usage, pas la propriete.</p>

<h2>Les quatre elements a verifier</h2>
<ul>
<li><strong>Le nom de domaine.</strong> Il doit etre enregistre a votre nom, pas a celui de votre prestataire. C\'est votre adresse : sans elle, vous repartez de zero.</li>
<li><strong>Les acces a l\'hebergement.</strong> Vous devez pouvoir vous connecter au serveur, meme si vous ne vous en servez jamais.</li>
<li><strong>Le code source.</strong> Une copie complete doit vous etre remise, ou vous devez avoir acces au depot qui la contient.</li>
<li><strong>La clause de cession.</strong> Elle doit figurer noir sur blanc dans le devis ou le contrat.</li>
</ul>

<h2>Le test en trente secondes</h2>
<p>Posez cette question a votre prestataire actuel : <em>si je pars demain, qu\'est ce que j\'emporte ?</em> Une reponse claire et immediate est bon signe. Une hesitation, ou un renvoi vers un contrat introuvable, vous dit tout ce qu\'il faut savoir.</p>

<h2>Ce que je m\'engage a faire</h2>
<p>A la fin de chaque projet, je remets le code source, la documentation, et les droits sont cedes. Le nom de domaine et l\'hebergement sont a votre nom des le premier jour, meme quand c\'est moi qui les gere. Vous restez libre de partir a tout moment, et c\'est justement ce qui rend la relation saine.</p>',
                'titre_en' => 'Who owns the code of my website?',
                'contenu_en' => '<p>Many business owners discover the answer on the day they want to change providers. And on that day, it is too late to negotiate. Here is what to check, and when.</p>

<h2>The legal principle</h2>
<p>Under French law, code is protected as a work of authorship. Unless stated otherwise in writing, <strong>the developer remains its author</strong>, even if you paid for it. Paying for a service does not automatically transfer the rights: that transfer must be explicit, written, and state its scope and duration.</p>
<p>In other words, without a transfer clause in your contract, you paid for use, not ownership.</p>

<h2>The four items to check</h2>
<ul>
<li><strong>The domain name.</strong> It must be registered in your name, not your provider\'s. It is your address: without it, you start from scratch.</li>
<li><strong>Hosting access.</strong> You must be able to log in to the server, even if you never use it.</li>
<li><strong>The source code.</strong> A complete copy must be handed to you, or you must have access to the repository holding it.</li>
<li><strong>The transfer clause.</strong> It must appear in black and white in the quote or the contract.</li>
</ul>

<h2>The thirty second test</h2>
<p>Ask your current provider this question: <em>if I leave tomorrow, what do I take with me?</em> A clear, immediate answer is a good sign. Hesitation, or a reference to a contract nobody can find, tells you everything you need to know.</p>

<h2>What I commit to</h2>
<p>At the end of every project, I hand over the source code and the documentation, and the rights are transferred. The domain name and hosting are in your name from day one, even when I manage them. You remain free to leave at any time, and that is precisely what makes the relationship healthy.</p>',
            ],
            [
                'slug' => 'site-lent-que-faire',
                'categorie' => 'Conseils & services',
                'titre' => 'Mon site est lent : par ou commencer ?',
                'contenu' => '<p>Un visiteur sur deux abandonne un site qui met plus de trois secondes a s\'afficher. La bonne nouvelle, c\'est que dans la majorite des cas la cause est simple et la correction rapide. Voici l\'ordre dans lequel chercher.</p>

<h2>1. Les images, neuf fois sur dix</h2>
<p>C\'est de loin la premiere cause. Une photo sortie d\'un appareil pese souvent 4 megaoctets alors que 200 kilooctets suffisent a l\'ecran. Sur un site de dix pages, cela represente des dizaines de megaoctets telecharges pour rien.</p>
<p>La correction est mecanique : redimensionner aux dimensions reellement affichees, compresser, et adopter un format moderne. Comptez quelques heures pour un gain souvent spectaculaire.</p>

<h2>2. L\'hebergement sous dimensionne</h2>
<p>Un hebergement mutualise a trois euros par mois partage sa machine avec des centaines d\'autres sites. Aux heures de pointe, votre site attend son tour. Si votre activite depend de votre site, c\'est une economie qui coute cher.</p>

<h2>3. Les greffons accumules</h2>
<p>Sur les sites construits avec un gestionnaire de contenu, chaque extension ajoute du code charge sur toutes les pages, meme celles qui ne s\'en servent pas. Un site qui en compte quarante en utilise rarement plus de dix. Desactiver les autres suffit souvent.</p>

<h2>4. Ce que vous chargez sans le savoir</h2>
<p>Polices, statistiques, chat en direct, boutons de partage, chacun ajoute des allers retours vers des serveurs exterieurs. Pris un par un ils semblent negligeables. Additionnes, ils doublent le temps d\'affichage.</p>

<h2>Comment mesurer avant d\'agir</h2>
<p>Testez votre site sur PageSpeed Insights, l\'outil gratuit de Google. Regardez surtout la note mobile, plus severe et plus proche de la realite de vos visiteurs. Notez le chiffre avant, corrigez, remesurez. Sans mesure, on optimise a l\'aveugle.</p>
<p>Si vous voulez que quelqu\'un regarde a votre place, c\'est le premier point que j\'examine dans un Diagnostic, avec les sauvegardes et la securite.</p>',
                'titre_en' => 'My website is slow: where do I start?',
                'contenu_en' => '<p>One visitor in two abandons a site that takes more than three seconds to display. The good news is that in most cases the cause is simple and the fix is quick. Here is the order in which to look.</p>

<h2>1. Images, nine times out of ten</h2>
<p>By far the first cause. A photo straight from a camera often weighs 4 megabytes when 200 kilobytes are enough on screen. On a ten page site, that means dozens of megabytes downloaded for nothing.</p>
<p>The fix is mechanical: resize to the dimensions actually displayed, compress, and adopt a modern format. Count a few hours for an often spectacular gain.</p>

<h2>2. Undersized hosting</h2>
<p>Shared hosting at three euros a month shares its machine with hundreds of other sites. At peak times, your site waits its turn. If your business depends on your site, that is an expensive saving.</p>

<h2>3. Accumulated plugins</h2>
<p>On sites built with a content manager, every extension adds code loaded on all pages, even those that never use it. A site with forty of them rarely uses more than ten. Disabling the rest is often enough.</p>

<h2>4. What you load without knowing</h2>
<p>Fonts, analytics, live chat, share buttons, each adds round trips to outside servers. One by one they seem negligible. Added up, they double the display time.</p>

<h2>How to measure before acting</h2>
<p>Test your site on PageSpeed Insights, Google\'s free tool. Look above all at the mobile score, stricter and closer to your visitors\' reality. Note the number before, fix, measure again. Without measurement, you optimise blind.</p>
<p>If you would rather someone looked for you, this is the first point I examine in a Diagnostic, along with backups and security.</p>',
            ],
            [
                'slug' => 'cahier-des-charges-site-web',
                'categorie' => 'Conseils & services',
                'titre' => 'Comment ecrire un cahier des charges simple pour son site ?',
                'contenu' => '<p>Vous n\'avez pas besoin de trente pages ni de vocabulaire technique. Un bon cahier des charges tient en deux pages et repond a cinq questions. Voici comment l\'ecrire vous meme, en une heure.</p>

<h2>1. Pourquoi ce projet existe</h2>
<p>Une phrase, pas plus. <em>Je veux arreter de prendre les commandes par telephone.</em> <em>Je veux que mes adherents s\'inscrivent sans que je ressaisisse leurs informations.</em> Cette phrase est la boussole : chaque fois qu\'une question se posera, elle donnera la reponse.</p>

<h2>2. Qui va s\'en servir</h2>
<p>Listez les types d\'utilisateurs et ce que chacun doit pouvoir faire. Un client passe commande. Vous suivez les commandes. Votre comptable exporte les factures. Cette liste dit deja beaucoup du travail a fournir.</p>

<h2>3. Ce que le site doit faire, du plus important au moins important</h2>
<p>Ecrivez chaque fonction en une ligne, en commencant par un verbe. Ensuite, et c\'est l\'etape que tout le monde saute, classez les en trois groupes : <strong>indispensable</strong>, <strong>utile</strong>, <strong>plus tard</strong>. Ce classement vous evitera de payer aujourd\'hui des choses dont vous n\'aurez besoin que dans deux ans.</p>

<h2>4. Ce qui existe deja</h2>
<p>Votre logiciel de caisse, votre fichier client, votre agenda, vos photos, votre logo. Precisez ce qui doit etre repris et ce qui peut disparaitre. C\'est souvent la que se cachent les surprises budgetaires.</p>

<h2>5. Vos contraintes reelles</h2>
<p>Votre budget, meme approximatif, et votre echeance si vous en avez une. Annoncer un budget ne vous dessert pas : cela permet de vous proposer ce qui rentre dedans plutot que de vous faire perdre du temps.</p>

<h2>Ce qu\'il ne faut surtout pas ecrire</h2>
<p>Ne decrivez pas les solutions techniques, decrivez les besoins. Ecrivez <em>je veux etre prevenu quand une commande arrive</em> et non <em>il faut un webhook qui envoie une notification</em>. Le premier laisse le professionnel proposer le meilleur moyen, le second l\'enferme dans le votre.</p>
<p>Si vous m\'envoyez ces cinq reponses, meme en vrac, je peux vous chiffrer le projet sous 48 heures ouvrees.</p>',
                'titre_en' => 'How to write a simple specification for your website',
                'contenu_en' => '<p>You do not need thirty pages or technical vocabulary. A good specification fits on two pages and answers five questions. Here is how to write it yourself, in an hour.</p>

<h2>1. Why this project exists</h2>
<p>One sentence, no more. <em>I want to stop taking orders by phone.</em> <em>I want my members to sign up without me retyping their details.</em> That sentence is the compass: whenever a question arises, it gives the answer.</p>

<h2>2. Who will use it</h2>
<p>List the types of users and what each must be able to do. A customer places an order. You track orders. Your accountant exports invoices. This list already says a lot about the work involved.</p>

<h2>3. What the site must do, from most to least important</h2>
<p>Write each function on one line, starting with a verb. Then, and this is the step everyone skips, sort them into three groups: <strong>essential</strong>, <strong>useful</strong>, <strong>later</strong>. This sorting saves you from paying today for things you will only need in two years.</p>

<h2>4. What already exists</h2>
<p>Your point of sale software, your customer file, your calendar, your photos, your logo. State what must be carried over and what can disappear. This is often where budget surprises hide.</p>

<h2>5. Your real constraints</h2>
<p>Your budget, even approximate, and your deadline if you have one. Naming a budget does not work against you: it lets people offer what fits inside it rather than waste your time.</p>

<h2>What you should never write</h2>
<p>Do not describe technical solutions, describe needs. Write <em>I want to be notified when an order arrives</em>, not <em>we need a webhook sending a notification</em>. The first lets the professional propose the best means, the second locks him into yours.</p>
<p>If you send me those five answers, even roughly, I can price the project within 48 working hours.</p>',
            ],
            [
                'slug' => 'developpement-marque-blanche-agences',
                'categorie' => 'Conseils & services',
                'titre' => 'Le developpement en marque blanche, comment ca marche ?',
                'contenu' => '<p>Cet article s\'adresse aux agences web et aux studios de design qui recoivent des demandes techniques qu\'ils ne peuvent pas traiter en interne. La marque blanche permet de dire oui sans embaucher.</p>

<h2>Le principe, en une phrase</h2>
<p>Je developpe, vous livrez. Votre client ne me connait pas, ne me voit jamais, et n\'a aucune raison de savoir que j\'existe. Le travail sort sous votre nom, dans votre relation commerciale.</p>

<h2>Pourquoi les agences y viennent</h2>
<ul>
<li><strong>Ne plus refuser de missions.</strong> Une demande technique qui sortait de votre perimetre devient une mission facturee.</li>
<li><strong>Pas de charge fixe.</strong> Vous payez au projet, pas au mois. Quand il n\'y a pas de travail, cela ne coute rien.</li>
<li><strong>Garder la relation client.</strong> Vous restez l\'interlocuteur unique, celui qui connait le contexte et qui encaisse.</li>
<li><strong>Absorber les pics.</strong> Trois projets qui tombent en meme temps ne se refusent plus.</li>
</ul>

<h2>Comment cela se passe concretement</h2>
<p>Vous me transmettez le besoin, tel que votre client vous l\'a exprime. Je vous reponds sous 48 heures ouvrees avec un chiffrage detaille, que vous appliquez a votre marge. Une fois valide, je developpe et je vous rends compte a chaque jalon, dans un format que vous pouvez transferer tel quel a votre client.</p>
<p>A la livraison, vous recevez le code, la documentation et les acces. Les droits sont cedes, sans reserve.</p>

<h2>Les trois regles que je m\'impose</h2>
<ul>
<li><strong>Aucun contact direct avec votre client</strong>, sauf si vous me le demandez explicitement.</li>
<li><strong>Aucune mention de mon nom</strong> dans le code, les documents ou les pages livrees.</li>
<li><strong>Aucune approche commerciale</strong> de vos clients, ni pendant, ni apres. C\'est une question de confiance, et sans elle ce mode de travail n\'a aucun sens.</li>
</ul>

<h2>Ce que je prends et ce que je ne prends pas</h2>
<p>Je fais du developpement web et mobile : sites, applications metier, interfaces de programmation, espaces d\'administration, applications iOS et Android. Je ne fais ni design graphique, ni redaction, ni publicite en ligne. Si votre besoin est ailleurs, je vous le dirai plutot que de vous faire perdre du temps.</p>',
                'titre_en' => 'White label development: how does it work?',
                'contenu_en' => '<p>This article is for web agencies and design studios receiving technical requests they cannot handle in house. White label lets you say yes without hiring.</p>

<h2>The principle, in one sentence</h2>
<p>I develop, you deliver. Your client does not know me, never sees me, and has no reason to know I exist. The work ships under your name, inside your commercial relationship.</p>

<h2>Why agencies come to it</h2>
<ul>
<li><strong>Stop turning work down.</strong> A technical request that fell outside your scope becomes a billed assignment.</li>
<li><strong>No fixed cost.</strong> You pay per project, not per month. When there is no work, it costs nothing.</li>
<li><strong>Keep the client relationship.</strong> You remain the single point of contact, the one who knows the context and gets paid.</li>
<li><strong>Absorb peaks.</strong> Three projects landing at once no longer have to be refused.</li>
</ul>

<h2>How it works in practice</h2>
<p>You pass me the need, as your client expressed it to you. I answer within 48 working hours with a detailed quote, to which you apply your margin. Once approved, I develop and report at every milestone, in a format you can forward to your client as is.</p>
<p>On delivery, you receive the code, the documentation and the access. The rights are transferred, without reservation.</p>

<h2>The three rules I impose on myself</h2>
<ul>
<li><strong>No direct contact with your client</strong>, unless you explicitly ask for it.</li>
<li><strong>No mention of my name</strong> in the code, the documents or the delivered pages.</li>
<li><strong>No commercial approach</strong> to your clients, during or after. It is a matter of trust, and without it this way of working makes no sense.</li>
</ul>

<h2>What I take and what I do not</h2>
<p>I do web and mobile development: websites, business applications, programming interfaces, administration areas, iOS and Android apps. I do not do graphic design, copywriting or online advertising. If your need lies elsewhere, I will tell you rather than waste your time.</p>',
            ],
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $confirme = (bool) $input->getOption('confirmer');
        $garder = (bool) $input->getOption('garder-anciens');

        $repo = $this->em->getRepository(Post::class);
        $existants = $repo->findAll();
        $articles = $this->articles();

        $io->section('Ce qui va se passer');
        if (!$garder) {
            $io->text(sprintf('  %d article(s) existant(s) seront SUPPRIMES.', count($existants)));
        } else {
            $io->text(sprintf('  %d article(s) existant(s) seront conserves.', count($existants)));
        }
        $io->text(sprintf('  %d article(s) seront crees, en francais et en anglais :', count($articles)));
        foreach ($articles as $a) {
            $io->text('    - '.$a['titre']);
        }

        if (!$confirme) {
            $io->note('Rien n\'a ete fait. Relance avec --confirmer pour appliquer.');

            return Command::SUCCESS;
        }

        // 1. Suppression des anciens
        $supprimes = 0;
        if (!$garder) {
            foreach ($existants as $ancien) {
                $this->em->remove($ancien);
                ++$supprimes;
            }
            $this->em->flush();
        }

        // 2. Categorie d'accueil des articles
        $depotCategorie = $this->em->getRepository(Category::class);

        // 3. Creation
        $crees = 0;
        foreach ($articles as $a) {
            // Un slug identique existe deja (cas --garder-anciens) : on passe.
            if ($repo->findOneBy(['slug' => $a['slug']])) {
                continue;
            }

            $categorie = $depotCategorie->findOneBy(['name' => $a['categorie']]);
            if (null === $categorie) {
                $categorie = new Category();
                $categorie->setName($a['categorie']);
                if (method_exists($categorie, 'setSlug')) {
                    $categorie->setSlug($this->slugifier($a['categorie']));
                }
                $this->em->persist($categorie);
                $this->em->flush();
            }

            $post = new Post();
            $post->setTitle($a['titre'])
                ->setSlug($a['slug'])
                ->setContent($a['contenu'])
                ->setTitleEn($a['titre_en'])
                ->setContentEn($a['contenu_en'])
                ->setStatus('published')
                ->setCategory($categorie);

            if (method_exists($post, 'setCreatedAt')) {
                $post->setCreatedAt(new \DateTime());
            }

            $this->em->persist($post);
            ++$crees;
        }

        $this->em->flush();

        $io->success(sprintf(
            '%d article(s) supprime(s), %d article(s) publie(s) en francais et en anglais.',
            $supprimes,
            $crees
        ));
        $io->note('Pense a vider le cache du serveur depuis /admin/parametres.');

        return Command::SUCCESS;
    }

    private function slugifier(string $texte): string
    {
        $texte = mb_strtolower(trim($texte));
        $texte = strtr($texte, [
            "\u{e0}" => 'a', "\u{e2}" => 'a', "\u{e9}" => 'e', "\u{e8}" => 'e',
            "\u{ea}" => 'e', "\u{eb}" => 'e', "\u{ee}" => 'i', "\u{ef}" => 'i',
            "\u{f4}" => 'o', "\u{f9}" => 'u', "\u{fb}" => 'u', "\u{e7}" => 'c',
        ]);
        $texte = (string) preg_replace('/[^a-z0-9]+/', '-', $texte);

        return trim($texte, '-');
    }
}
