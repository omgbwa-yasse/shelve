#!/usr/bin/env node

const fs = require('fs').promises;
const path = require('path');
const { execSync } = require('child_process');

console.log('🚀 Configuration initiale du serveur MCP Shelve\n');

async function setup() {
    try {
        // Vérifier que nous sommes dans le bon répertoire
        const packagePath = path.join(process.cwd(), 'package.json');
        const packageExists = await fs.access(packagePath).then(() => true).catch(() => false);

        if (!packageExists) {
            console.error('❌ Erreur: package.json non trouvé. Êtes-vous dans le répertoire mcp ?');
            process.exit(1);
        }

        console.log('✅ Vérification du répertoire... OK');

        // Créer le fichier .env s'il n'existe pas
        const envPath = path.join(process.cwd(), '.env');
        const envExists = await fs.access(envPath).then(() => true).catch(() => false);

        if (!envExists) {
            const envExamplePath = path.join(process.cwd(), '.env.example');
            const envExampleExists = await fs.access(envExamplePath).then(() => true).catch(() => false);

            if (envExampleExists) {
                const envContent = await fs.readFile(envExamplePath, 'utf8');
                await fs.writeFile(envPath, envContent);
                console.log('✅ Fichier .env créé depuis .env.example');
            } else {
                await createDefaultEnv(envPath);
                console.log('✅ Fichier .env créé avec les valeurs par défaut');
            }
        } else {
            console.log('✅ Fichier .env existe déjà');
        }

        // Créer les répertoires nécessaires
        const directories = [
            'logs',
            'docs/generated',
            'tests/fixtures'
        ];

        for (const dir of directories) {
            const dirPath = path.join(process.cwd(), dir);
            try {
                await fs.mkdir(dirPath, { recursive: true });
                console.log(`✅ Répertoire créé: ${dir}`);
            } catch (error) {
                if (error.code !== 'EEXIST') {
                    console.warn(`⚠️  Impossible de créer le répertoire ${dir}: ${error.message}`);
                }
            }
        }

        // Vérifier Ollama
        console.log('\n🔍 Vérification d\'Ollama...');
        try {
            execSync('ollama --version', { stdio: 'pipe' });
            console.log('✅ Ollama est installé');

            try {
                execSync('ollama list', { stdio: 'pipe' });
                console.log('✅ Ollama est accessible');
            } catch (error) {
                console.log('⚠️  Ollama n\'est pas démarré. Lancez "ollama serve" dans un autre terminal');
            }
        } catch (error) {
            console.log('❌ Ollama n\'est pas installé. Visitez https://ollama.ai pour l\'installer');
        }

        // Vérifier Node.js version
        const nodeVersion = process.version;
        const majorVersion = parseInt(nodeVersion.slice(1).split('.')[0]);

        if (majorVersion >= 16) {
            console.log(`✅ Version Node.js: ${nodeVersion}`);
        } else {
            console.log(`⚠️  Version Node.js ${nodeVersion} détectée. Version 16+ recommandée`);
        }

        // Instructions finales
        console.log('\n🎉 Configuration terminée avec succès !\n');
        console.log('📋 Prochaines étapes :');
        console.log('   1. Configurez vos variables d\'environnement dans .env');
        console.log('   2. Assurez-vous qu\'Ollama est démarré: ollama serve');
        console.log('   3. Téléchargez un modèle: ollama pull llama3.2');
        console.log('   4. Démarrez le serveur: npm run dev');
        console.log('   5. Testez l\'API: http://localhost:3001/api/health\n');

    } catch (error) {
        console.error('❌ Erreur lors de la configuration:', error.message);
        process.exit(1);
    }
}

async function createDefaultEnv(envPath) {
    const defaultEnv = `# Configuration du serveur
PORT=3001
HOST=0.0.0.0
NODE_ENV=development
LOG_LEVEL=debug

# Configuration Ollama
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_TIMEOUT=120000
OLLAMA_DEFAULT_MODEL=llama3.2
OLLAMA_SUMMARY_MODEL=llama3.2
OLLAMA_KEYWORDS_MODEL=llama3.2
OLLAMA_TITLE_MODEL=llama3.2
OLLAMA_ANALYSIS_MODEL=llama3.2
OLLAMA_MAX_TOKENS=2000
OLLAMA_MAX_CONCURRENT=5
OLLAMA_RETRY_ATTEMPTS=3
OLLAMA_RETRY_DELAY=1000

# Configuration base de données
DB_HOST=localhost
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=
DB_DATABASE=shelve
DB_CONNECTION_LIMIT=10
DB_ACQUIRE_TIMEOUT=60000
DB_TIMEOUT=60000

# Configuration sécurité
CORS_ORIGIN=*
SESSION_SECRET=your-super-secret-session-key-change-this-in-production
TRUST_PROXY=false

# Configuration rate limiting
RATE_LIMIT_WINDOW=900000
RATE_LIMIT_MAX=100

# Limites de contenu
MAX_FILE_SIZE=10mb
BODY_LIMIT=10mb

# Configuration logs
LOG_DIR=logs`;

    await fs.writeFile(envPath, defaultEnv);
}

// Exécuter le setup
setup();
