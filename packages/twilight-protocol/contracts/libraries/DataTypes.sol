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
}
