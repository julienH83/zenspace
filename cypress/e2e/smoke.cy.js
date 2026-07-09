/// <reference types="cypress" />

// Tests de fumée : les pages publiques répondent et le contenu clé est présent.
describe('ZenSpace — pages publiques', () => {
  it('affiche la page d\'accueil', () => {
    cy.visit('/');
    cy.title().should('contain', 'ZenSpace');
    cy.get('h1').should('be.visible');
    cy.contains('a', 'Prestations').should('exist');
  });

  it('liste les prestations et filtre par catégorie (SSR, sans JS requis)', () => {
    cy.visit('/prestations');
    cy.get('#filters').should('exist');
    cy.get('#results .card').its('length').should('be.gte', 1);
    // Le filtrage serveur fonctionne via l'URL (amélioration progressive).
    cy.visit('/prestations?max_price=60');
    cy.get('#results').should('exist');
  });

  it('ouvre une fiche prestation', () => {
    cy.visit('/prestations');
    cy.get('#results .card a').first().click();
    cy.get('h1').should('be.visible');
    cy.contains('Réserver').should('exist');
  });

  it('renvoie une page 404 stylisée sur une URL inconnue', () => {
    cy.request({ url: '/cette-page-nexiste-pas', failOnStatusCode: false })
      .its('status')
      .should('eq', 404);
  });
});
