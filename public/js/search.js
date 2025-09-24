/**
 * Sistema de búsqueda reutilizable para tablas de datos
 * 
 * Uso:
 * 1. Incluir este archivo en tu página
 * 2. Crear un input con clase 'search-input' y id único
 * 3. Crear un contenedor con clase 'search-results-info' y id único
 * 4. Llamar a initializeTableSearch() con la configuración
 */

class TableSearch {
    constructor(config) {
        this.config = {
            searchInputId: 'searchInput',
            resultsInfoId: 'searchResultsInfo',
            tableRowsSelector: 'tbody tr[data-id]',
            searchFields: ['nombre', 'dni', 'telefono'], // Índices de las columnas a buscar
            minSearchLength: 3,
            showAllWhenEmpty: true,
            showAllWhenLessThanMin: true,
            ...config
        };
        
        this.allItems = [];
        this.filteredItems = [];
        this.searchInput = null;
        this.resultsInfo = null;
    }
    
    init() {
        this.searchInput = document.getElementById(this.config.searchInputId);
        this.resultsInfo = document.getElementById(this.config.resultsInfoId);
        
        if (!this.searchInput || !this.resultsInfo) {
            console.error('TableSearch: No se encontraron los elementos necesarios');
            return;
        }
        
        this.loadItems();
        this.setupEventListeners();
        this.updateResultsInfo();
    }
    
    loadItems() {
        const rows = document.querySelectorAll(this.config.tableRowsSelector);
        this.allItems = Array.from(rows).map(row => {
            const cells = row.querySelectorAll('td');
            const item = {
                element: row,
                id: row.dataset.id,
                data: {}
            };
            
            // Extraer datos de las celdas según los índices especificados
            this.config.searchFields.forEach((field, index) => {
                if (cells[index]) {
                    item.data[field] = cells[index].textContent.trim();
                }
            });
            
            return item;
        });
        
        this.filteredItems = [...this.allItems];
    }
    
    setupEventListeners() {
        this.searchInput.addEventListener('input', (event) => {
            this.handleSearch(event);
        });
    }
    
    handleSearch(event) {
        const searchTerm = event.target.value.trim().toLowerCase();
        
        if (searchTerm.length === 0) {
            if (this.config.showAllWhenEmpty) {
                this.showAllItems();
            } else {
                this.hideAllItems();
            }
        } else if (searchTerm.length >= this.config.minSearchLength) {
            this.filterItems(searchTerm);
        } else {
            if (this.config.showAllWhenLessThanMin) {
                this.showAllItems();
            } else {
                this.hideAllItems();
            }
        }
        
        this.updateResultsInfo();
    }
    
    showAllItems() {
        this.allItems.forEach(item => {
            item.element.style.display = '';
        });
        this.filteredItems = [...this.allItems];
    }
    
    hideAllItems() {
        this.allItems.forEach(item => {
            item.element.style.display = 'none';
        });
        this.filteredItems = [];
    }
    
    filterItems(searchTerm) {
        this.filteredItems = this.allItems.filter(item => {
            return this.config.searchFields.some(field => {
                const value = item.data[field] || '';
                return value.toLowerCase().includes(searchTerm);
            });
        });
        
        // Mostrar/ocultar filas según el filtro
        this.allItems.forEach(item => {
            if (this.filteredItems.includes(item)) {
                item.element.style.display = '';
            } else {
                item.element.style.display = 'none';
            }
        });
    }
    
    updateResultsInfo() {
        const searchTerm = this.searchInput.value.trim();
        
        if (searchTerm.length === 0) {
            this.resultsInfo.textContent = `Mostrando todos los elementos (${this.allItems.length})`;
            this.resultsInfo.classList.remove('hidden');
        } else if (searchTerm.length < this.config.minSearchLength) {
            if (this.config.showAllWhenLessThanMin) {
                this.resultsInfo.textContent = `Mostrando todos los elementos (${this.allItems.length}) - Escribe ${this.config.minSearchLength}+ caracteres para filtrar`;
            } else {
                this.resultsInfo.textContent = `Escribe al menos ${this.config.minSearchLength} caracteres para buscar`;
            }
            this.resultsInfo.classList.remove('hidden');
        } else {
            this.resultsInfo.textContent = `Encontrados ${this.filteredItems.length} elemento(s) de ${this.allItems.length} total`;
            this.resultsInfo.classList.remove('hidden');
        }
    }
    
    // Método para actualizar la lista cuando se elimina un elemento
    removeItem(itemId) {
        this.allItems = this.allItems.filter(item => item.id !== itemId.toString());
        this.filteredItems = this.filteredItems.filter(item => item.id !== itemId.toString());
        this.updateResultsInfo();
    }
    
    // Método para recargar la lista completa
    reload() {
        this.loadItems();
        this.updateResultsInfo();
    }
}

// Función de conveniencia para inicializar búsqueda en una tabla
function initializeTableSearch(config = {}) {
    const search = new TableSearch(config);
    search.init();
    return search;
}

// Función para crear HTML del buscador
function createSearchHTML(searchInputId, resultsInfoId, placeholder = '🔍 Buscar...') {
    return `
        <div class="search-container">
            <div class="search-box">
                <input type="text" id="${searchInputId}" class="search-input" placeholder="${placeholder}" autocomplete="off">
                <div class="search-results-info" id="${resultsInfoId}"></div>
            </div>
        </div>
    `;
}
