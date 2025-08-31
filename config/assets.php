<?php

return [
    // When true, Laravel will never try to use the Vite dev server (hot reloading)
    // and will always read compiled assets from public/build.
    'force_build' => env('ASSETS_FORCE_BUILD', false),
];
