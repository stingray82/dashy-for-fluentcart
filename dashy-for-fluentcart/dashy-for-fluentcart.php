<?php
/**
 * Plugin Name:       Dashy For FluentCart
 * Description:       Easily add custom tabs to the FluentCart customer dashboard using pages, posts, custom post types, or shortcodes, with custom icons and menu positioning.
 * Tested up to:      7.1.1
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Version:           1.0.4
 * Author:            ReallyUsefulPlugins.com
 * Author URI:        https://reallyusefulplugins.com
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain:       dashy-for-fluentcart
 * Website:           https://reallyusefulplugins.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define('RUP_DASHY_FC_VERSION', '1.0.4');
define( 'RUP_DASHY_FC_PLUGIN_FILE', __FILE__ );
define( 'RUP_DASHY_FC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RUP_DASHY_FC_OPTION', 'rup_dashy_fc_tabs' );
define( 'RUP_DASHY_FC_MENU_CACHE', 'rup_dashy_fc_customer_menu_positions' );

/**
 * Default FluentCart-compatible icon.
 */
function rup_dashy_fc_default_icon_svg() {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M4 12h16M4 19h16"/><path d="M7 3v4M12 10v4M17 17v4"/></svg>';
}

/**
 * SVG allow-list used for saved/rendered dashboard icons.
 */
function rup_dashy_fc_svg_allowed_html() {
    return array(
        'svg' => array(
            'xmlns' => true, 'xmlns:xlink' => true, 'width' => true, 'height' => true,
            'viewbox' => true, 'fill' => true, 'stroke' => true, 'style' => true,
            'class' => true, 'aria-hidden' => true, 'role' => true,
            'preserveaspectratio' => true, 'version' => true,
        ),
        'g' => array(
            'fill' => true, 'stroke' => true, 'transform' => true, 'class' => true,
            'opacity' => true, 'clip-path' => true, 'mask' => true,
        ),
        'path' => array(
            'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,
            'stroke-linecap' => true, 'stroke-linejoin' => true, 'stroke-miterlimit' => true,
            'fill-rule' => true, 'clip-rule' => true, 'opacity' => true, 'transform' => true,
            'class' => true, 'clip-path' => true, 'mask' => true,
        ),
        'rect' => array(
            'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true,
            'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,
            'opacity' => true, 'transform' => true, 'class' => true, 'clip-path' => true,
        ),
        'circle' => array(
            'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true,
            'stroke-width' => true, 'opacity' => true, 'transform' => true, 'class' => true,
            'clip-path' => true,
        ),
        'ellipse' => array(
            'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true,
            'stroke' => true, 'stroke-width' => true, 'opacity' => true, 'transform' => true,
            'class' => true, 'clip-path' => true,
        ),
        'line' => array(
            'x1' => true, 'x2' => true, 'y1' => true, 'y2' => true, 'stroke' => true,
            'stroke-width' => true, 'stroke-linecap' => true, 'opacity' => true,
            'transform' => true, 'class' => true,
        ),
        'polyline' => array(
            'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,
            'stroke-linecap' => true, 'stroke-linejoin' => true, 'opacity' => true,
            'transform' => true, 'class' => true,
        ),
        'polygon' => array(
            'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,
            'stroke-linejoin' => true, 'fill-rule' => true, 'opacity' => true,
            'transform' => true, 'class' => true,
        ),
        'defs' => array(),
        'clippath' => array( 'id' => true, 'clipPathUnits' => true, 'clippathunits' => true ),
        'mask' => array(
            'id' => true, 'x' => true, 'y' => true, 'width' => true, 'height' => true,
            'maskUnits' => true, 'maskunits' => true,
        ),
        'use' => array(
            'href' => true, 'xlink:href' => true, 'x' => true, 'y' => true, 'width' => true,
            'height' => true, 'fill' => true, 'stroke' => true, 'transform' => true, 'class' => true,
        ),
        'symbol' => array( 'id' => true, 'viewbox' => true, 'preserveaspectratio' => true ),
        'lineargradient' => array(
            'id' => true, 'x1' => true, 'x2' => true, 'y1' => true, 'y2' => true,
            'gradientunits' => true, 'gradienttransform' => true,
        ),
        'radialgradient' => array(
            'id' => true, 'cx' => true, 'cy' => true, 'r' => true, 'fx' => true, 'fy' => true,
            'gradientunits' => true, 'gradienttransform' => true,
        ),
        'stop' => array( 'offset' => true, 'stop-color' => true, 'stop-opacity' => true ),
        'title' => array(),
        'desc' => array(),
    );
}

/**
 * Add our icon markup to FluentCart's sanitizer rather than replacing its list.
 */
add_filter( 'fct_allowed_svg_tags', function( $allowed ) {
    $allowed = is_array( $allowed ) ? $allowed : array();
    foreach ( rup_dashy_fc_svg_allowed_html() as $tag => $attributes ) {
        $allowed[ $tag ] = isset( $allowed[ $tag ] ) && is_array( $allowed[ $tag ] )
            ? array_merge( $allowed[ $tag ], $attributes )
            : $attributes;
    }
    return $allowed;
} );

function rup_dashy_fc_sanitize_inline_svg( $svg ) {
    $clean = wp_kses( (string) $svg, rup_dashy_fc_svg_allowed_html() );
    return trim( $clean );
}

/**
 * Compact SVG number formatting.
 */
function rup_dashy_fc_svg_number( $number ) {
    $number = round( (float) $number, 6 );
    $out = rtrim( rtrim( sprintf( '%.6F', $number ), '0' ), '.' );
    return ( '' === $out || '-0' === $out ) ? '0' : $out;
}

/**
 * Convert a path that depends on fill-rule="evenodd" into ordinary non-zero
 * winding geometry. FluentCart's final icon sanitization currently preserves
 * path `d` and `fill` but can remove fill-rule/clip-rule, so we make the path
 * visually independent of those attributes before handing it to FluentCart.
 *
 * The first closed subpath keeps its direction; subsequent closed subpaths are
 * reversed so they become holes under the default non-zero fill rule. This is
 * the common structure used by compound icon glyphs (including SVGRepo's
 * accessibility icon).
 */
