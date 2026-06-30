<?php
/**
 * icons.php — inline SVG icon set (no emojis, crisp at any size).
 * Usage: echo icon('heart', 'w-6 h-6', ['aria-hidden'=>'true']);
 */

declare(strict_types=1);

function icon(string $name, string $class = 'icon', array $attrs = []): string
{
    $paths = [
        'heart'    => '<path d="M12 21s-7.5-4.6-10-9.3C.6 8.7 2 5 5.5 5 7.6 5 9 6.3 12 9c3-2.7 4.4-4 6.5-4C22 5 23.4 8.7 22 11.7 19.5 16.4 12 21 12 21Z"/>',
        'eye'      => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'book'     => '<path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2V5Z"/><path d="M18 3v18"/>',
        'braille'  => '<circle cx="8" cy="7" r="1.6"/><circle cx="8" cy="12" r="1.6"/><circle cx="8" cy="17" r="1.6"/><circle cx="15" cy="7" r="1.6"/><circle cx="15" cy="12" r="1.6"/>',
        'hand'     => '<path d="M7 11V6a1.7 1.7 0 0 1 3.4 0V10m0 0V4.5a1.7 1.7 0 0 1 3.4 0V10m0 0V6a1.7 1.7 0 0 1 3.4 0v6c0 4-2.6 7-7 7-3 0-4.7-1.3-6.2-3.6L3 16.5c-.7-1.2.7-2.6 2-2l2 1.3"/>',
        'sparkle'  => '<path d="M12 3l1.8 5.4L19 10l-5.2 1.6L12 17l-1.8-5.4L5 10l5.2-1.6L12 3Z"/><path d="M18.5 14.5l.6 1.8 1.8.6-1.8.6-.6 1.8-.6-1.8-1.8-.6 1.8-.6.6-1.8Z"/>',
        'star'     => '<path d="M12 3l2.7 5.8 6.3.8-4.7 4.3 1.2 6.3L12 17.8 6.2 20.3l1.2-6.3L2.7 9.6l6.3-.8L12 3Z"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-up'    => '<path d="M12 19V5M6 11l6-6 6 6"/>',
        'chevron-right' => '<path d="M9 6l6 6-6 6"/>',
        'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'    => '<path d="M6 6l12 12M18 6 6 18"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'minus'    => '<path d="M5 12h14"/>',
        'contrast' => '<circle cx="12" cy="12" r="9"/><path d="M12 3v18a9 9 0 0 0 0-18Z" fill="currentColor" stroke="none"/>',
        'motion'   => '<path d="M3 12h4l2-7 4 14 2-7h6"/>',
        'volume'   => '<path d="M4 9v6h4l5 4V5L8 9H4Z"/><path d="M16 8a5 5 0 0 1 0 8M18.5 5.5a9 9 0 0 1 0 13"/>',
        'text'     => '<path d="M4 7V5h16v2M9 19h6M12 5v14"/>',
        'whatsapp' => '<path d="M12 3a9 9 0 0 0-7.7 13.6L3 21l4.6-1.2A9 9 0 1 0 12 3Z"/><path d="M8.5 8.6c.2-.5.4-.5.7-.5h.5c.2 0 .4 0 .6.5l.7 1.6c.1.3 0 .5-.1.7l-.4.5c-.2.2-.3.4-.1.7.3.6.9 1.3 1.5 1.7.5.4.8.4 1 .2l.5-.5c.2-.2.4-.2.7-.1l1.5.7c.3.1.4.3.4.5 0 .9-.7 1.6-1.5 1.7-.7 0-1.6 0-4-1.6-2-1.4-3-3.3-3.1-3.6-.1-.3-.6-1.2-.6-2.2 0-1 .5-1.4.7-1.5Z" fill="currentColor" stroke="none"/>',
        'phone'    => '<path d="M5 4h3l1.5 5-2 1.5a12 12 0 0 0 6 6L15 14l5 1.5V19a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/>',
        'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'map-pin'  => '<path d="M12 21s7-5.7 7-11a7 7 0 1 0-14 0c0 5.3 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        'users'    => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20a6 6 0 0 1 12 0M16 5.5a3 3 0 0 1 0 5.8M21 20a6 6 0 0 0-4-5.6"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/>',
        'check'    => '<path d="M5 13l4 4L19 7"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'shield'   => '<path d="M12 3l8 3v6c0 5-3.4 8-8 9-4.6-1-8-4-8-9V6l8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'gift'     => '<rect x="3" y="9" width="18" height="12" rx="1.5"/><path d="M3 13h18M12 9v12M12 9C9 9 7 7.5 7 6a2 2 0 0 1 5-1 2 2 0 0 1 5 1c0 1.5-2 3-5 3Z"/>',
        'grad'     => '<path d="M3 9l9-4 9 4-9 4-9-4Z"/><path d="M7 11v4c0 1.5 2.5 3 5 3s5-1.5 5-3v-4M21 9v5"/>',
        'rocket'   => '<path d="M5 15c-1 1-1.5 4-1.5 4s3-.5 4-1.5M14 7s3-3 6-3c0 3-3 6-3 6l-4 4-3-3 4-4Z"/><path d="M9 12l3 3M14.5 9.5h.01"/>',
        'globe'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 2.5 15 0 18M12 3c-2.5 2.6-2.5 15 0 18"/>',
        'palette'  => '<path d="M12 3a9 9 0 1 0 0 18c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.2 0-1 .8-1.8 1.8-1.8H17a4 4 0 0 0 4-4c0-4.4-4-7.7-9-7.7Z"/><circle cx="7.5" cy="11.5" r="1"/><circle cx="12" cy="8" r="1"/><circle cx="16" cy="11" r="1"/>',
        'lock'     => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'dashboard'=> '<rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="5" rx="2"/><rect x="13" y="10" width="8" height="11" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/>',
        'image'    => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="m4 18 5-5 4 4 3-3 4 4"/>',
        'trash'    => '<path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/>',
        'edit'     => '<path d="M4 20h4L18 10l-4-4L4 16v4Z"/><path d="m13 7 4 4"/>',
        'chart'    => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'inbox'    => '<path d="M3 13h5l1.5 3h5L16 13h5"/><path d="M3 13 5 5h14l2 8v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-6Z"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 13.5a7.8 7.8 0 0 0 0-3l2-1.5-2-3.4-2.3 1a7.6 7.6 0 0 0-2.6-1.5L14 1h-4l-.5 2.6A7.6 7.6 0 0 0 6.9 5.1l-2.3-1-2 3.4 2 1.5a7.8 7.8 0 0 0 0 3l-2 1.5 2 3.4 2.3-1a7.6 7.6 0 0 0 2.6 1.5L10 23h4l.5-2.6a7.6 7.6 0 0 0 2.6-1.5l2.3 1 2-3.4-2-1.5Z"/>',
        'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5 19 19M19 5l-1.5 1.5M6.5 17.5 5 19"/>',
        'moon'     => '<path d="M21 12.8A8 8 0 1 1 11.2 3a6 6 0 0 0 9.8 9.8Z"/>',
        'quote'    => '<path d="M7 7H5a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h2v3H4M19 7h-2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h2v3h-3"/>',
        'play'     => '<path d="M7 5v14l11-7L7 5Z" fill="currentColor" stroke="none"/>',
        'receipt'  => '<path d="M5 3h14v18l-2.5-1.5L14 21l-2-1.5L10 21l-2.5-1.5L5 21V3Z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'tree'     => '<path d="M12 3 5 12h4l-3 5h12l-3-5h4L12 3Z"/><path d="M12 17v4"/>',
        'badge'    => '<circle cx="12" cy="10" r="6"/><path d="M9 15l-1 6 4-2 4 2-1-6"/>',
        'document' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
        'download' => '<path d="M12 3v12M7 10l5 5 5-5M5 21h14"/>',
        'database' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
    ];

    $p = $paths[$name] ?? '<circle cx="12" cy="12" r="9"/>';
    $extra = '';
    foreach ($attrs as $k => $v) {
        $extra .= ' ' . $k . '="' . e((string) $v) . '"';
    }
    if (!isset($attrs['aria-hidden']) && !isset($attrs['aria-label'])) {
        $extra .= ' aria-hidden="true"';
    }
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
        . 'stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"' . $extra . '>' . $p . '</svg>';
}
