// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.30;

/// @title DataTypes
/// @author Tony (Lipeng) He <lipeng.he@uwaterloo.ca>
/// @notice Shapes the blog stores on chain and returns to callers.
library DataTypes {
	/// @notice A category an entry can belong to.
	/// @param id Identifier the author assigned to the category.
	/// @param name Display name shown alongside the entries.
	/// @param description Longer description of what the category collects.
	struct CategoryMetadata {
		uint256 id;
		string name;
		string description;
	}

	/// @notice A blog entry minted as an NFT.
	/// @param tokenId Token identifier of the entry.
	/// @param categoryId Category the entry was filed under.
	/// @param createdAt Block timestamp at which the entry was minted.
	/// @param tokenUri Location of the entry's content and metadata.
	struct EntryMetadata {
		uint256 tokenId;
		uint256 categoryId;
		uint256 createdAt;
		string tokenUri;
	}
}
