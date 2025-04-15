<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chariot de documents</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <h2 class="mb-4">Chariot de documents</h2>

        <div class="card mb-4">
            <div class="card-body">
                <div class="cart-options">
                    <ul class="list-group" id="listGroup">
                        <li class="list-group-item" id="listItem">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="option1">
                                <label class="form-check-label" for="option1">Option 1</label>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <div>
                <button class="btn btn-outline-secondary" id="emptyCart">Vider</button>
            </div>
            <div>
                <button class="btn btn-primary me-2" id="addToCart">Ajouter les reçus</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButton = document.getElementById('addToCart');
            const emptyCartButton = document.getElementById('emptyCart');
            const listGroup = document.getElementById('listGroup');

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function fetchDollies() {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '/api/dollies', true);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        // Vider la liste actuelle
                        listGroup.innerHTML = '';

                        // Remplir la liste avec les données reçues
                        response.forEach(function(dolly, index) {
                            const li = document.createElement('li');
                            li.className = 'list-group-item';
                            li.innerHTML = `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="option${index}"
                                        data-dolly-id="${dolly.id}" data-mail-transaction-id="${dolly.mail_transaction_id || ''}">
                                    <label class="form-check-label" for="option${index}">${dolly.name || 'Reçu #' + dolly.id}</label>
                                </div>
                            `;
                            listGroup.appendChild(li);
                        });

                        if (response.length === 0) {
                            listGroup.innerHTML = '<li class="list-group-item">Aucun reçu disponible</li>';
                        }
                    } else {
                        console.error('Erreur lors de la récupération des dollies:', xhr.status);
                    }
                };

                xhr.onerror = function() {
                    console.error('Une erreur réseau s\'est produite.');
                };

                xhr.send();
            }

            // Fetch dollies on page load
            fetchDollies();

            // Gérer le bouton "Vider"
            emptyCartButton.addEventListener('click', function() {
                listGroup.innerHTML = '<li class="list-group-item">Aucun reçu disponible</li>';
            });



            // Gérer le bouton "Ajouter les reçus"
            addToCartButton.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.form-check-input:checked');

                if (checkboxes.length === 0) {
                    alert('Veuillez sélectionner au moins un document');
                    return;
                }

                let successCount = 0;
                let errorCount = 0;
                let processedCount = 0;

                checkboxes.forEach(function(checkbox) {
                    const dollyId = checkbox.getAttribute('data-dolly-id');
                    const mailTransactionId = checkbox.getAttribute('data-mail-transaction-id');

                    if (!dollyId || !mailTransactionId) {
                        errorCount++;
                        processedCount++;
                        checkIfComplete();
                        return;
                    }

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '/api/dolly-mail-transactions', true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

                    xhr.onload = function() {
                        processedCount++;

                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                successCount++;
                            } else {
                                errorCount++;
                            }
                        } else {
                            errorCount++;
                        }

                        checkIfComplete();
                    };

                    xhr.onerror = function() {
                        processedCount++;
                        errorCount++;
                        checkIfComplete();
                    };

                    xhr.send(JSON.stringify({
                        dolly_id: dollyId,
                        mail_transaction_id: mailTransactionId
                    }));
                });



                function checkIfComplete() {
                    if (processedCount === checkboxes.length) {
                        if (errorCount === 0) {
                            alert(`${successCount} document(s) ajouté(s) au chariot avec succès`);
                            window.close(); // Fermer la fenêtre si tout est ajouté avec succès
                        } else {
                            alert(`${successCount} document(s) ajouté(s) au chariot, ${errorCount} erreur(s)`);
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
