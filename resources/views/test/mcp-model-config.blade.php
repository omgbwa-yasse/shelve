<!DOCTYPE html>
<html>
<head>
    <title>Test MCP Model Configuration</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test MCP Model Configuration</h1>

    <div id="models-info">
        <h2>Modèles par défaut configurés :</h2>
        <div id="models-list">Chargement...</div>
    </div>

    <div id="test-section">
        <h2>Test des actions MCP :</h2>
        <button onclick="testEnrich()">Test Enrich</button>
        <button onclick="testExtractKeywords()">Test Extract Keywords</button>
        <button onclick="testClassify()">Test Classify</button>
        <div id="test-results"></div>
    </div>

    <script>
        const apiBaseUrl = '/mcp';

        // Charger les modèles par défaut au chargement de la page
        window.onload = function() {
            loadDefaultModels();
        };

        async function loadDefaultModels() {
            try {
                const response = await fetch(`${apiBaseUrl}/models/defaults`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    displayModels(data.models);
                } else {
                    document.getElementById('models-list').innerHTML = 'Erreur lors du chargement des modèles';
                }
            } catch (error) {
                document.getElementById('models-list').innerHTML = 'Erreur de connexion: ' + error.message;
            }
        }

        function displayModels(models) {
            const modelsList = document.getElementById('models-list');
            let html = '<ul>';
            for (const [type, model] of Object.entries(models)) {
                html += `<li><strong>${type}:</strong> ${model}</li>`;
            }
            html += '</ul>';
            modelsList.innerHTML = html;
        }

        async function testEnrich() {
            await testMcpAction('enrich', 1); // Utilise l'ID de record 1
        }

        async function testExtractKeywords() {
            await testMcpAction('extract-keywords', 1);
        }

        async function testClassify() {
            await testMcpAction('classify', 1);
        }

        async function testMcpAction(action, recordId) {
            try {
                const resultsDiv = document.getElementById('test-results');
                resultsDiv.innerHTML = `Test en cours: ${action}...`;

                const response = await fetch(`${apiBaseUrl}/${action}/${recordId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    resultsDiv.innerHTML += `<br><strong>Succès ${action}:</strong> ${JSON.stringify(data, null, 2)}`;
                } else {
                    resultsDiv.innerHTML += `<br><strong>Erreur ${action}:</strong> ${data.error || 'Erreur inconnue'}`;
                }
            } catch (error) {
                document.getElementById('test-results').innerHTML += `<br><strong>Erreur de connexion ${action}:</strong> ${error.message}`;
            }
        }
    </script>
</body>
</html>
