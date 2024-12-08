module.exports = {
  content: ['./**/*.{php,html,js}'],
  safelist: [
    {
      pattern: /.*/  // This will generate EVERY possible class for updating the tailwind.min.css
    }
  ],
  prefix: 'tailwind-', // Add this to prepend 'tailwind-' to all classes
}
