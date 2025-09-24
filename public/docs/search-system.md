# Sistema de Búsqueda Reutilizable

Este sistema proporciona componentes CSS y JavaScript reutilizables para implementar búsqueda en tiempo real en tablas de datos.

## Archivos Incluidos

- `public/css/search.css` - Estilos reutilizables
- `public/js/search.js` - Funcionalidad JavaScript
- `public/docs/search-system.md` - Esta documentación

## Uso Básico

### 1. Incluir archivos

```html
<link rel="stylesheet" href="/public/css/search.css">
<script src="/public/js/search.js"></script>
```

### 2. Crear HTML del buscador

```html
<div class="search-container">
    <div class="search-box">
        <input type="text" id="miBuscador" class="search-input" placeholder="🔍 Buscar..." autocomplete="off">
        <div class="search-results-info" id="miInfoResultados"></div>
    </div>
</div>
```

### 3. Inicializar JavaScript

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const search = initializeTableSearch({
        searchInputId: 'miBuscador',
        resultsInfoId: 'miInfoResultados',
        tableRowsSelector: 'tbody tr[data-id]',
        searchFields: ['nombre', 'email', 'telefono'], // Índices de columnas
        minSearchLength: 3,
        showAllWhenEmpty: true,
        showAllWhenLessThanMin: true
    });
});
```

## Configuración Avanzada

### Opciones de Configuración

```javascript
const config = {
    searchInputId: 'searchInput',           // ID del input de búsqueda
    resultsInfoId: 'searchResultsInfo',     // ID del contenedor de información
    tableRowsSelector: 'tbody tr[data-id]', // Selector de filas de la tabla
    searchFields: ['nombre', 'dni'],        // Índices de columnas a buscar
    minSearchLength: 3,                     // Longitud mínima para filtrar
    showAllWhenEmpty: true,                 // Mostrar todos cuando está vacío
    showAllWhenLessThanMin: true            // Mostrar todos con menos de minSearchLength
};
```

### Variantes de Diseño

#### Tamaños
```html
<!-- Pequeño -->
<div class="search-container small">

<!-- Normal (por defecto) -->
<div class="search-container">

<!-- Grande -->
<div class="search-container large">
```

#### Ancho del Input
```html
<!-- Ancho completo -->
<input class="search-input full-width">

<!-- 75% del ancho -->
<input class="search-input three-quarters">

<!-- 50% del ancho -->
<input class="search-input half-width">

<!-- 90% del ancho (por defecto) -->
<input class="search-input">
```

#### Temas
```html
<!-- Tema oscuro (por defecto) -->
<div class="search-container">

<!-- Tema claro -->
<div class="search-container light">
```

## API de JavaScript

### Clase TableSearch

```javascript
const search = new TableSearch(config);
search.init();                    // Inicializar
search.reload();                  // Recargar datos
search.removeItem(itemId);        // Eliminar elemento
```

### Función de Conveniencia

```javascript
const search = initializeTableSearch(config);
```

### Crear HTML Dinámicamente

```javascript
const html = createSearchHTML('miInput', 'miInfo', '🔍 Buscar usuarios...');
document.getElementById('contenedor').innerHTML = html;
```

## Ejemplos de Implementación

### Lista de Usuarios

```html
<div class="search-container">
    <div class="search-box">
        <input type="text" id="userSearch" class="search-input" placeholder="🔍 Buscar usuarios..." autocomplete="off">
        <div class="search-results-info" id="userResults"></div>
    </div>
</div>

<table>
    <tbody>
        <tr data-id="1">
            <td>Juan Pérez</td>
            <td>juan@email.com</td>
            <td>123456789</td>
        </tr>
    </tbody>
</table>

<script>
initializeTableSearch({
    searchInputId: 'userSearch',
    resultsInfoId: 'userResults',
    searchFields: ['nombre', 'email', 'telefono']
});
</script>
```

### Lista de Productos

```html
<div class="search-container light small">
    <div class="search-box">
        <input type="text" id="productSearch" class="search-input full-width" placeholder="🔍 Buscar productos..." autocomplete="off">
        <div class="search-results-info" id="productResults"></div>
    </div>
</div>
```

## Responsive

El sistema es completamente responsive y se adapta automáticamente a dispositivos móviles:

- En pantallas < 768px: Input ocupa 100% del ancho
- En pantallas < 480px: Padding reducido y fuente más pequeña
- Font-size 16px en móviles para evitar zoom en iOS

## Personalización

### CSS Custom Properties

```css
:root {
    --search-bg: #2a2a2a;
    --search-border: #404040;
    --search-input-bg: #1e1e1e;
    --search-text: white;
    --search-placeholder: #888;
    --search-focus: #667eea;
}
```

### Estilos Adicionales

```css
/* Personalizar el contenedor */
.mi-buscador-personalizado {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 20px;
}

/* Personalizar el input */
.mi-input-personalizado {
    border-radius: 25px;
    padding: 15px 20px;
}
```
