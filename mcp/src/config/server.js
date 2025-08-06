module.exports = {
    // Configuration du serveur Express
    port: process.env.PORT || 3001,
    host: process.env.HOST || '0.0.0.0',
    
    // CORS
    cors: {
        origin: process.env.CORS_ORIGIN || '*',
        methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        allowedHeaders: ['Content-Type', 'Authorization'],
        credentials: true
    },
    
    // Sécurité
    trustProxy: process.env.TRUST_PROXY || false,
    
    // Limites
    bodyLimit: process.env.BODY_LIMIT || '10mb',
    
    // Session
    session: {
        secret: process.env.SESSION_SECRET || 'shelve-mcp-secret-key',
        resave: false,
        saveUninitialized: false,
        cookie: {
            secure: process.env.NODE_ENV === 'production',
            httpOnly: true,
            maxAge: 24 * 60 * 60 * 1000 // 24 heures
        }
    }
};
