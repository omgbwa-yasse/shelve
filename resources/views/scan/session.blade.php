@extends('layouts.app')
@section('content')
<style>
/* Styles pour les manipulations d'images */
.processing {
    opacity: 0.7;
    cursor: progress;
}

/* Style pour le bouton Source */
#refreshScanners {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    transition: all 0.3s ease;
}

#refreshScanners:hover {
    background-color: #0d6efd;
    color: white;
}

#refreshScanners i {
    margin-right: 5px;
}

/* Animation pour la détection des scanners */
@keyframes scanning {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.05); }
    100% { opacity: 1; transform: scale(1); }
}

.scanning-animation {
    animation: scanning 1.5s infinite;
}

/* Animation de balayage pour la détection des scanners */
@keyframes scanBeam {
    0% { transform: translateY(-100%); opacity: 0; }
    10% { opacity: 0.8; }
    90% { opacity: 0.8; }
    100% { transform: translateY(100%); opacity: 0; }
}

.scan-animation-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    pointer-events: none;
}

.scan-beam {
    position: absolute;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, rgba(0,123,255,0) 0%, rgba(0,123,255,1) 50%, rgba(0,123,255,0) 100%);
    animation: scanBeam 2s linear infinite;
    box-shadow: 0 0 10px #0d6efd, 0 0 20px #0d6efd;
}

#scanned-image {
    transform-origin: center;
    position: relative; /* Pour le positionnement des éléments de recadrage */
}

.manipulation-overlay, .filter-selector {
    animation: fadeInDown 0.3s ease;
}

