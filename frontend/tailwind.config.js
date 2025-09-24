/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}",
  ],
  theme: {
    extend: {
      colors: {
        'deep-blue': '#0f2b5b',
        'sea-blue': '#1e88e5',
        'light-blue': '#90caf9',
      },
      gradientColorStops: {
        'deep-blue': '#0f2b5b',
        'sea-blue': '#1e88e5',
        'white': '#ffffff',
      }
    },
  },
  plugins: [],
}