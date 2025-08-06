module.exports = {
    // Configuration de la base de données
    host: process.env.DB_HOST || 'localhost',
    port: process.env.DB_PORT || 3306,
    user: process.env.DB_USERNAME || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_DATABASE || 'shelve',
    
    // Options de connexion
    connectionLimit: parseInt(process.env.DB_CONNECTION_LIMIT) || 10,
    acquireTimeout: parseInt(process.env.DB_ACQUIRE_TIMEOUT) || 60000,
    timeout: parseInt(process.env.DB_TIMEOUT) || 60000,
    
    // Configuration Knex
    client: 'mysql2',
    pool: {
        min: 2,
        max: 10
    },
    migrations: {
        tableName: 'mcp_migrations',
        directory: '../../migrations'
    }
};
