#!/bin/bash
# ---------------------------------------------------------------------------
# Sequence de demarrage de walidbelbeche.fr
#
# Regle numero un : on n'appelle AUCUNE commande qui touche la base avant que
# la base ait repondu. Au demarrage du conteneur, PHP est pret en une seconde
# et MySQL met souvent dix a trente secondes a ouvrir son port. Tout ce qui
# part dans cet intervalle echoue avec « [2002] Connection refused », alors
# que rien n'est casse.
#
# Regle numero deux : si la base ne repond jamais, on s'arrete proprement au
# lieu de derouler dix commandes qui echoueront toutes de la meme facon et
# noieront la vraie cause dans deux cents lignes de trace.
# ---------------------------------------------------------------------------

set -u
cd /home/container/webroot || exit 1

PHP=php84

echo "⏳ Attente de la base de donnees..."
if ! $PHP bin/console app:base:attendre --timeout=90 --intervalle=3; then
    echo "❌ La base n'a pas repondu. Les commandes de demarrage sont ignorees."
    echo "   Le site reste servi : verifie le service MySQL, puis relance le serveur."
    exit 0
fi

# app:db:maj et non doctrine:schema:update --force : cette commande maison
# n'applique que les CREATE TABLE, CREATE INDEX et ALTER ... ADD. Elle ne
# supprime ni ne modifie jamais une colonne existante. Un schema:update lance
# au mauvais moment peut effacer des donnees, ce qui n'a rien a faire dans une
# sequence de demarrage automatique.
echo "🗂  Mise a jour du schema (ajouts uniquement)..."
$PHP bin/console app:db:maj --appliquer

echo "🧹 Vidage du cache..."
$PHP bin/console cache:clear --no-warmup
$PHP bin/console cache:warmup

echo "🗃  Classement des realisations..."
$PHP bin/console app:projets:classer

# La prospection ne se relance pas a chaque redemarrage : l'API publique est
# interrogee pour rien et les doublons sont ecartes de toute facon. Lance-la
# a la main quand tu veux elargir la liste :
#   php84 bin/console app:prospects:import "agence web" --ville=Rouen --limit=10
#
# $PHP bin/console app:prospects:import "agence communication" --departement=27 --limit=15
# $PHP bin/console app:prospects:import "developpeur web" --ville=Caen --limit=10

echo "💾 Sauvegarde si la derniere date de plus de 24 h..."
$PHP bin/console app:sauvegarde --si-necessaire || echo "⚠️  Sauvegarde impossible, le demarrage continue."

echo "🩺 Controle de sante..."
$PHP bin/console app:surveillance || true

echo "[SUCCESS] run.sh execute."