function rup_dashy_fc_evenodd_path_to_nonzero( $d ) {
    preg_match_all( '/[a-zA-Z]|[-+]?(?:\d*\.\d+|\d+\.?)(?:[eE][-+]?\d+)?/', (string) $d, $matches );
    $tokens = $matches[0];
    if ( empty( $tokens ) ) {
        return (string) $d;
    }

    $counts = array( 'M'=>2, 'L'=>2, 'H'=>1, 'V'=>1, 'C'=>6, 'S'=>4, 'Q'=>4, 'T'=>2, 'A'=>7, 'Z'=>0 );
    $i = 0;
    $cmd = '';
    $x = 0.0;
    $y = 0.0;
    $sub_start = array( 0.0, 0.0 );
    $last_cubic_ctrl = null;
    $last_quad_ctrl  = null;
    $subpaths = array();
    $current = null;

    $flush = function() use ( &$current, &$subpaths ) {
        if ( is_array( $current ) ) {
            $subpaths[] = $current;
        }
        $current = null;
    };

    while ( $i < count( $tokens ) ) {
        if ( preg_match( '/^[a-zA-Z]$/', $tokens[ $i ] ) ) {
            $cmd = $tokens[ $i++ ];
        } elseif ( '' === $cmd ) {
            return (string) $d;
        }

        $upper = strtoupper( $cmd );
        $relative = ( $cmd !== $upper );
        if ( ! isset( $counts[ $upper ] ) ) {
            return (string) $d;
        }

        if ( 'Z' === $upper ) {
            if ( is_array( $current ) ) {
                $current['closed'] = true;
            }
            $x = $sub_start[0];
            $y = $sub_start[1];
            $last_cubic_ctrl = null;
            $last_quad_ctrl = null;
            $cmd = '';
            continue;
        }

        $need = $counts[ $upper ];
        if ( $i + $need > count( $tokens ) ) {
            break;
        }

        $vals = array();
        for ( $n = 0; $n < $need; $n++ ) {
            if ( preg_match( '/^[a-zA-Z]$/', $tokens[ $i + $n ] ) ) {
                break 2;
            }
            $vals[] = (float) $tokens[ $i + $n ];
        }
        $i += $need;

        $from = array( $x, $y );

        if ( 'M' === $upper ) {
            $nx = $relative ? $x + $vals[0] : $vals[0];
            $ny = $relative ? $y + $vals[1] : $vals[1];
            $flush();
            $current = array( 'start' => array( $nx, $ny ), 'segments' => array(), 'closed' => false );
            $x = $nx; $y = $ny; $sub_start = array( $x, $y );
            $last_cubic_ctrl = null; $last_quad_ctrl = null;
            // Subsequent coordinate pairs after M are implicit L commands.
            $cmd = $relative ? 'l' : 'L';
            continue;
        }

        if ( ! is_array( $current ) ) {
            return (string) $d;
        }

        switch ( $upper ) {
            case 'L':
                $nx = $relative ? $x + $vals[0] : $vals[0];
                $ny = $relative ? $y + $vals[1] : $vals[1];
                $current['segments'][] = array( 'type'=>'L', 'from'=>$from, 'to'=>array($nx,$ny) );
                $x=$nx; $y=$ny; $last_cubic_ctrl=null; $last_quad_ctrl=null;
                break;
            case 'H':
                $nx = $relative ? $x + $vals[0] : $vals[0];
                $current['segments'][] = array( 'type'=>'L', 'from'=>$from, 'to'=>array($nx,$y) );
                $x=$nx; $last_cubic_ctrl=null; $last_quad_ctrl=null;
                break;
            case 'V':
                $ny = $relative ? $y + $vals[0] : $vals[0];
                $current['segments'][] = array( 'type'=>'L', 'from'=>$from, 'to'=>array($x,$ny) );
                $y=$ny; $last_cubic_ctrl=null; $last_quad_ctrl=null;
                break;
            case 'C':
                $c1=array($relative?$x+$vals[0]:$vals[0],$relative?$y+$vals[1]:$vals[1]);
                $c2=array($relative?$x+$vals[2]:$vals[2],$relative?$y+$vals[3]:$vals[3]);
                $to=array($relative?$x+$vals[4]:$vals[4],$relative?$y+$vals[5]:$vals[5]);
                $current['segments'][]=array('type'=>'C','from'=>$from,'c1'=>$c1,'c2'=>$c2,'to'=>$to);
                $x=$to[0];$y=$to[1];$last_cubic_ctrl=$c2;$last_quad_ctrl=null;
                break;
            case 'S':
                $c1 = $last_cubic_ctrl ? array( 2*$x-$last_cubic_ctrl[0], 2*$y-$last_cubic_ctrl[1] ) : array($x,$y);
                $c2=array($relative?$x+$vals[0]:$vals[0],$relative?$y+$vals[1]:$vals[1]);
                $to=array($relative?$x+$vals[2]:$vals[2],$relative?$y+$vals[3]:$vals[3]);
                $current['segments'][]=array('type'=>'C','from'=>$from,'c1'=>$c1,'c2'=>$c2,'to'=>$to);
                $x=$to[0];$y=$to[1];$last_cubic_ctrl=$c2;$last_quad_ctrl=null;
                break;
            case 'Q':
                $c=array($relative?$x+$vals[0]:$vals[0],$relative?$y+$vals[1]:$vals[1]);
                $to=array($relative?$x+$vals[2]:$vals[2],$relative?$y+$vals[3]:$vals[3]);
                $current['segments'][]=array('type'=>'Q','from'=>$from,'c'=>$c,'to'=>$to);
                $x=$to[0];$y=$to[1];$last_quad_ctrl=$c;$last_cubic_ctrl=null;
                break;
            case 'T':
                $c = $last_quad_ctrl ? array( 2*$x-$last_quad_ctrl[0], 2*$y-$last_quad_ctrl[1] ) : array($x,$y);
                $to=array($relative?$x+$vals[0]:$vals[0],$relative?$y+$vals[1]:$vals[1]);
                $current['segments'][]=array('type'=>'Q','from'=>$from,'c'=>$c,'to'=>$to);
                $x=$to[0];$y=$to[1];$last_quad_ctrl=$c;$last_cubic_ctrl=null;
                break;
            case 'A':
                $to=array($relative?$x+$vals[5]:$vals[5],$relative?$y+$vals[6]:$vals[6]);
                $current['segments'][]=array('type'=>'A','from'=>$from,'rx'=>$vals[0],'ry'=>$vals[1],'rot'=>$vals[2],'large'=>(int)$vals[3],'sweep'=>(int)$vals[4],'to'=>$to);
                $x=$to[0];$y=$to[1];$last_cubic_ctrl=null;$last_quad_ctrl=null;
                break;
        }
    }
    $flush();

    if ( count( $subpaths ) < 2 ) {
        return (string) $d;
    }

    $parts = array();
    foreach ( $subpaths as $index => $sub ) {
        $segments = $sub['segments'];
        $reverse = ( $index > 0 && ! empty( $sub['closed'] ) );
        if ( $reverse && ! empty( $segments ) ) {
            $start = $segments[ count( $segments ) - 1 ]['to'];
            $parts[] = 'M' . rup_dashy_fc_svg_number($start[0]) . ' ' . rup_dashy_fc_svg_number($start[1]);
            foreach ( array_reverse( $segments ) as $seg ) {
                if ( 'L' === $seg['type'] ) {
                    $parts[] = 'L' . rup_dashy_fc_svg_number($seg['from'][0]) . ' ' . rup_dashy_fc_svg_number($seg['from'][1]);
                } elseif ( 'C' === $seg['type'] ) {
                    $parts[] = 'C' . rup_dashy_fc_svg_number($seg['c2'][0]) . ' ' . rup_dashy_fc_svg_number($seg['c2'][1]) . ' ' . rup_dashy_fc_svg_number($seg['c1'][0]) . ' ' . rup_dashy_fc_svg_number($seg['c1'][1]) . ' ' . rup_dashy_fc_svg_number($seg['from'][0]) . ' ' . rup_dashy_fc_svg_number($seg['from'][1]);
                } elseif ( 'Q' === $seg['type'] ) {
                    $parts[] = 'Q' . rup_dashy_fc_svg_number($seg['c'][0]) . ' ' . rup_dashy_fc_svg_number($seg['c'][1]) . ' ' . rup_dashy_fc_svg_number($seg['from'][0]) . ' ' . rup_dashy_fc_svg_number($seg['from'][1]);
                } elseif ( 'A' === $seg['type'] ) {
                    $parts[] = 'A' . rup_dashy_fc_svg_number($seg['rx']) . ' ' . rup_dashy_fc_svg_number($seg['ry']) . ' ' . rup_dashy_fc_svg_number($seg['rot']) . ' ' . (int)$seg['large'] . ' ' . ( $seg['sweep'] ? 0 : 1 ) . ' ' . rup_dashy_fc_svg_number($seg['from'][0]) . ' ' . rup_dashy_fc_svg_number($seg['from'][1]);
                }
            }
        } else {
            $parts[] = 'M' . rup_dashy_fc_svg_number($sub['start'][0]) . ' ' . rup_dashy_fc_svg_number($sub['start'][1]);
            foreach ( $segments as $seg ) {
                if ( 'L' === $seg['type'] ) {
                    $parts[] = 'L' . rup_dashy_fc_svg_number($seg['to'][0]) . ' ' . rup_dashy_fc_svg_number($seg['to'][1]);
                } elseif ( 'C' === $seg['type'] ) {
                    $parts[] = 'C' . rup_dashy_fc_svg_number($seg['c1'][0]) . ' ' . rup_dashy_fc_svg_number($seg['c1'][1]) . ' ' . rup_dashy_fc_svg_number($seg['c2'][0]) . ' ' . rup_dashy_fc_svg_number($seg['c2'][1]) . ' ' . rup_dashy_fc_svg_number($seg['to'][0]) . ' ' . rup_dashy_fc_svg_number($seg['to'][1]);
                } elseif ( 'Q' === $seg['type'] ) {
                    $parts[] = 'Q' . rup_dashy_fc_svg_number($seg['c'][0]) . ' ' . rup_dashy_fc_svg_number($seg['c'][1]) . ' ' . rup_dashy_fc_svg_number($seg['to'][0]) . ' ' . rup_dashy_fc_svg_number($seg['to'][1]);
                } elseif ( 'A' === $seg['type'] ) {
                    $parts[] = 'A' . rup_dashy_fc_svg_number($seg['rx']) . ' ' . rup_dashy_fc_svg_number($seg['ry']) . ' ' . rup_dashy_fc_svg_number($seg['rot']) . ' ' . (int)$seg['large'] . ' ' . (int)$seg['sweep'] . ' ' . rup_dashy_fc_svg_number($seg['to'][0]) . ' ' . rup_dashy_fc_svg_number($seg['to'][1]);
                }
            }
        }
        if ( ! empty( $sub['closed'] ) ) {
            $parts[] = 'Z';
        }
    }

    return implode( ' ', $parts );
}

