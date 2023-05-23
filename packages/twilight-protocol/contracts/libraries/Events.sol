// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.18;

library Events {
	// CREATE
	event CategoryCreated(uint256 id);
	event EntryMinted(uint256 tokenId);

	// UPDATE
	event BlogUriUpdated(string uri);
	event CategoryUpdated(uint256 id);

	// READ
	// DELETE
	event CategoryDeleted(uint256 id);
}
