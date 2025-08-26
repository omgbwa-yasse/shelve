<!DOCTYPE html>
<html>
<head>
    <title>Test Batch Handler</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Test du système de parapheur</h1>

    <button id="testListBatches">Tester la liste des parapheurs</button>
    <button id="testCreateBatch">Tester la création d'un parapheur</button>

    <div id="results"></div>

    <script>
        // Configuration des headers AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#testListBatches').click(function() {
            $.ajax({
                url: '/batch-handler/list',
                method: 'GET',
                success: function(response) {
                    $('#results').html('<h3>Liste des parapheurs:</h3><pre>' + JSON.stringify(response, null, 2) + '</pre>');
                },
                error: function(xhr) {
                    $('#results').html('<h3>Erreur:</h3><pre>' + xhr.responseText + '</pre>');
                }
            });
        });

        $('#testCreateBatch').click(function() {
            $.ajax({
                url: '/batch-handler/create',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    code: 'TEST001',
                    name: 'Test Parapheur',
                    mail_ids: [1, 2, 3] // IDs de test
                }),
                success: function(response) {
                    $('#results').html('<h3>Création de parapheur:</h3><pre>' + JSON.stringify(response, null, 2) + '</pre>');
                },
                error: function(xhr) {
                    $('#results').html('<h3>Erreur:</h3><pre>' + xhr.responseText + '</pre>');
                }
            });
        });
    </script>
</body>
</html>
