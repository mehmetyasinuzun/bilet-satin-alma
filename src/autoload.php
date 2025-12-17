<?php
/**
 * PSR-4 Autoloader
 * 
 * OOP Prensipleri:
 * - Namespace-based class loading
 * - Lazy loading pattern
 */

spl_autoload_register(function ($class) {
    // Namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $baseDir = __DIR__ . '/';
    
    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }
    
    // Get the relative class name
    $relativeClass = substr($class, $len);
    
    // Replace the namespace prefix with the base directory
    // Replace namespace separators with directory separators
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
