import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './resources/views/vendor/filament-panels/components/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
