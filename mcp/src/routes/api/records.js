const express = require('express');
const recordsController = require('../../controllers/recordsController');
const { asyncHandler, validateRequest } = require('../../middleware/errorHandler');
const Joi = require('joi');

const router = express.Router();

// Schéma de validation pour la reformulation simplifiée
const recordReformulationSchema = Joi.object({
    id: Joi.string().optional(),
    record_id: Joi.string().optional(),
    name: Joi.string().required().max(500),
    date: Joi.string().optional().allow(''),
    content: Joi.string().optional().allow('', null),
    author: Joi.object({
        name: Joi.string().optional().allow('')
    }).optional(),
    children: Joi.array().items(
        Joi.object({
            name: Joi.string().optional(),
            date: Joi.string().optional(),
            content: Joi.string().optional()
        })
    ).optional()
}).or('id', 'record_id'); // Au moins un des deux doit être présent

/**
 * @route POST /api/records/reformulate
 * @desc Reformuler le nom d'un enregistrement - Version simplifiée
 */
router.post('/reformulate',
    validateRequest(recordReformulationSchema),
    asyncHandler(recordsController.reformulateRecord)
);

module.exports = router;
