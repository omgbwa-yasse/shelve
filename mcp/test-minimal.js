#!/usr/bin/env node

/**
 * Test minimal pour l'endpoint de reformulation
 */

const axios = require('axios');

const API_URL = 'http://localhost:3001/api/records/reformulate';

console.log('🎯 Test endpoint de reformulation minimal');
console.log(`📡 URL: ${API_URL}\n`);

async function testReformulation() {
  // Test avec données simples
  const testData = {
    id: "TEST001",
    name: "Documents travaux mairie",
    date: "1920-1925",
    content: "Travaux d'agrandissement de la mairie"
  };

  try {
    console.log('📤 Test de reformulation...');
    console.log('Données:', JSON.stringify(testData, null, 2));

    const response = await axios.post(API_URL, testData, {
      headers: { 'Content-Type': 'application/json' },
      timeout: 30000
    });

    console.log('\n✅ Succès !');
    console.log(`📥 Réponse: ${JSON.stringify(response.data, null, 2)}`);

    // Vérifier le format de la réponse
    if (response.data.id && response.data.new_name) {
      console.log('\n🎉 Format de réponse correct !');
      console.log(`   ID: ${response.data.id}`);
      console.log(`   Nouveau nom: "${response.data.new_name}"`);
      return true;
    } else {
      console.log('\n❌ Format de réponse incorrect');
      return false;
    }

  } catch (error) {
    console.log('\n❌ Erreur:', error.message);

    if (error.code === 'ECONNREFUSED') {
      console.log('\n💡 Le serveur n\'est pas démarré.');
      console.log('   Démarrez-le avec: npm run dev');
    } else if (error.response) {
      console.log(`   Statut: ${error.response.status}`);
      console.log(`   Erreur: ${JSON.stringify(error.response.data, null, 2)}`);
    }

    return false;
  }
}

// Tests multiples
async function testMultiple() {
  const tests = [
    {
      id: "TEST002",
      name: "Registre personnel",
      content: "Registre du personnel de la mairie"
    },
    {
      id: "TEST003",
      name: "Plans école",
      date: "1958",
      author: { name: "Architecte Martin" },
      children: [
        { name: "Plan général", date: "1958" },
        { name: "Plan détaillé", date: "1959" }
      ]
    }
  ];

  let success = 0;

  for (let i = 0; i < tests.length; i++) {
    console.log(`\n🔄 Test ${i + 1}/${tests.length}`);

    try {
      const response = await axios.post(API_URL, tests[i], {
        headers: { 'Content-Type': 'application/json' },
        timeout: 30000
      });

      console.log(`✅ "${tests[i].name}" → "${response.data.new_name}"`);
      success++;
    } catch (error) {
      console.log(`❌ Échec: ${error.message}`);
    }
  }

  console.log(`\n📊 Résultats: ${success}/${tests.length} tests réussis`);
  return success === tests.length;
}

async function runTests() {
  console.log('═'.repeat(50));
  console.log('        TEST ENDPOINT REFORMULATION MINIMAL');
  console.log('═'.repeat(50));

  // Test principal
  const mainTest = await testReformulation();

  if (mainTest) {
    // Tests supplémentaires
    const multipleTest = await testMultiple();

    if (multipleTest) {
      console.log('\n🎉 Tous les tests réussis !');
      console.log('\n✨ L\'endpoint fonctionne parfaitement.');
      console.log('\n📖 Utilisation:');
      console.log('   POST /api/records/reformulate');
      console.log('   { "id": "...", "name": "...", ... }');
      console.log('   → { "id": "...", "new_name": "..." }');
    }
  }
}

runTests().catch(console.error);
