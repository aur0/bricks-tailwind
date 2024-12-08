module.exports = {
  content: ['./**/*.{php,html,js}'],
  safelist: [
    {
      pattern: /.*/  // This will generate EVERY possible class
    }
  ]
}