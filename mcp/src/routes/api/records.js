const express = require('express');
const recordsController = require('../../controllers/recordsController');
const { asyncHandler, validateRequest } = require('../../middleware/errorHandler');
const Joi = require('joi');

const router = express.Router();

// Schéma de validation pour la reformulation simplifiée
const recordReformulationSchema = Joi.object({
    id: Joi.string().required(),
    name: Joi.string().required().max(500),
    date: Joi.string().optional(),
    content: Joi.string().optional().max(10000),
    author: Joi.object({
        name: Joi.string().optional()
    }).optional(),
    children: Joi.array().items(
        Joi.object({
            name: Joi.string().optional(),
            date: Joi.string().optional(),
            content: Joi.string().optional()
        })
    ).optional()
});

/**
 * @route POST /api/records/reformulate
 * @desc Reformuler le nom d'un enregistrement - Version simplifiée
 */
router.post('/reformulate',
    validateRequest(recordReformulationSchema),
    asyncHandler(recordsController.reformulateRecord)
);

module.exports = router;
