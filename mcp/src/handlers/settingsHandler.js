const { getSettings } = require('../utils/database');
const { logger } = require('../utils/logger');

/**
 * Récupère tous les paramètres liés à l'IA
 */
module.exports.getSettings = async (req, res) => {
  try {
    // Filtrer par catégorie si spécifiée dans la requête
    const { category } = req.query;

    let settings;
    if (category) {
      const db = require('../utils/database').getDb();
      settings = await db('settings')
        .select('settings.*', 'setting_categories.name as category_name')
        .leftJoin('setting_categories', 'settings.category_id', 'setting_categories.id')
        .where('setting_categories.name', 'like', `%${category}%`);
    } else {
      settings = await getSettings();
    }

    // Formater les paramètres pour la réponse
    const formattedSettings = settings.map(setting => ({
      name: setting.name,
      type: setting.type,
      value: setting.value ? JSON.parse(setting.value) : JSON.parse(setting.default_value),
      description: setting.description,
      category: setting.category_name,
      isSystem: setting.is_system
    }));

    return res.json({
      success: true,
      settings: formattedSettings
    });
  } catch (error) {
    logger.error(`Erreur lors de la récupération des paramètres: ${error.message}`);
    return res.status(500).json({ error: true, message: error.message });
  }
};

/**
 * Récupère un paramètre spécifique par son nom
 */
module.exports.getSetting = async (req, res) => {
  try {
    const { name } = req.params;

    if (!name) {
      return res.status(400).json({ error: true, message: 'Le nom du paramètre est requis' });
    }

    const setting = await getSettings(name);

    if (!setting) {
      return res.status(404).json({ error: true, message: 'Paramètre non trouvé' });
    }

    // Formater le paramètre pour la réponse
    const formattedSetting = {
      name: setting.name,
      type: setting.type,
      value: setting.value ? JSON.parse(setting.value) : JSON.parse(setting.default_value),
      description: setting.description,
      category: setting.category_name,
      isSystem: setting.is_system
    };

    return res.json({
      success: true,
      setting: formattedSetting
    });
  } catch (error) {
    logger.error(`Erreur lors de la récupération du paramètre: ${error.message}`);
    return res.status(500).json({ error: true, message: error.message });
  }
};
