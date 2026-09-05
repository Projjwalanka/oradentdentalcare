<?php
declare(strict_types=1);

/**
 * Inline SVG icons — no icon font, no sprite request, no layout shift.
 * Every path is drawn on a 24×24 grid with currentColor so icons inherit
 * text colour automatically.
 */

const ICONS = [
    'phone'    => '<path d="M6.6 2.7a1.7 1.7 0 0 1 2.3.6l1.3 2.2a1.7 1.7 0 0 1-.3 2.1l-1 .9a10.6 10.6 0 0 0 4.6 4.6l.9-1a1.7 1.7 0 0 1 2.1-.3l2.2 1.3a1.7 1.7 0 0 1 .6 2.3l-.8 1.4a2.6 2.6 0 0 1-2.9 1.2C11 16.9 7.1 13 5 6.5a2.6 2.6 0 0 1 1.2-2.9Z"/>',
    'whatsapp' => '<path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm5.3 14c-.2.6-1.2 1.2-1.7 1.2-.5.1-1 .1-1.6-.1-.4-.1-.9-.3-1.5-.6a11 11 0 0 1-4.2-3.9c-.3-.5-.7-1.2-.7-2s.4-1.2.6-1.4a.7.7 0 0 1 .5-.2h.4c.1 0 .3 0 .5.4l.7 1.6c0 .2 0 .3-.1.4l-.3.4-.2.2c-.1.1-.2.2 0 .5.2.4.8 1.3 1.6 2 1 .7 1.6.9 1.9 1 .2.2.4.1.5 0l.7-.8c.2-.2.3-.2.5-.1l1.5.7c.2.1.4.2.4.3.1.1.1.5 0 1Z"/>',
    'calendar' => '<path d="M8 2v3M16 2v3M3.5 9h17M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>',
    'clock'    => '<circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.9"/><path d="M12 7v5.2l3.2 2" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>',
    'pin'      => '<path d="M12 21.5s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/><circle cx="12" cy="10.3" r="2.6" fill="none" stroke="currentColor" stroke-width="1.9"/>',
    'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2.4" fill="none" stroke="currentColor" stroke-width="1.9"/><path d="m4 7 8 5.6L20 7" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>',
    'check'    => '<path d="m4.5 12.6 4.7 4.7L19.5 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>',
    'check-c'  => '<circle cx="12" cy="12" r="9.2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m8 12.3 2.7 2.7L16 9.6" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>',
    'right'    => '<path d="m9 5 7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
    'left'     => '<path d="m15 5-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
    'arrow'    => '<path d="M4 12h15m0 0-6-6m6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>',
    'menu'     => '<path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"/>',
    'close'    => '<path d="m6 6 12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round"/>',
    'shield'   => '<path d="M12 2.6l7.4 3v5.7c0 4.6-3.1 8.4-7.4 10.1-4.3-1.7-7.4-5.5-7.4-10.1V5.6Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m8.8 11.9 2.3 2.3 4.2-4.4" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>',
    'sparkle'  => '<path d="M12 2.6l1.9 5.3 5.3 1.9-5.3 1.9L12 17l-1.9-5.3L4.8 9.8l5.3-1.9Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M18.4 15.4l.8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8Z" fill="currentColor"/>',
    'rupee'    => '<path d="M7 4h10M7 8.4h10M15.3 4c0 3.3-2.1 5-5.3 5H7l8.3 11" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>',
    'heart'    => '<path d="M12 20.3S3.8 15.5 3.8 9.8A4.6 4.6 0 0 1 12 7a4.6 4.6 0 0 1 8.2 2.8c0 5.7-8.2 10.5-8.2 10.5Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>',
    'family'   => '<circle cx="8.4" cy="7.4" r="2.9" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="16.6" cy="9" r="2.2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M2.8 20c0-3.4 2.5-5.7 5.6-5.7s5.6 2.3 5.6 5.7M15 14.6c3 0 5.2 1.9 5.2 5.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
    'star'     => '<path d="m12 3 2.6 5.6 6.1.8-4.5 4.2 1.2 6-5.4-3-5.4 3 1.2-6L3.3 9.4l6.1-.8Z" fill="currentColor"/>',
    'sun'      => '<circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.9"/><path d="M12 2.6v2.2M12 19.2v2.2M4.3 4.3l1.6 1.6M18.1 18.1l1.6 1.6M2.6 12h2.2M19.2 12h2.2M4.3 19.7l1.6-1.6M18.1 5.9l1.6-1.6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>',
    'moon'     => '<path d="M20 14.4A8.2 8.2 0 0 1 9.6 4 8.4 8.4 0 1 0 20 14.4Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>',
    'directions' => '<path d="M12 2.4 21.6 12 12 21.6 2.4 12Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9.6 14.4v-2.6a2 2 0 0 1 2-2h3.2m0 0-1.8-1.8m1.8 1.8-1.8 1.8" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',

    /* ---- service icons ---- */
    'tooth'      => '<path d="M12 3.2c-1.6 0-2.3.7-4 .7-2.2 0-3.8 1.7-3.8 4.3 0 2.2.7 3.3 1.3 5.3.5 1.7.6 4 1.1 5.6.3 1 .8 1.7 1.6 1.7 1.2 0 1.4-1.4 1.7-3 .3-1.5.6-2.6 2.1-2.6s1.8 1.1 2.1 2.6c.3 1.6.5 3 1.7 3 .8 0 1.3-.7 1.6-1.7.5-1.6.6-3.9 1.1-5.6.6-2 1.3-3.1 1.3-5.3 0-2.6-1.6-4.3-3.8-4.3-1.7 0-2.4-.7-4-.7Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
    'tooth-rct'  => '<path d="M12 3.2c-1.6 0-2.3.7-4 .7-2.2 0-3.8 1.7-3.8 4.3 0 2.2.7 3.3 1.3 5.3.5 1.7.6 4 1.1 5.6.3 1 .8 1.7 1.6 1.7 1.2 0 1.4-1.4 1.7-3 .3-1.5.6-2.6 2.1-2.6s1.8 1.1 2.1 2.6c.3 1.6.5 3 1.7 3 .8 0 1.3-.7 1.6-1.7.5-1.6.6-3.9 1.1-5.6.6-2 1.3-3.1 1.3-5.3 0-2.6-1.6-4.3-3.8-4.3-1.7 0-2.4-.7-4-.7Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M10.2 7.6 12 13.4l1.8-5.8" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
    'implant'    => '<path d="M9 3.4h6l-.7 3.2H9.7Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 6.6v14.2M9.4 9.4h5.2M9.8 12.4h4.4M10.2 15.4h3.6M10.6 18.4h2.8" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
    'surgery'    => '<path d="m3.4 20.6 8.2-8.2M14.6 9.2 20.2 3.6c.3-.3.2-.7-.2-.8l-2.5-.4c-.4 0-.8.1-1 .4l-4.6 5.3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.6 12.4 9.4 10.2a1.4 1.4 0 0 1 0-2l1.3-1.3 4.5 4.5-1.3 1.3a1.4 1.4 0 0 1-2 0Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M3.4 20.6a1.6 1.6 0 0 1 0-2.3l1-1 2.3 2.3-1 1a1.6 1.6 0 0 1-2.3 0Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
    'extraction' => '<path d="M12 3.6c-1.4 0-2 .6-3.4.6-1.9 0-3.3 1.5-3.3 3.7 0 1.9.6 2.9 1.1 4.6.4 1.5.5 3.4 1 4.8.2.9.7 1.5 1.4 1.5 1 0 1.2-1.2 1.5-2.6.2-1.3.5-2.2 1.7-2.2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M15.4 14.6h6M18.4 11.6v6" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" transform="rotate(45 18.4 14.6)"/>',
    'clean'      => '<path d="M6.6 21.4h10.8l-1-8.4H7.6Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8.4 13V4.6a2.2 2.2 0 0 1 2.2-2.2h2.8a2.2 2.2 0 0 1 2.2 2.2V13M9.4 16.6h5.2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
    'filling'    => '<path d="M12 3.4c-1.6 0-2.3.7-3.9.7-2.2 0-3.7 1.6-3.7 4.2 0 2.1.7 3.2 1.2 5.1.5 1.7.6 3.9 1.1 5.4.3 1 .8 1.6 1.5 1.6 1.2 0 1.4-1.3 1.7-2.9.3-1.4.6-2.5 2.1-2.5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><rect x="13.4" y="6.6" width="7" height="7" rx="1.4" fill="none" stroke="currentColor" stroke-width="1.7" transform="rotate(12 16.9 10.1)"/>',
    'smile'      => '<circle cx="12" cy="12" r="9.2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M7.4 13.4a4.8 4.8 0 0 0 9.2 0Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M8.8 8.6v.8M15.2 8.6v.8" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>',
    'child'      => '<circle cx="12" cy="8" r="4.6" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M10.4 7.4v.6M13.6 7.4v.6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10.2 10a2.6 2.6 0 0 0 3.6 0" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M5.4 21.4c0-3.4 2.9-6 6.6-6s6.6 2.6 6.6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
];

function icon(string $name, string $class = ''): string
{
    $body = ICONS[$name] ?? ICONS['check'];
    $cls  = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';
    return '<svg' . $cls . ' viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">'
        . $body . '</svg>';
}
