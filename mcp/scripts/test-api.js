#!/usr/bin/env node

/**
 * Script de test de l'API MCP Shelve
 * Vérifie les endpoints principaux
 */

const axios = require('axios');

const BASE_URL = process.env.MCP_URL || 'http://localhost:3001';
const API_URL = `${BASE_URL}/api`;

console.log('🧪 Test de l\'API MCP Shelve...');
console.log(`📡 URL: ${API_URL}\n`);

async function testEndpoint(name, method, url, data = null) {
  try {
    const config = {
      method,
      url: `${API_URL}${url}`,
      timeout: 10000,
      headers: {
        'Content-Type': 'application/json'
      }
    };

    if (data) {
      config.data = data;
    }

    const response = await axios(config);

    console.log(`✅ ${name}: ${response.status} ${response.statusText}`);
    if (response.data) {
      console.log(`   📊 Réponse: ${JSON.stringify(response.data).substring(0, 100)}...`);
    }
    return true;

  } catch (error) {
    console.log(`❌ ${name}: ${error.message}`);
    if (error.response) {
      console.log(`   📊 Statut: ${error.response.status}`);
      console.log(`   📊 Erreur: ${JSON.stringify(error.response.data).substring(0, 100)}...`);
    }
    return false;
  }
}

async function runTests() {
  const tests = [];

  // Test 1: Health check
  tests.push(await testEndpoint(
    'Health Check',
    'GET',
    '/health'
  ));

  // Test 2: Models list
  tests.push(await testEndpoint(
    'Liste des modèles',
    'GET',
    '/models'
  ));

  // Test 3: Reformulation archivistique
  tests.push(await testEndpoint(
    'Reformulation archivistique',
    'POST',
    '/records/process/title',
    {
      title: "Documents construction école",
      content: "Plans et correspondance relatifs à la construction de l'école primaire entre 1920 et 1925",
      model: "llama3.2",
      options: {
        type: "archival",
        style: "formal",
        maxLength: 120
      }
    }
  ));

  // Test 4: Extraction de mots-clés
  tests.push(await testEndpoint(
    'Extraction mots-clés',
    'POST',
    '/records/process/keywords',
    {
      content: "Ce document traite de l'urbanisme et de l'aménagement du territoire communal avec focus sur les espaces verts et la circulation.",
      model: "llama3.2",
      options: {
        maxKeywords: 10,
        language: "fr"
      }
    }
  ));

  // Test 5: Génération de résumé
  tests.push(await testEndpoint(
    'Génération résumé',
    'POST',
    '/records/process/summary',
    {
      content: "Procès-verbal de la séance du conseil municipal du 15 mars 1965 présidée par le maire M. Dubois. Ordre du jour : budget 1965, travaux voirie, projet bibliothèque municipale.",
      model: "llama3.2",
      options: {
        length: "medium",
        style: "formal"
      }
    }
  ));

  console.log('\n📊 Résultats des tests:');
  const successCount = tests.filter(t => t).length;
  const totalCount = tests.length;
  const successRate = Math.round((successCount / totalCount) * 100);

  console.log(`✅ ${successCount}/${totalCount} tests réussis (${successRate}%)`);

  if (successCount === totalCount) {
    console.log('\n🎉 Tous les tests sont passés ! L\'API fonctionne correctement.');
  } else {
    console.log('\n⚠️  Certains tests ont échoué. Vérifiez la configuration.');
    console.log('\n🔧 Points à vérifier:');
    console.log('   • Le serveur MCP est-il démarré ?');
    console.log('   • Ollama est-il accessible ?');
    console.log('   • Le modèle llama3.2 est-il téléchargé ?');
    console.log('   • La base de données est-elle connectée ?');
    process.exit(1);
  }
}

// Gestion des erreurs globales
process.on('unhandledRejection', (error) => {
  console.error('❌ Erreur non gérée:', error.message);
  process.exit(1);
});

// Lancement des tests
runTests().catch(error => {
  console.error('❌ Erreur lors des tests:', error.message);
  process.exit(1);
});
