/// <reference types="cypress" />

describe("Index", function () {
	it("should load the homepage and its scripts", function () {
		cy.visit("/")
		cy.document().then((document) => {
			const scripts = [...document.scripts]
				.map((script) => script.src)
				.filter(
					(src) => src && new URL(src).origin === document.location.origin
				)
			for (const src of scripts) {
				cy.request(src)
					.its("headers.content-type")
					.should("match", /^(?:application|text)\/javascript(?:;|$)/u)
			}
			return scripts
		})
		cy.get('[data-cy="indexNewsletter"]').should("exist")
	})
})
