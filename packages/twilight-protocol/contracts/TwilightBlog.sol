// SPDX-License-Identifier: GPL-3.0-or-later
pragma solidity 0.8.18;

import "@openzeppelin/contracts/utils/structs/EnumerableSet.sol";
import "@openzeppelin/contracts-upgradeable/proxy/utils/Initializable.sol";
import "@openzeppelin/contracts-upgradeable/access/OwnableUpgradeable.sol";

import { ITwilightBlog } from "./interfaces/ITwilightBlog.sol";
import { Events } from "./libraries/Events.sol";
import { DataTypes } from "./libraries/DataTypes.sol";
import { Constants } from "./libraries/Constants.sol";
import { CategoryAlreadyExists, CategoryDoesNotExist } from "./libraries/Errors.sol";

// Keep track of blog info, and blog entry categories
contract TwilightBlog is ITwilightBlog, Initializable, OwnableUpgradeable {
	using EnumerableSet for EnumerableSet.UintSet;

	uint256 internal constant _VERSION = 1;
	string public blogUri;

	EnumerableSet.UintSet private _entryIds;
	mapping(uint256 => DataTypes.EntryMetadata) private _entryIdToMetadata;

	EnumerableSet.UintSet private _catgeoryIds;
	mapping(uint256 => DataTypes.CategoryMetadata) private _categoryIdToMetadata;
	mapping(uint256 => EnumerableSet.UintSet) private _categoryIdToEntryIds;

	/* Modifiers */
	modifier categoryExists(uint256 categoryId) {
		if (!_catgeoryIds.contains(categoryId)) revert CategoryDoesNotExist();
		_;
	}

	modifier categoryDoesNotExist(uint256 categoryId) {
		if (_catgeoryIds.contains(categoryId)) revert CategoryAlreadyExists();
		_;
	}

	/* External functions */
	// CREATE
	function createCategory(
		uint256 id_,
		string memory name_,
		string memory description_
	) external override onlyOwner categoryDoesNotExist(id_) {
		_catgeoryIds.add(id_);
		_categoryIdToMetadata[id_] = DataTypes.CategoryMetadata({
			id: id_,
			name: name_,
			description: description_
		});

		emit Events.CategoryCreated(id_);
	}

	function addEntryToCategory(
		uint256 categoryId,
		uint256 entryId
	) external override onlyOwner categoryExists(categoryId) {
		_categoryIdToEntryIds[categoryId].add(entryId);
	}

	// UPDATE
	function updateBlogUri(string memory uri_) external override onlyOwner {
		blogUri = uri_;
	}

	function updateCategoryName(
		uint256 categoryId,
		string memory name_
	) external override onlyOwner categoryExists(categoryId) {
		_categoryIdToMetadata[categoryId].name = name_;

		emit Events.CategoryUpdated(categoryId);
	}

	// DELETE
	function deleteCategory(
		uint256 categoryId
	) external override onlyOwner categoryExists(categoryId) {
		_catgeoryIds.remove(categoryId);
		delete _categoryIdToMetadata[categoryId];
		delete _categoryIdToEntryIds[categoryId];

		emit Events.CategoryDeleted(categoryId);
	}

	// READ
	function author() external view override returns (address) {
		return owner();
	}

	function categories() external view override returns (DataTypes.CategoryMetadata[] memory) {
		uint256[] memory categoryIds = getCategoryIds();
		uint256 categoryCount = categoryIds.length;

		DataTypes.CategoryMetadata[] memory categoryDetails = new DataTypes.CategoryMetadata[](
			categoryCount
		);
		for (uint256 i = 0; i < categoryCount; i++) {
			categoryDetails[i] = getCategoryDetail(categoryIds[i]);
		}

		return categoryDetails;
	}

	function entries(uint256 categoryId) external view override returns (uint256[] memory) {
		return getCategoryEntryIds(categoryId);
	}

	/* Public functions */
	function initialize(string memory uri_) public override initializer {
		__Ownable_init();

		blogUri = uri_;
		_catgeoryIds.add(Constants.DEFAULT_CATEGORY_ID);
		_categoryIdToMetadata[Constants.DEFAULT_CATEGORY_ID] = DataTypes.CategoryMetadata({
			id: Constants.DEFAULT_CATEGORY_ID,
			name: Constants.DEFAULT_CATEGORY_NAME,
			description: Constants.DEFAULT_CATEGORY_DESCRIPTION
		});
	}

	function getCategoryIds() public view override returns (uint256[] memory) {
		return _catgeoryIds.values();
	}

	function getCategoryDetail(
		uint256 categoryId
	) public view override categoryExists(categoryId) returns (DataTypes.CategoryMetadata memory) {
		return _categoryIdToMetadata[categoryId];
	}

	function getCategoryEntryIds(
		uint256 categoryId
	) public view override categoryExists(categoryId) returns (uint256[] memory) {
		return _categoryIdToEntryIds[categoryId].values();
	}
}