/**
 * Convert any even-odd paths before FluentCart gets a chance to remove the
 * rule attributes.
 */
function rup_dashy_fc_flatten_evenodd_paths( $svg ) {
    return preg_replace_callback(
        '/<path\b([^>]*)>/i',
        function( $match ) {
            $attrs = $match[1];
            if ( ! preg_match( '/\b(?:fill-rule|clip-rule)\s*=\s*(["\'])evenodd\1/i', $attrs ) ) {
                return $match[0];
            }
            if ( ! preg_match( '/\bd\s*=\s*(["\'])(.*?)\1/is', $attrs, $d_match ) ) {
                return $match[0];
            }
            $converted = rup_dashy_fc_evenodd_path_to_nonzero( html_entity_decode( $d_match[2], ENT_QUOTES ) );
            $attrs = preg_replace( '/\bd\s*=\s*(["\'])(.*?)\1/is', 'd="' . esc_attr( $converted ) . '"', $attrs, 1 );
            $attrs = preg_replace( '/\s+(?:fill-rule|clip-rule)\s*=\s*(["\']).*?\1/i', '', $attrs );
            return '<path' . $attrs . '>';
        },
        (string) $svg
    );
}

/**
 * Apply a uniform scale/translation directly to SVG path coordinates.
 * Keeping the transform inside `d` avoids relying on SVG attributes that a
 * later sanitizer may remove.
 */
function rup_dashy_fc_transform_path_d( $d, $scale, $tx, $ty ) {
    preg_match_all( '/[a-zA-Z]|[-+]?(?:\d*\.\d+|\d+\.?)(?:[eE][-+]?\d+)?/', (string) $d, $matches );
    $tokens = $matches[0];
    $counts = array( 'M'=>2, 'L'=>2, 'H'=>1, 'V'=>1, 'C'=>6, 'S'=>4, 'Q'=>4, 'T'=>2, 'A'=>7, 'Z'=>0 );
    $out = array();
    $i = 0;
    $cmd = '';
    $first_moveto_group = false;

    while ( $i < count( $tokens ) ) {
        if ( preg_match( '/^[a-zA-Z]$/', $tokens[ $i ] ) ) {
            $cmd = $tokens[ $i++ ];
            $first_moveto_group = ( 'M' === strtoupper( $cmd ) );
        } elseif ( '' === $cmd ) {
            return (string) $d;
        }

        $upper = strtoupper( $cmd );
        $relative = ( $cmd !== $upper );
        if ( ! isset( $counts[ $upper ] ) ) {
            return (string) $d;
        }
        if ( 'Z' === $upper ) {
            $out[] = 'Z';
            $cmd = '';
            continue;
        }

        $need = $counts[ $upper ];
        if ( $i + $need > count( $tokens ) ) {
            break;
        }
        for ( $n = 0; $n < $need; $n++ ) {
            if ( preg_match( '/^[a-zA-Z]$/', $tokens[ $i + $n ] ) ) {
                return implode( ' ', $out );
            }
        }
        $vals = array_map( 'floatval', array_slice( $tokens, $i, $need ) );
        $i += $need;

        $emit_cmd = $cmd;
        if ( 'M' === $upper && ! $first_moveto_group ) {
            $emit_cmd = $relative ? 'l' : 'L';
            $upper = 'L';
        }

        $pos = function( $value, $offset ) use ( $scale, $relative ) {
            return ( $value * $scale ) + ( $relative ? 0 : $offset );
        };
        $delta = function( $value ) use ( $scale ) { return $value * $scale; };

        switch ( $upper ) {
            case 'M':
            case 'L':
            case 'T':
                $vals[0] = $pos( $vals[0], $tx );
                $vals[1] = $pos( $vals[1], $ty );
                break;
            case 'H':
                $vals[0] = $pos( $vals[0], $tx );
                break;
            case 'V':
                $vals[0] = $pos( $vals[0], $ty );
                break;
            case 'C':
                for ( $n = 0; $n < 6; $n += 2 ) {
                    $vals[$n]   = $pos( $vals[$n], $tx );
                    $vals[$n+1] = $pos( $vals[$n+1], $ty );
                }
                break;
            case 'S':
            case 'Q':
                for ( $n = 0; $n < 4; $n += 2 ) {
                    $vals[$n]   = $pos( $vals[$n], $tx );
                    $vals[$n+1] = $pos( $vals[$n+1], $ty );
                }
                break;
            case 'A':
                $vals[0] = abs( $delta( $vals[0] ) );
                $vals[1] = abs( $delta( $vals[1] ) );
                $vals[5] = $pos( $vals[5], $tx );
                $vals[6] = $pos( $vals[6], $ty );
                break;
        }

        $out[] = $emit_cmd . implode( ' ', array_map( 'rup_dashy_fc_svg_number', $vals ) );
        if ( 'M' === strtoupper( $cmd ) ) {
            $first_moveto_group = false;
        }
    }

    return implode( ' ', $out );
}

