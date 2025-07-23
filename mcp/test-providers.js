// Script de test pour les providers IA
const multiProviderAiService = require('./src/services/multi-provider-ai.service');
const configService = require('./src/services/config.service');

async function testProviders() {
  console.log('🧪 Test des providers IA multi-providers\n');

  try {
    // 1. Test de la configuration
    console.log('📋 1. Récupération de la configuration...');
    const config = await multiProviderAiService.getProvidersConfig();
    console.log(`   Provider par défaut: ${config.defaultProvider}`);
    console.log(`   Modèle par défaut: ${config.defaultModel}`);
    console.log(`   Timeout: ${config.requestTimeout}ms\n`);

    // 2. Test du statut des providers
    console.log('🔍 2. Vérification du statut des providers...');
    const status = await multiProviderAiService.checkProvidersStatus();

    for (const [name, info] of Object.entries(status.providers)) {
      const symbol = info.available ? '✅' : '❌';
      console.log(`   ${symbol} ${name}: ${info.available ? 'Disponible' : info.error || 'Indisponible'}`);
      if (info.available && info.models.length > 0) {
        console.log(`      Modèles: ${info.models.slice(0, 3).join(', ')}${info.models.length > 3 ? '...' : ''}`);
      }
    }
    console.log();

    // 3. Test de génération avec le provider par défaut
    console.log('🎯 3. Test de génération avec le provider par défaut...');
    const testPrompt = 'Bonjour, comment allez-vous ? Répondez en une phrase courte.';

    const response = await multiProviderAiService.generate(
      testPrompt,
      null, // Modèle par défaut
      { temperature: 0.5, max_tokens: 50 }
    );

    if (response.success) {
      console.log(`   ✅ Génération réussie avec ${response.provider}`);
      console.log(`   Modèle: ${response.model}`);
      console.log(`   Réponse: "${response.content.trim()}"`);
      console.log(`   Stats: ${JSON.stringify(response.stats)}`);
    } else {
      console.log(`   ❌ Échec de génération: ${response.error}`);
    }
    console.log();

    // 4. Test avec un provider spécifique (si disponible)
    const availableProviders = Object.keys(status.providers).filter(
      name => status.providers[name].available && name !== config.defaultProvider
    );

    if (availableProviders.length > 0) {
      const testProvider = availableProviders[0];
      console.log(`🔄 4. Test avec un provider spécifique: ${testProvider}...`);

      const specificResponse = await multiProviderAiService.generate(
        testPrompt,
        null,
        { temperature: 0.5, max_tokens: 50 },
        testProvider
      );

      if (specificResponse.success) {
        console.log(`   ✅ Génération réussie avec ${testProvider}`);
        console.log(`   Réponse: "${specificResponse.content.trim()}"`);
      } else {
        console.log(`   ❌ Échec avec ${testProvider}: ${specificResponse.error}`);
      }
    } else {
      console.log('🔄 4. Aucun provider alternatif disponible pour les tests');
    }
    console.log();

    // 5. Test des fonctions spécialisées
    console.log('⚡ 5. Test des fonctions spécialisées...');

    const testText = 'Ceci est un document d\'archive concernant la gestion des ressources humaines de l\'entreprise ABC pour l\'année 2023. Il contient des informations sur les recrutements, les formations et les évaluations du personnel.';

    // Test résumé
    console.log('   📝 Test de génération de résumé...');
    const summaryResponse = await multiProviderAiService.generateSummary(testText);
    if (summaryResponse.success) {
      console.log(`   ✅ Résumé généré: "${summaryResponse.content.trim()}"`);
    } else {
      console.log(`   ❌ Échec du résumé: ${summaryResponse.error}`);
    }

    // Test mots-clés
    console.log('   🔤 Test d\'extraction de mots-clés...');
    const keywordsResponse = await multiProviderAiService.extractKeywords(testText, 5);
    if (keywordsResponse.success) {
      console.log(`   ✅ Mots-clés extraits: "${keywordsResponse.content.trim()}"`);
    } else {
      console.log(`   ❌ Échec de l'extraction: ${keywordsResponse.error}`);
    }

    console.log('\n🎉 Test des providers terminé avec succès !');

  } catch (error) {
    console.error('❌ Erreur pendant les tests:', error.message);
    console.error(error.stack);
  }
}

// Exécuter les tests si le script est lancé directement
if (require.main === module) {
  testProviders().then(() => {
    console.log('\n✨ Tous les tests sont terminés.');
    process.exit(0);
  }).catch(error => {
    console.error('\n💥 Erreur fatale:', error);
    process.exit(1);
  });
}

module.exports = { testProviders };
