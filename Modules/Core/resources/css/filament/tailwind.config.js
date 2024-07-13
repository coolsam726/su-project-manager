import preset from '../../../../../vendor/filament/filament/tailwind.config.preset'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'
export default {
    presets: [preset],
    content: [
        '../*/app/**/*.php',
        '../*/resources/**/*.php',
        './resources/views/filament/**/*.blade.php',
        '../../vendor/filament/**/*.blade.php',
        '../../vendor/awcodes/filament-table-repeater/**/*.blade.php',
    ],
    plugins: [
        forms,
        typography,
    ],
}
