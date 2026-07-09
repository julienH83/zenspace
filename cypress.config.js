const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8080',
    supportFile: false,
    fixturesFolder: false,
    video: false,
    defaultCommandTimeout: 8000,
    specPattern: 'cypress/e2e/**/*.cy.js',
  },
});
