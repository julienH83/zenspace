/// <reference types="cypress" />

// Parcours d'authentification (compte de démonstration du seed).
describe('ZenSpace — connexion', () => {
  it('connecte un client et accède à son espace', () => {
    cy.visit('/connexion');
    cy.get('input[name="email"]').type('client@zenspace.fr');
    cy.get('input[name="password"]').type('Client1234!');
    cy.get('form').submit();
    // Redirigé vers l'espace client.
    cy.location('pathname').should('eq', '/mon-compte');
    cy.contains('réservation', { matchCase: false }).should('exist');
  });

  it('refuse des identifiants invalides sans révéler l\'existence du compte', () => {
    cy.visit('/connexion');
    cy.get('input[name="email"]').type('inconnu@example.com');
    cy.get('input[name="password"]').type('MauvaisMotDePasse1!');
    cy.get('form').submit();
    cy.contains('Identifiants incorrects').should('exist');
  });
});
