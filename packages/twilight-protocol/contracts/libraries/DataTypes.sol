// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.18;

library DataTypes {
	struct CategoryMetadata {
		uint256 id;
		string name;
		string description;
	}

	struct EntryMetadata {
		uint256 tokenId;
		uint256 categoryId;
		uint256 createdAt;
		string tokenUri;
	}
}