/**
 * Bake the source viewBox into a fixed 20x20 coordinate system. Path geometry
 * is rewritten directly, so the icon remains correctly scaled even when
 * FluentCart's final sanitizer removes viewBox/transform attributes.
 */
function rup_dashy_fc_bake_viewbox( $svg ) {
    if ( ! preg_match( '/<svg\b([^>]*)>(.*)<\/svg>/is', (string) $svg, $match ) ) {
        return (string) $svg;
    }

    $attrs = $match[1];
    $inner = $match[2];
    if ( ! preg_match( '/\bviewBox\s*=\s*(["\'])\s*([-+\d.eE]+)[,\s]+([-+\d.eE]+)[,\s]+([-+\d.eE]+)[,\s]+([-+\d.eE]+)\s*\1/i', $attrs, $vb ) ) {
        return (string) $svg;
    }

    $min_x = (float) $vb[2]; $min_y = (float) $vb[3];
    $vb_w = (float) $vb[4]; $vb_h = (float) $vb[5];
    if ( $vb_w <= 0 || $vb_h <= 0 ) {
        return (string) $svg;
    }

    $scale = min( 20 / $vb_w, 20 / $vb_h );
    $tx = ( 20 - ( $vb_w * $scale ) ) / 2 - ( $min_x * $scale );
    $ty = ( 20 - ( $vb_h * $scale ) ) / 2 - ( $min_y * $scale );

    $inner = preg_replace_callback(
        '/<path\b([^>]*)>/i',
        function( $path_match ) use ( $scale, $tx, $ty ) {
            $path_attrs = $path_match[1];
            if ( ! preg_match( '/\bd\s*=\s*(["\'])(.*?)\1/is', $path_attrs, $d_match ) ) {
                return $path_match[0];
            }
            $new_d = rup_dashy_fc_transform_path_d( html_entity_decode( $d_match[2], ENT_QUOTES ), $scale, $tx, $ty );
            $path_attrs = preg_replace( '/\bd\s*=\s*(["\'])(.*?)\1/is', 'd="' . esc_attr( $new_d ) . '"', $path_attrs, 1 );
            return '<path' . $path_attrs . '>';
        },
        $inner
    );

    $attrs = preg_replace( '/\s+(?:width|height|viewBox|viewbox)\s*=\s*(["\']).*?\1/i', '', $attrs );
    $attrs .= ' width="20" height="20" viewBox="0 0 20 20"';

    return '<svg' . $attrs . '>' . $inner . '</svg>';
}

/**
 * Normalise an inline SVG for FluentCart's navigation.
 */
/**
 * Convert an SVG's own colours to currentColor so it follows FluentCart's
 * customer navigation colour scheme. Geometry-related values such as none,
 * transparent and paint-server references are preserved. Gradient stop colours
 * are normalised too, allowing complex SVGs to become monochrome without
 * destroying their structure.
 */
function rup_dashy_fc_normalize_svg_colours( $svg ) {
    $svg = (string) $svg;

    $svg = preg_replace_callback(
        '/\b(fill|stroke|stop-color)\s*=\s*(["\'])(.*?)\2/i',
        function( $match ) {
            $attribute = strtolower( $match[1] );
            $quote     = $match[2];
            $value     = trim( $match[3] );
            $lower     = strtolower( $value );

            if ( '' === $value || in_array( $lower, array( 'none', 'transparent', 'currentcolor', 'inherit' ), true ) || str_starts_with( $lower, 'url(' ) ) {
                return $match[0];
            }

            return $attribute . '=' . $quote . 'currentColor' . $quote;
        },
        $svg
    );

    return $svg;
}

function rup_dashy_fc_normalize_svg( $svg, $normalize_colours = false ) {
    $svg = rup_dashy_fc_flatten_evenodd_paths( (string) $svg );
    $svg = rup_dashy_fc_bake_viewbox( $svg );
    if ( $normalize_colours ) {
        $svg = rup_dashy_fc_normalize_svg_colours( $svg );
    }
    $svg = rup_dashy_fc_sanitize_inline_svg( $svg );
    if ( ! $svg ) {
        return '';
    }

    $is_stroke_icon = (bool) preg_match( '/\bstroke\s*=\s*["\'][^"\']+["\']/i', $svg );

    if ( preg_match( '/<svg\b([^>]*)>/i', $svg, $match ) ) {
        $attrs = $match[1];
        $attrs = preg_replace( '/\s+(?:width|height)\s*=\s*(["\']).*?\1/i', '', $attrs );
        $attrs .= ' width="20" height="20"';

        if ( ! preg_match( '/\bclass\s*=/i', $attrs ) ) {
            $attrs .= ' class="dashy-fc-nav-icon' . ( $is_stroke_icon ? ' dashy-fc-nav-icon--stroke' : '' ) . '"';
        } else {
            $attrs = preg_replace_callback(
                '/\bclass\s*=\s*(["\'])(.*?)\1/i',
                function( $m ) use ( $is_stroke_icon ) {
                    $classes = trim( $m[2] . ' dashy-fc-nav-icon' . ( $is_stroke_icon ? ' dashy-fc-nav-icon--stroke' : '' ) );
                    return 'class=' . $m[1] . esc_attr( $classes ) . $m[1];
                },
                $attrs,
                1
            );
        }

        if ( $is_stroke_icon ) {
            if ( preg_match( '/\bfill\s*=\s*(["\']).*?\1/i', $attrs ) ) {
                $attrs = preg_replace( '/\bfill\s*=\s*(["\']).*?\1/i', 'fill="none"', $attrs, 1 );
            } else {
                $attrs .= ' fill="none"';
            }
        }

        if ( ! preg_match( '/\baria-hidden\s*=/i', $attrs ) ) {
            $attrs .= ' aria-hidden="true"';
        }

        $svg = preg_replace( '/<svg\b[^>]*>/i', '<svg' . $attrs . '>', $svg, 1 );
    }

    return rup_dashy_fc_sanitize_inline_svg( $svg );
}

/**
 * Convert an SVG icon setting into markup FluentCart can render.
 *
 * FluentCart's icon_svg contract is SVG-specific. Dashy therefore accepts
 * inline SVG or a local WordPress Media Library SVG attachment only.
 */
