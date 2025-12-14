<?php

if (!function_exists('render_stars')) {
    function render_stars(float $rating): string
    {
        $html = '';
        $full_stars = floor($rating);
        $has_half = ($rating - $full_stars) >= 0.5;
        $empty_stars = 5 - ceil($rating);

        for ($i = 0; $i < $full_stars; $i++) {
            $html .= '<i class="fas fa-star"></i>';
        }
        if ($has_half) {
            $html .= '<i class="fas fa-star-half-alt"></i>';
        }
        for ($i = 0; $i < $empty_stars; $i++) {
            $html .= '<i class="far fa-star"></i>';
        }
        return $html;
    }
}
?>