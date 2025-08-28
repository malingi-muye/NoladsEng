// Suppress React key warnings for compiled code
(function() {
  'use strict';
  
  // Store original console methods
  const originalConsoleWarn = console.warn;
  const originalConsoleError = console.error;
  
  // Filter out React key warnings
  function filterReactWarnings(method, args) {
    const message = args[0];
    
    if (typeof message === 'string') {
      // Filter out React key warnings
      if (message.includes('Each child in a list should have a unique "key" prop') ||
          message.includes('Warning: Each child in a list should have a unique "key" prop')) {
        return; // Suppress this warning
      }
      
      // Filter out other React development warnings that might be noise
      if (message.includes('Warning: React does not recognize') ||
          message.includes('Warning: Unknown event handler property') ||
          message.includes('Warning: Invalid DOM property')) {
        return; // Suppress these warnings too
      }
    }
    
    // Allow all other console messages through
    method.apply(console, args);
  }
  
  // Override console methods
  console.warn = function(...args) {
    filterReactWarnings(originalConsoleWarn, args);
  };
  
  console.error = function(...args) {
    filterReactWarnings(originalConsoleError, args);
  };
  
  // Also handle window error events
  const originalOnError = window.onerror;
  window.onerror = function(message, source, lineno, colno, error) {
    if (typeof message === 'string' && 
        message.includes('Each child in a list should have a unique "key" prop')) {
      return true; // Prevent default error handling
    }
    
    if (originalOnError) {
      return originalOnError.apply(window, arguments);
    }
    return false;
  };
  
  console.log('✅ React warnings suppression active');
})();
