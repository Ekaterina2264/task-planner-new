<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" content="#ffffff" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="default" />
<title>{{ isset($title) ? $title . ' — Tasksk' : 'Tasksk' }}</title>
<link rel="manifest" href="/manifest.webmanifest" />
<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
