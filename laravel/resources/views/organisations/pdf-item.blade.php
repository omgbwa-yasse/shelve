{{-- organisations/pdf-item.blade.php --}}
<div class="org-item">
    <div class="org-content {{ $item['level'] === 0 ? 'root' : 'unit' }}"
         style="margin-left: {{ $item['level'] * 20 }}px">

        <!-- Ligne de connexion verticale -->
        @if($item['level'] > 0)
            <div class="connector"
                 style="position: absolute;
                        left: {{ ($item['level'] * 20) - 10 }}px;
                        border-left: 1px solid #dee2e6;
                        height: 100%;
                        top: 0;">
            </div>
        @endif

        <!-- Contenu principal -->
        <div class="main-content" style="position: relative;">
            <!-- Ligne horizontale pour les éléments non-racine -->
            @if($item['level'] > 0)
                <div class="horizontal-line"
                     style="position: absolute;
                            left: -10px;
                            top: 50%;
                            width: 10px;
                            border-top: 1px solid #dee2e6;">
                </div>
            @endif

            <!-- En-tête de l'élément -->
            <div class="header"
                 style="background-color: {{ $item['level'] === 0 ? '#f8f9fa' : 'white' }};
                        padding: 10px;
                        border: 1px solid #dee2e6;
                        border-left: 4px solid {{ $item['level'] === 0 ? '#0d6efd' : '#28a745' }};
                        margin-bottom: 5px;
                        break-inside: avoid;">

                <!-- Code et niveau -->
                <div style="margin-bottom: 5px;">
                    <span class="code"
                          style="color: {{ $item['level'] === 0 ? '#0d6efd' : '#28a745' }};
                                 font-weight: bold;
                                 font-size: 12pt;">
                        {{ $item['organisation']->code }}
                    </span>

                    <span class="level-badge"
                          style="float: right;
                                 background-color: {{ $item['level'] === 0 ? '#0d6efd' : '#28a745' }};
                                 color: white;
                                 padding: 2px 8px;
                                 border-radius: 12px;
                                 font-size: 8pt;">
                        {{ $item['level'] === 0 ? 'Direction' : 'Niveau ' . $item['level'] }}
                    </span>
                </div>

                <!-- Nom -->
                <div class="name"
                     style="font-weight: bold;
                            font-size: 11pt;
                            color: #333;
                            margin-bottom: {{ $item['organisation']->description ? '5px' : '0' }};">
                    {{ $item['organisation']->name }}
                </div>

                <!-- Description (si elle existe) -->
                @if($item['organisation']->description)
                    <div class="description"
                         style="color: #666;
                                font-style: italic;
                                font-size: 9pt;
                                margin-top: 5px;
                                padding-top: 5px;
                                border-top: 1px dotted #dee2e6;">
                        {{ $item['organisation']->description }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Sous-éléments -->
        @if($item['children']->count() > 0)
            <div class="children" style="margin-top: 10px;">
                @foreach($item['children'] as $child)
                    @include('organisations.pdf-item', ['item' => $child])
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Styles spécifiques pour l'impression -->
<style>
    @media print {
        .org-item {
            break-inside: avoid;
        }

        .main-content {
            break-inside: avoid;
        }

        .header {
            break-inside: avoid;
        }

        /* Assure que les lignes de connexion s'impriment correctement */
        .connector, .horizontal-line {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }

    /* Styles pour améliorer la lisibilité des lignes de connexion sur fond blanc */
    .connector, .horizontal-line {
        border-color: #dee2e6 !important;
    }

    /* Amélioration du contraste pour l'impression */
    .level-badge {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
</style>
