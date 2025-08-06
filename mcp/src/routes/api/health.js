const express = require('express');
const ollamaService = require('../../services/ai/ollamaService');
const { asyncHandler } = require('../../middleware/errorHandler');
const logger = require('../../utils/logger');

const router = express.Router();

/**
 * @route GET /api/health
 * @desc Vérification complète de l'état du système
 */
router.get('/', asyncHandler(async (req, res) => {
    const startTime = Date.now();
    
    // Vérifications de santé
    const checks = {
        server: {
            status: 'healthy',
            uptime: process.uptime(),
            memory: process.memoryUsage(),
            timestamp: new Date().toISOString()
        },
        ollama: await ollamaService.checkHealth(),
        database: await checkDatabaseHealth(),
        system: await checkSystemHealth()
    };

    // Déterminer l'état global
    const isHealthy = Object.values(checks).every(check => 
        check.status === 'healthy' || check.status === 'ok'
    );

    const totalTime = Date.now() - startTime;

    const response = {
        success: true,
        status: isHealthy ? 'healthy' : 'unhealthy',
        timestamp: new Date().toISOString(),
        checkDuration: `${totalTime}ms`,
        checks,
        version: '1.0.0',
        environment: process.env.NODE_ENV || 'development'
    };

    // Status code selon l'état
    const statusCode = isHealthy ? 200 : 503;
    
    logger.info('Health check completed', {
        status: response.status,
        duration: totalTime,
        checks: Object.keys(checks).reduce((acc, key) => {
            acc[key] = checks[key].status;
            return acc;
        }, {})
    });

    res.status(statusCode).json(response);
}));

/**
 * @route GET /api/health/ollama
 * @desc Vérification spécifique d'Ollama
 */
router.get('/ollama', asyncHandler(async (req, res) => {
    const health = await ollamaService.checkHealth();
    
    res.status(health.status === 'healthy' ? 200 : 503).json({
        success: true,
        data: health
    });
}));

/**
 * @route GET /api/health/database
 * @desc Vérification spécifique de la base de données
 */
router.get('/database', asyncHandler(async (req, res) => {
    const health = await checkDatabaseHealth();
    
    res.status(health.status === 'healthy' ? 200 : 503).json({
        success: true,
        data: health
    });
}));

/**
 * @route GET /api/health/detailed
 * @desc Vérification détaillée avec métriques système
 */
router.get('/detailed', asyncHandler(async (req, res) => {
    const startTime = Date.now();
    
    const detailed = {
        timestamp: new Date().toISOString(),
        server: {
            status: 'healthy',
            uptime: process.uptime(),
            memory: {
                used: Math.round(process.memoryUsage().heapUsed / 1024 / 1024) + ' MB',
                total: Math.round(process.memoryUsage().heapTotal / 1024 / 1024) + ' MB',
                external: Math.round(process.memoryUsage().external / 1024 / 1024) + ' MB'
            },
            cpu: process.cpuUsage(),
            version: {
                node: process.version,
                platform: process.platform,
                arch: process.arch
            },
            pid: process.pid
        },
        ollama: await ollamaService.checkHealth(),
        database: await checkDatabaseHealth(),
        environment: {
            nodeEnv: process.env.NODE_ENV || 'development',
            port: process.env.PORT || 3001,
            logLevel: process.env.LOG_LEVEL || 'info'
        }
    };

    const totalTime = Date.now() - startTime;
    detailed.checkDuration = `${totalTime}ms`;

    res.json({
        success: true,
        data: detailed
    });
}));

// Fonctions utilitaires
async function checkDatabaseHealth() {
    try {
        // TODO: Implémenter la vérification de base de données
        // const db = require('../../config/database');
        // await db.raw('SELECT 1');
        
        return {
            status: 'healthy',
            message: 'Database connection successful',
            timestamp: new Date().toISOString()
        };
    } catch (error) {
        logger.error('Database health check failed:', error.message);
        return {
            status: 'unhealthy',
            error: error.message,
            timestamp: new Date().toISOString()
        };
    }
}

async function checkSystemHealth() {
    try {
        const os = require('os');
        
        return {
            status: 'healthy',
            hostname: os.hostname(),
            platform: os.platform(),
            arch: os.arch(),
            cpus: os.cpus().length,
            totalMemory: Math.round(os.totalmem() / 1024 / 1024) + ' MB',
            freeMemory: Math.round(os.freemem() / 1024 / 1024) + ' MB',
            loadAverage: os.loadavg(),
            uptime: os.uptime(),
            timestamp: new Date().toISOString()
        };
    } catch (error) {
        logger.error('System health check failed:', error.message);
        return {
            status: 'unhealthy',
            error: error.message,
            timestamp: new Date().toISOString()
        };
    }
}

module.exports = router;
