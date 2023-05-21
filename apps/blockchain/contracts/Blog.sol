// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity ^0.8.18;

import "@openzeppelin/contracts/utils/structs/EnumerableSet.sol";

import { IBlog } from "./interfaces/IBlog.sol";
import { DataTypes } from "./libraries/DataTypes.sol";

// Keep track of blog info, and blog entry categories
contract Blog is IBlog {
	using EnumerableSet for EnumerableSet.UintSet;

	// Constants
	uint256 public constant DEFAULT_CATEGORY_ID = 1;

	// Blog author
	address public author;

	// Blog info
	// string public blogUri;
	string public title;
	string public description;

	// Blog storage
	EnumerableSet.UintSet private _catgeoryIds;
	mapping(uint256 => DataTypes.CategoryDetail) private _categoryIdToDetail;
	mapping(uint256 => EnumerableSet.UintSet) private _categoryIdToEntryIds;

	constructor(string memory title_, string memory description_) {
		author = msg.sender;

		// Set blog info
		title = title_;
		description = description_;

		// Create default category
		_catgeoryIds.add(DEFAULT_CATEGORY_ID);
		_categoryIdToDetail[DEFAULT_CATEGORY_ID] = DataTypes.CategoryDetail({
			name: "Default",
			description: "Default category"
		});
	}

	// External functions
	function updateBlogTitle(string memory title_) external override {
		require(msg.sender == author, "Blog: Only author can update blog title");
		title = title_;
	}

	function updateBlogDescription(string memory description_) external override {
		require(msg.sender == author, "Blog: Only author can update blog description");
		description = description_;
	}

	function createCategory(
		uint256 id_,
		string memory name_,
		string memory description_
	) external override {
		require(msg.sender == author, "Blog: Only author can create category");
		require(!_catgeoryIds.contains(id_), "Blog: Category already exists");

		_catgeoryIds.add(id_);
		_categoryIdToDetail[id_] = DataTypes.CategoryDetail({ name: name_, description: description_ });
	}

	function addEntryToCategory(uint256 categoryId, uint256 entryId) external override {
		require(msg.sender == author, "Blog: Only author can add entry to category");
		require(_catgeoryIds.contains(categoryId), "Blog: Category does not exist");

		_categoryIdToEntryIds[categoryId].add(entryId);
	}

	function listCategories() external view override returns (DataTypes.CategoryDetail[] memory) {
		uint256[] memory categoryIds = getCategoryIds();
		uint256 categoryCount = categoryIds.length;

		DataTypes.CategoryDetail[] memory categories = new DataTypes.CategoryDetail[](categoryCount);
		for (uint256 i = 0; i < categoryCount; i++) {
			categories[i] = getCategoryDetail(categoryIds[i]);
		}

		return categories;
	}

	// Public functions
	function getCategoryIds() public view override returns (uint256[] memory) {
		return _catgeoryIds.values();
	}

	function getCategoryDetail(
		uint256 categoryId
	) public view override returns (DataTypes.CategoryDetail memory) {
		return _categoryIdToDetail[categoryId];
	}

	function getCategoryEntryIds(uint256 categoryId) public view override returns (uint256[] memory) {
		return _categoryIdToEntryIds[categoryId].values();
	}
}