function rup_dashy_fc_icon_svg( $icon, $normalize_colours = false ) {
    $icon = trim( (string) $icon );
    if ( '' === $icon ) {
        return rup_dashy_fc_normalize_svg( rup_dashy_fc_default_icon_svg(), $normalize_colours );
    }

    if ( str_starts_with( ltrim( $icon ), '<svg' ) ) {
        $svg = rup_dashy_fc_normalize_svg( $icon, $normalize_colours );
        return $svg ?: rup_dashy_fc_normalize_svg( rup_dashy_fc_default_icon_svg(), $normalize_colours );
    }

    if ( filter_var( $icon, FILTER_VALIDATE_URL ) ) {
        $attachment_id = attachment_url_to_postid( $icon );
        if ( $attachment_id && 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) {
            $path = get_attached_file( $attachment_id );
            if ( $path && is_readable( $path ) ) {
                $contents = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                $svg = rup_dashy_fc_normalize_svg( $contents, $normalize_colours );
                if ( $svg ) {
                    return $svg;
                }
            }
        }

        return rup_dashy_fc_normalize_svg( rup_dashy_fc_default_icon_svg(), $normalize_colours );
    }

    return rup_dashy_fc_normalize_svg( rup_dashy_fc_default_icon_svg(), $normalize_colours );
}

/**
 * Keep Dashy icons at the same visual size as FluentCart's native icons and
 * neutralise broad theme/portal fill rules for outline SVGs only.
 */
add_action( 'fluent_cart/customer_dashboard/enqueue_assets', function() {
    $css = '
        .dashy-fc-nav-icon { width:20px !important; height:20px !important; flex:0 0 20px; display:block; }
        svg.dashy-fc-nav-icon--stroke { fill:none !important; }
    ';
    wp_register_style( 'dashy-for-fluentcart-icons', false, array(), RUP_DASHY_FC_VERSION );
    wp_enqueue_style( 'dashy-for-fluentcart-icons' );
    wp_add_inline_style( 'dashy-for-fluentcart-icons', $css );
}, 20 );

function rup_dashy_fc_get_tabs() {
    $tabs = get_option( RUP_DASHY_FC_OPTION, array() );
    return is_array( $tabs ) ? $tabs : array();
}

/**
 * Sanitize tab settings.
 */
function rup_dashy_fc_sanitize_tabs( $tabs ) {
    if ( ! is_array( $tabs ) ) {
        return array();
    }

    $clean = array();
    $used_slugs = array();

    foreach ( $tabs as $tab ) {
        if ( ! is_array( $tab ) ) {
            continue;
        }

        $name = sanitize_text_field( wp_unslash( $tab['name'] ?? '' ) );
        $slug = sanitize_title( wp_unslash( $tab['slug'] ?? '' ) );
        if ( ! $slug && $name ) {
            $slug = sanitize_title( $name );
        }
        if ( ! $name || ! $slug || in_array( $slug, $used_slugs, true ) ) {
            continue;
        }

        $content_type = sanitize_key( $tab['content_type'] ?? 'page' );
        if ( ! in_array( $content_type, array( 'page', 'shortcode' ), true ) ) {
            $content_type = 'page';
        }

        $post_type = sanitize_key( $tab['post_type'] ?? 'page' );
        $source    = trim( (string) wp_unslash( $tab['content_source'] ?? '' ) );
        if ( 'page' === $content_type ) {
            if ( is_numeric( $source ) ) {
                $source = (string) absint( $source );
            } else {
                $post = get_page_by_path( sanitize_title( $source ), OBJECT, $post_type );
                if ( $post instanceof WP_Post ) {
                    $source = (string) $post->ID;
                } else {
                    $source = sanitize_text_field( $source );
                }
            }
        } else {
            $source = wp_kses_post( $source );
        }

        $icon = trim( (string) wp_unslash( $tab['icon'] ?? '' ) );
        if ( str_starts_with( ltrim( $icon ), '<svg' ) ) {
            $icon = rup_dashy_fc_sanitize_inline_svg( $icon );
        } elseif ( filter_var( $icon, FILTER_VALIDATE_URL ) ) {
            $attachment_id = attachment_url_to_postid( $icon );
            $icon = ( $attachment_id && 'image/svg+xml' === get_post_mime_type( $attachment_id ) ) ? esc_url_raw( $icon ) : '';
        } else {
            $icon = '';
        }

        $insert_after = sanitize_key( $tab['insert_after'] ?? 'last' );
        if ( ! $insert_after ) {
            $insert_after = 'last';
        }

        $normalize_icon_colours = ! empty( $tab['normalize_icon_colours'] ) ? 1 : 0;

        $clean[] = array(
            'name'                   => $name,
            'slug'                   => $slug,
            'icon'                   => $icon,
            'normalize_icon_colours' => $normalize_icon_colours,
            'content_type'   => $content_type,
            'post_type'      => $post_type ?: 'page',
            'content_source' => $source,
            'insert_after'   => $insert_after,
        );
        $used_slugs[] = $slug;
    }

    return $clean;
}

add_action( 'admin_init', function() {
    register_setting(
        'rup_dashy_fc_options_group',
        RUP_DASHY_FC_OPTION,
        array( 'sanitize_callback' => 'rup_dashy_fc_sanitize_tabs' )
    );
} );

/**
 * Admin menu under FluentCart when available, with Tools fallback.
 */
add_action( 'admin_menu', function() {
    global $admin_page_hooks;
    if ( isset( $admin_page_hooks['fluent-cart'] ) ) {
        add_submenu_page(
            'fluent-cart',
            __( 'Dashy', 'dashy-for-fluentcart' ),
            __( 'Dashy', 'dashy-for-fluentcart' ),
            'manage_options',
            'dashy-for-fluentcart',
            'rup_dashy_fc_render_admin_page'
        );
    } else {
        add_management_page(
            __( 'Dashy For FluentCart', 'dashy-for-fluentcart' ),
            __( 'Dashy For FluentCart', 'dashy-for-fluentcart' ),
            'manage_options',
            'dashy-for-fluentcart',
            'rup_dashy_fc_render_admin_page'
        );
    }
}, 99 );

add_action( 'admin_enqueue_scripts', function() {
    $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
    if ( 'dashy-for-fluentcart' === $page ) {
        wp_enqueue_media();
        wp_enqueue_script( 'jquery' );
    }
} );

/**
 * AJAX helper for content picker.
 */
