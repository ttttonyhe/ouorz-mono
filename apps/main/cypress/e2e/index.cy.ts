/// <reference types="cypress" />

describe("Index", function () {
	it("should load the homepage and its scripts", function () {
		cy.visit("/")
		cy.document()
			.then((document) =>
				[...document.scripts]
					.map((script) => script.src)
					.filter(
						(src) => src && new URL(src).origin === document.location.origin
					)
			)
			.each((src: string) => {
				cy.request(src)
					.its("headers.content-type")
					.should("match", /^(?:application|text)\/javascript(?:;|$)/u)
			})
		cy.get('[data-cy="indexNewsletter"]').should("exist")
	})
})
