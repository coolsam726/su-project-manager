import preset from '../../../../../vendor/filament/filament/tailwind.config.preset'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'
export default {
    presets: [preset],
    content: [
        '../*/app/**/*.php',
        './resources/views/filament/**/*.blade.php',
        '../../vendor/filament/**/*.blade.php',
    ],
    plugins: [
        forms,
        typography,
    ],
}
