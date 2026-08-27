// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.30;

import { DataTypes } from "../libraries/DataTypes.sol";

/// @title ITwilightBlog
/// @author Tony (Lipeng) He <lipeng.he@uwaterloo.ca>
/// @notice Blog metadata and the categories its entries are filed under.
/// @dev Every writing function is restricted to the blog's author.
interface ITwilightBlog {
	/// @notice Initializes the proxy with its author and metadata URI.
	/// @param uri_ Location of the blog's metadata.
	function initialize(string memory uri_) external;

	/// @notice Adds a category entries can be filed under.
	/// @param id_ Identifier to assign to the category; must not already exist.
	/// @param name_ Display name of the category.
	/// @param description_ Longer description of what the category collects.
	function createCategory(
		uint256 id_,
		string calldata name_,
		string calldata description_
	) external;

	/// @notice Files an existing entry under an existing category.
	/// @param categoryId Category to file the entry under.
	/// @param entryId Entry to file.
	function addEntryToCategory(uint256 categoryId, uint256 entryId) external;

	/// @notice Points the blog at a new metadata URI.
	/// @param uri_ Location the blog should resolve to.
	function updateBlogUri(string calldata uri_) external;

	/// @notice Renames an existing category.
	/// @param categoryId Category to rename.
	/// @param name_ New display name.
	function updateCategoryName(uint256 categoryId, string calldata name_) external;

	/// @notice Removes a category and the entry list attached to it.
	/// @param categoryId Category to remove.
	function deleteCategory(uint256 categoryId) external;

	/// @notice The address allowed to write to this blog.
	/// @return The author's address.
	function author() external view returns (address);

	/// @notice Every category with its metadata resolved.
	/// @return Metadata for each category, in insertion order.
	function categories() external view returns (DataTypes.CategoryMetadata[] memory);

	/// @notice The entries filed under a category.
	/// @param categoryId Category to read.
	/// @return Entry identifiers filed under the category.
	function entries(uint256 categoryId) external view returns (uint256[] memory);

	/// @notice Identifiers of every category, without resolving their metadata.
	/// @return Category identifiers, in insertion order.
	function getCategoryIds() external view returns (uint256[] memory);

	/// @notice Metadata for a single category.
	/// @param categoryId Category to read.
	/// @return Metadata of the category.
	function getCategoryDetail(
		uint256 categoryId
	) external view returns (DataTypes.CategoryMetadata memory);

	/// @notice Entry identifiers filed under a category.
	/// @param categoryId Category to read.
	/// @return Entry identifiers filed under the category.
	function getCategoryEntryIds(uint256 categoryId) external view returns (uint256[] memory);
}
