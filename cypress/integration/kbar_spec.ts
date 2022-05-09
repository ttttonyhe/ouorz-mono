/// <reference types="cypress" />
/// <reference types="@testing-library/cypress" />

describe('Kbar', function () {
	this.beforeEach(() => {
		cy.viewport('macbook-13')
		cy.visit('/')
	})

	context('enter key combination cmd + k', () => {
		this.beforeEach(() => {
			cy.get('body').type('{ctrl}k', { release: false })
		})

		it('should display kbar background and panel', function () {
			// kbar background and panel
			cy.get('[data-cy="kbar-bg"]').should('exist')
			cy.get('[data-cy="kbar-panel"]').should('exist')
		})

		context('search kbar list items', () => {
			it('should display only list items match input value', function () {
				// search kbar list items
				cy.get('[data-cy="kbar-input"]').type('search blog posts')
				cy.get('[data-cy="tabs-list"]')
					.last()
					.children()
					.should('have.length', 1)
			})
		})

		context('use kbar to search articles', () => {
			it('should list all articles', function () {
				// use kbar to search articles
				cy.get('[data-cy="kbar-input"]').type('search blog posts')
				// start intercepting the request to get search results
				cy.intercept('GET', '**/searchIndexes', {
					fixture: 'searchIndex.json',
				}).as('requestSearchIndex')
				cy.get('[data-cy="tabs-list"]').last().click()
				// wait for request to be finished
				cy.wait('@requestSearchIndex')
				// empty input value
				cy.get('[data-cy="kbar-input"]').clear()
				// display search results
				cy.get('[data-cy="tabs-list"]')
					.last()
					.children()
					.first()
					.contains('Title 1')
			})
		})
	})

	context('click on cmd + k button', () => {
		this.beforeEach(() => {
			cy.get('[data-cy="cmdkbutton"]').click({ force: true })
		})

		it('should display kbar background and panel', function () {
			// kbar background and panel
			cy.get('[data-cy="kbar-bg"]').should('exist')
			cy.get('[data-cy="kbar-panel"]').should('exist')
		})
	})
})
