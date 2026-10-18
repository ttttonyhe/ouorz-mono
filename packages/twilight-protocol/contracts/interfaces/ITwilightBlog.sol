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

	/// @notice Files an entry identifier under an existing category.
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

	/// @notice Removes up to 256 entries, then deletes the category once its list is empty.
	/// @param categoryId Category to remove.
	/// @return True when deleted; otherwise call again to continue cleanup.
	function deleteCategory(uint256 categoryId) external returns (bool);

	/// @notice The address allowed to write to this blog.
	/// @return The author's address.
	function author() external view returns (address);

	/// @notice A page of categories with their metadata resolved.
	/// @param start Inclusive offset in the current category set.
	/// @param end Exclusive offset, at most 256 positions after start.
	/// @return Metadata for each category, in unspecified order.
	function categories(
		uint256 start,
		uint256 end
	) external view returns (DataTypes.CategoryMetadata[] memory);

	/// @notice The entries filed under a category.
	/// @param categoryId Category to read.
	/// @param start Inclusive offset in the current entry set.
	/// @param end Exclusive offset, at most 256 positions after start.
	/// @return Entry identifiers filed under the category.
	function entries(
		uint256 categoryId,
		uint256 start,
		uint256 end
	) external view returns (uint256[] memory);

	/// @notice A page of category identifiers without resolving their metadata.
	/// @param start Inclusive offset in the current category set.
	/// @param end Exclusive offset, at most 256 positions after start.
	/// @return Category identifiers, in unspecified order.
	function getCategoryIds(uint256 start, uint256 end) external view returns (uint256[] memory);

	/// @notice Metadata for a single category.
	/// @param categoryId Category to read.
	/// @return Metadata of the category.
	function getCategoryDetail(
		uint256 categoryId
	) external view returns (DataTypes.CategoryMetadata memory);

	/// @notice Entry identifiers filed under a category.
	/// @param categoryId Category to read.
	/// @param start Inclusive offset in the current entry set.
	/// @param end Exclusive offset, at most 256 positions after start.
	/// @return Entry identifiers filed under the category.
	function getCategoryEntryIds(
		uint256 categoryId,
		uint256 start,
		uint256 end
	) external view returns (uint256[] memory);
}
