#!/usr/bin/env node

/**
 * Test spécifique pour l'endpoint de reformulation simplifiée
 */

const axios = require('axios');

const BASE_URL = process.env.MCP_URL || 'http://localhost:3001';
const API_URL = `${BASE_URL}/api`;

console.log('🧪 Test de reformulation simplifiée MCP Shelve...');
console.log(`📡 URL: ${API_URL}\n`);

async function testReformulation() {
  try {
    // Exemple de données comme spécifié
    const testData = {
      id: "REC001",
      name: "Documents travaux mairie",
      date: "1920-1925", 
      content: "Dossier contenant les plans, devis et correspondance relatifs aux travaux d'agrandissement de la mairie entre 1920 et 1925",
      author: {
        name: "Architecte Dubois"
      },
      children: [
        {
          name: "Plans architecte",
          date: "1920",
          content: "Plans de l'extension de la mairie réalisés par l'architecte Dubois"
        },
        {
          name: "Devis entrepreneur", 
          date: "1921",
          content: "Devis détaillé des travaux établi par l'entreprise Martin"
        },
        {
          name: "Correspondance administrative",
          date: "1920-1925", 
          content: "Échanges entre la mairie et les différents intervenants du projet"
        }
      ]
    };

    console.log('📤 Envoi de la requête de reformulation...');
    console.log('Données d\'entrée:', JSON.stringify(testData, null, 2));
    console.log('');

    const response = await axios.post(`${API_URL}/records/reformulate`, testData, {
      headers: {
        'Content-Type': 'application/json'
      },
      timeout: 30000 // 30 secondes pour l'IA
    });

    console.log('✅ Reformulation réussie !');
    console.log('📤 Réponse:');
    console.log(`   ID: ${response.data.id}`);
    console.log(`   Nom original: "${testData.name}"`);
    console.log(`   Nouveau nom: "${response.data.new_name}"`);
    
    return true;

  } catch (error) {
    console.log('❌ Erreur lors de la reformulation:', error.message);
    
    if (error.response) {
      console.log(`   📊 Statut: ${error.response.status}`);
      console.log(`   📊 Erreur: ${JSON.stringify(error.response.data, null, 2)}`);
    }
    
    return false;
  }
}

async function testHealthFirst() {
  try {
    console.log('🔍 Vérification de l\'état du serveur...');
    const response = await axios.get(`${API_URL}/health`, { timeout: 5000 });
    console.log('✅ Serveur disponible\n');
    return true;
  } catch (error) {
    console.log('❌ Serveur non disponible:', error.message);
    console.log('   Assurez-vous que le serveur MCP est démarré\n');
    return false;
  }
}

// Test d'exemples multiples
async function testMultipleExamples() {
  const examples = [
    {
      id: "REC002",
      name: "Registre personnel enseignant",
      date: "1945-1975",
      content: "Registre du personnel enseignant de l'école primaire communale",
      author: { name: "Secrétariat mairie" }
    },
    {
      id: "REC003", 
      name: "Dossier construction école",
      content: "Travaux de construction de la nouvelle école primaire",
      children: [
        { name: "Permis de construire", date: "1958" },
        { name: "Plans", date: "1958-1959" }
      ]
    },
    {
      id: "REC004",
      name: "Correspondance préfet",
      date: "1960-1965",
      content: "Correspondance échangée avec la préfecture concernant les affaires communales"
    }
  ];

  console.log('📋 Test avec plusieurs exemples...\n');
  
  let successCount = 0;
  
  for (let i = 0; i < examples.length; i++) {
    const example = examples[i];
    console.log(`🔄 Test ${i + 1}/${examples.length} - ID: ${example.id}`);
    
    try {
      const response = await axios.post(`${API_URL}/records/reformulate`, example, {
        headers: { 'Content-Type': 'application/json' },
        timeout: 30000
      });
      
      console.log(`   ✅ "${example.name}" → "${response.data.new_name}"`);
      successCount++;
      
    } catch (error) {
      console.log(`   ❌ Erreur: ${error.message}`);
    }
    
    console.log('');
  }
  
  console.log(`📊 Résultats: ${successCount}/${examples.length} reformulations réussies`);
  return successCount === examples.length;
}

// Exécution des tests
async function runAllTests() {
  console.log('═══════════════════════════════════════════════════════');
  console.log('           TEST REFORMULATION SIMPLIFIÉE MCP          ');
  console.log('═══════════════════════════════════════════════════════\n');
  
  // 1. Vérifier l'état du serveur
  const healthOk = await testHealthFirst();
  if (!healthOk) {
    process.exit(1);
  }
  
  // 2. Test simple
  console.log('🎯 Test de reformulation simple...\n');
  const simpleOk = await testReformulation();
  
  if (simpleOk) {
    console.log('\n' + '─'.repeat(50) + '\n');
    
    // 3. Tests multiples
    const multipleOk = await testMultipleExamples();
    
    if (multipleOk) {
      console.log('\n🎉 Tous les tests sont passés ! L\'endpoint de reformulation fonctionne parfaitement.');
      console.log('\n📖 Utilisation:');
      console.log(`   POST ${API_URL}/records/reformulate`);
      console.log('   Content-Type: application/json');
      console.log('   Body: { id, name, date?, content?, author?, children? }');
      console.log('   Response: { id, new_name }');
    } else {
      console.log('\n⚠️  Certains tests ont échoué.');
      process.exit(1);
    }
  } else {
    console.log('\n❌ Le test principal a échoué.');
    process.exit(1);
  }
}

// Gestion des erreurs
process.on('unhandledRejection', (error) => {
  console.error('❌ Erreur non gérée:', error.message);
  process.exit(1);
});

// Lancement
runAllTests().catch(error => {
  console.error('❌ Erreur lors des tests:', error.message);
  process.exit(1);
});
