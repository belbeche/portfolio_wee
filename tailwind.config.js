/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./assets/*.js",
        "./assets/styles/*.css",
        "./templates/**/*.html.twig",
    ],
    variants: {
        extends: {
            display: ['group-focus'],
            opacity: ['group-focus'],
            inset: ['group-focus']
        },
        aspectRatio: ['responsive'],
    },
    theme: {},
    plugins: [
        require('@tailwindcss/forms'),
  ],
}
