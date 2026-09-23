/**
 * Flexible Grid Template Examples
 * This file demonstrates how to create grid templates where the first column
 * has its own parameter and other columns repeat their width.
 */

// Example 1: Basic usage with fixed first column width
function exampleBasicGrid() {
  const container = document.querySelector('.example-container-1');
  const firstColumnWidth = '200px';
  const repeatColumnWidth = '1fr';
  const totalColumns = 5;
  
  applyGridTemplate(container, firstColumnWidth, repeatColumnWidth, totalColumns);
}

// Example 2: Using percentage for first column
function examplePercentageGrid() {
  const container = document.querySelector('.example-container-2');
  const firstColumnWidth = '25%';
  const repeatColumnWidth = '1fr';
  const totalColumns = 4;
  
  applyGridTemplate(container, firstColumnWidth, repeatColumnWidth, totalColumns);
}

// Example 3: Using fraction units
function exampleFractionGrid() {
  const container = document.querySelector('.example-container-3');
  const firstColumnWidth = '2fr';
  const repeatColumnWidth = '1fr';
  const totalColumns = 6;
  
  applyGridTemplate(container, firstColumnWidth, repeatColumnWidth, totalColumns);
}

// Example 4: Dynamic grid based on data
function createDynamicGrid(data, container, firstColumnWidth = '150px', repeatColumnWidth = '1fr') {
  const totalColumns = data.length + 1; // +1 for header column
  applyGridTemplate(container, firstColumnWidth, repeatColumnWidth, totalColumns);
  
  // Add header
  const header = document.createElement('div');
  header.className = 'grid-header';
  header.textContent = 'Header';
  container.appendChild(header);
  
  // Add data columns
  data.forEach(item => {
    const column = document.createElement('div');
    column.className = 'grid-column';
    column.textContent = item.name;
    container.appendChild(column);
  });
}

// Example 5: Responsive grid with different breakpoints
function createResponsiveGrid(container, breakpoints) {
  // breakpoints: { mobile: { first: '100%', repeat: '1fr' }, tablet: { first: '200px', repeat: '1fr' }, desktop: { first: '300px', repeat: '1fr' } }
  
  const mediaQuery = window.matchMedia;
  
  function updateGrid() {
    let config;
    if (window.innerWidth < 768) {
      config = breakpoints.mobile;
    } else if (window.innerWidth < 1024) {
      config = breakpoints.tablet;
    } else {
      config = breakpoints.desktop;
    }
    
    const totalColumns = container.children.length;
    applyGridTemplate(container, config.first, config.repeat, totalColumns);
  }
  
  // Initial setup
  updateGrid();
  
  // Listen for resize
  window.addEventListener('resize', updateGrid);
}

// Example 6: Grid with custom CSS classes
function createGridWithClasses(container, className, firstColumnWidth, repeatColumnWidth, totalColumns) {
  container.className = `grid-container ${className}`;
  applyGridTemplate(container, firstColumnWidth, repeatColumnWidth, totalColumns);
}

// Example 7: Auto-sizing grid based on content
function createAutoSizingGrid(container, items) {
  const firstColumnWidth = 'auto';
  const repeatColumnWidth = 'minmax(100px, 1fr)';
  const totalColumns = items.length + 1;
  
  applyGridTemplate(container, firstColumnWidth, repeatColumnWidth, totalColumns);
  
  // Add items
  items.forEach(item => {
    const element = document.createElement('div');
    element.className = 'grid-item';
    element.textContent = item;
    container.appendChild(element);
  });
}

// Example 8: Grid with minimum and maximum constraints
function createConstrainedGrid(container, firstColumnWidth, repeatColumnWidth, totalColumns) {
  const constrainedFirst = `minmax(150px, ${firstColumnWidth})`;
  const constrainedRepeat = `minmax(100px, ${repeatColumnWidth})`;
  
  applyGridTemplate(container, constrainedFirst, constrainedRepeat, totalColumns);
}

// Utility function to create grid items
function createGridItem(content, className = 'grid-item') {
  const item = document.createElement('div');
  item.className = className;
  item.textContent = content;
  return item;
}

// Example 9: Complex grid with different column types
function createComplexGrid(container, columns) {
  // columns: [{ width: '200px', type: 'fixed' }, { width: '1fr', type: 'flexible' }, { width: '2fr', type: 'flexible' }]
  
  let gridTemplate = '';
  columns.forEach((column, index) => {
    if (index === 0) {
      gridTemplate += column.width;
    } else {
      gridTemplate += ` ${column.width}`;
    }
  });
  
  container.style.gridTemplateColumns = gridTemplate;
}

// Example 10: Grid with auto-fit for responsive behavior
function createAutoFitGrid(container, firstColumnWidth, repeatColumnWidth, minColumnWidth = '200px') {
  const gridTemplate = `${firstColumnWidth} repeat(auto-fit, minmax(${minColumnWidth}, ${repeatColumnWidth}))`;
  container.style.gridTemplateColumns = gridTemplate;
}

// Export functions for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    createFlexibleGridTemplate,
    applyGridTemplate,
    createDynamicGrid,
    createResponsiveGrid,
    createGridWithClasses,
    createAutoSizingGrid,
    createConstrainedGrid,
    createComplexGrid,
    createAutoFitGrid
  };
} 