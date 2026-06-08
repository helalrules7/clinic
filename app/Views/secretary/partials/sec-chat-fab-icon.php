<?php
/**
 * Secretary chat FAB — animated SVG icon (§39).
 * Glass circular FAB surface is in chat-widget.css (.chat-fab--glass).
 */
if (!function_exists('sec_chat_fab_icon')) {
    function sec_chat_fab_icon(): string
    {
        return <<<'HTML'
<span class="sec-chat-fab-icon" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path class="scf-bubble" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
<path class="scf-dot scf-dot-1" d="M8 10h.01"/>
<path class="scf-dot scf-dot-2" d="M12 10h.01"/>
<path class="scf-dot scf-dot-3" d="M16 10h.01"/>
</svg>
</span>
HTML;
    }
}
