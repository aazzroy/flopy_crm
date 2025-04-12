<?php
/**
 * URL Helper
 * 
 * This file contains helper functions for URL manipulation and redirection.
 */

/**
 * Redirect to a specified page
 * 
 * @param string $page Page to redirect to
 * @return void
 */
function redirect($page) {
    header('location: ' . URLROOT . '/' . $page);
    exit;
}

/**
 * Get the current page URL
 * 
 * @return string Current URL
 */
function currentUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
        "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

/**
 * Get base URL with optional path
 * 
 * @param string $path Path to append to base URL
 * @return string Base URL with optional path
 */
function baseUrl($path = '') {
    return URLROOT . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Get assets URL (CSS, JS, images)
 * 
 * @param string $path Path to the asset
 * @return string Asset URL
 */
function assetUrl($path) {
    return PUBLICROOT . '/' . ltrim($path, '/');
}

/**
 * Check if current URL matches a pattern
 * 
 * @param string $pattern URL pattern to check against
 * @return boolean True if matches, false otherwise
 */
function urlIs($pattern) {
    $path = $_SERVER['REQUEST_URI'];
    
    // If not absolute URL pattern, assume relative to URLROOT
    if(strpos($pattern, 'http') !== 0) {
        $pattern = baseUrl($pattern);
    }
    
    return $path === parse_url($pattern, PHP_URL_PATH) || 
           strpos($path, parse_url($pattern, PHP_URL_PATH)) === 0;
}

/**
 * Generate pagination links
 * 
 * @param int $page Current page
 * @param int $totalPages Total number of pages
 * @param string $url Base URL for pagination links
 * @return string HTML for pagination links
 */
function paginationLinks($page, $totalPages, $url = '') {
    if($totalPages <= 1) {
        return '';
    }
    
    $output = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if($page > 1) {
        $output .= '<li class="page-item"><a class="page-link" href="' . 
                  $url . '?page=' . ($page - 1) . '" aria-label="Previous">&laquo;</a></li>';
    } else {
        $output .= '<li class="page-item disabled"><a class="page-link" href="#" aria-label="Previous">&laquo;</a></li>';
    }
    
    // Page numbers
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);
    
    if($startPage > 1) {
        $output .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=1">1</a></li>';
        if($startPage > 2) {
            $output .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
    }
    
    for($i = $startPage; $i <= $endPage; $i++) {
        if($i == $page) {
            $output .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $output .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    if($endPage < $totalPages) {
        if($endPage < $totalPages - 1) {
            $output .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
        $output .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if($page < $totalPages) {
        $output .= '<li class="page-item"><a class="page-link" href="' . 
                  $url . '?page=' . ($page + 1) . '" aria-label="Next">&raquo;</a></li>';
    } else {
        $output .= '<li class="page-item disabled"><a class="page-link" href="#" aria-label="Next">&raquo;</a></li>';
    }
    
    $output .= '</ul></nav>';
    
    return $output;
} 