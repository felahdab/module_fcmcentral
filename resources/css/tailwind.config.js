import preset from '/app/vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/{{ classPathPrefix }}**/*.php',
        './resources/views/filament/{{ viewPathPrefix }}**/*.blade.php',
        '/app/vendor/filament/**/*.blade.php',
    ],
}
