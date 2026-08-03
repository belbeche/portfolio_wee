#!/bin/bash
# ============================================================
# Synchronise le .env du serveur avec le modele .env.dist
# ------------------------------------------------------------
# Regle d'or : on AJOUTE les cles manquantes, on ne MODIFIE
# jamais une valeur existante. Les secrets poses a la main en
# prod survivent donc a tous les deploiements.
#
# A appeler au demarrage, avant cache:clear, par exemple en
# premiere ligne de run.sh :
#   bash /home/container/webroot/bin/env-sync.sh
# ============================================================
set -u

DIR="$(cd "$(dirname "$0")/.." && pwd)"
DIST="$DIR/.env.dist"
ENVF="$DIR/.env"

if [ ! -f "$DIST" ]; then
    echo "[env-sync] $DIST introuvable, rien a faire."
    exit 0
fi

# Premier demarrage : pas de .env du tout -> on part du modele.
if [ ! -f "$ENVF" ]; then
    cp "$DIST" "$ENVF"
    echo "[env-sync] .env cree depuis le modele. PENSE A REMPLIR LES SECRETS (APP_SECRET, DATABASE_URL, API_RESOURCE_*)."
    exit 0
fi

added=0
while IFS= read -r line; do
    # On ne considere que les lignes de definition KEY=...
    case "$line" in
        [A-Z]*=*) ;;
        *) continue ;;
    esac
    key="${line%%=*}"
    if ! grep -q "^${key}=" "$ENVF"; then
        {
            echo ""
            echo "# [env-sync $(date +%F)] cle ajoutee depuis .env.dist, valeur par defaut a verifier"
            echo "$line"
        } >> "$ENVF"
        echo "[env-sync] + $key (valeur par defaut du modele)"
        added=$((added+1))
    fi
done < "$DIST"

if [ "$added" -eq 0 ]; then
    echo "[env-sync] .env deja complet, aucune modification."
else
    echo "[env-sync] $added cle(s) ajoutee(s). Les valeurs existantes n'ont pas ete touchees."
fi
