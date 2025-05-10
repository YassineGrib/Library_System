<?php
/**
 * Helper functions for the application
 */

/**
 * Generate a URL for the application
 * 
 * @param string $path The path to generate a URL for
 * @return string The full URL
 */
function url($path = '') {
    // Remove leading slash if present
    $path = ltrim($path, '/');
    
    // Base URL is always the same
    $baseUrl = '/Library_System/';
    
    // Return the full URL
    return $baseUrl . $path;
}

/**
 * Redirect to a URL
 * 
 * @param string $path The path to redirect to
 */
function redirect($path = '') {
    header('Location: ' . url($path));
    exit;
}

/**
 * Get the current URL path
 * 
 * @return string The current URL path
 */
function current_path() {
    $uri = $_SERVER['REQUEST_URI'];
    
    // Remove base path
    $basePathPattern = '/Library_System/';
    if (strpos($uri, $basePathPattern) === 0) {
        $uri = substr($uri, strlen($basePathPattern));
    }
    
    // Remove query string
    $uri = strtok($uri, '?');
    
    // Trim slashes
    $uri = trim($uri, '/');
    
    return $uri;
}

/**
 * Check if the current URL path matches a pattern
 * 
 * @param string $pattern The pattern to match
 * @return bool Whether the current URL path matches the pattern
 */
function is_current_path($pattern) {
    $path = current_path();
    
    // Exact match
    if ($path === $pattern) {
        return true;
    }
    
    // Pattern match
    if (strpos($pattern, '*') !== false) {
        $pattern = str_replace('*', '.*', $pattern);
        return preg_match('#^' . $pattern . '$#', $path) === 1;
    }
    
    return false;
}
