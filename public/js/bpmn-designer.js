// BPMN Designer - Enhanced Version with Connections & Auto-Update
(function() {
    'use strict';

    let droppedElements = [];
    let connections = [];
    let selectedElement = null;
    let elementCounter = 0;
    let connectionMode = false;
    let sourceElement = null;

    // Canvas et Panel
    const canvas = document.getElementById('bpmnCanvas');
    const propertiesPanel = document.getElementById('propertiesPanel');
    const emptyMessage = document.getElementById('emptyCanvasMessage');
    const connectionBtn = document.getElementById('connectionMode');
    const connectBtn = document.getElementById('connectElement');
    const connectionStatus = document.getElementById('connectionStatus');

    // Initialize
    init();

    function init() {
        // Drag Handlers
        document.querySelectorAll('.bpmn-element').forEach(element => {
            element.addEventListener('dragstart', handleDragStart);
        });

        canvas.addEventListener('dragover', handleDragOver);
        canvas.addEventListener('drop', handleDrop);
        canvas.addEventListener('dragleave', handleDragLeave);

        // Buttons
        document.getElementById('clearCanvas')?.addEventListener('click', clearCanvas);
        document.getElementById('generateXML')?.addEventListener('click', generateXML);
        document.getElementById('deleteElement')?.addEventListener('click', deleteSelectedElement);

        if (connectionBtn) {
            connectionBtn.addEventListener('click', toggleConnectionMode);
        }

        if (connectBtn) {
            connectBtn.addEventListener('click', startConnection);
        }

        // Load existing BPMN if in edit mode
        loadExistingBPMN();

        // Auto-update on form submit
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                autoGenerateXML();
            });
        }
    }

    function loadExistingBPMN() {
        const xmlField = document.getElementById('bpmn_xml');
        if (!xmlField || !xmlField.value) return;

        const xmlContent = xmlField.value.trim();
        if (xmlContent.includes('<!-- Configuration à définir ultérieurement -->')) return;
        if (xmlContent.includes('<!-- Vos éléments BPMN -->')) return;

        // Parse XML to extract elements and positions
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xmlContent, 'text/xml');

        // Extract shapes (positions)
        const shapes = xmlDoc.querySelectorAll('bpmndi\\:BPMNShape, BPMNShape');
        const elements = xmlDoc.querySelectorAll('bpmn\\:process > *, process > *');

        shapes.forEach(shape => {
            const elementId = shape.getAttribute('bpmnElement');
            const boundsEl = shape.querySelector('dc\\:Bounds, Bounds');

            if (boundsEl && elementId) {
                const x = parseInt(boundsEl.getAttribute('x')) || 0;
                const y = parseInt(boundsEl.getAttribute('y')) || 0;

                // Find corresponding element
                const element = Array.from(elements).find(el => el.getAttribute('id') === elementId);
                if (element) {
                    const type = element.tagName.replace('bpmn:', '');
                    const name = element.getAttribute('name') || getDefaultName(type);

                    createDroppedElement(type, x, y, elementId, name);
                }
            }
        });

        if (droppedElements.length > 0 && emptyMessage) {
            emptyMessage.style.display = 'none';
        }

        updateStats();
    }

    function handleDragStart(e) {
        const type = this.getAttribute('data-type');
        e.dataTransfer.effectAllowed = 'copy';
        e.dataTransfer.setData('text/plain', type);
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        canvas.classList.add('drag-over');
    }

    function handleDragLeave(e) {
        if (e.target === canvas) {
            canvas.classList.remove('drag-over');
        }
    }

    function handleDrop(e) {
        e.preventDefault();
        canvas.classList.remove('drag-over');

        const type = e.dataTransfer.getData('text/plain');
        if (!type) return;

        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left + canvas.scrollLeft;
        const y = e.clientY - rect.top + canvas.scrollTop;

        createDroppedElement(type, x, y);

        if (emptyMessage) {
            emptyMessage.style.display = 'none';
        }

        autoGenerateXML();
        updateStats();
    }

    function createDroppedElement(type, x, y, customId = null, customName = null) {
        elementCounter++;
        const id = customId || `${type}_${elementCounter}`;
        const name = customName || getDefaultName(type);

        const element = document.createElement('div');
        element.className = 'dropped-element';
        element.id = id;
        element.style.left = x + 'px';
        element.style.top = y + 'px';
        element.setAttribute('data-type', type);
        element.setAttribute('data-name', name);

        element.innerHTML = `
            ${getElementIcon(type)}
            <div class="small mt-1 text-center fw-bold">${name}</div>
            <span class="element-badge" onclick="quickDelete('${id}')">×</span>
        `;

        element.addEventListener('click', (e) => {
            e.stopPropagation();
            if (connectionMode) {
                handleConnectionClick(element);
            } else {
                selectElement(element);
            }
        });

        makeElementDraggable(element);

        canvas.appendChild(element);
        droppedElements.push({
            id: id,
            type: type,
            name: name,
            description: '',
            x: x,
            y: y
        });
    }

    function startConnection() {
        if (!selectedElement) return;

        connectionMode = true;
        sourceElement = selectedElement;

        if (connectionBtn) {
            connectionBtn.disabled = false;
            connectionBtn.classList.add('active');
        }

        if (connectionStatus) {
            connectionStatus.innerHTML = '<strong class="text-info">Cliquez sur l\'élément cible</strong>';
        }

        selectedElement.classList.add('connection-source');
    }

    function toggleConnectionMode() {
        connectionMode = !connectionMode;

        if (connectionMode) {
            connectionStatus.innerHTML = '<strong class="text-info">Sélectionnez l\'élément source</strong>';
        } else {
            connectionStatus.textContent = 'Mode connexion désactivé';
            sourceElement = null;
            document.querySelectorAll('.connection-source').forEach(el => {
                el.classList.remove('connection-source');
            });
        }
    }

    function handleConnectionClick(target) {
        if (!sourceElement) {
            sourceElement = target;
            target.classList.add('connection-source');
            connectionStatus.innerHTML = '<strong class="text-info">Cliquez sur l\'élément cible</strong>';
            return;
        }

        if (target.id === sourceElement.id) {
            showNotification('Impossible de connecter un élément à lui-même', 'warning');
            return;
        }

        // Create connection
        connections.push({
            id: `flow_${connections.length + 1}`,
            source: sourceElement.id,
            target: target.id
        });

        drawConnection(sourceElement, target);

        connectionMode = false;
        sourceElement.classList.remove('connection-source');
        sourceElement = null;

        if (connectionBtn) {
            connectionBtn.classList.remove('active');
        }

        connectionStatus.innerHTML = '<span class="text-success">✓ Connexion créée</span>';

        autoGenerateXML();
        updateStats();
        showNotification('Connexion créée avec succès', 'success');
    }

    function drawConnection(source, target) {
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        const sourceRect = source.getBoundingClientRect();
        const targetRect = target.getBoundingClientRect();
        const canvasRect = canvas.getBoundingClientRect();

        const x1 = sourceRect.left - canvasRect.left + sourceRect.width / 2;
        const y1 = sourceRect.top - canvasRect.top + sourceRect.height / 2;
        const x2 = targetRect.left - canvasRect.left + targetRect.width / 2;
        const y2 = targetRect.top - canvasRect.top + targetRect.height / 2;

        // Create or get SVG container
        let svg = canvas.querySelector('svg');
        if (!svg) {
            svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.style.position = 'absolute';
            svg.style.top = '0';
            svg.style.left = '0';
            svg.style.width = '100%';
            svg.style.height = '100%';
            svg.style.pointerEvents = 'none';
            svg.style.zIndex = '0';

            // Add arrow marker
            const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
            const marker = document.createElementNS('http://www.w3.org/2000/svg', 'marker');
            marker.setAttribute('id', 'arrowhead');
            marker.setAttribute('markerWidth', '10');
            marker.setAttribute('markerHeight', '7');
            marker.setAttribute('refX', '9');
            marker.setAttribute('refY', '3.5');
            marker.setAttribute('orient', 'auto');

            const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
            polygon.setAttribute('points', '0 0, 10 3.5, 0 7');
            polygon.setAttribute('fill', '#6c757d');

            marker.appendChild(polygon);
            defs.appendChild(marker);
            svg.appendChild(defs);

            canvas.insertBefore(svg, canvas.firstChild);
        }

        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);
        line.setAttribute('stroke', '#6c757d');
        line.setAttribute('stroke-width', '2');
        line.setAttribute('marker-end', 'url(#arrowhead)');
        line.setAttribute('data-source', source.id);
        line.setAttribute('data-target', target.id);

        svg.appendChild(line);
    }

    function makeElementDraggable(element) {
        let isDragging = false;
        let currentX, currentY, initialX, initialY;

        element.addEventListener('mousedown', dragStart);

        function dragStart(e) {
            if (e.target.classList.contains('element-badge')) return;
            if (connectionMode) return;

            isDragging = true;
            initialX = e.clientX - element.offsetLeft;
            initialY = e.clientY - element.offsetTop;

            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', dragEnd);
        }

        function drag(e) {
            if (!isDragging) return;
            e.preventDefault();

            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;

            element.style.left = currentX + 'px';
            element.style.top = currentY + 'px';

            updateConnections(element);
        }

        function dragEnd() {
            if (isDragging) {
                const elementData = droppedElements.find(el => el.id === element.id);
                if (elementData) {
                    elementData.x = parseInt(element.style.left);
                    elementData.y = parseInt(element.style.top);
                }
                autoGenerateXML();
            }
            isDragging = false;
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', dragEnd);
        }
    }

    function updateConnections(movedElement) {
        const svg = canvas.querySelector('svg');
        if (!svg) return;

        const lines = svg.querySelectorAll('line');
        lines.forEach(line => {
            const sourceId = line.getAttribute('data-source');
            const targetId = line.getAttribute('data-target');

            if (sourceId === movedElement.id || targetId === movedElement.id) {
                const sourceEl = document.getElementById(sourceId);
                const targetEl = document.getElementById(targetId);

                if (sourceEl && targetEl) {
                    const sourceRect = sourceEl.getBoundingClientRect();
                    const targetRect = targetEl.getBoundingClientRect();
                    const canvasRect = canvas.getBoundingClientRect();

                    const x1 = sourceRect.left - canvasRect.left + sourceRect.width / 2;
                    const y1 = sourceRect.top - canvasRect.top + sourceRect.height / 2;
                    const x2 = targetRect.left - canvasRect.left + targetRect.width / 2;
                    const y2 = targetRect.top - canvasRect.top + targetRect.height / 2;

                    line.setAttribute('x1', x1);
                    line.setAttribute('y1', y1);
                    line.setAttribute('x2', x2);
                    line.setAttribute('y2', y2);
                }
            }
        });
    }

    function selectElement(element) {
        if (selectedElement) {
            selectedElement.classList.remove('selected');
        }

        selectedElement = element;
        element.classList.add('selected');

        const elementData = droppedElements.find(el => el.id === element.id);
        if (elementData) {
            document.getElementById('elementId').value = elementData.id;
            document.getElementById('elementType').value = getTypeLabel(elementData.type);
            document.getElementById('elementName').value = elementData.name;
            document.getElementById('elementDescription').value = elementData.description || '';

            propertiesPanel.style.display = 'block';

            if (connectionBtn) {
                connectionBtn.disabled = false;
            }
            if (connectionStatus) {
                connectionStatus.textContent = 'Prêt à connecter';
            }

            document.getElementById('elementName').onchange = function() {
                elementData.name = this.value;
                element.querySelector('.fw-bold').textContent = this.value;
                element.setAttribute('data-name', this.value);
                autoGenerateXML();
            };

            document.getElementById('elementDescription').onchange = function() {
                elementData.description = this.value;
                autoGenerateXML();
            };
        }
    }

    function deleteSelectedElement() {
        if (!selectedElement) return;

        const id = selectedElement.id;

        // Remove connections
        connections = connections.filter(conn => conn.source !== id && conn.target !== id);

        // Remove SVG lines
        const svg = canvas.querySelector('svg');
        if (svg) {
            svg.querySelectorAll(`line[data-source="${id}"], line[data-target="${id}"]`).forEach(line => {
                line.remove();
            });
        }

        droppedElements = droppedElements.filter(el => el.id !== id);
        selectedElement.remove();
        selectedElement = null;
        propertiesPanel.style.display = 'none';

        if (droppedElements.length === 0 && emptyMessage) {
            emptyMessage.style.display = 'block';
        }

        autoGenerateXML();
        updateStats();
    }

    window.quickDelete = function(id) {
        const element = document.getElementById(id);
        if (element) {
            // Remove connections
            connections = connections.filter(conn => conn.source !== id && conn.target !== id);

            // Remove SVG lines
            const svg = canvas.querySelector('svg');
            if (svg) {
                svg.querySelectorAll(`line[data-source="${id}"], line[data-target="${id}"]`).forEach(line => {
                    line.remove();
                });
            }

            droppedElements = droppedElements.filter(el => el.id !== id);
            element.remove();

            if (selectedElement && selectedElement.id === id) {
                selectedElement = null;
                propertiesPanel.style.display = 'none';
            }

            if (droppedElements.length === 0 && emptyMessage) {
                emptyMessage.style.display = 'block';
            }

            autoGenerateXML();
            updateStats();
        }
    };

    function clearCanvas() {
        if (!confirm('Voulez-vous vraiment effacer tous les éléments ?')) return;

        droppedElements = [];
        connections = [];
        selectedElement = null;
        elementCounter = 0;

        document.querySelectorAll('.dropped-element').forEach(el => el.remove());
        const svg = canvas.querySelector('svg');
        if (svg) svg.remove();

        propertiesPanel.style.display = 'none';

        if (emptyMessage) {
            emptyMessage.style.display = 'block';
        }

        document.getElementById('bpmn_xml').value = getEmptyBPMN();
        updateStats();
    }

    function autoGenerateXML() {
        // Auto-generate without switching tabs
        const xml = buildXML();
        document.getElementById('bpmn_xml').value = xml;
    }

    function generateXML() {
        if (droppedElements.length === 0) {
            alert('Aucun élément sur le canvas. Ajoutez des éléments BPMN d\'abord.');
            return;
        }

        const xml = buildXML();
        document.getElementById('bpmn_xml').value = xml;

        const codeTab = new bootstrap.Tab(document.getElementById('code-tab'));
        codeTab.show();

        showNotification('XML BPMN généré avec succès !', 'success');
    }

    function buildXML() {
        let xml = '<?xml version="1.0" encoding="UTF-8"?>\n';
        xml += '<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL" xmlns:bpmndi="http://www.omg.org/spec/BPMN/20100524/DI" xmlns:dc="http://www.omg.org/spec/DD/20100524/DC" id="Definitions_1">\n';
        xml += '  <bpmn:process id="Process_1" isExecutable="true">\n';

        // Elements
        droppedElements.forEach(element => {
            xml += `    <bpmn:${element.type} id="${element.id}" name="${escapeXml(element.name)}"`;

            if (element.description) {
                xml += `>\n`;
                xml += `      <bpmn:documentation>${escapeXml(element.description)}</bpmn:documentation>\n`;
                xml += `    </bpmn:${element.type}>\n`;
            } else {
                xml += ' />\n';
            }
        });

        // Connections
        connections.forEach(conn => {
            xml += `    <bpmn:sequenceFlow id="${conn.id}" sourceRef="${conn.source}" targetRef="${conn.target}" />\n`;
        });

        xml += '  </bpmn:process>\n';

        // Diagram
        xml += '  <bpmndi:BPMNDiagram id="BPMNDiagram_1">\n';
        xml += '    <bpmndi:BPMNPlane id="BPMNPlane_1" bpmnElement="Process_1">\n';

        droppedElements.forEach(element => {
            xml += `      <bpmndi:BPMNShape id="${element.id}_di" bpmnElement="${element.id}">\n`;
            xml += `        <dc:Bounds x="${element.x}" y="${element.y}" width="100" height="80" />\n`;
            xml += `      </bpmndi:BPMNShape>\n`;
        });

        xml += '    </bpmndi:BPMNPlane>\n';
        xml += '  </bpmndi:BPMNDiagram>\n';
        xml += '</bpmn:definitions>';

        return xml;
    }

    function updateStats() {
        const elementsCountEl = document.getElementById('elementsCount');
        const connectionsCountEl = document.getElementById('connectionsCount');

        if (elementsCountEl) elementsCountEl.textContent = droppedElements.length;
        if (connectionsCountEl) connectionsCountEl.textContent = connections.length;
    }

    function getElementIcon(type) {
        const icons = {
            startEvent: '<i class="bi bi-play-circle text-success" style="font-size: 2.5rem;"></i>',
            endEvent: '<i class="bi bi-stop-circle text-danger" style="font-size: 2.5rem;"></i>',
            intermediateEvent: '<i class="bi bi-circle text-warning" style="font-size: 2.5rem;"></i>',
            task: '<i class="bi bi-check-square text-primary" style="font-size: 2.5rem;"></i>',
            userTask: '<i class="bi bi-person-check text-info" style="font-size: 2.5rem;"></i>',
            serviceTask: '<i class="bi bi-gear text-secondary" style="font-size: 2.5rem;"></i>',
            scriptTask: '<i class="bi bi-code-square text-dark" style="font-size: 2.5rem;"></i>',
            exclusiveGateway: '<i class="bi bi-diamond text-warning" style="font-size: 2.5rem;"></i>',
            parallelGateway: '<i class="bi bi-plus-diamond text-success" style="font-size: 2.5rem;"></i>',
            inclusiveGateway: '<i class="bi bi-circle-square text-info" style="font-size: 2.5rem;"></i>',
            subProcess: '<i class="bi bi-box text-primary" style="font-size: 2.5rem;"></i>'
        };
        return icons[type] || '<i class="bi bi-question-circle" style="font-size: 2.5rem;"></i>';
    }

    function getDefaultName(type) {
        const names = {
            startEvent: 'Début',
            endEvent: 'Fin',
            intermediateEvent: 'Événement',
            task: 'Tâche',
            userTask: 'Tâche Utilisateur',
            serviceTask: 'Tâche Service',
            scriptTask: 'Script',
            exclusiveGateway: 'XOR Gateway',
            parallelGateway: 'AND Gateway',
            inclusiveGateway: 'OR Gateway',
            subProcess: 'Sous-processus'
        };
        return names[type] || 'Élément';
    }

    function getTypeLabel(type) {
        const labels = {
            startEvent: 'Événement de Début',
            endEvent: 'Événement de Fin',
            intermediateEvent: 'Événement Intermédiaire',
            task: 'Tâche',
            userTask: 'Tâche Utilisateur',
            serviceTask: 'Tâche Service',
            scriptTask: 'Tâche Script',
            exclusiveGateway: 'Porte Exclusive (XOR)',
            parallelGateway: 'Porte Parallèle (AND)',
            inclusiveGateway: 'Porte Inclusive (OR)',
            subProcess: 'Sous-processus'
        };
        return labels[type] || type;
    }

    function escapeXml(text) {
        if (!text) return '';
        return text.replace(/&/g, '&amp;')
                   .replace(/</g, '&lt;')
                   .replace(/>/g, '&gt;')
                   .replace(/"/g, '&quot;')
                   .replace(/'/g, '&apos;');
    }

    function getEmptyBPMN() {
        return `<?xml version="1.0" encoding="UTF-8"?>
<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL" xmlns:bpmndi="http://www.omg.org/spec/BPMN/20100524/DI" xmlns:dc="http://www.omg.org/spec/DD/20100524/DC" id="Definitions_1">
  <bpmn:process id="Process_1" isExecutable="true">
    <!-- Vos éléments BPMN -->
  </bpmn:process>
</bpmn:definitions>`;
    }

    function showNotification(message, type = 'info') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '11';

        toastContainer.innerHTML = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-check-circle me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        document.body.appendChild(toastContainer);

        const toastElement = toastContainer.querySelector('.toast');
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', () => {
            toastContainer.remove();
        });
    }
})();