.filter-option {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.filter-option:hover {
    transform: scale(1.05);
}

/* Styles pour le recadrage avancé */
.crop-overlay {
    position: absolute;
    pointer-events: none;
}

.crop-point {
    user-select: none;
    touch-action: none;
}

.crop-point:hover, .crop-point:active {
    background-color: rgba(0, 123, 255, 1) !important;
    transform: translate(-50%, -50%) scale(1.2) !important;
}

.crop-controls {
    animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <span class="d-flex align-items-center justify-content-center bg-white text-primary rounded-circle mr-2" style="width: 38px; height: 38px;">
                <i class="bi bi-scanner" style="font-size: 1.7rem;"></i>
            </span>
            <span class="ml-1" style="font-weight: 500; font-size: 1.15rem;">Numérisation de documents</span>
        </div>
        <div class="card-body p-3">
            <div class="d-flex align-items-center flex-wrap">
                <!-- Sélection du scanner -->
                <div class="input-group me-3 mb-2" style="width: auto;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-hdd-network" title="Scanner"></i>
                        </span>
                    </div>
                    <select id="scannerSelect" class="form-control" style="width: 200px;">
                        <option value="" selected disabled>Choisir un scanner</option>
                        <!-- Les scanners seront chargés dynamiquement -->
                    </select>
                    <button id="refreshScanners" class="btn btn-outline-primary" title="Détecter les scanners">
                        <i class="bi bi-search"></i> Source
                    </button>
                </div>

                <!-- Choix de la résolution -->
                <div class="input-group me-3 mb-2" style="width: auto;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-aspect-ratio" title="Résolution"></i>
                        </span>
                    </div>
                    <select id="scanResolution" class="form-control" style="width: 150px;">
                        <option value="100">100 dpi (basse)</option>
                        <option value="300" selected>300 dpi (standard)</option>
                        <option value="600">600 dpi (haute)</option>
                        <option value="1200">1200 dpi (très haute)</option>
                    </select>
                </div>

                <!-- Format de sortie -->
                <div class="input-group me-3 mb-2" style="width: auto;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-file-earmark" title="Format"></i>
                        </span>
                    </div>
                    <select id="scanFormat" class="form-control" style="width: 120px;">
                        <option value="pdf" selected>PDF</option>
                        <option value="jpg">JPG</option>
                        <option value="png">PNG</option>
                        <option value="tiff">TIFF</option>
                    </select>
                </div>

                <!-- Mode couleur -->
                <div class="input-group me-3 mb-2" style="width: auto;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-palette" title="Couleur"></i>
                        </span>
                    </div>
                    <select id="scanColorMode" class="form-control" style="width: 150px;">
                        <option value="color" selected>Couleur</option>
                        <option value="grayscale">Niveaux de gris</option>
                        <option value="bw">Noir et blanc</option>
                    </select>
                </div>

                <!-- Recto/verso -->
                <div class="input-group me-3 mb-2" style="width: auto;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-book" title="Recto/Verso"></i>
                        </span>
                    </div>
                    <select id="scanSides" class="form-control" style="width: 120px;">
                        <option value="single" selected>Recto</option>
                        <option value="duplex">Recto/Verso</option>
                    </select>
                </div>

                <!-- Bouton de démarrage -->
                <button id="startScanBtn" class="btn btn-success mb-2" disabled>
                    <i class="bi bi-camera-fill me-1"></i> Numériser
                </button>


            </div>

            <!-- Options avancées -->
            <div id="advancedOptions" class="mt-3 pt-3 border-top">
                <div class="d-flex flex-wrap">
                    <!-- Taille de la page -->
                    <div class="input-group me-3 mb-2" style="width: auto;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-rulers" title="Format papier"></i>
                            </span>
                        </div>
                        <select id="scanPaperSize" class="form-control" style="width: 140px;">
                            <option value="auto" selected>Automatique</option>
                            <option value="a4">A4 (210×297mm)</option>
                            <option value="a3">A3 (297×420mm)</option>
                            <option value="letter">Lettre (216×279mm)</option>
                            <option value="legal">Legal (216×356mm)</option>
                        </select>
                    </div>

                    <!-- Rotation -->
                    <div class="input-group me-3 mb-2" style="width: auto;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-arrow-clockwise" title="Rotation"></i>
                            </span>
                        </div>
                        <select id="scanRotation" class="form-control" style="width: 120px;">
                            <option value="0" selected>0° (Aucune)</option>
                            <option value="90">90° Droite</option>
                            <option value="180">180°</option>
                            <option value="270">90° Gauche</option>
                            <option value="auto">Auto</option>
                        </select>
                    </div>

                    <!-- Contraste -->
                    <div class="input-group me-3 mb-2" style="width: auto;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-circle-half" title="Contraste"></i>
                            </span>
                        </div>
                        <select id="scanContrast" class="form-control" style="width: 120px;">
                            <option value="normal" selected>Normal</option>
                            <option value="high">Élevé</option>
                            <option value="low">Faible</option>
                        </select>
                    </div>

                    <!-- OCR -->
                    <div class="form-check form-switch ms-3 mb-2 d-flex align-items-center">
                        <input class="form-check-input me-2" type="checkbox" id="scanOcr" checked>
                        <label class="form-check-label" for="scanOcr">
                            <i class="bi bi-file-text me-1"></i> OCR (reconnaissance de texte)
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="scan-session-main">
        <div class="row">
            <div class="col-md-8">
                <div id="scan-current-page" class="border rounded p-3 text-center" style="min-height:400px">
                    <div class="text-center py-5">
                        <i class="bi bi-image text-muted" style="font-size: 4.5rem;"></i>
                        <p class="mt-3 text-muted">Sélectionnez un scanner et cliquez sur "Numériser" pour commencer</p>
                    </div>
                </div>
            </div>
            <div class="col-md-1">
                <div class="d-flex flex-column gap-3 justify-content-start align-items-center mt-3">
                    <button class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;" disabled title="Supprimer la page">
                        <i class="bi bi-trash" style="font-size: 1.5rem;"></i>
                    </button>
                    <button class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;" disabled title="Rotation 90° droite">
                        <i class="bi bi-arrow-clockwise" style="font-size: 1.5rem;"></i>
                    </button>
                    <button class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;" disabled title="Rotation 90° gauche">
                        <i class="bi bi-arrow-counterclockwise" style="font-size: 1.5rem;"></i>
                    </button>
                    <button class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;" disabled title="Recadrer">
                        <i class="bi bi-crop" style="font-size: 1.5rem;"></i>
                    </button>
                    <button class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;" disabled title="Améliorer le contraste">
                        <i class="bi bi-circle-half" style="font-size: 1.5rem;"></i>
                    </button>
                    <button class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;" disabled title="Ajuster la luminosité">
                        <i class="bi bi-brightness-high" style="font-size: 1.5rem;"></i>
                    </button>
                    <button class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;" disabled title="Redresser">
                        <i class="bi bi-grid-3x3" style="font-size: 1.5rem;"></i>
                    </button>
                    <button class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;" disabled title="Filtres">
                        <i class="bi bi-magic" style="font-size: 1.5rem;"></i>
                    </button>
                    <button class="btn btn-light rounded-circle shadow-sm" style="width: 45px; height: 45px;" disabled title="Annoter">
                        <i class="bi bi-pencil" style="font-size: 1.5rem;"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <h5>Pages numérisées</h5>
                <div id="scan-thumbnails" class="d-flex flex-row-reverse flex-wrap gap-2 overflow-auto" style="max-height:500px">
                    <div class="text-center p-3 w-100">
                        <i class="bi bi-images text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-2 text-muted small">Aucune page numérisée</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
function loadScanSession(sessionId) {
    $('#scan-session-main').html('<div class="text-center py-5"><span class="spinner-border"></span> Chargement...</div>');
    $.get("/scan/pages/"+sessionId, function(data) {
        $('#scan-session-main').html(data);
    });
}
window.loadScanSession = loadScanSession;

$(document).ready(function() {
    // Options avancées maintenant toujours visibles par défaut

    // Activer le bouton de numérisation lorsqu'un scanner est sélectionné
    $('#scannerSelect').change(function() {
        if($(this).val()) {
            $('#startScanBtn').prop('disabled', false);
        } else {
            $('#startScanBtn').prop('disabled', true);
        }
    });

    // Ajouter des gestionnaires d'événements pour les boutons d'action
    $(document).on('click', '.btn[title="Rotation 90° droite"]', function() {
        rotateImage(90);
    });

    $(document).on('click', '.btn[title="Rotation 90° gauche"]', function() {
        rotateImage(-90);
    });

    $(document).on('click', '.btn[title="Recadrer"]', function() {
        cropImage();
    });

    $(document).on('click', '.btn[title="Améliorer le contraste"]', function() {
        enhanceContrast();
    });

    $(document).on('click', '.btn[title="Ajuster la luminosité"]', function() {
        adjustBrightness();
    });

    $(document).on('click', '.btn[title="Redresser"]', function() {
        straightenImage();
    });

    $(document).on('click', '.btn[title="Filtres"]', function() {
        applyFilter();
    });

    $(document).on('click', '.btn[title="Annoter"]', function() {
        annotateImage();
    });

    $(document).on('click', '.btn[title="Supprimer la page"]', function() {
        // Simuler la suppression de la page
        $('#scan-current-page').html(`
            <div class="text-center py-5">
                <i class="bi bi-image text-muted" style="font-size: 4.5rem;"></i>
                <p class="mt-3 text-muted">Page supprimée. Cliquez sur "Numériser" pour scanner une nouvelle page</p>
            </div>
        `);

        // Désactiver tous les boutons d'action
        $('.col-md-1 .btn').prop('disabled', true).removeClass('btn-outline-secondary').addClass('btn-light');

        // Vider les vignettes
        $('#scan-thumbnails').html(`
            <div class="text-center p-3 w-100">
                <i class="bi bi-images text-muted" style="font-size: 2rem;"></i>
                <p class="mt-2 text-muted small">Aucune page numérisée</p>
            </div>
        `);
    });

    // Simuler la numérisation lorsque le bouton est cliqué
    $('#startScanBtn').click(function() {
        const selectedScanner = $('#scannerSelect').val();
        const selectedResolution = $('#scanResolution').val();
        const selectedFormat = $('#scanFormat').val();
        const selectedColorMode = $('#scanColorMode').val();
        const selectedSides = $('#scanSides').val();

        // Options avancées
        const paperSize = $('#scanPaperSize').val();
        const rotation = $('#scanRotation').val();
        const contrast = $('#scanContrast').val();
        const ocrEnabled = $('#scanOcr').is(':checked');

        // Afficher un message de chargement
        $('#scan-current-page').html(`
            <div class="text-center py-5">
                <span class="spinner-border text-primary"></span>
                <p class="mt-3">Numérisation en cours avec le scanner "${$('#scannerSelect option:selected').text()}"...</p>
                <p class="text-muted small">
                    ${selectedColorMode === 'color' ? 'Couleur' :
                      selectedColorMode === 'grayscale' ? 'Niveaux de gris' : 'Noir et blanc'} |
                    ${selectedResolution} dpi | ${selectedFormat.toUpperCase()} |
                    ${selectedSides === 'duplex' ? 'Recto/Verso' : 'Recto'}
                </p>
            </div>
        `);

        // Simuler le temps de numérisation
        setTimeout(function() {
            const sessionId = 'scan-' + Date.now();
            simulateScan(sessionId, selectedResolution);
        }, 2000);
    });
});

// Fonction pour simuler la numérisation
function simulateScan(sessionId, resolution) {
    // Utiliser la résolution passée en paramètre ou récupérer depuis le select
    const scanResolution = resolution || $('#scanResolution').val();
    const format = $('#scanFormat').val();
    const colorMode = $('#scanColorMode').val();
    const sides = $('#scanSides').val();
    const paperSize = $('#scanPaperSize').val();
    const ocr = $('#scanOcr').is(':checked');

    // Texte différent selon le format sélectionné
    let formatText = '';
    switch(format) {
        case 'pdf': formatText = 'Document PDF'; break;
        case 'jpg': formatText = 'Image JPG'; break;
        case 'png': formatText = 'Image PNG'; break;
        case 'tiff': formatText = 'Image TIFF'; break;
    }

    // Modifier l'URL de l'image de prévisualisation selon les options sélectionnées
    let imageUrl = 'https://via.placeholder.com/600x800';

    // Ajouter un texte descriptif basé sur les options
    let colorText = '';
    if (colorMode === 'bw') {
        colorText = 'N%26B';
        imageUrl = imageUrl.replace('placeholder.com', 'placeholder.com/g');
    } else if (colorMode === 'grayscale') {
        colorText = 'Gris';
        imageUrl = imageUrl.replace('placeholder.com', 'placeholder.com/g');
    } else {
        colorText = 'Couleur';
    }

    const imageText = `${formatText}_${colorText}_${scanResolution}dpi${ocr ? '_OCR' : ''}`;
    imageUrl += `?text=${imageText}`;

    // Ici nous simulons une numérisation réussie
    $('#scan-current-page').html(`
        <div class="text-center py-4">
            <img src="${imageUrl}" class="img-fluid border" alt="Document numérisé" id="scanned-image">
            ${ocr ? '<div class="badge bg-info mt-2 mb-2">Reconnaissance de texte (OCR) effectuée</div>' : ''}
        </div>
    `);

    // Activer les boutons d'action
    $('.col-md-1 .btn').prop('disabled', false).removeClass('btn-light').addClass('btn-outline-secondary');

    // Ajouter la vignette
    $('#scan-thumbnails').html(`
        <div class="border rounded p-1 mb-2 position-relative" style="width: 100px;">
            <img src="https://via.placeholder.com/100x140?text=Page+1+${format}" class="img-fluid" alt="Page 1">
            <button class="btn btn-sm btn-danger position-absolute top-0 end-0" style="font-size: 10px; padding: 0.15rem 0.4rem;">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `);

    // Afficher les options post-numérisation
    $('<div class="mt-3 text-center">')
        .append('<button class="btn btn-primary me-2"><i class="bi bi-camera me-1"></i> Numériser une autre page</button>')
        .append('<button class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Terminer et enregistrer</button>')
        .appendTo('#scan-current-page');
}

// Fonction pour simuler la rotation d'image
function rotateImage(degrees) {
    var img = $('#scanned-image');
    if(img.length) {
        var currentRotation = img.data('rotation') || 0;
        var newRotation = currentRotation + degrees;
        img.css({
            'transform': 'rotate(' + newRotation + 'deg)',
            'transition': 'transform 0.3s ease'
        });
        img.data('rotation', newRotation);
    }
}

// Fonction pour le recadrage d'image avec 4 points de contrôle
function cropImage() {
    var img = $('#scanned-image');
    if(img.length) {
        // Initialiser l'interface de recadrage avec 4 points de contrôle
        initializeCropPoints(img);
    }
}

// Fonction pour initialiser les 4 points de recadrage
function initializeCropPoints(img) {
    // Supprimer tout overlay précédent
    $('.crop-overlay, .crop-point').remove();

    // Récupérer les dimensions et la position de l'image
    var imgPos = img.position();
    var imgWidth = img.width();
    var imgHeight = img.height();
    var imgOffset = img.offset();

    // Créer un overlay pour le recadrage
    var cropOverlay = $('<div class="crop-overlay">')
        .css({
            'position': 'absolute',
            'top': imgPos.top,
            'left': imgPos.left,
            'width': imgWidth,
            'height': imgHeight,
            'z-index': 100,
            'box-sizing': 'border-box'
        })
        .appendTo(img.parent());

    // Points initiaux (aux 4 coins de l'image)
    var cropPoints = [
        { x: 0, y: 0 },                       // Haut gauche
        { x: imgWidth, y: 0 },                // Haut droite
        { x: imgWidth, y: imgHeight },        // Bas droite
        { x: 0, y: imgHeight }                // Bas gauche
    ];

    // Créer les 4 points de contrôle draggable
    var pointElements = [];
    var pointLabels = ['TL', 'TR', 'BR', 'BL'];

    for (var i = 0; i < 4; i++) {
        var point = $('<div class="crop-point" data-point="' + i + '">')
            .css({
                'position': 'absolute',
                'width': '20px',
                'height': '20px',
                'background-color': 'rgba(0, 123, 255, 0.8)',
                'border': '2px solid white',
                'border-radius': '50%',
                'cursor': 'move',
                'z-index': 101,
                'transform': 'translate(-50%, -50%)',
                'box-shadow': '0 0 5px rgba(0, 0, 0, 0.5)',
                'top': (imgPos.top + cropPoints[i].y) + 'px',
                'left': (imgPos.left + cropPoints[i].x) + 'px'
            })
            .append('<span style="color:white; font-size:10px; line-height:16px; display:block; text-align:center;">' + pointLabels[i] + '</span>')
            .appendTo(img.parent());

        pointElements.push(point);

        // Rendre le point draggable
        makeDraggable(point, i, cropPoints, img, imgPos);
    }

    // Ajouter des lignes de contour reliant les points
    updateCropLines(cropPoints, cropOverlay, imgPos);

    // Ajouter des contrôles d'action
    var controls = $('<div class="crop-controls">')
        .css({
            'position': 'absolute',
            'bottom': '10px',
            'left': '10px',
            'right': '10px',
            'background-color': 'rgba(255, 255, 255, 0.9)',
            'padding': '10px',
            'border-radius': '4px',
            'box-shadow': '0 0 10px rgba(0, 0, 0, 0.2)',
            'z-index': 102,
            'display': 'flex',
            'justify-content': 'space-between'
        })
        .appendTo(img.parent());

    // Ajouter boutons d'action
    $('<button class="btn btn-secondary btn-sm me-2">Annuler</button>').appendTo(controls).click(function() {
        cleanupCropInterface();
        showToast('Recadrage annulé');
    });

    $('<button class="btn btn-success btn-sm">Appliquer le recadrage</button>').appendTo(controls).click(function() {
        applyCrop(img, cropPoints, imgWidth, imgHeight);
    });

    // Ajouter instructions
    showToast('Déplacez les 4 points pour définir la zone de recadrage', 5000);
}

// Fonction pour rendre un point draggable
function makeDraggable(element, index, points, img, imgPos) {
    var isDragging = false;
    var startX, startY;
    var imgWidth = img.width();
    var imgHeight = img.height();

    element.on('mousedown touchstart', function(e) {
        isDragging = true;

        // Empêcher le comportement par défaut du navigateur
        if (e.type === 'touchstart') {
            var touch = e.originalEvent.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
        } else {
            startX = e.clientX;
            startY = e.clientY;
            e.preventDefault();
        }

        $(document).on('mousemove touchmove', function(e) {
            if (!isDragging) return;

            var clientX, clientY;
            if (e.type === 'touchmove') {
                var touch = e.originalEvent.touches[0];
                clientX = touch.clientX;
                clientY = touch.clientY;
            } else {
                clientX = e.clientX;
                clientY = e.clientY;
            }

            // Calculer la nouvelle position relative à l'image
            var newX = Math.max(0, Math.min(clientX - img.offset().left, imgWidth));
            var newY = Math.max(0, Math.min(clientY - img.offset().top, imgHeight));

            // Mettre à jour la position du point
            points[index].x = newX;
            points[index].y = newY;

            // Mettre à jour visuellement le point
            element.css({
                'left': (imgPos.left + newX) + 'px',
                'top': (imgPos.top + newY) + 'px'
            });

            // Mettre à jour les lignes de contour
            updateCropLines(points, $('.crop-overlay'), imgPos);
        });

        $(document).on('mouseup touchend', function() {
            isDragging = false;
            $(document).off('mousemove touchmove mouseup touchend');
        });
    });
}

// Fonction pour mettre à jour les lignes de contour
function updateCropLines(points, cropOverlay, imgPos) {
    cropOverlay.empty();

    var canvas = $('<canvas>').attr({
        'width': cropOverlay.width(),
        'height': cropOverlay.height()
    }).css({
        'position': 'absolute',
        'top': 0,
        'left': 0
    }).appendTo(cropOverlay);

    var ctx = canvas[0].getContext('2d');

    // Dessiner un polygone semi-transparent
    ctx.clearRect(0, 0, canvas.width(), canvas.height());
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);

    for (var i = 1; i < points.length; i++) {
        ctx.lineTo(points[i].x, points[i].y);
    }

    ctx.closePath();
    ctx.strokeStyle = 'rgba(0, 123, 255, 1)';
    ctx.lineWidth = 2;
    ctx.stroke();

    // Remplir l'intérieur du polygone avec une couleur semi-transparente
    ctx.fillStyle = 'rgba(0, 123, 255, 0.1)';
    ctx.fill();
}

// Fonction pour appliquer le recadrage
function applyCrop(img, points, imgWidth, imgHeight) {
    // Simuler un recadrage avancé avec correction de perspective
    img.css({
        'opacity': '0.5',
        'transition': 'all 0.5s ease'
    });

    showToast('Application du recadrage et correction de perspective...');

    // Simuler un temps de traitement
    setTimeout(function() {
        // Nettoyer l'interface de recadrage
        cleanupCropInterface();

        // Simuler l'effet final (dans un cas réel, on utiliserait une transformation CSS ou une nouvelle image)
        img.css({
            'opacity': '1',
            'transform-origin': 'center',
            'transform': 'perspective(1000px) rotateX(2deg) rotateY(-2deg)',
            'box-shadow': '2px 2px 10px rgba(0,0,0,0.2)'
        });

        showToast('Image recadrée avec correction de perspective');
    }, 1500);
}

// Fonction pour nettoyer l'interface de recadrage
function cleanupCropInterface() {
    $('.crop-overlay, .crop-point, .crop-controls').remove();
}

// Fonction pour améliorer le contraste de l'image
function enhanceContrast() {
    var img = $('#scanned-image');
    if(img.length) {
        // Sauvegarde de l'état actuel du contraste
        var currentContrast = img.data('contrast') || 100;

        // Simuler l'interface d'ajustement du contraste
        showManipulationOverlay('contraste', function(value) {
            // Appliquer le nouveau contraste (de 50% à 150%)
            var newContrast = Math.min(Math.max(value, 50), 150);
            img.css({
                'filter': `contrast(${newContrast}%)`,
                'transition': 'filter 0.3s ease'
            });
            img.data('contrast', newContrast);
            showToast(`Contraste ajusté à ${newContrast}%`);
        }, currentContrast);
    }
}

// Fonction pour ajuster la luminosité
function adjustBrightness() {
    var img = $('#scanned-image');
    if(img.length) {
        // Sauvegarde de l'état actuel de la luminosité
        var currentBrightness = img.data('brightness') || 100;

        // Simuler l'interface d'ajustement de la luminosité
        showManipulationOverlay('luminosité', function(value) {
            // Appliquer la nouvelle luminosité (de 50% à 150%)
            var newBrightness = Math.min(Math.max(value, 50), 150);
            img.css({
                'filter': `brightness(${newBrightness}%)`,
                'transition': 'filter 0.3s ease'
            });
            img.data('brightness', newBrightness);
            showToast(`Luminosité ajustée à ${newBrightness}%`);
        }, currentBrightness);
    }
}

// Fonction pour redresser l'image automatiquement
function straightenImage() {
    var img = $('#scanned-image');
    if(img.length) {
        // Simuler le traitement de redressement
        img.addClass('processing');

        setTimeout(function() {
            // Simuler la fin du traitement
            img.removeClass('processing');
            img.css({
                'transform': 'rotate(0deg)',
                'transition': 'transform 0.5s ease'
            });
            img.data('rotation', 0);
            showToast('Image redressée automatiquement');
        }, 1200);
    }
}

// Fonction pour appliquer des filtres à l'image
function applyFilter() {
    var img = $('#scanned-image');
    if(img.length) {
        // Liste des filtres disponibles
        var filters = [
            { name: 'Normal', value: 'none' },
            { name: 'Noir et blanc', value: 'grayscale(100%)' },
            { name: 'Sépia', value: 'sepia(70%)' },
            { name: 'Contraste élevé', value: 'contrast(150%)' },
            { name: 'Clarté', value: 'brightness(120%)' },
            { name: 'Flou', value: 'blur(1px)' }
        ];

        // Afficher la sélection de filtres
        showFilterSelector(filters, function(selectedFilter) {
            img.css({
                'filter': selectedFilter.value,
                'transition': 'filter 0.3s ease'
            });
            showToast(`Filtre "${selectedFilter.name}" appliqué`);
        });
    }
}

// Fonction pour annoter l'image
function annotateImage() {
    var img = $('#scanned-image');
    if(img.length) {
        // Créer une couche d'annotation superposée
        if (!$('#annotation-overlay').length) {
            var imgPos = img.position();
            var imgWidth = img.width();
            var imgHeight = img.height();

            $('<div id="annotation-overlay">')
                .css({
                    'position': 'absolute',
                    'top': imgPos.top,
                    'left': imgPos.left,
                    'width': imgWidth,
                    'height': imgHeight,
                    'z-index': 100,
                    'pointer-events': 'none'
                })
                .appendTo(img.parent());

            showToast('Mode annotation activé. Utilisez la souris pour dessiner');

            // Ici, on simulerait l'ajout d'un système de dessin
            // Dans une implémentation réelle, on utiliserait Canvas ou SVG
        } else {
            $('#annotation-overlay').remove();
            showToast('Mode annotation désactivé');
        }
    }
}

// Fonction utilitaire pour afficher une notification toast
function showToast(message, duration = 3000) {
    // Supprimer les toasts existants
    $('.toast-notification').remove();

    // Créer un nouveau toast
    $('<div class="toast-notification">')
        .text(message)
        .css({
            'position': 'fixed',
            'bottom': '20px',
            'right': '20px',
            'background-color': 'rgba(0,0,0,0.7)',
            'color': 'white',
            'padding': '10px 15px',
            'border-radius': '4px',
            'z-index': 1050,
            'opacity': 0,
            'transition': 'opacity 0.3s ease'
        })
        .appendTo('body')
        .animate({ opacity: 1 }, 300);

    // Disparaître après la durée spécifiée
    setTimeout(function() {
        $('.toast-notification').animate({ opacity: 0 }, 300, function() {
            $(this).remove();
        });
    }, duration);
}

// Fonction pour afficher une interface de manipulation sur l'image
function showManipulationOverlay(type, callback, initialValue = 100) {
    // Supprimer les overlays existants
    $('.manipulation-overlay').remove();

    var overlayContent = '';

    switch(type) {
        case 'recadrage':
            overlayContent = `
                <div class="d-flex flex-column p-3">
                    <h5>Recadrage</h5>
                    <div class="d-flex justify-content-center mb-3">
                        <button class="btn btn-sm btn-outline-secondary me-2">Automatique</button>
                        <button class="btn btn-sm btn-outline-secondary me-2">4:3</button>
                        <button class="btn btn-sm btn-outline-secondary me-2">16:9</button>
                        <button class="btn btn-sm btn-outline-secondary">1:1</button>
                    </div>
                    <div class="text-end">
                        <button class="btn btn-secondary btn-sm me-2 cancel-btn">Annuler</button>
                        <button class="btn btn-primary btn-sm apply-btn">Appliquer</button>
                    </div>
                </div>
            `;
            break;

        case 'contraste':
        case 'luminosité':
            overlayContent = `
                <div class="d-flex flex-column p-3">
                    <h5>Ajustement de ${type}</h5>
                    <input type="range" class="form-range mb-3" min="50" max="150" value="${initialValue}" id="${type}-slider">
                    <div class="text-end">
                        <button class="btn btn-secondary btn-sm me-2 cancel-btn">Annuler</button>
                        <button class="btn btn-primary btn-sm apply-btn">Appliquer</button>
                    </div>
                </div>
            `;
            break;
    }

    // Créer l'overlay
    var overlay = $('<div class="manipulation-overlay">')
        .html(overlayContent)
        .css({
            'position': 'absolute',
            'top': '10px',
            'left': '10px',
            'right': '10px',
            'background-color': 'white',
            'border-radius': '4px',
            'box-shadow': '0 2px 8px rgba(0,0,0,0.2)',
            'z-index': 150
        })
        .appendTo('#scan-current-page');

    // Gérer les événements
    overlay.find('.cancel-btn').click(function() {
        overlay.remove();
    });

    overlay.find('.apply-btn').click(function() {
        var value = overlay.find(`#${type}-slider`).val() || initialValue;
        if (callback) callback(value);
        overlay.remove();
    });
}

// Fonction pour afficher le sélecteur de filtres
function showFilterSelector(filters, callback) {
    // Supprimer les sélecteurs existants
    $('.filter-selector').remove();

    var filterOptions = '';
    filters.forEach(function(filter) {
        filterOptions += `<div class="filter-option col-4 text-center mb-3" data-filter='${JSON.stringify(filter)}'>
            <div class="filter-preview border p-2 mb-1">
                <img src="${$('#scanned-image').attr('src')}" style="width: 100%; max-height: 80px; object-fit: cover; filter: ${filter.value};">
            </div>
            <div class="small">${filter.name}</div>
        </div>`;
    });

    // Créer le sélecteur
    var selector = $('<div class="filter-selector">')
        .html(`
            <div class="p-3">
                <h5>Sélectionnez un filtre</h5>
                <div class="row filter-options">
                    ${filterOptions}
                </div>
                <div class="text-end mt-2">
                    <button class="btn btn-secondary btn-sm close-filters">Fermer</button>
                </div>
            </div>
        `)
        .css({
            'position': 'absolute',
            'top': '10px',
            'left': '10px',
            'right': '10px',
            'background-color': 'white',
            'border-radius': '4px',
            'box-shadow': '0 2px 8px rgba(0,0,0,0.2)',
            'z-index': 150,
            'max-height': '80%',
            'overflow-y': 'auto'
        })
        .appendTo('#scan-current-page');

    // Gérer les événements
    selector.find('.close-filters').click(function() {
        selector.remove();
    });

    selector.find('.filter-option').click(function() {
        var selectedFilter = JSON.parse($(this).attr('data-filter'));
        if (callback) callback(selectedFilter);
        selector.remove();
    });
}

// Fonction pour détecter les scanners disponibles
function detectScanners() {
    // Afficher un indicateur de chargement
    showToast('Recherche des scanners...', 'info');
    $('#scannerSelect').prop('disabled', true);

    // Animation du bouton pendant la recherche
    const $refreshButton = $('#refreshScanners');
    const originalHtml = $refreshButton.html();

    $refreshButton.html('<i class="bi bi-arrow-repeat"></i> Recherche...');
    $refreshButton.addClass('scanning-animation');
    $refreshButton.prop('disabled', true);

    // Appel AJAX à l'endpoint de détection des scanners
    $.ajax({
        url: "{{ route('scan.detect-scanners') }}",
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            // Vider la liste actuelle
            $('#scannerSelect').empty().append('<option value="" selected disabled>Choisir un scanner</option>');

            if (response.success && response.scanners && response.scanners.length > 0) {
                // Ajouter les scanners détectés à la liste déroulante
                response.scanners.forEach(scanner => {
                    $('#scannerSelect').append(`<option value="${scanner.id}">${scanner.name}</option>`);
                });
                showToast(`${response.scanners.length} scanner(s) détecté(s)`, 'success');
            } else {
                showToast('Aucun scanner détecté', 'warning');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur lors de la détection des scanners:', error);
            showToast('Erreur lors de la détection des scanners', 'error');
        },
        complete: function() {
            // Réactiver le sélecteur et le bouton
            $('#scannerSelect').prop('disabled', false);
            $refreshButton.html(originalHtml);
            $refreshButton.removeClass('scanning-animation');
            $refreshButton.prop('disabled', false);
        }
    });
}

// Événement pour le bouton de détection des scanners
$(document).ready(function() {
    $('#refreshScanners').click(function(e) {
        e.preventDefault();
        detectScanners();
    });

    // Détecter automatiquement les scanners au chargement
    detectScanners();
});
</script>
@endsection
