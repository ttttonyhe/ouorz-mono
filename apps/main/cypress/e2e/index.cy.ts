/// <reference types="cypress" />

describe("Index", function () {
	it("should display some posts", function () {
		cy.visit("/")
		// cy.get('[data-cy="showIndexPosts"]').click()
		// cy.get('[data-cy="indexPosts"]').children().should("not.be.empty")
		cy.get('[data-cy="indexNewsletter"]').should("exist")
	})
})
