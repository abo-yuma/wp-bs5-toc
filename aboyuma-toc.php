<?php

/**
 * Plugin Name: ABO YUMA Table of Contents
 * Description: Automatically generates a Table of Contents from H2 and H3 headings.
 * Version: 1.0.0
 * Author: ABO YUMA
 */

defined('ABSPATH') || exit;

/**
 * Generate TOC and inject into content
 */
function aboyuma_toc_generate($content) {

    if (!is_singular('post')) return $content;

    preg_match_all('/<h([2-3])(.*?)>(.*?)<\/h[2-3]>/', $content, $matches, PREG_OFFSET_CAPTURE);

    if (empty($matches[0])) return $content;

    $toc = '<nav class="toc"><strong>Table of Contents</strong><ul>';
    $current_h2_open = false;

    foreach ($matches[0] as $i => $match) {

        $level = $matches[1][$i][0];
        $title = strip_tags($matches[3][$i][0]);
        $id = 'toc-' . $i;

        // Replace heading with ID
        $original_heading = $match[0];
        $new_heading = '<h' . $level . ' id="' . esc_attr($id) . '">' . $matches[3][$i][0] . '</h' . $level . '>';

        $content = str_replace($original_heading, $new_heading, $content);

        // Build TOC
        if ($level == 2) {

            if ($current_h2_open) {
                $toc .= '</ul></li>';
            }

            $toc .= '<li><a href="#' . esc_attr($id) . '">' . esc_html($title) . '</a><ul>';
            $current_h2_open = true;
        } elseif ($level == 3 && $current_h2_open) {

            $toc .= '<li><a href="#' . esc_attr($id) . '">' . esc_html($title) . '</a></li>';
        }
    }

    if ($current_h2_open) {
        $toc .= '</ul></li>';
    }

    $toc .= '</ul></nav>';

    return $toc . $content;
}
add_filter('the_content', 'aboyuma_toc_generate');


/**
 * Add inline CSS
 */
function aboyuma_toc_styles() {

    $css = "
    .toc {
        font-size: 15px;
        line-height: 1.6;
        border: 1px solid var(--bs-border-color, #ddd);
        padding: 16px;
        border-radius: 8px;
        background: var(--bs-body-bg, #fff);
        margin-bottom: 1.5rem;
    }

    .toc strong {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
    }

    .toc ul {
        margin: 0;
        padding-left: 18px;
        list-style-type: square;
    }

    .toc ul ul {
        margin-top: 4px;
    }

    .toc li {
        margin: 4px 0;
    }

    .toc a {
        text-decoration: none;
    }

    .toc a:hover {
        text-decoration: underline;
    }
    ";

    wp_register_style('aboyuma-toc-style', false);
    wp_enqueue_style('aboyuma-toc-style');
    wp_add_inline_style('aboyuma-toc-style', $css);
}
add_action('wp_enqueue_scripts', 'aboyuma_toc_styles');
