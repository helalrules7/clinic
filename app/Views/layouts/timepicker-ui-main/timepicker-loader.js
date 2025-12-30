// Timepicker UI Loader
// This file loads timepicker-ui from local files instead of CDN

// Import TimepickerUI from local file
import { TimepickerUI } from "./index.js";

// Import CSS (this will be handled by the link tag in main.php, but we include it here for completeness)
// Note: CSS imports in JS modules may not work in all browsers, so we rely on the <link> tag in main.php

// Expose TimepickerUI globally for backward compatibility
if (typeof window !== 'undefined') {
    window.TimepickerUI = TimepickerUI;
}

// Export for ES modules
export { TimepickerUI };
