const titleService = require('../services/processing/titleService');
const logger = require('../utils/logger');
const { ValidationError } = require('../utils/errors');

class RecordsController {

    /**
     * Reformuler le nom d'un enregistrement - Version simplifiée
     */
    async reformulateRecord(req, res, next) {
        try {
            const { id, record_id, name, date, content, author, children } = req.body;
            
            // Accepter soit 'id' soit 'record_id'
            const recordId = id || record_id;
            
            if (!recordId || !name) {
                throw new ValidationError('L\'ID et le nom sont requis');
            }

            const startTime = Date.now();
            
            // Construire le contexte complet pour la reformulation
            let fullContext = content || '';
            
            // Ajouter les informations de date si disponible
            if (date) {
                fullContext += `\nDate: ${date}`;
            }
            
            // Ajouter les informations d'auteur si disponible
            if (author && author.name) {
                fullContext += `\nAuteur: ${author.name}`;
            }
            
            // Ajouter le contexte des enfants si disponible
            if (children && children.length > 0) {
                fullContext += '\nDocuments associés:';
                children.forEach((child, index) => {
                    if (child.name) fullContext += `\n- ${child.name}`;
                    if (child.date) fullContext += ` (${child.date})`;
                    if (child.content) fullContext += ` : ${child.content.substring(0, 200)}`;
                });
            }
            
            // Reformuler avec le service de titre en mode archivistique
            const newName = await titleService.reformulate(
                name, 
                fullContext, 
                null, // model par défaut
                { 
                    type: 'archival', 
                    style: 'formal',
                    maxLength: 150 
                }
            );
            
            const duration = Date.now() - startTime;
            logger.performance('Record reformulation', duration);

            // Réponse simplifiée comme demandé
            res.json({ 
                id: recordId,
                original_name: name,
                new_name: newName,
                success: true
            });
            
        } catch (error) {
            next(error);
        }
    }
}

module.exports = new RecordsController();