add_action( 'wp_ajax_rup_dashy_fc_get_content_sources', function() {
    check_ajax_referer( 'rup_dashy_fc_content_sources', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'dashy-for-fluentcart' ) ), 403 );
    }

    $post_type = sanitize_key( wp_unslash( $_POST['post_type'] ?? 'page' ) );
    $object = get_post_type_object( $post_type );
    if ( ! $object || empty( $object->public ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid post type.', 'dashy-for-fluentcart' ) ), 400 );
    }

    $ids = get_posts( array(
        'post_type'      => $post_type,
        'post_status'    => array( 'publish', 'private' ),
        'posts_per_page' => 300,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ) );

    $results = array();
    foreach ( $ids as $id ) {
        $post = get_post( $id );
        if ( $post ) {
            $results[] = array(
                'id'    => (string) $id,
                'slug'  => (string) $post->post_name,
                'title' => html_entity_decode( get_the_title( $id ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
            );
        }
    }

    wp_send_json_success( $results );
} );

/**
 * Learn FluentCart's live menu keys so the settings screen can offer positions.
 */
function rup_dashy_fc_menu_label( $key, $item ) {
    if ( is_array( $item ) ) {
        foreach ( array( 'label', 'title', 'name' ) as $label_key ) {
            if ( ! empty( $item[ $label_key ] ) && is_scalar( $item[ $label_key ] ) ) {
                return sanitize_text_field( $item[ $label_key ] );
            }
        }
    }
    return ucwords( str_replace( array( '-', '_' ), ' ', sanitize_key( $key ) ) );
}

function rup_dashy_fc_get_menu_positions() {
    $defaults = array(
        'dashboard'     => 'Dashboard',
        'orders'        => 'Orders',
        'subscriptions' => 'Subscriptions',
        'licenses'      => 'Licenses',
        'downloads'     => 'Downloads',
        'profile'       => 'Profile',
    );
    $cached = get_option( RUP_DASHY_FC_MENU_CACHE, array() );
    return array_merge( $defaults, is_array( $cached ) ? $cached : array() );
}

/**
 * Find a selected WP content object.
 */
function rup_dashy_fc_get_content_post( $tab ) {
    $source = trim( (string) ( $tab['content_source'] ?? '' ) );
    $post_type = sanitize_key( $tab['post_type'] ?? 'page' );

    if ( is_numeric( $source ) ) {
        $post = get_post( absint( $source ) );
        if ( $post && $post->post_type === $post_type ) {
            return $post;
        }
    }

    if ( $source ) {
        $post = get_page_by_path( sanitize_title( $source ), OBJECT, $post_type );
        if ( $post ) {
            return $post;
        }
        return get_page_by_path( $source, OBJECT, $post_type );
    }

    return null;
}

/**
 * Render a custom tab body.
 */
function rup_dashy_fc_render_tab_content( $tab ) {
    echo '<div class="fluent-cart-custom-page-content dashy-for-fluentcart-content">';

    if ( 'shortcode' === ( $tab['content_type'] ?? '' ) ) {
        echo do_shortcode( (string) ( $tab['content_source'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    } else {
        $post = rup_dashy_fc_get_content_post( $tab );
        if ( $post instanceof WP_Post ) {
            echo apply_filters( 'the_content', $post->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
            echo '<p>' . esc_html__( 'Content not found. Check the selected content source in Dashy settings.', 'dashy-for-fluentcart' ) . '</p>';
        }
    }

    echo '</div>';
}

/**
 * Register every configured tab through FluentCart's native endpoint API.
 */
add_action( 'init', function() {
    $tabs = rup_dashy_fc_get_tabs();
    if ( empty( $tabs ) ) {
        return;
    }

    $api = null;
    if ( function_exists( 'fluent_cart_api' ) ) {
        $api = fluent_cart_api();
    } elseif ( class_exists( '\\FluentCart\\Api\\FluentCartGeneralApi' ) ) {
        $api = \FluentCart\Api\FluentCartGeneralApi::getInstance();
    }

    if ( ! $api || ! is_callable( array( $api, 'addCustomerDashboardEndpoint' ) ) ) {
        return;
    }

    foreach ( $tabs as $tab ) {
        $slug = sanitize_title( $tab['slug'] ?? '' );
        if ( ! $slug ) {
            continue;
        }

        $endpoint = array(
            'title'           => sanitize_text_field( $tab['name'] ?? $slug ),
            'icon_svg'        => rup_dashy_fc_icon_svg( $tab['icon'] ?? '', ! empty( $tab['normalize_icon_colours'] ) ),
            'render_callback' => function() use ( $tab ) {
                rup_dashy_fc_render_tab_content( $tab );
            },
        );

        $api->addCustomerDashboardEndpoint( $slug, $endpoint );
    }
}, 20 );

/**
 * Reorder custom tabs while caching the current native FluentCart menu.
 */
add_filter( 'fluent_cart/global_customer_menu_items', function( $items, $context = array() ) {
    if ( ! is_array( $items ) ) {
        return $items;
    }

    $observed = array();
    foreach ( $items as $key => $item ) {
        $clean_key = sanitize_key( $key );
        if ( $clean_key ) {
            $observed[ $clean_key ] = rup_dashy_fc_menu_label( $clean_key, $item );
        }
    }
    if ( $observed ) {
        update_option( RUP_DASHY_FC_MENU_CACHE, $observed, false );
    }

    $tabs = rup_dashy_fc_get_tabs();
    foreach ( $tabs as $tab ) {
        $slug = sanitize_title( $tab['slug'] ?? '' );
        if ( ! $slug || ! array_key_exists( $slug, $items ) ) {
            continue;
        }

        $custom_item = $items[ $slug ];
        unset( $items[ $slug ] );
        $after = sanitize_key( $tab['insert_after'] ?? 'last' );

        if ( 'first' === $after ) {
            $items = array( $slug => $custom_item ) + $items;
            continue;
        }
        if ( 'last' === $after || ! array_key_exists( $after, $items ) ) {
            $items[ $slug ] = $custom_item;
            continue;
        }

        $new = array();
        foreach ( $items as $key => $item ) {
            $new[ $key ] = $item;
            if ( $key === $after ) {
                $new[ $slug ] = $custom_item;
            }
        }
        $items = $new;
    }

    return $items;
}, 20, 2 );

/**
 * Admin notice when FluentCart is unavailable.
 */
add_action( 'admin_notices', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( function_exists( 'fluent_cart_api' ) || class_exists( '\\FluentCart\\Api\\FluentCartGeneralApi' ) ) {
        return;
    }
    $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
    if ( 'dashy-for-fluentcart' === $page ) {
        echo '<div class="notice notice-warning"><p>' . esc_html__( 'Dashy For FluentCart is ready, but FluentCart does not appear to be active. Custom tabs will register when FluentCart is available.', 'dashy-for-fluentcart' ) . '</p></div>';
    }
} );

/**
 * Render admin UI.
 */
function rup_dashy_fc_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $tabs = rup_dashy_fc_get_tabs();
    $post_types = get_post_types( array( 'public' => true ), 'objects' );
    $positions = rup_dashy_fc_get_menu_positions();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Dashy For FluentCart', 'dashy-for-fluentcart' ); ?></h1>
        <p><?php esc_html_e( 'Add native FluentCart customer dashboard tabs that render a WordPress page/post/custom post type or a shortcode.', 'dashy-for-fluentcart' ); ?></p>
        <p><strong><?php esc_html_e( 'Icons:', 'dashy-for-fluentcart' ); ?></strong> <?php esc_html_e( 'FluentCart uses an SVG-only icon slot. Paste inline SVG or select an SVG attachment from the Media Library; raster formats are not accepted. Enable Match Dashboard Colour per tab when you want the SVG to inherit the FluentCart navigation colour.', 'dashy-for-fluentcart' ); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields( 'rup_dashy_fc_options_group' ); ?>
            <table id="rup-dashy-fc-tabs" class="widefat striped" style="max-width:1500px;">
                <thead><tr>
                    <th><?php esc_html_e( 'Tab Name', 'dashy-for-fluentcart' ); ?></th>
                    <th><?php esc_html_e( 'Slug', 'dashy-for-fluentcart' ); ?></th>
                    <th><?php esc_html_e( 'Icon', 'dashy-for-fluentcart' ); ?></th>
                    <th><?php esc_html_e( 'Match Dashboard Colour', 'dashy-for-fluentcart' ); ?></th>
                    <th><?php esc_html_e( 'Content Type', 'dashy-for-fluentcart' ); ?></th>
                    <th><?php esc_html_e( 'Post Type', 'dashy-for-fluentcart' ); ?></th>
                    <th><?php esc_html_e( 'Content', 'dashy-for-fluentcart' ); ?></th>
                    <th><?php esc_html_e( 'Position', 'dashy-for-fluentcart' ); ?></th>
                    <th><?php esc_html_e( 'Action', 'dashy-for-fluentcart' ); ?></th>
                </tr></thead>
                <tbody>
                <?php foreach ( $tabs as $index => $tab ) : ?>
                    <?php rup_dashy_fc_render_admin_row( $index, $tab, $post_types, $positions ); ?>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><button type="button" id="rup-dashy-fc-add" class="button"><?php esc_html_e( 'Add Tab', 'dashy-for-fluentcart' ); ?></button></p>
            <?php submit_button(); ?>
        </form>
    </div>
    <style>
        #rup-dashy-fc-tabs input[type=text], #rup-dashy-fc-tabs select { width:100%; min-width:120px; }
        #rup-dashy-fc-tabs td { vertical-align:top; }
        #rup-dashy-fc-tabs .rup-dashy-fc-icon { min-width:220px; }
        #rup-dashy-fc-tabs .rup-dashy-fc-source { min-width:230px; }
        #rup-dashy-fc-tabs .rup-dashy-fc-icon-preview svg { width:20px; height:20px; margin-top:6px; vertical-align:middle; }
    </style>
    <script>
    jQuery(function($){
        var counter = <?php echo (int) count( $tabs ); ?>;
        var nonce = <?php echo wp_json_encode( wp_create_nonce( 'rup_dashy_fc_content_sources' ) ); ?>;
        var postTypeOptions = <?php
            $pt_html = '';
            foreach ( $post_types as $pt ) {
                $pt_html .= '<option value="' . esc_attr( $pt->name ) . '">' . esc_html( $pt->labels->singular_name ) . '</option>';
            }
            echo wp_json_encode( $pt_html );
        ?>;
        var positionOptions = <?php
            $pos_html = '<option value="last">Last item</option><option value="first">First item</option>';
            foreach ( $positions as $key => $label ) {
                $pos_html .= '<option value="' . esc_attr( $key ) . '">After ' . esc_html( $label ) . '</option>';
            }
            echo wp_json_encode( $pos_html );
        ?>;

        function esc(value){ return $('<div>').text(value || '').html(); }
        function indexOfRow($row){
            var name = $row.find('[name*="[name]"]').attr('name') || '';
            var match = name.match(/rup_dashy_fc_tabs\[(\d+)\]/);
            return match ? match[1] : '';
        }
        function sourceName(i){ return 'rup_dashy_fc_tabs[' + i + '][content_source]'; }
        function pageField(i, value){
            return '<input type="hidden" class="rup-dashy-fc-source-value" name="'+sourceName(i)+'" value="'+esc(value)+'">' +
                   '<select class="rup-dashy-fc-source-select rup-dashy-fc-source" data-selected="'+esc(value)+'"><option>Loading…</option></select>';
        }
        function shortcodeField(i, value){ return '<input type="text" class="rup-dashy-fc-source" name="'+sourceName(i)+'" value="'+esc(value)+'" placeholder="[your_shortcode]">'; }
        function loadSources($row){
            var $select = $row.find('.rup-dashy-fc-source-select');
            if (!$select.length) return;
            var $hidden = $row.find('.rup-dashy-fc-source-value');
            var selected = String($hidden.val() || $select.data('selected') || '');
            $select.html('<option>Loading…</option>').prop('disabled', true);
            $.post(ajaxurl, {action:'rup_dashy_fc_get_content_sources', nonce:nonce, post_type:$row.find('.rup-dashy-fc-post-type').val()})
              .done(function(resp){
                  var html = '<option value="">Select content…</option>', matched = false;
                  if(resp && resp.success && $.isArray(resp.data)){
                      $.each(resp.data, function(_, item){
                          var isMatch = selected === String(item.id) || selected === item.slug;
                          if(isMatch){ matched = true; $hidden.val(String(item.id)); }
                          html += '<option value="'+esc(item.id)+'" '+(isMatch?'selected':'')+'>'+esc(item.title+' ('+item.slug+', ID '+item.id+')')+'</option>';
                      });
                  }
                  if(selected && !matched) html += '<option value="'+esc(selected)+'" selected>Saved value: '+esc(selected)+'</option>';
                  $select.html(html).prop('disabled', false);
              });
        }

        $('#rup-dashy-fc-add').on('click', function(){
            var i = counter++;
            var row = '<tr>'+
              '<td><input type="text" name="rup_dashy_fc_tabs['+i+'][name]" required></td>'+
              '<td><input type="text" name="rup_dashy_fc_tabs['+i+'][slug]" placeholder="support"></td>'+
              '<td><input type="text" class="rup-dashy-fc-icon" name="rup_dashy_fc_tabs['+i+'][icon]" placeholder="Inline SVG or Media Library SVG"><p><button type="button" class="button rup-dashy-fc-media">Select SVG</button></p></td>'+
              '<td><label><input type="checkbox" name="rup_dashy_fc_tabs['+i+'][normalize_icon_colours]" value="1"> Use dashboard colour</label></td>'+
              '<td><select class="rup-dashy-fc-content-type" name="rup_dashy_fc_tabs['+i+'][content_type]"><option value="page">Page/Post/Custom Post</option><option value="shortcode">Shortcode</option></select></td>'+
              '<td><select class="rup-dashy-fc-post-type" name="rup_dashy_fc_tabs['+i+'][post_type]">'+postTypeOptions+'</select></td>'+
              '<td class="rup-dashy-fc-source-cell">'+pageField(i, '')+'</td>'+
              '<td><select name="rup_dashy_fc_tabs['+i+'][insert_after]">'+positionOptions+'</select></td>'+
              '<td><button type="button" class="button rup-dashy-fc-remove">Remove</button></td>'+
              '</tr>';
            var $row = $(row).appendTo('#rup-dashy-fc-tabs tbody');
            loadSources($row);
        });

        $('#rup-dashy-fc-tabs').on('click', '.rup-dashy-fc-remove', function(){ $(this).closest('tr').remove(); });
        $('#rup-dashy-fc-tabs').on('change', '.rup-dashy-fc-content-type', function(){
            var $row = $(this).closest('tr'), i = indexOfRow($row), type = $(this).val();
            $row.find('.rup-dashy-fc-post-type').prop('disabled', type !== 'page').closest('td').css('opacity', type === 'page' ? 1 : .45);
            $row.find('.rup-dashy-fc-source-cell').html(type === 'page' ? pageField(i, '') : shortcodeField(i, ''));
            if(type === 'page') loadSources($row);
        });
        $('#rup-dashy-fc-tabs').on('change', '.rup-dashy-fc-post-type', function(){ loadSources($(this).closest('tr')); });
        $('#rup-dashy-fc-tabs').on('change', '.rup-dashy-fc-source-select', function(){ $(this).siblings('.rup-dashy-fc-source-value').val($(this).val()); });
        $('#rup-dashy-fc-tabs').on('click', '.rup-dashy-fc-media', function(){
            var $field = $(this).closest('td').find('.rup-dashy-fc-icon');
            var frame = wp.media({
                title:'Select SVG dashboard icon',
                button:{text:'Use SVG'},
                library:{type:'image/svg+xml'},
                multiple:false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                var mime = attachment.mime || attachment.post_mime_type || '';
                if (mime !== 'image/svg+xml' && !/\.svg(?:[?#].*)?$/i.test(attachment.url || '')) {
                    window.alert('FluentCart dashboard icons must be SVG files.');
                    return;
                }
                $field.val(attachment.url);
            });
            frame.open();
        });
        $('#rup-dashy-fc-tabs tbody tr').each(function(){
            var $row = $(this), type = $row.find('.rup-dashy-fc-content-type').val();
            if(type !== 'page') $row.find('.rup-dashy-fc-post-type').prop('disabled', true).closest('td').css('opacity', .45);
            loadSources($row);
        });
    });
    </script>
    <?php
}

function rup_dashy_fc_render_admin_row( $index, $tab, $post_types, $positions ) {
    $type = $tab['content_type'] ?? 'page';
    ?>
    <tr>
        <td><input type="text" name="<?php echo esc_attr( RUP_DASHY_FC_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][name]" value="<?php echo esc_attr( $tab['name'] ?? '' ); ?>" required></td>
        <td><input type="text" name="<?php echo esc_attr( RUP_DASHY_FC_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][slug]" value="<?php echo esc_attr( $tab['slug'] ?? '' ); ?>"></td>
        <td>
            <input type="text" class="rup-dashy-fc-icon" name="<?php echo esc_attr( RUP_DASHY_FC_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][icon]" value="<?php echo esc_attr( $tab['icon'] ?? '' ); ?>" placeholder="Inline SVG or Media Library SVG">
            <p><button type="button" class="button rup-dashy-fc-media"><?php esc_html_e( 'Select SVG', 'dashy-for-fluentcart' ); ?></button></p>
        </td>
        <td>
            <label>
                <input type="checkbox" name="<?php echo esc_attr( RUP_DASHY_FC_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][normalize_icon_colours]" value="1" <?php checked( ! empty( $tab['normalize_icon_colours'] ) ); ?>>
                <?php esc_html_e( 'Use dashboard colour', 'dashy-for-fluentcart' ); ?>
            </label>
            <p class="description"><?php esc_html_e( 'Converts SVG fill/stroke colours to currentColor so the icon follows FluentCart navigation styling.', 'dashy-for-fluentcart' ); ?></p>
        </td>
        <td><select class="rup-dashy-fc-content-type" name="<?php echo esc_attr( RUP_DASHY_FC_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][content_type]">
            <option value="page" <?php selected( $type, 'page' ); ?>><?php esc_html_e( 'Page/Post/Custom Post', 'dashy-for-fluentcart' ); ?></option>
            <option value="shortcode" <?php selected( $type, 'shortcode' ); ?>><?php esc_html_e( 'Shortcode', 'dashy-for-fluentcart' ); ?></option>
        </select></td>
        <td style="opacity:<?php echo 'page' === $type ? '1' : '.45'; ?>"><select class="rup-dashy-fc-post-type" name="<?php echo esc_attr( RUP_DASHY_FC_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][post_type]" <?php disabled( 'page' !== $type ); ?>>
            <?php foreach ( $post_types as $pt ) : ?>
                <option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $tab['post_type'] ?? 'page', $pt->name ); ?>><?php echo esc_html( $pt->labels->singular_name ); ?></option>
            <?php endforeach; ?>
        </select></td>
        <td class="rup-dashy-fc-source-cell">
            <?php if ( 'shortcode' === $type ) : ?>
                <input type="text" class="rup-dashy-fc-source" name="<?php echo esc_attr( RUP_DASHY_FC_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][content_source]" value="<?php echo esc_attr( $tab['content_source'] ?? '' ); ?>" placeholder="[your_shortcode]">
            <?php else : ?>
                <input type="hidden" class="rup-dashy-fc-source-value" name="<?php echo esc_attr( RUP_DASHY_FC_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][content_source]" value="<?php echo esc_attr( $tab['content_source'] ?? '' ); ?>">
                <select class="rup-dashy-fc-source-select rup-dashy-fc-source" data-selected="<?php echo esc_attr( $tab['content_source'] ?? '' ); ?>"><option><?php esc_html_e( 'Loading…', 'dashy-for-fluentcart' ); ?></option></select>
            <?php endif; ?>
        </td>
        <td><select name="<?php echo esc_attr( RUP_DASHY_FC_OPTION ); ?>[<?php echo esc_attr( $index ); ?>][insert_after]">
            <option value="last" <?php selected( $tab['insert_after'] ?? 'last', 'last' ); ?>><?php esc_html_e( 'Last item', 'dashy-for-fluentcart' ); ?></option>
            <option value="first" <?php selected( $tab['insert_after'] ?? 'last', 'first' ); ?>><?php esc_html_e( 'First item', 'dashy-for-fluentcart' ); ?></option>
            <?php foreach ( $positions as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $tab['insert_after'] ?? 'last', $key ); ?>><?php echo esc_html( 'After ' . $label ); ?></option>
            <?php endforeach; ?>
        </select></td>
        <td><button type="button" class="button rup-dashy-fc-remove"><?php esc_html_e( 'Remove', 'dashy-for-fluentcart' ); ?></button></td>
    </tr>
    <?php
}

// ──────────────────────────────────────────────────────────────────────────
// Updater bootstrap
// ──────────────────────────────────────────────────────────────────────────
add_action(
	'plugins_loaded',
	function () {
		$updater_file = RUP_DASHY_FC_PLUGIN_DIR . 'inc/updater.php';

		if ( ! file_exists( $updater_file ) ) {
			return;
		}

		require_once $updater_file;

		$updater_config = array(
			'vendor'      => 'rup',
			'plugin_file' => plugin_basename( RUP_DASHY_FC_PLUGIN_FILE ),
			'slug'        => 'dashy-for-fluentcart',
			'name'        => 'dashy for fluentcart',
			'version'     => RUP_DASHY_FC_VERSION,
			'key'         => '',
			'server'      => 'https://raw.githubusercontent.com/stingray82/dashy-for-fluentcart/main/uupd/index.json',
		);

		\RUP\Updater\Updater_V2::register( $updater_config );
	},
	20
);
