const knex = require('knex');
const { logger } = require('./logger');

let db = null;

/**
 * Initialise la connexion à la base de données MySQL
 */
async function initDatabaseConnection() {
  try {
    db = knex({
      client: 'mysql2',
      connection: {
        host: process.env.DB_HOST || 'localhost',
        port: process.env.DB_PORT || 3306,
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_DATABASE || 'shelve'
      },
      pool: {
        min: 2,
        max: 10
      },
      debug: process.env.NODE_ENV === 'development'
    });

    // Tester la connexion
    await db.raw('SELECT 1+1 AS result');
    return db;
  } catch (error) {
    logger.error(`Erreur d'initialisation de la base de données: ${error.message}`);
    throw error;
  }
}

/**
 * Récupère l'instance de connexion à la base de données
 */
function getDb() {
  if (!db) {
    throw new Error('La connexion à la base de données n\'a pas été initialisée');
  }
  return db;
}

/**
 * Récupère les paramètres depuis la table settings
 * @param {string} name - Nom du paramètre à récupérer (optionnel)
 * @returns {Promise<Object|Array>} - Le paramètre demandé ou tous les paramètres
 */
async function getSettings(name = null) {
  try {
    const query = getDb()('settings')
      .select('settings.*', 'setting_categories.name as category_name')
      .leftJoin('setting_categories', 'settings.category_id', 'setting_categories.id');

    if (name) {
      query.where('settings.name', name);
      return await query.first();
    }

    return await query;
  } catch (error) {
    logger.error(`Erreur lors de la récupération des paramètres: ${error.message}`);
    throw error;
  }
}

module.exports = {
  initDatabaseConnection,
  getDb,
  getSettings
};
