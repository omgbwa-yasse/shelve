#!/usr/bin/env bash
#
# Prépare la base de test `shelve_test`.
#
# Depuis la migration de baseline (0001_01_01_000000_baseline_schema.php), un simple
# `php artisan migrate` reconstruit un schéma complet à partir de rien. Ce script se
# contente donc de créer la base vide et de lancer la migration — il existe surtout
# pour ne pas avoir à se souvenir du nom de la base ni de l'emplacement du client MySQL.
#
# Usage :
#   scripts/setup-test-db.sh                    reconstruit shelve_test de zéro
#   scripts/setup-test-db.sh regenerate-schema  régénère la baseline depuis shelve_db
#
set -euo pipefail

MYSQL_BIN="${MYSQL_BIN:-/c/wamp64_New/bin/mysql/mysql9.1.0/bin}"
MYSQL="$MYSQL_BIN/mysql.exe"
MYSQLDUMP="$MYSQL_BIN/mysqldump.exe"
DB_USER="${DB_USERNAME:-root}"
SOURCE_DB="${SOURCE_DB:-shelve_db}"
TEST_DB="${TEST_DB:-shelve_test}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SCHEMA="$ROOT/database/schema/baseline-schema.sql"

[ -x "$MYSQL" ] || { echo "Client mysql introuvable : $MYSQL" >&2
                     echo "Définir MYSQL_BIN vers le répertoire des binaires MySQL." >&2; exit 1; }

# ---------------------------------------------------------------------------
# Régénération de la baseline
# ---------------------------------------------------------------------------
if [ "${1:-}" = "regenerate-schema" ]; then
    echo "Régénération de la baseline depuis '$SOURCE_DB' (lecture seule)…"

    # `migrations` est délibérément exclue : Laravel crée et gère cette table
    # lui-même. L'inclure ferait échouer la migration de baseline.
    TABLES=$("$MYSQL" -u "$DB_USER" -N -e \
        "SELECT table_name FROM information_schema.tables
          WHERE table_schema='$SOURCE_DB' AND table_type='BASE TABLE'
            AND table_name <> 'migrations';" | tr -d '\r' | tr '\n' ' ')

    mkdir -p "$(dirname "$SCHEMA")"
    {
        # Les tables se référencent mutuellement : l'ordre d'un dump ne suit pas
        # les dépendances, les contraintes doivent être désactivées au chargement.
        echo "SET FOREIGN_KEY_CHECKS=0;"
        # shellcheck disable=SC2086
        "$MYSQLDUMP" -u "$DB_USER" --no-data --skip-add-drop-table --skip-comments \
                     --no-tablespaces "$SOURCE_DB" $TABLES
        echo "SET FOREIGN_KEY_CHECKS=1;"
    } > "$SCHEMA"

    echo "✓ $SCHEMA ($(grep -c 'CREATE TABLE' "$SCHEMA") tables)"
    echo "  Ne pas renommer ce fichier en 'mysql-schema.sql' : sous ce nom, Laravel"
    echo "  le chargerait via le binaire 'mysql', absent du PATH sous WAMP."
    exit 0
fi

# ---------------------------------------------------------------------------
# Reconstruction de la base de test
# ---------------------------------------------------------------------------
[ -f "$SCHEMA" ] || { echo "Baseline absente : $SCHEMA" >&2
                      echo "La régénérer : scripts/setup-test-db.sh regenerate-schema" >&2; exit 1; }

echo "Reconstruction de '$TEST_DB'…"
"$MYSQL" -u "$DB_USER" -e \
    "DROP DATABASE IF EXISTS \`$TEST_DB\`;
     CREATE DATABASE \`$TEST_DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

cd "$ROOT"
DB_DATABASE="$TEST_DB" php artisan migrate --force --no-interaction

TABLES_COUNT=$("$MYSQL" -u "$DB_USER" -N -e \
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$TEST_DB';")
echo "✓ '$TEST_DB' prête — $TABLES_COUNT tables"
