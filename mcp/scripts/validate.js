#!/usr/bin/env node

/**
 * Script de validation du serveur MCP Shelve
 * Vérifie que tous les composants sont prêts pour le déploiement
 */

const fs = require('fs');
const path = require('path');

console.log('🔍 Validation du serveur MCP Shelve...\n');

const checks = [];

// 1. Vérification des fichiers essentiels
const essentialFiles = [
  'package.json',
  '.env.example',
  'src/index.js',
  'src/config/server.js',
  'src/services/ai/ollamaService.js',
  'src/services/processing/titleService.js',
  'src/controllers/recordsController.js',
  'templates/title/archival.txt',
  'docs/QUICK_START.md'
];

essentialFiles.forEach(file => {
  const exists = fs.existsSync(file);
  checks.push({
    name: `Fichier ${file}`,
    status: exists ? 'OK' : 'MANQUANT',
    success: exists
  });
});

// 2. Vérification de la structure des dossiers
const directories = [
  'src/controllers',
  'src/services/ai',
  'src/services/processing',
  'src/routes/api',
  'src/middleware',
  'templates/title',
  'docs',
  'tests',
  'logs'
];

directories.forEach(dir => {
  const exists = fs.existsSync(dir);
  checks.push({
    name: `Dossier ${dir}`,
    status: exists ? 'OK' : 'MANQUANT',
    success: exists
  });
});

// 3. Vérification du package.json
try {
  const pkg = JSON.parse(fs.readFileSync('package.json', 'utf8'));
  const requiredDeps = ['express', 'axios', 'winston', 'joi', 'cors'];
  const requiredDevDeps = ['nodemon', 'jest'];

  const hasDeps = requiredDeps.every(dep => pkg.dependencies && pkg.dependencies[dep]);
  const hasDevDeps = requiredDevDeps.every(dep => pkg.devDependencies && pkg.devDependencies[dep]);

  checks.push({
    name: 'Dépendances de production',
    status: hasDeps ? 'OK' : 'MANQUANTES',
    success: hasDeps
  });

  checks.push({
    name: 'Dépendances de développement',
    status: hasDevDeps ? 'OK' : 'MANQUANTES',
    success: hasDevDeps
  });

  const hasScripts = pkg.scripts && pkg.scripts.start && pkg.scripts.dev;
  checks.push({
    name: 'Scripts npm',
    status: hasScripts ? 'OK' : 'MANQUANTS',
    success: hasScripts
  });

} catch (error) {
  checks.push({
    name: 'package.json valide',
    status: 'ERREUR',
    success: false,
    error: error.message
  });
}

// 4. Vérification des templates
const templates = ['archival.txt', 'reformulation.txt', 'generation.txt'];
templates.forEach(template => {
  const templatePath = path.join('templates/title', template);
  const exists = fs.existsSync(templatePath);
  if (exists) {
    try {
      const content = fs.readFileSync(templatePath, 'utf8');
      const hasVariables = content.includes('{{') && content.includes('}}');
      checks.push({
        name: `Template ${template}`,
        status: hasVariables ? 'OK' : 'FORMAT INVALIDE',
        success: hasVariables
      });
    } catch (error) {
      checks.push({
        name: `Template ${template}`,
        status: 'ERREUR LECTURE',
        success: false
      });
    }
  }
});

// 5. Affichage des résultats
console.log('📋 Résultats de la validation:\n');

const grouped = {
  success: checks.filter(c => c.success),
  failed: checks.filter(c => !c.success)
};

// Succès
if (grouped.success.length > 0) {
  console.log('✅ Éléments validés:');
  grouped.success.forEach(check => {
    console.log(`   ✓ ${check.name}`);
  });
  console.log('');
}

// Échecs
if (grouped.failed.length > 0) {
  console.log('❌ Problèmes détectés:');
  grouped.failed.forEach(check => {
    console.log(`   ✗ ${check.name}: ${check.status}`);
    if (check.error) {
      console.log(`     Erreur: ${check.error}`);
    }
  });
  console.log('');
}

// Statistiques
const successRate = Math.round((grouped.success.length / checks.length) * 100);
console.log(`📊 Validation: ${grouped.success.length}/${checks.length} réussies (${successRate}%)\n`);

// Instructions finales
if (grouped.failed.length === 0) {
  console.log('🎉 Validation réussie ! Le serveur MCP est prêt.');
  console.log('\n📖 Prochaines étapes:');
  console.log('   1. npm install');
  console.log('   2. Configurer .env');
  console.log('   3. Démarrer Ollama');
  console.log('   4. npm run dev');
  console.log('\n📚 Consultez docs/QUICK_START.md pour les détails');
} else {
  console.log('⚠️  Validation incomplète. Veuillez corriger les problèmes détectés.');
  process.exit(1);
}
